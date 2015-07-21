<?php

/*
Copyright 2008-2010 ITLife, Ltd. http://itlife-studio.ru


*/
function infra_admin_modified($etag = '')
{
	//$v изменение которой должно создавать новую копию кэша
	$conf = infra_config();
	if ($conf['debug']) {
		return;
	}
	$now = gmdate('D, d M Y H:i:s', time()).' GMT';
	infra_cache_yes();
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$last_modified = infra_admin_time();
		/*
			Warning: strtotime(): It is not safe to rely on the system's timezone settings. You are *required* to use the date.timezone setting or the date_default_timezone_set() function. In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier. We selected the timezone 'UTC' for now, but please set date.timezone to select your timezone
		*/
		if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) > $last_modified) {
			if (empty($_SERVER['HTTP_IF_NONE_MATCH']) || $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
				//header('ETag: '.$etag);
				//header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE']);
				header('HTTP/1.0 304 Not Modified');
				exit;
			}
		}
	}

	header('ETag: '.$etag);
	header('Last-Modified: '.$now);
}
/*function infra_admin($break=null,$ans=array('msg'=>'Требуется авторизация','result'=>0)){
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
	infra_cache_no(); //@header('Cache-control:no-store');Метка о том что это место нельзя кэшировать для всех. нужно выставлять даже с session_start так как сессия может быть уже запущенной
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
}*/
function infra_admin($break = null, $ans = array('msg' => 'Требуется авторизация', 'result' => 0))
{
	//infra_admin(true) - пропускает только если ты администратор, иначе выкидывает окно авторизации
	//infra_admin(false) - пропускает только если ты НЕ администратор, иначе выкидывает окно авторизации
	//$ans выводится в json если нажать отмена
	//infra_admin(array('login','pass'));
	$data = infra_config();
	$data = $data['admin'];
	$_ADM_NAME = $data['login'];
	$_ADM_PASS = $data['password'];
	$admin = null;//Неизвестно

	$realkey = md5($_ADM_NAME.$_ADM_PASS.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);

	if (is_array($break)) {
		$admin = ($break[0] === $_ADM_NAME && $break[1] === $_ADM_PASS);
		if ($admin) {
			infra_view_setCookie('infra_admin', $realkey);
		} else {
			infra_view_setCookie('infra_admin');
		}
	} else {
		$key = infra_view_getCookie('infra_admin');

		$admin = ($key === $realkey);
		if ($break === false) {
			infra_view_setCookie('infra_admin');
			$admin = false;
		} elseif ($break === true && !$admin) {
			$admin = (@$_SERVER['PHP_AUTH_USER'] == $_ADM_NAME && @$_SERVER['PHP_AUTH_PW'] == $_ADM_PASS);
			if ($admin) {
				infra_view_setCookie('infra_admin', $realkey);
			} else {
				header('WWW-Authenticate: Basic realm="Protected Area"');
				header('HTTP/1.0 401 Unauthorized');
				echo infra_json_encode($ans);
				exit;
			}
		}
	}

	if ($admin) {
		infra_admin_time_set();
		infra_cache_no();//Администратор может видеть кэш страниц?
	}

	return $admin;
}
function infra_admin_time_set($t = null)
{
	$dirs = infra_dirs();
	if (is_null($t)) {
		$t = time();
	}
	$adm = array('time' => $t);

	infra_mem_set('infra_admin_time', $adm);
	infra_once('infra_admin_time', $adm['time']);
}

function infra_admin_time()
{
	return infra_once('infra_admin_time', function () {
		$adm = infra_mem_get('infra_admin_time');
		if (!$adm) {
			$adm = array();
		}
		if (!isset($adm['time'])) {
			$adm['time'] = 0;
		}

		/*$t=infra_admin_lastupdate_time();
		if($t>$adm['time']){
			infra_admin_time_set($t);
			$adm['time']=$t;
		}*/
		return $adm['time'];
	});
}
function infra_admin_cache($name, $call, $args = array(), $re = false)
{
	//Запускается один раз для админа, остальные разы возвращает кэш из памяти
	return infra_once('infra_admin_cache'.$name, function ($args, $name) use ($name, $call, $re) {
		$conf = infra_config();

		$strargs = infra_hash($args);
		$name = 'infra_admin_once_'.$name.$strargs;
		if ($re) {
			infra_mem_delete($name);
		}

		$execute=true;
		if (!$conf['debug'] && !$re && !infra_admin()) {
			$execute=false;
		}

		if (!$execute) {
			$atime = infra_admin_time();

			$data = infra_mem_get($name);

			if (!$data || $data['time'] < $atime) {
				$execute=true;
			}
		}
		

		if ($execute) {
			$data = array('time' => time());

			//здесь для примера показана
			//@header('Cache-control:no-store');//Метка о том что это место нельзя кэшировать для всех. нужно выставлять даже с session_start

			$cache = infra_cache_check(function () use ($call, &$args, &$data, $re) {
				$data['result'] = call_user_func_array($call, array_merge($args, array($re)));
			});

			
			if ($cache) {
				infra_mem_set($name, $data);
			}

			//} elseif ($data) {
				//Если текущие данные не кэшируются, то удаляются ???
				//infra_mem_flush();
			//	infra_mem_delete('infra_admin_once_'.$name);
			//}
		}

		return $data['result'];
	}, array($args, $name), $re);
}
