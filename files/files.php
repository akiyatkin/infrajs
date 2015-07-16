<?php


infra_require('*files/files.inc.php');
$type=$_GET['type'];
$conf=infra_config();



$ans=array();

	if($type=='pages'){
		$dir=$conf['files']['folder_pages'];
		$exts=array('docx','tpl','mht','html');
	}else if($type=='files'){
		$dir=$conf['files']['folder_files'];
		$exts=array();
	}else if($type=='blog'){
		$dir=$conf['files']['folder_blog'];
		$exts=array('docx','tpl','mht','html');
	}else if($type=='texts'){
		$dir=$conf['files']['folder_texts'];
		$exts=array('docx','tpl','mht','html');
	}else if($type=='events'){
		$dir=$conf['files']['folder_events'];
		$exts=array('docx','tpl','mht','html');
	}else if(isset($_GET['dir'])){
		$dir=$_GET['dir'];
		$opt=$conf['files']['folders'][$dir];
		if(!$opt){
			return infra_echo($ans,'Неопределённый dir',0);
		}
		if($opt=='info')$exts=array('docx','tpl','mht','html');
		else if($opt=='files')$exts=array();
		else return infra_echo($ans,'Некорректный config',0);
	}else{
		return infra_echo($ans,'Неопределённый type',0);
	}

if(isset($_REQUEST['seo'])){
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
	infra_require('*seo/seo.inc.php');
	$items=infra_admin_cache('files seo',function($type,$dir,$exts){
		$list=infra_loadJSON('*files/files.php?type='.$type.'&list');
		$items=array();
		foreach($list as &$item){
			$v=&seo_createItem($items,$item['title']);

			$page=infra_loadTEXT('*files/files.php?type='.$type.'&id='.$item['id'].'&show');
			seo_pageResearch($page,$v);

			//seo_pageResearch($item['preview'],$v);
		}
		return $items;
	},array($type,$dir,$exts));
	$ans['items']=$items;
	return infra_echo($ans,'ok',1);
}
if(@$_GET['id']){//Загрузка файла
	$id=$_GET['id'];
	
	$res=files_search($dir,$id);
	if(isset($_GET['image'])){
		if($res['images']){
			$data=file_get_contents(infra_tofs($res['images'][0]['src']));
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
			echo files_article($src);
		}

		return;


	}else if(isset($_GET['load'])){
		$conf=infra_config();
		if(!$res){
			//@header("Status: 404 Not Found");
			//@header("HTTP/1.0 404 Not Found");
			$dirs=infra_dirs();
			@header('location: http://'.$_SERVER['HTTP_HOST'].'/'.infra_view_getRoot().'?Файлы/'.$id);
		}else{
			@header('location: http://'.$_SERVER['HTTP_HOST'].'/'.infra_view_getRott().'?*autoedit/download.php?'.$dir.$res['file']);
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
	$ar=files_list($dir,$start,$count,$exts);
	$ar=array_values($ar);
	return infra_echo($ar);
}else{
	return infra_echo($ans,'Недостаточно параметров');
}
