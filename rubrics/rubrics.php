<?php

require_once(__DIR__.'../infra/infra.php');

infra_require('*rubrics/rubrics.inc.php');
$type=$_GET['type'];
$conf=infra_config();

/*if($type=='redirect'){
 //Была мысль чтобы загрузка данных для некоего слоя всегда приходило к редиректу.. а на клиенте слой показывал бы ошибку 404 а при обновлении открывал бы главную
	//Признано лишней сущностью и закоменчено.
	global $infra;
	infra_wait($infra,'oninit',function(){
		$conf=infra_config();
		$r=infra_view_getRoot(ROOT);
		$src='http://'.$_SERVER['HTTP_HOST'].'/'.$r;
		@header('location: '.$src); 
		exit;
	});
}*/
$ans=array();
if(empty($conf['rubrics']['list'][$type]))return infra_err($ans,'Неопределённый type');
$dir='infra/data/'.$type.'/';
if($conf['rubrics']['list'][$type]['type']=='info'){
	$exts=array('docx','tpl','mht','html');
}else{
	$exts=array();
}

if(!empty($_GET['id'])){//Загрузка файла
	$id=$_GET['id'];
	
	$res=rub_search($dir,$id,$exts);

	if(isset($_GET['image'])){
		if($res['images']){
			$data=file_get_contents(ROOT.infra_tofs($res['images'][0]['src']));
			echo $data;
		}else{
			//@header('HTTP/1.1 404 Not Found');
		}
		return;
	}else if(isset($_GET['show'])){

		$conf=infra_config();
		if(!$res){
			//@header("Status: 404 Not Found");
			//@header("HTTP/1.0 404 Not Found");
		}else{
			$conf=infra_config();
			$src=$dir.$res['file'];
			echo rub_article($src);
		}

		return;


	}else if(isset($_GET['load'])){
		$conf=infra_config();
		if(!$res){
			//@header("Status: 404 Not Found");
			//@header("HTTP/1.0 404 Not Found");
			@header('location: http://'.$_SERVER['HTTP_HOST'].'/'.infra_view_getRoot(ROOT).'?Файлы/'.$id);
		}else{
			@header('location: http://'.$_SERVER['HTTP_HOST'].'/'.infra_view_getRoot(ROOT).'?*autoedit/download.php?'.$dir.$res['file']);
		}
		exit;
	}else{
		return infra_echo($res);
	}
}else if(isset($_GET['list'])){
	$lim=@$_GET['lim'];
	$p=explode(',',$lim);
	$start=0;
	$count=0;
	if($p){
		$start=$p[0];
		$count=@$p[1];
	}
	$ar=rub_list($dir,$start,$count,$exts);
	$ar=array_values($ar);
	$ans['list']=$ar;
	return infra_ret($ans);
}else{
	return infra_echo($ans,'Недостаточно параметров');
}
