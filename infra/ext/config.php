<?php
//Copyright 2008-2013 http://itlife-studio.ru
/*
	infra_config
*/


global $infra_config;
$infra_config=array();

if(DIRECTORY_SEPARATOR=='/'){
	function infra_realpath($dir){
		return realpath($dir);
	}
	function infra_getcwd(){
		return getcwd();
	}
}else{
	function infra_realpath($dir){
		$dir=realpath($dir);
		return str_replace(DIRECTORY_SEPARATOR,'/',$dir);
	}
	function infra_getcwd(){
		$dir=getcwd();
		return str_replace(DIRECTORY_SEPARATOR,'/',$dir);
	}
}

function infra_dirs(){
	global $infra_dirs;
	if(!empty($infra_dirs))return $infra_dirs;
	
	/*
	echo __DIR__.'/';//АД расположение текущего сайта
	echo '<br>';
 	echo infra_realpath($_SERVER['DOCUMENT_ROOT']).'/';//АД Корень вебсервера
 	echo '<br>';
 	echo infra_realpath(__DIR__.'/../../../../').'/';//АД Путь до vendor
 	echo '<br>';
 	echo infra_getcwd().'/';//АД расположение сайта
 	echo '<br>';
	echo infra_realpath(__DIR__.'/../../../../../').'/';//АД Папка vendor'а
	*/


	$vendorroot=substr(infra_realpath(__DIR__.'/../../../../../'),strlen(infra_realpath($_SERVER['DOCUMENT_ROOT']))).'/';//AВ до vendor
	//$vendor=infra_dir2();
	$siteroot=substr(infra_getcwd(),strlen(infra_realpath($_SERVER['DOCUMENT_ROOT']))).'/';//AВ Путь до сайта
	
	if($siteroot==$vendorroot){
		$ROOT='';
	}else{
		//Определить путь от сайта до vendor
		//Найти пересечение.. 
		//то сколько папок осталось у ROOT это точки ../
		//то сколько папок осталось у vendor это путь после точек
		$sr=explode('/',$siteroot);
		$vr=explode('/',$vendorroot);

		$i=0;
		while ($sr[$i]===$vr[$i])$i++;
		$downcount=sizeof($sr)-$i;
		$down=str_repeat('..'.'/',sizeof($sr)-$i-1);
		$up=implode('/',array_slice($vr, $i));
		$ROOT=$down.$up;
	}
	//$ROOT=infra_getcwd().'/';

	$infra_dirs=array(
		'ROOT'=>$ROOT,
		'cache'=>$ROOT.'infra/cache/',
		'data'=>$ROOT.'infra/data/',
		'backup'=>$ROOT.'infra/backup/',
		'search'=>array(
			$ROOT.'infra/data/',
			$ROOT.'infra/layers/',
			$ROOT.'vendor/itlife/infrajs/'
		)
	);
	$vendors=__DIR__.'/../../../../';
	$list=scandir($vendors);
	foreach($list as $name){
		if($name[0]=='.')continue;
		if(!is_dir($vendors.$name))continue;
		$infra_dirs['search'][]=$ROOT.'vendor/'.$name.'/';
	}
	//echo '<pre>';
	//print_r($dirs);
 	//echo '<br>'.DIRECTORY_SEPARATOR; //AВ Путь до корня веб сервера
 	//echo '<br>'.'.'.DIRECTORY_SEPARATOR;//ОВ до сайта
 	//exit;
 	return $infra_dirs;
}
function &infra_config($sec=false){
	$sec=$sec?'secure':'unsec';

	global $infra_config;
	if(isset($infra_config[$sec]))return $infra_config[$sec];
	
	
	


	$dirs=infra_dirs();
	$dirs['search']=array_reverse($dirs['search']);
 	$data=array();

 	foreach($dirs['search'] as $src){
 		if(is_dir($src)){
			$list=scandir($src);
			foreach($list as $name){
				if($name[0]=='.')continue;
				if(!is_dir($src.$name))continue;
				if(!is_file($src.$name.'/.config.json'))continue;

				$d=file_get_contents($src.$name.'/.config.json');
				$d=infra_json_decode($d);
				if(is_array($d))foreach($d as $k=>&$v) {
					if(@!is_array($data[$k]))$data[$k]=array();
					if(isset($d[$k]['pub'])&&isset($data[$k]['pub'])){
						$d[$k]['pub']=array_unique(array_merge($d[$k]['pub'],$data[$k]['pub']));
					}
					if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
					else $data[$k]=$v;
				}
			}
		}
		if(is_file($src.'.config.json')){
			$d=file_get_contents($src.'.config.json');
			$d=infra_json_decode($d);
			if(is_array($d))foreach($d as $k=>&$v) {
				if(@!is_array($data[$k]))$data[$k]=array();
				if(isset($d[$k]['pub'])&&isset($data[$k]['pub'])){
					$d[$k]['pub']=array_unique(array_merge($d[$k]['pub'],$data[$k]['pub']));
				}
				if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
				else $data[$k]=$v;
			}
		}
 	}
	$infra_config['unsec']=$data;
	foreach($data as $i=>$part){
		$pub=@$part['pub'];
		if(is_array($pub)){
			foreach($part as $name=>$val){
				if(!in_array($name,$pub)){
					unset($data[$i][$name]);
				}
			}
		}else{
			unset($data[$i]);
		}
	}
	$data['debug']=$infra_config['unsec']['debug'];
	$infra_config['secure']=$data;
	return $infra_config[$sec];
}