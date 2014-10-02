<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*cart/catalog.inc.php');
	$conf=infra_config();
	@define('CATDIR',$conf['cart']['dir']);
	
	$type=infra_strtolower(@$_GET['type']);
	$val=strip_tags(@$_GET['val']);
	$art=strip_tags(@$_GET['art']);

	
	
	$args=array($type,$val,$art);

	if($type=='stat'){
		if(isset($_GET['submit'])&&$_GET['submit']){
			$submit=true;
		}else{
			$submit=false;
		}
		$ans=array('result'=>1);
		$dir='infra/data/';
		$data=infra_loadJSON($dir.'catalog_stat.json');
		if($submit){
			if(!$val)return infra_echo($ans);
			infra_cache_no();
			$val=infra_State_forFS($val);
			if(!$data){
				$data=array('users'=>array(),'cat_id'=>0,'time'=>time());
			}
			
			$id=infra_view_getCookie('cat_id');
			$time=infra_view_getCookie('cat_time');
			if(!$time||!$id||$time!=$data['time']){
				$id=++$data['cat_id'];
				infra_view_setCookie('cat_id',$id);
				infra_view_setCookie('cat_time',$data['time']);
			}
			$ans['cat_id']=$id;
			$ans['cat_time']=$time;

			$user=array('cat_id'=>$id,'list'=>array(),'time'=>time());
			foreach($data['users'] as $k=>$v){
				if($v['cat_id']==$id){
					$user=$v;
					unset($data['users'][$k]);
					break;
				}
			}
			$data['users']=array_values($data['users']);

			foreach($user['list'] as $k=>$v){
				if($v['val']==$val){
					unset($user['list'][$k]);
					break;
				}
			}
			$user['list']=array_values($user['list']);
			$search=infra_loadJSON('*cart/catalog.php?type=search&val='.$val);
			$count=sizeof($search['list']);
			array_unshift($user['list'],array('val'=>$val,'time'=>time(),'count'=>$count));

			if(sizeof($user['list'])>10){
				$user['list']=array_slice($user['list'],0,10);
			}
			array_unshift($data['users'],$user);

			if(sizeof($data['users'])>100){
				$data['users']=array_slice($data['users'],0,50);
			}
			file_put_contents(ROOT.$dir.'catalog_stat.json',infra_json_encode($data));
			$ans['data']=$data;
			return infra_echo($ans);
		}else{
			
			infra_admin_modified();
			if(!$data){
				$data=array('users'=>array(),'cat_id'=>0,'time'=>time());//100 10 user list array('val'=>$val,'time'=>time())
			}
			$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Статистика поиска по каталогу&show');;
			//time
			//Поиск, Поиск, Поиск
			$ans['stat']=$data;
			return infra_echo($ans);
		}
	}
	infra_admin_modified();

	$ans=infra_cache(array($conf['cart']['dir'],$conf['cart']['1c'],$conf['cart']['prod']),'catalog',function($type,$val,$art){
		$conf=infra_config();
		$data=cat_init();
		$ans=array(
			'val'=>$val,
			'type'=>$type,
			'art'=>$art
		);
		$val=infra_strtolower($val);
		$art=infra_strtolower($art);
		if($type=='rubrics'){
			$data=$data['childs'];
			foreach($data as &$gr){
				unset($gr['childs']);
				unset($gr['data']);
			}
			
			$ans['childs']=$data;
		
		}else if($type=='pos'){
			$ans['pos']=false;
			$pos=&xls_runPoss($data,function(&$pos) use(&$val,&$art){
				if(infra_strtolower($pos['Производитель'])!==$val)return;
				if(infra_strtolower($pos['article'])!==$art)return;
				return $pos;
			});

			
			if($pos){
				$name=infra_strtolower($pos['Производитель']);
				$prods=xls_init($conf['cart']['prod']);
				$pos['Подпись']=@$prods['descr']['Подпись'];
				$prod=&xls_runPoss($prods,function(&$prod) use($name){
					if(infra_strtolower($prod['Производитель'])==$name)return $prod;
				});

				if($prod)$pos['producer']=$prod;
				else $pos['producer']=array('Производитель'=>$pos['Производитель']);

				$ans['result']=1;
				$ans['pos']=&$pos;

				

				//$pos['images']=infra_load('*pages/list.php?src='.$conf['cart']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
				//$pos['text']=infra_load('*pages/get.php?src='.$conf['cart']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
			}
		}else if($type=='seo'){
			infra_require('*seo/seo.inc.php');
			//нужно найти все странинцы по данным Поиск для каталога это все существующие Производители, Группы
			//items:[{data:'Имя производителя',title:'',keywords:'',description:''}] в таком виде
			//кэш для безопасности и вообще мало ли кто ещё будет тыкать файл надо что не висело всё.

			//ВЗРПФ
				/*
					Title – 50-80 знаков (обычно – 75);
					Keywords - до 250 (250 – максимум, ориентируйтесь на ударные первые 150 знаков);
					Description – около 150-200.
				*/

			//==========
			if($val=='stat'){
				$page=infra_loadTEXT('*files/files.php?type=texts&id=Статистика поиска по каталогу&show');;
				$v=array('data'=>true);
				seo_pageResearch($page,$v);
				$ans['items']=array($v);

			}else if($val=='producers'){
				$page=infra_loadTEXT('*files/files.php?type=texts&id=Производители&show');;
				$v=array('data'=>true);
				seo_pageResearch($page,$v);
				$ans['items']=array($v);
			}else if($val=='search'){
				$list=infra_loadJSON('*pages/list.php?src='.CATDIR.'&f=0&d=1&onlyname=1&obj=1');//Каждая папка это производитель
				$list=$list['obj'];

				foreach($list as $name=>&$vvv){
					$vvv=seo_createItem($list,$name);//Создали из папок
					$pages=infra_loadJSON('*pages/list.php?src='.CATDIR.$name.'/'.'&f=1&d=0&onlyname=1&e=docx,mht,tpl');//Каждая папка это производитель
					if(isset($pages[0])){
						$page=infra_loadTEXT('*pages/get.php?'.CATDIR.$name.'/'.$pages[0]);
						seo_pageResearch($page,$vvv);
					}				
				}
				//==========
				$prods=xls_init($conf['cart']['prod'],array('Ссылка parent'=>true));//@$prods['descr']['Подпись'];
				$prod=&xls_runPoss($prods,function(&$prod) use(&$list){//Из Excel всё взяли Производители.xls
					if(!isset($prod['Производитель']))return;
					$p=$prod['Производитель'];
					$vv=&seo_createItem($list,$p);//Создали из производителей указанных в Производители.xls
					$list[$p]['title']=$p.' '.$prod['parent']['descr']['Тайтл'];
					if(isset($prod['Описание группы'])){
						$list[$p]['description']=$prod['Описание группы'];
						$lim=250;
						if(mb_strlen($prod['Описание группы'])>$lim){
							$list[$p]['description']=preg_replace("/\..*/",".",$prod['Описание группы']);
						}
					}
				});

				//==========
				xls_runPoss($data,function(&$pos) use(&$list){		
					if(!isset($pos['Производитель']))return;
					seo_createItem($list,$pos['Производитель']);//Создали из производителей указанных в Excel
				});

				//==========
				xls_runGroups($data,function(&$group) use(&$list){
					$title=$group['title'];
					$v=&seo_createItem($list,$title);//Создали из группы указанных у Excel
					//$page=infra_loadTEXT('*pages/get.php?'.CATDIR.$title);
					//cat_seo_pageResearch($page,$v);

				});

				//==========
				$l=infra_loadJSON('*pages/list.php?src='.CATDIR.'&f=1&d=0&onlyname=1&e=mht,docx,tpl');//Каждый файл это страница
				foreach($l as $name){
					$fdata=infra_nameinfo($name);
					$v=&seo_createItem($list,$fdata['name']);//Создали из mht docx файлов
					$page=infra_loadTEXT('*pages/get.php?'.CATDIR.$name);
					seo_pageResearch($page,$v);	

				}
				//\\============	
						
				
				$ans['items']=array_values($list);
			}else if($val=='pos'){
				$items=array();
				xls_runPoss($data,function($pos) use(&$items){
					$conf=&infra_config();
					
					$files=explode(',',@$pos['Файлы']);
					foreach($files as $f){
						if(!$f)continue;
						$f=trim($f);
						xls_preparePosFiles($pos,$conf['cart']['dir'].$f, array('Производитель','article'));
					}
					xls_preparePosFiles($pos,$conf['cart']['dir'], array('Производитель','article') );
					$v=&seo_createItem($items,array('producer'=>$pos['Производитель'],'article'=>$pos['article']),$pos['Производитель'].' '.$pos['Артикул']);

					$v2=infra_cache($pos['texts'],'cat_seo_pos',function($v,$texts){
						infra_require('*files/files.inc.php');
						foreach($texts as $k=>$t){
							$texts[$k]=files_article($t);
						}
						$page=implode(' ',$texts);
						seo_pageResearch($page,$v);
						return $v;
					},array($v,$pos['texts']));

					foreach($v2 as $kkk=>$vvv)$v[$kkk]=$vvv;


				});

				$ans['items']=$items;
			}
		
		}
		if($type=='producers'){
			$prods=array();

			xls_runPoss($data,function(&$pos) use(&$prods){
				@$prods[$pos['Производитель']]++;
			});
			
			$ans['producers']=$prods;
		}
		return $ans;
	},$args,isset($_GET['re']));





	$ans=infra_admin_cache('cat admin',function(&$ans){
		$type=infra_strtolower($ans['type']);
		$art=infra_strtolower($ans['art']);
		$val=infra_strtolower($ans['val']);

		if($type=='producers'){
			$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Производители&show');;
		}
		if($type=='rubrics'){
			$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Каталог&show');;
		}
		if($type=='pos'){
			$ans['phone']=infra_loadJSON('*Телефон.json');
			$conf=infra_config();
			if($ans['pos']){
				$pos=$ans['pos'];
				$files=explode(',',@$pos['Файлы']);

				foreach($files as $f){
					if(!$f)continue;
					$f=trim($f);
					xls_preparePosFiles($pos,$conf['cart']['dir'].$f, array('Производитель','article'));
				}

				xls_preparePosFiles($pos,$conf['cart']['dir'], array('Производитель','article') );

				$files=array();

				foreach($pos['files'] as $f){
					if(is_string($f)){
						$d=infra_srcinfo($f);
					}else{
						$d=$f;
						$f=$d['src'];
					}
					$d['size']=round(filesize(ROOT.infra_tofs($f))/1000000,2);
					if(!$d['size'])$d['size']='0.01';
					
					$files[]=$d;
				} 
				
				$pos['files']=$files;
				if($pos['texts']){
					infra_require('*files/files.inc.php');
					foreach($pos['texts'] as $k=>$t){
						$pos['texts'][$k]=files_article($t);
					}
				}
				$ans['pos']=$pos;
			}
		}	
		return $ans;
	},array(&$ans));

	

	return infra_echo($ans);
	

?>
