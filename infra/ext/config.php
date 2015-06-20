<?php
//Copyright 2008-2013 http://itlife-studio.ru
/*
	infra_config
*/
@define('ROOT','../../../../');

global $infra_config;
$infra_config=array();
function &infra_config($sec=false){
	$sec=$sec?'secure':'unsec';

	global $infra_config;
	if(isset($infra_config[$sec]))return $infra_config[$sec];
	
	$dirs=array(
		'infra/data/',
		'infra/layers/',
		'vendor/akiyatkin/infrajs/',
		'infra/plugins/'
	);
	$dirs=array_reverse($dirs);
	
 	$data=array();
 	foreach($dirs as $src){
 		if(is_dir(ROOT.$src)){
			$list=scandir(ROOT.$src);
			foreach($list as $name){
				if($name[0]=='.')continue;
				if(!is_dir(ROOT.$src.$name))continue;
				if(!is_file(ROOT.$src.$name.'/.config.json'))continue;

				$d=file_get_contents(ROOT.$src.$name.'/.config.json');
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
		if(is_file(ROOT.$src.'.config.json')){
			$d=file_get_contents(ROOT.$src.'.config.json');
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