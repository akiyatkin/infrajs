<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*files/xls.php');
	infra_require('*catalog/catalog.inc.php');
	$conf=infra_config();
	@define('CATDIR',$conf['catalog']['dir']);
	
	$type=infra_strtolower(@$_GET['type']);
	$val=strip_tags(@$_GET['val']);
	$art=strip_tags(@$_GET['art']);

	$re=isset($_GET['re']);
	$prod=(string)$_REQUEST['prod'];
	$prod=strip_tags($prod);
	$args=array($type,$val,$art,$prod);

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
			$search=infra_loadJSON('*catalog/catalog.php?type=search&val='.$val);
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
			
			
		}
	}
	infra_admin_modified();
	
	$ans=infra_cache(array($conf['catalog']['dir'],$conf['catalog']['prod']),'catalog',function($type,$val,$art,$prod){
		
		$conf=infra_config();
		$data=cat_init();
		$ans=array(
			'val'=>$val,
			'type'=>$type,
			'art'=>$art
		);
		$prod=infra_strtolower($prod);
		$val=infra_strtolower($val);
		$art=infra_strtolower($art);
		if($prod){
			if(!xls_runPoss($data,function(&$pos) use($prod){
				if($prod==infra_strtolower($pos['Производитель']))return true;
			}))$prod='';
		}
		
		$ans['prod']=$prod;
		if($type=='rubrics'){
			$data=$data['childs'];
			foreach($data as &$gr){
				$pos=&xls_runPoss($gr,function&(&$pos){
					return $pos;
				});
				if($pos){
					$gr['pos']=array('article'=>$pos['article'],'producer'=>$pos['Производитель']);
				}
				unset($gr['desrc']);
				unset($gr['childs']);
				unset($gr['data']);
			}
			$ans['childs']=$data;
		}else if($type=='pos'){
			$ans['pos']=false;
			$pos=&xls_runPoss($data,function(&$val,&$art,&$pos){
				if(infra_strtolower($pos['Производитель'])!==$val)return;
				if(infra_strtolower($pos['article'])!==$art)return;
				return $pos;
			},array(&$val,&$art));
			if($pos){
				$name=infra_strtolower($pos['Производитель']);
				$prods=xls_init2($conf['catalog']['prod']);
				$pos['Подпись']=@$prods['descr']['Подпись'];
				$prod=&xls_runPoss($prods,function($name, &$prod){
					if(infra_strtolower($prod['Производитель'])==$name)return $prod;
				},array($name));

				if($prod)$pos['producer']=$prod;
				else $pos['producer']=array('Производитель'=>$pos['Производитель']);

				$ans['result']=1;
				$ans['pos']=&$pos;

				
				//$pos['images']=infra_load('*pages/list.php?src='.$conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
				//$pos['text']=infra_load('*pages/get.php?src='.$conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
			}
		}else if($type=='bread'){
			
			$groups=array();
			$prods=array();

			if($val){
				$d=xls_runGroups($data,function(&$g) use($val){
					if(infra_strtolower($g['title'])==$val)return $g;
				});
				if(!$d)$d=&$data;
			}else{
				$d=&$data;
			}

			xls_runPoss($d,function(&$prods, &$pos){
				$prods[infra_strtolower($pos['Производитель'])]=$pos['Производитель'];
			},array(&$prods));
			

			$prodpage=isset($prods[$val]);
			if(!$prodpage){
				$conf=infra_config();
				if(infra_theme($conf['catalog']['dir'].$val.'/')){
					$prodpage=true;
				}
			}

			if($prod){
				$ans['sel']=$prods[$prod];//Правильное имя параметра sel - клик пользователя
				if(!$ans['sel'])$prod='';
			}
			if($prodpage){
				$ans['sel']=$prods[$val];//Правильное имя параметра sel - клик пользователя
			}

			$prods=array_values($prods);

			$ans['prodpage']=$prodpage;

			if($prodpage){
				$prod=$val;
				$prods=array();
			}
			
			if($prod){//Выбран производитель
				infra_forr($data['childs'],function(&$groups,$prod, &$g){//Оставляем тольк те группы в которхы есть этот производитель
						xls_runPoss($g,function(&$groups,$prod, &$pos) use($g){
							$p=mb_strtolower($pos['Производитель']);
							if($p==$prod){
								$title=$g['title'];
								$name=$g['descr']['Наименование'];
								if(!$name)$name=$title;
								if(!$title)return;
								$groups[]=array('name'=>$name,'title'=>$title);
								return false;
							}
						},array(&$groups,$prod));
				},array(&$groups,$prod));
			}

			$ans['prods']=$prods;
			
			if(!$prodpage){
				/*if($prod&&$val&&$val!=='каталог'){//Надо убедится что выбранная группа в принципе содержит хоть одного такого производителя	
					if(!infra_forr($groups,function($g) use($val){
						if(infra_strtolower($g['title'])==$val)return true;
					})){
						$prod='';
						$ans['prod']=$prod;
					}
				}
				echo '<pre>';
				var_dump($groups);
				exit;*/

				if(!$prod){
					$groups=array();
					infra_forr($data['childs'],function(&$groups,$prod, &$g){
						$name=$g['descr']['Наименование'];
						$title=$g['title'];
						if(!$name)$name=$title;						
						$groups[]=array('name'=>$name,'title'=>$title);
					},array(&$groups,$prod));

				
				}
				
				$search=infra_loadJSON('*catalog/catalog.php?type=search&val='.$val);
				if($search['is']=='search'){
					$poss=$search['list'];
					$groups=array();
					$prods=array();
					infra_forr($poss,function(&$prods,&$groups,&$pos){
						$title=$pos['group_title'];
						$name=$pos['group_name'];	
						$prods[$pos['Производитель']]=$pos['Производитель'];
						$groups[$name]=array('name'=>$name,'title'=>$title);
					},array(&$prods,&$groups));
					$groups=array_values($groups);
					$ans['prods']=array_keys($prods);
				}
					
			}
			
			$ans['groups']=$groups;
			
		}else if($type=='available'){
			$available=infra_loadJSON($conf['catalog']['available']);
			if(!$available)$available=array();

			$list=array();
			foreach($available['groups'] as &$group){
				if(!$group['list'])$group['list']=array();
				foreach($group['list'] as $k=>$id){
					list($prod,$art)=explode('/',$id);
					$art=trim($art);
					if(!$list[$prod])$list[$prod]=array();
					$group['list'][$k]=array('prod'=>$prod,'art'=>$art);
					$list[$prod][$art]=true;
				}
			}
			xls_runPoss($data,function($list, &$pos){
				if($list[$pos['Производитель']]&&$list[$pos['Производитель']][$pos['article']]){
					$list[$pos['Производитель']][$pos['article']]=&$pos;
				}
			},array(&$list));
			foreach($available['groups'] as &$group){
				foreach($group['list'] as $k=>$v){
					if(!is_array($list[$v['prod']][$v['art']])){
						unset($group['list'][$k]);
						continue;
					}
					$pos=$list[$v['prod']][$v['art']];
					$p=array(
						'Производитель'=>$pos['Производитель'],
						'Артикул'=>$pos['Артикул'],
						'article'=>$pos['article'],
						'Наименование'=>$pos['Наименование']
					);
					$group['list'][$k]=$p;

				}
				//$group['list']=array_values($group['list']);
			}
			$ans['available']=$available;
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
				$prods=xls_init2($conf['catalog']['prod'],array('Ссылка parent'=>true));//@$prods['descr']['Подпись'];
				$prod=&xls_runPoss($prods,function(&$list, &$prod){//Из Excel всё взяли Производители.xls
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
				},array(&$list));

				//==========
				xls_runPoss($data,function(&$list,&$pos){		
					if(!isset($pos['Производитель']))return;
					seo_createItem($list,$pos['Производитель']);//Создали из производителей указанных в Excel
				},array(&$list));

				//==========
				xls_runGroups($data,function(&$list,&$group){
					$title=$group['title'];
					$v=&seo_createItem($list,$title);//Создали из группы указанных у Excel
					//$page=infra_loadTEXT('*pages/get.php?'.CATDIR.$title);
					//cat_seo_pageResearch($page,$v);

				},array(&$list));

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
				xls_runPoss($data,function(&$items,$pos){
					$v=&seo_createItem($items,array('producer'=>$pos['Производитель'],'article'=>$pos['article']),$pos['Производитель'].' '.$pos['Артикул'].'. '.$pos['Наименование']);
					$pos=infra_loadJSON('*catalog/catalog.php?type=pos&val='.$pos['Производитель'].'&art='.$pos['article']);

					if(isset($pos['pos'])&&isset($pos['pos']['texts'])){
						$page=implode('',$pos['pos']['texts']);
					}
					seo_pageResearch($page,$v);
				},array(&$items));

				$ans['items']=$items;
			}
		}else if($type=='search'){
			$ans['list']=array();
			if($val=='изменения'){
				$ans['result']=1;
				$ans['title']='Последние изменения';
				$ans['descr']='Последнии позиций, у которых изменился текст полного описания.';
				
				//Смотрим дату изменения папки для каждой позиции кэшируем на изменение XLS файлов как всё здесь...
				//Позиции без папок игнорируются
				return $ans;
			}
			$group=&xls_runGroups($data,function(&$val,&$group){
				if(infra_strtolower($group['title'])==$val)return $group;
			},array(&$val));
			$posscount=0;
			if($group){
				$ans['is']='group';
				$ans['result']=1;
				$ans['title']=$group['name'];
				$ans['descr']=@$group['descr']['Описание группы'];
				$ans['list']=$group['data'];

				if($group['parent_title']){
					/*$parent=&xls_runGroups($data,function($val,&$group){
						if($group['title']==$val)return $group;
					},array($group['parent_title']));*/
					$ans['parent']=array('title'=>$group['parent_title']);
				}
				if($prod){
					if(!xls_runPoss($group,function(&$pos) use($prod){
						if($prod==infra_strtolower($pos['Производитель']))return true;
					})){
						$prod='';
						$ans['prod']=$prod;
					}
				}

				if($group['childs']){
					$ans['childs']=array();
					foreach($group['childs'] as &$v){
						if($prod){
							$r=false;
							xls_runPoss($v,function(&$pos) use($prod,&$r,&$posscount){
								if($prod==infra_strtolower($pos['Производитель'])){
									$posscount++;
									$r=true;
								}
							});
							if(!$r)continue;//не найдено неодной нужной позиции, группу не добавляем в список.

						}else{
							xls_runPoss($v,function(&$pos) use(&$posscount){
								$posscount++;
							});
						}
						$ans['childs'][]=array('title'=>$v['title']);
					}
				}
				//Есть левый prod и перешли в группу где нет этого прода. группа найдена но нет подгрупп и нет позиций
				//
				$ans['text']='*pages/get.php?'.CATDIR.$group['title'];
			}else{

				$dir=infra_theme(CATDIR.$val.'/');
				$poss=array();
				xls_runPoss($data,function(&$poss,&$val,&$pos){
					if(infra_strtolower(@$pos['Производитель'])==$val){
						$poss[]=&$pos;
					}
				},array(&$poss,&$val));

				if($dir||sizeof($poss)){
					$ans['is']='producer';
					$prods=xls_init2($conf['catalog']['prod']);

					$prod=&xls_runPoss($prods,function($val, &$prod){
						if(infra_strtolower($prod['Производитель'])==$val)return $prod;
					},array($val));

					
					if($prod){
						$name=$prod['Производитель'];
					}else if(sizeof($poss)){
						$name=$poss[0]['Производитель'];
					}else{
						$dir=infra_toutf($dir);
						$p=explode('/',$dir);
						$folder=$p[sizeof($p)-2];
						$name=$folder;
					}
					$ans['parent']=array('title'=>'Каталог');
					$ans['producer']=true;
					$ans['title']='Производитель '.$name;
					$ans['result']=1;
					$ans['descr']=@$prod['Описание группы'];
					$ans['list']=$poss;

					$list=infra_loadJSON('*pages/list.php?onlyname=1&e=mht,docx,tpl&src='.CATDIR.$name.'/');
					if(isset($list[0])){
						$ans['text']='*pages/get.php?'.CATDIR.$name.'/'.$list[0];
					}
				}else{//ищим позиции подходящие под запрос
					$ans['is']='search';
					$data=xls_init2(CATDIR,array('Имя файла'=>$conf['catalog']['Имя файла'],'Ссылка parent'=>true));
					cat_prepareData($data);
					$v=explode(' ',$val);
					foreach($v as &$s)$s=trim($s);
					xls_runPoss($data,function(&$v,&$poss,&$pos){
						$str=$pos['Артикул'];
						$gr=@$pos['parent'];
						while($gr){

							$str.=' '.$gr['title'];
							$gr=$gr['parent'];
						}
						
						$str.=' '.$pos['article'];
						//$str.=' '.$pos['group_title'];
						$str.=' '.$pos['Наименование'];
						$str.=' '.$pos['Производитель'];
						$str.=' '.$pos['Описание'];
						$str=infra_strtolower($str);
						foreach($v as $s)if(strstr($str,$s)===false)return;

						unset($pos['parent']);
						$poss[]=&$pos;
					},array(&$v,&$poss));
					if(sizeof($poss)){
						$ans['result']=1;
						$ans['title']='Поиск: '.$ans['val'];
						//$ans['descr']='Найдено позиций: '.sizeof($poss);
						$ans['list']=$poss;
					}
					$ans['text']='*pages/get.php?'.CATDIR.$val;
				}
			}
			if(!$ans['producer']&&$prod){
				$list2=array();
				for($i=0,$l=sizeof($ans['list']);$i<$l;$i++){
					if($prod==infra_strtolower($ans['list'][$i]['Производитель'])){
						$list2[]=$ans['list'][$i];
					}
				}
				$ans['list']=$list2;
				$ans['descr'].='<p>Найдено позиций: '.($posscount+sizeof($ans['list']).'</p>');
			}else{
				$ans['descr'].='<p>Найдено позиций: '.($posscount+sizeof($ans['list']).'</p>');
			}

		}else if($type=='producers'){
			$prods=array();

			xls_runPoss($data,function(&$prods, &$pos){
				@$prods[$pos['Производитель']]++;
			},array(&$prods));
			
			$ans['producers']=$prods;
		}
		return $ans;
	},$args,isset($_GET['re']));

	$ans=infra_admin_cache('cat admin',function(&$ans){
		$type=infra_strtolower($ans['type']);
		$art=infra_strtolower($ans['art']);
		$val=infra_strtolower($ans['val']);
		if(isset($_GET['submit'])&&$_GET['submit']){
			$submit=true;
		}else{
			$submit=false;
		}
		if($type=='stat'&&!$submit){
			$dir='infra/data/';
			$data=infra_loadJSON($dir.'catalog_stat.json');
			if(!$data){
				$data=array('users'=>array(),'cat_id'=>0,'time'=>time());//100 10 user list array('val'=>$val,'time'=>time())
			}
			$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Статистика поиска по каталогу&show');;
			//time
			//Поиск, Поиск, Поиск
			$ans['stat']=$data;
			return $ans;
		}
		if($type=='sale'){
			$list=infra_loadJSON('*sale.json');
			$items=array();
			if(is_array($list)){
				foreach($list as $item){
					$pos=infra_loadJSON('*catalog/catalog.php?type=pos&val='.$item['producer'].'&art='.$item['article']);
					$pos=$pos['pos'];
					unset($pos['texts']);
					unset($pos['files']);
					$pos['sale']=$item;
					$items[]=$pos;
				}
			}
			$ans['items']=$items;
			return $ans;
		}
		if($type=='producers'){
			$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Производители&show');;
		}
		if($type=='rubrics'){
			$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Каталог&show');;
		}
		if($type=='search'){
			if(isset($ans['text'])){
				$ans['text']=infra_loadTEXT($ans['text']);
			}
			if($val=='изменения'){
				$data=cat_init();
				//Смотрим дату изменения папки для каждой позиции кэшируем на изменение XLS файлов как всё здесь...
				//И дату изменения файлов в папке
				//Позиции без папок игнорируются
				$poss=array();
				xls_runPoss($data,function(&$poss,&$pos){
					$conf=infra_config();
					$dir=infra_theme($conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/');
					if(!$dir)return;

					$pos['time']=filemtime(ROOT.$dir);
					$list=infra_loadJSON('*pages/list.php?src='.infra_toutf($dir).'&onlyname=1');
					foreach($list as $f){
						$t=$dir.infra_tofs($f);
						$t=filemtime(ROOT.$t);
						if($t>$pos['time'])$pos['time']=$t;
					}
					$poss[]=&$pos;
				},array(&$poss));
				usort($poss,function($a, $b){
				    if($a['time']==$b['time'])return 0;
					return ($a['time']>$b['time'])?-1:1;
				});
				$ans['list']=array_slice($poss,0,30);
			};
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
					xls_preparePosFiles($pos,$conf['catalog']['dir'].$f, array('Производитель','article'));
				}

				xls_preparePosFiles($pos,$conf['catalog']['dir'], array('Производитель','article') );

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
