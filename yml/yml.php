<?php
	require_once(__DIR__.'/../infra/infra.php');
	infra_require('*files/xls.php');
	/*
	<!—- category id положительные цифры —->
	<!—- offer id положительные цифры и латинские символы —->
	<!—- price десятые должны отделяться точкой, не запятой —->
	<!—- store возможность купить в розничном магазине —->
	<!—- pickup возможность зарезирвировать и забрать самостоятельно —->
	<!—- delivery true — товар доставляется на условиях, которые описываются в партнерском интерфейсе в разделе Параметры размещения.-->
	<!—- company — полное наименование компании не показывается в каталоге -->
	<!—- name — наименование компании без организационной формы -->
	*/
	function yml_parse($data){
		$gid=0;
		$pid=0;
		$gorups=array();
		$poss=array();
		$conf=infra_config();
		if(!$conf['yml'])die('Требуется конфиг yml');
		if(!$conf['yml']['name'])die('В конфиге yml требуется указать name. Наименование компании без организационный формы');
		if(!$conf['yml']['company'])die('В конфиге yml требуется указать company, название компании с организационной формой ООО и тп');
		xls_runPoss($data,function(&$pos){
			$pos['Описание']=yml_tostr($pos['Описание']);
		});
		xls_runGroups($data,function(&$group,$i,&$parent) use(&$gid,&$groups){
			$group['id']=++$gid;
			if($parent){
				$group['parentId']=$parent['id'];
			}
			$groups[]=&$group;
		});

		xls_runPoss($data,function(&$pos,$i,&$group) use(&$pid,&$poss){
			$pos['id']=++$pid;
			$pos['categoryId']=$group['id'];
			$poss[]=&$pos;
		});


		$conf=infra_config();

		$d=array(
			"conf"=>$conf,
			"site"=>infra_view_getHost().'/'.infra_view_getRoot(),
			"poss"=>$poss,
			"groups"=>$groups
		);
		
		$html=infra_template_parse('*yml/yml.tpl',$d);
		return $html;
	}
	function yml_tostr($str){
		$str=preg_replace('/&/','&amp;',$str);
		$str=preg_replace('/</','&lt;',$str);
		$str=preg_replace('/>/','&gt;',$str);
		$str=preg_replace('/"/','&quot;',$str);
		$str=preg_replace("/'/",'&apos;',$str);
		return $str;
	}
	function yml_init(){
		infra_require('*cart/catalog.inc.php');
		$data=cat_init();

		xls_runGroups($data,function(&$group,$i,&$parent){
			$group['data']=array_filter($group['data'],function(&$pos){//Убираем позиции у которых не указана цена
				if($pos['Синхронизация']!='Да')return false;
				if(!$pos['Цена'])return false;
				return true;
			});
			$group['data']=array_values($group['data']);
		});

		xls_runGroups($data,function(&$group,$i,&$parent){			
			if($group['childs']){
				$group['childs']=array_filter($group['childs'],function(&$g){
					if(!$g['data']&&!$g['childs'])return false;
					return true;
				});
				$group['childs']=array_values($group['childs']);
			}
		},array(),true);
		
		xls_runPoss($data,function(&$pos){
			$conf=infra_config();
			xls_preparePosFiles($pos,$conf['cart']['dir'], array('Производитель','article') );
			foreach($pos['images'] as $k=>$v){
				$src=$pos['images'][$k];
				$p=explode('/',$src);
				foreach($p as $i=>$n){
					$p[$i]=urlencode($n);
					$p[$i]=preg_replace('/\+/','%20',$p[$i]);
				}
				$pos['images'][$k]=implode('/',$p);
			}
		});

		return yml_parse($data);
	}
	if(isset($_GET['show'])){
		$html=infra_cache(array($conf['cart']['dir']),'ymlshow',function(){
			return yml_init();
		},array(),isset($_GET['re']));
		header("Content-type: text/xml");
		echo $html;
		
	};
