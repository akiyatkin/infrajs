<?php
@define('ROOT','../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_require('*cart/catalog.inc.php');
infra_require('*session/session.php');


/*
1 уровень поиск без нумерации и без фильтра cat_search
2 уровень применяется фильтр
определяется childs
3 уровень без кэша применяется нумерация и сортировка




*/
$conf=infra_config();
$cond=array($conf['cart']['dir'],$conf['cart']['1c'],$conf['cart']['prod']);


$count=8;

$check=infra_session_get('filtersadmit',array());


$val=strip_tags(@$_GET['val']);

$page=(int)$_GET['page'];
if(!$page)$page=1;

$sort=(string)$_GET['sort'];// price, name, def, group, producer
if(!in_array($sort,array('proce','name','group','producer')))$sort='def';

$reverse=(int)$_GET['reverse'];//0, 1 в 
if(!$reverse)$reverse=0;
else $reverse=1;


$args=array($val,$check,$sort,$reverse);
$ans=infra_cache($cond,'cart_search_php_page',function($val,$check,$sort,$reverse) use(&$cond){
	$ans=cat_search($val);//свой кэш, без страниц

	$yes=$check['yes'];
	if(!$yes)$yes=array();
	unset($check['yes']);
	$no=$check['no'];
	if(!$no)$no=array();
	unset($check['no']);

	$filters=array();
	$isgroup=array();
	$isgroup['Группы']=true;
	$ispos=array();
	$ispos['Производитель']=true;
	$ispos['Цена']=true;

	foreach($yes as $key=>$val){
		if(!isset($check[$key])){
			$check[$key]=array();
		}
	}
	foreach($no as $key=>$val){
		if(!isset($check[$key])){
			$check[$key]=array();
		}
	}
	

	foreach($check as $name=>$v){
		//if(!$v)continue;
		$noval=$no[$name];
		$yesval=$yes[$name];
		if(!$v&&!$noval&&!$yesval)continue;


		

		if(!$yesval){//Только сейчас фильтруем

		
			$poss=array();
			$fil=array('name'=>$name);
			$fil['no']=$noval;
			$fil['yes']=$yesval;
			if(!is_array($v)){//Диапазон
				$v=(string)$v;
				$fil['slide']=true;
				$r=explode('—',$v);
				$min=preg_replace('/\D/', '', $r[0]);
				$max=preg_replace('/\D/', '', $r[1]);
				$fil['max']=$max;
				$fil['min']=$min;
				foreach($ans['list'] as &$pos){
					$obj=$ispos[$name]?$pos:$pos['more'];
					
					if(($noval&&(!isset($obj[$name])||is_null($obj[$name])))||(isset($obj[$name])&&$obj[$name]>=$min&&$obj[$name]<=$max)){
						$poss[]=&$pos;
					}
				}
			}else{
				if($isgroup[$name]){
					$values=array();

					foreach($v as $key=>$is)if($is)$values[]=$key;
					$fil['values']=$values;

					foreach($ans['list'] as &$pos){
						foreach($values as $val){
							if(in_array($val,$pos['path'])){
								$poss[]=&$pos;
								break;
							}
						}
					}

				}else{
					
					$values=array();
					foreach($v as $key=>$is)if($is)$values[]=$key;
					$fil['values']=$values;
					foreach($ans['list'] as &$pos){
						$obj=$ispos[$name]?$pos:$pos['more'];
						$specified=cat_isSpecified($obj[$name]);
						if(
							(
								!$specified&&$noval
							)||
							(
								$specified&&in_array($obj[$name],$values)
							)
						){
							$poss[]=&$pos;
						}
					}

				}
			}
			$filters[]=$fil;
			$ans['list']=$poss;
		}else if(!$noval){//yes true, noval false, нужны только указанные
			$fil=array('name'=>$name);
			$fil['no']=$noval;
			$fil['yes']=$yesval;
			echo sizeof($ans['list']);
			exit;
			foreach($ans['list'] as &$pos){
				$obj=$ispos[$name]?$pos:$pos['more'];
				//if(isset($obj[$name])&&!is_null($obj[$name])){
				if(isset($obj[$name])){
					$poss[]=&$pos;
				}
			}			
			$filters[]=$fil;
			$ans['list']=$poss;
		}	
	}
	$ans['filters']=$filters;
	if($reverse){
		$ans['list']=array_reverse($ans['list']);
	}



	$ans['val']=$val;
	$conf=infra_config();
	if($ans['is']=='group'){
		$data=cat_init();
		$group=&xls_runGroups($data,function(&$group) use(&$ans){
			if($group['title']==$ans['name'])return $group;
		});

		$ans['title']=$group['title'];
		$ans['descr']=@$group['descr']['Описание группы'];
		if($group['parent_title']){
			$ans['parent']=array('title'=>$group['parent_title']);
		}
		/*if($group['childs']){
			$ans['childs']=array();
			foreach($group['childs'] as &$v){
				$pos=xls_runPoss($v,function(&$pos){
					return $pos;
				});
				if($pos){
					$pos=array('article'=>$pos['article'],'producer'=>$pos['Производитель']);
				}
				$ans['childs'][]=array('title'=>$v['title'],'pos'=>$pos);
			}
		}*/
		$src='*pages/get.php?'.$conf['cart']['dir'].$group['title'];
		$ans['text']=$src;
	}
	if($ans['is']=='producer'){
		$dir=infra_theme($conf['cart']['dir'].$val.'/');
		$poss=$ans['list'];
		$prods=xls_init($conf['cart']['prod']);
		$prod=&xls_runPoss($prods,function($val, &$prod){
			if(infra_strtolower($prod['Производитель'])==$val)return $prod;
		},array($val));
		$name=$ans['name'];
		$ans['title']='Производитель '.$name;
		$ans['descr']=@$prod['Описание группы'];
		$list=infra_loadJSON('*pages/list.php?onlyname=1&e=mht,docx,tpl&src='.$conf['cart']['dir'].$name.'/');
		if(isset($list[0])){
			$ans['text']='*pages/get.php?'.$conf['cart']['dir'].$name.'/'.$list[0];
		}
	}
	if($ans['is']=='search'){//ищим позиции подходящие под запрос
		if(sizeof($ans['list'])){
			$ans['title']='Поиск: '.$ans['val'];
			$ans['descr']='Найдено позиций: '.sizeof($ans['list']);
		}
		$ans['text']='*pages/get.php?'.$conf['cart']['dir'].$val;
	}
	if($ans['is']=='change'){
		$ans['title']='Последние изменения';
		$ans['descr']='Последнии позиций, у которых изменился текст полного описания.';
	}
	
	if(sizeof($ans['list'])){//Нужно найти общую группу в path и показать её подгруппы
		$groups=array();

		foreach($ans['list'] as &$pos){
			$path=$pos['path'];
			foreach($ans['list'] as &$pos){
				foreach($pos['path'] as $v)$groups[$v]=true;
				$rpath=array();
				foreach($path as $k=>$p){
					if($pos['path'][$k]==$p){
						$rpath[$k]=$p;
					}else{
						break;
					}
				}
				$path=$rpath;
			}

			break;
		}

		$data=cat_init();
		if(!sizeof($path)){
			$group=$data;
		}else{
			$g=$path[sizeof($path)-1];
			$group=xls_runGroups($data,function(&$group) use($g){
				if($group['title']==$g)return $group;
			});
		}

		$ans['childs']=array();
		foreach($group['childs'] as $g){
			if(!$groups[$g['title']])continue;
			$pos=&xls_runPoss($g,function(&$pos){
				return $pos;
			});
			if($pos){
				$pos=array('article'=>$pos['article'],'producer'=>$pos['Производитель']);
			}else{
				$pos=array();
			}
			$ans['childs'][]=array('title'=>$g['title'],'pos'=>$pos);
		}
	}
	return $ans;
},$args,isset($_GET['re']));

$pages=ceil(sizeof($ans['list'])/$count);
if($pages<$page)$page=$pages;

$ans['page']=$page;
$ans['pages']=$pages;
$ans['count']=sizeof($ans['list']);




$ans['numbers']=cat_numbers($page,$pages,11);
$ans['list']=array_slice($ans['list'],($page-1)*$count,$count);
if(sizeof($ans['list']))$ans['result']=1;
if(isset($ans['text'])){
	$ans['text']=infra_loadTEXT($ans['text']);
}
return infra_echo($ans);
?>