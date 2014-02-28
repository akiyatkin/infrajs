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
	if(!is_file(ROOT.'infra/data/.config.json')){
		die('<h1>Вам нужно создать файл infra/data/.config.json</h1>{"admin":{"login":"логин","password":"секрет","email":"admin@email.ru"}}');
	}

	//$atime=infra_admin_time();

	/*
	 Когда изменяется debug с 0 на 1 нужно последний раз авторизоватсья чтобы это изменение применилось... и потом так как debug 1 уже все изменения пройдут без авторизаций. Всё и так считывается каждый раз.
	 */
	//if(!$atime||!$data||$data['debug']||$data['time']<$atime){//Если была новая авторизация конфиг считываем снова
	 	$data=array();
		$src='infra/plugins/';
		$list=scandir(ROOT.$src);
		foreach($list as $name){
			if($name[0]=='.')continue;
			if(!is_dir(ROOT.$src.$name))continue;
			if(!is_file(ROOT.$src.$name.'/.config.json'))continue;

			$d=file_get_contents(ROOT.$src.$name.'/.config.json');
			$d=infra_json_decode($d);
			if(is_array($d))foreach($d as $k=>$v) {
				if(@!is_array($data[$k]))$data[$k]=array();
				if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
				else $data[$k]=$v;
			}
		}
		if(is_file(ROOT.'infra/layers/.config.json')){
			$d=file_get_contents(ROOT.'infra/layers/.config.json');
			$d=infra_json_decode($d);
			if(is_array($d))foreach($d as $k=>$v) {
				if(@!is_array($data[$k]))$data[$k]=array();
				if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
				else $data[$k]=$v;
			}
		}

		$d=file_get_contents(ROOT.'infra/data/.config.json');
		$d=infra_json_decode($d);
		if(is_array($d))foreach($d as $k=>$v) {
			if(@!is_array($data[$k]))$data[$k]=array();
			if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
			else $data[$k]=$v;
		}
		/*
		if(!$data['http'])$data['http']=array();
		if(!$data['http']['sitehost'])$data['http']['sitehost']=$_SERVER['HTTP_HOST'];
		if(!$data['http']['siteroot'])$data['http']['siteroot']='';//Абсолютный путь до папки корня системы от корня домена. На сервере это никак не используется. или для web запросов самому к себе 'svn/x5service/'
		*/
		
		//$data['time']=$data['debug']?0:$atime;
		//$data['time']=$atime;//Таже секунда не подойдёт не обновится. для обновления конфига авторизация должна пройти после изменения хотябы на секунду
		//infra_mem_set('infra_config',$data);
	//}

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
	if(isset($infra_config['unsec']['debug'])&&$infra_config['unsec']['debug']){
		$data['debug']=true;
	}else{
		$data['debug']=false;
	}
	//unset($data['admin']);
	//unset($data['mysql']);
	$infra_config['secure']=$data;
	return $infra_config[$sec];
}
?>