<?php
/*
Copyright 2008-2010 ITLife, Ltd. http://itlife-studio.ru


*/
@define('ROOT','../../../../');
function infra_admin_modified(){
	$conf=infra_config();
	if($conf['debug'])return;
	else $atime=infra_admin_time();
	$last_modified=$atime;
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	  // разобрать заголовок
	//@header('Cache-control:no-cache');//Метка о том что это место нельзя кэшировать для всех. нужно выставлять даже с session_start
	  $if_modified_since=preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
	  $if_modified_since=strtotime($if_modified_since); 
	  if ($if_modified_since<=$last_modified) {
		// кэш браузера до сих пор актуален
		header('HTTP/1.0 304 Not Modified');
		header('Cache-control: max-age=8640000, must-revalidate');
		exit;
	  }
	}
	//header('Cache-control: max-age=86400, must-revalidate');
	$last_modified=gmdate('D, d M Y H:i:s', $atime).' GMT';
	header('Last-Modified: ' . $last_modified);
}
function infra_admin($break=null,$ans=array('msg'=>'Требуется авторизация','result'=>0)){
	//infra_admin(true) - пропускает только если ты администратор, иначе выкидывает окно авторизации
	//infra_admin(false) - пропускает только если ты НЕ администратор, иначе выкидывает окно авторизации
	//$ans выводится в json если нажать отмена
	//infra_admin(array('login','pass'));
	$data=infra_config();
	$data=$data['admin'];
	$_ADM_NAME = $data['login'];
	$_ADM_PASS = $data['password'];
	$admin=null;//Неизвестно

	if(is_array($break)){
		$admin=($break[0]===$_ADM_NAME&&$break[1]===$_ADM_PASS);
	}
	infra_cache_no(); //@header('Cache-control:no-cache');Метка о том что это место нельзя кэшировать для всех. нужно выставлять даже с session_start
	//Кэш делается гостем.. так как скрыт за функцией infra_admin_cache исключение infra_cache когда кэшу интересны только даты изменения файлов.
	$r=session_start();

	if(is_null($admin)&&isset($_SESSION['ADMIN'])){
		$admin=(bool)$_SESSION['ADMIN'];
	}
	if(is_null($admin)){
		$admin=(@$_SERVER['PHP_AUTH_USER']==$_ADM_NAME&&@$_SERVER['PHP_AUTH_PW']==$_ADM_PASS);
		if($admin)$_SESSION['ADMIN']=true;
	}

	if($break===false){
		$admin=false;
		$_SESSION['ADMIN']=false;
	}
	if($admin){
		infra_admin_time_set();
	}

	if($break===true&&!$admin){
		header("WWW-Authenticate: Basic realm=\"Protected Area\"");
		header("HTTP/1.0 401 Unauthorized");
		unset($_SESSION['ADMIN']);
		echo infra_json_encode($ans);
		exit;
	}
	$_SESSION['ADMIN']=$admin;
	return $admin;
}
function infra_admin_time_set($t=null){
	$dir='infra/cache/';
	@mkdir(ROOT.'infra/cache/');
	if(is_null($t))$t=time();
	$adm=array("time"=>$t);
	file_put_contents(ROOT.$dir.'last_admin.json',infra_json_encode($adm));
}
/*function infra_admin_lastupdate_time(){
	return infra_once('infra_admin_lastupdate_time',function(){
		if(is_file(ROOT.'infra/update')){
			$data=array('time'=>time());
			file_put_contents(ROOT.'infra/cache/lastupdate.json',infra_json_encode($data));
			unlink(ROOT.'infra/update');
		}else{
			$data=infra_loadJSON('infra/cache/lastupdate.json');
			if(!$data){
				$data=array('time'=>time());
				file_put_contents(ROOT.'infra/cache/lastupdate.json',infra_json_encode($data));
			}
		}
		return $data['time'];
	});
}*/

function infra_admin_time(){
	return infra_once('infra_admin_time',function(){
		//if(is_file(ROOT.'admin')){//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
		//	unlink(ROOT.'admin');
		//	infra_admin_time_set();
		//}
		
		


		$adm=infra_loadJSON('infra/cache/last_admin.json');
		if(!$adm)$adm=array();
		if(!isset($adm['time']))$adm['time']=0;

		/*$t=infra_admin_lastupdate_time();
		if($t>$adm['time']){
			infra_admin_time_set($t);
			$adm['time']=$t;
		}*/
		return $adm['time'];
	});
}
function infra_admin_cache($name,$call,$args=array(),$re=false){//Запускается один раз для админа, остальные разы возвращает кэш из памяти
	$conf=infra_config();

	$strargs=infra_hash($args);
	$name=$name.$strargs;

	$data=infra_mem_get('infra_admin_once_'.$name);
	$atime=infra_admin_time();
	if($conf['debug']||$re||!$data||$data['time']<$atime){
		$data=array('time'=>time());

		//здесь для примера показана
		//@header('Cache-control:no-cache');//Метка о том что это место нельзя кэшировать для всех. нужно выставлять даже с session_start
					$header_name='cache-control';//Проверка установленного заголовока о запрете кэширования, до запуска кэшируемой фукцнии
					$list=headers_list();
					$cache_control=infra_forr($list,function($header_name, $row){
						$r=explode(':',$row);
						if(stristr($r[0],$header_name)!==false) return trim($r[1]);
					},array($header_name));
					if($cache_control)header_remove('cache-control');


		$data['result']=call_user_func_array($call,array_merge($args,array($re)));

					$list=headers_list();//Проверяем появился ли заголовок после запуска функции кэшируемой
					$cache_control2=infra_forr($list,function($header_name, $row){
						$r=explode(':',$row);
						if(stristr($r[0],$header_name)!==false) return trim($r[1]);
					},array($header_name));
					if(!$cache_control2&&$cache_control)@header('cache-control: '.$cache_control);

					if(!$re&&(!$cache_control2||stristr($cache_control2,'no-cache')===false)){
						//Кэшируем только если нет заголовка, или он не содержит no cache.
						//При повторном вызове session_start нужно руками вызывать header('cache-control:no-cache') чтобы информация была получена что обработка динамическая

		infra_mem_set('infra_admin_once_'.$name,$data);
					}else if($data){//Если текущие данные не кэшируются, то удаляются
						//infra_mem_flush();
						infra_mem_delete('infra_admin_once_'.$name);
					}


	}
	return $data['result'];
}
?>
