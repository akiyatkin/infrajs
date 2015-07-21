<?php
	

	infra_require('*seo/seo.inc.php');
	$type=infra_toutf(@$_REQUEST['type']);
	$id=infra_toutf(@$_REQUEST['id']);
	$submit=(bool)@$_REQUEST['submit'];
	$layers=infra_toutf(urldecode(@$_REQUEST['layers']));

	@set_time_limit(300);//может быть отключена на хостинге
	$ans=array('result'=>0,'id'=>$id,'type'=>$type);
	
	if($type=='item'){
		$ans=infra_admin_cache('seoitemopt',function($id){
			$ans['result']=1;
			$p=explode('|',$id);
			$name=array_shift($p);
			$link=implode('|',$p);

			$seo=infrajs_seo_getSeo($name);
			
			if(isset($seo['all'][$link])){
				$item=$seo['all'][$link];
			}else{
				$item=array();
			}
			$ans['item']=$item;
			return $ans;
		},array($id));
		return infra_echo($ans);
	}else if($type=='sitemap'){
		$html=infra_admin_cache('sitemap',function(){
			$data=array();
			$all=array();
			$list=infrajs_seo_list();
			foreach($list as $name){
				$seo=infrajs_seo_getSeo($name);
				$all=array_merge($all,$seo['all']);
			}
			$data['list']=array();
			foreach($all as $k=>$v){
				if(!$k) $data['list'][]=array('link'=>$k);
				else $data['list'][]=array('link'=>$k,'q'=>'?');
			}

			$data['host']=$_SERVER['HTTP_HOST'];
			$data['root']=infra_view_getRoot();
			$data['date']=time();
			$html=infra_template_parse('*seo/sitemap.tpl',$data,'sitemap');
			return $html;
		});
		header('content-type: application/xhtml+xml');
		echo $html;
		exit;
	}
	infra_admin(true);
	if($submit)infra_cache_no();
	
	
	if($type=='seo'){
		$list=infrajs_seo_list();
		$ans['result']=1;
		$ans['list']=$list;
	}else if($type=='allitems'){
		$name=$id;
		$seo=infrajs_seo_getSeo($name);
		//$seo['user']=$seo['defitem']['user'];
		$seo['items']=$seo['defitems'];
		$ans['seo']=$seo;
		/*$seo=seo_all($name);
		$seo['items']=array();
		if(isset($seo['itemslist'])){
			
			$r=infra_loadJSON($seo['itemslist']);
			$seo['items']=$r['items'];
			foreach($seo['items'] as &$v){
				$v['link']=infra_template_parse(array($seo['link']),$v['data']);
			}
		}

		$ans['seo']=$seo;*/
	}else if($type=='editname'){
		$name=$id;
		$data=infrajs_seo_getSeo($name);

		$names=infrajs_seo_list();
		
		$ans['names']=$names;
		$ans['data']=$data;

	}else if($type=='edititem'){	
		$p=explode('|',$id);
		$name=array_shift($p);
		$link=implode('|',$p);
		$ans['name']=$name;//то что ищим
		$ans['link']=$link;//то что ищим

		$seo=infrajs_seo_getSeoItem($name,$link);
			
		if($seo['item']){//учитывает деф и созданные.
			$da=$seo['item']['data'];
		}else if($seo['defitem']){
			$da=$seo['defitem']['data'];
		}else{
			$da=false;
		}
		if(!$submit){
			//Распарсить json. Нет itemslist, Нет item не найден
			//item обязательно должен быть либо в дефолтных, либо в созданных, либо в itemslist
			if(isset($seo['tpl'])){
				$tpl=infra_template_parse(array($seo['tpl']),$da);
				$ans['tpl']=$tpl;


				if(isset($seo['json'])){
					$json=infra_template_parse(array($seo['json']),$da);
					$d=infra_loadJSON($json);
					$ans['text']=infra_template_parse($tpl,$d);
				}else{
					$ans['text']=infra_loadTEXT($tpl);
				}
				$ans['text']=strip_tags($ans['text'],'<p><a><ul><li><table><tr><td><b><strong><h1><h2><h3>');
			}
			$ans['result']=1;
			$ans['seo']=array(
				'name'=>$seo['name'],
				'item'=>@$seo['item'],
				'defitem'=>@$seo['defitem']
			);
		}else{
			if(!$da){
				$ans['msg']='Для редактирвоания нужно создать страницу';
			}else{
				$item=$_REQUEST['seo'];
				$item['data']=$da;
				$src=infrajs_seo_saveitem($name,$item);
				$ans['noclose']=1;
				$ans['msg']='Сохранено '.date('d.m.Y H:i',filemtime($src));
				$ans['result']=1;
			}
		}

	}else if($type=='delitem'){	

		$p=explode('|',$id);
		$name=array_shift($p);
		$link=implode('|',$p);

		$ans['name']=$name;//то что ищим
		$ans['link']=$link;//то что ищим
		if($submit){
			$ans['result']=seo_delitem($name,$link);
			if($ans['result']){
				$ans['msg']='Cтраница "'.$link.'" удалена!';
			}else{
				$ans['msg']='Cтраница не удалена '.$link;
			}
			
		}else if(!$submit){
			$seo=infrajs_seo_getSeo($name);
			
			
			foreach($seo['items'] as $item){
				if($item['link']==$link){
					$seo['item']=$item;
					break;
				}
			}
			$ans['seo']=array(
				'name'=>$seo['name'],
				'item'=>@$seo['item']
			);
			/*if($seo['item']){
				$def=seo_alldef($name);
				foreach($def['items'] as $item){
					if($item['link']==$link){
						$def['item']=$item;
						break;
					}
				}
				$ans['def']=array(
					'name'=>$def['name'],
					'item'=>@$def['item']
				);
			}*/
		}
	}else if($type=='additem'){
		if(!$submit){
			$seo=infrajs_seo_getSeo($id);
			unset($seo['items']);
			$ans['seo']=$seo;
		}else{
			$name=$id;
			$seo=infrajs_seo_getSeo($name);

			$itemdata=$_REQUEST['itemdata'];
			$data=infra_json_decode($itemdata);
			$link=infra_template_parse(array($seo['link']),$data);

			$item=array('data'=>$data);

			$r=false;
			foreach($seo['items'] as $k=>$i){
				if($i['link']==$link){
					$r=true;
					$ans['msg']='Cтраница "'.$link.'" уже есть в списке';
					break;
				}
			}

			if(!$r){
				$seo['items'][]=$item;
				$ans['msg']='Страница '.$link.' добавлена в список';
				infrajs_seo_saveitem($name,$item);
			}
			$id=$name.'|'.$link;
			$ans['js']="infrajs.SEO('edititem','".$id."')";
			$ans['result']=1;
		}
	}
	
	return infra_echo($ans);
?>