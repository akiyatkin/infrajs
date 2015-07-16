<?php

function infra_view_getHost()
{
	return $_SERVER['HTTP_HOST'];
}
function infra_view_getSchema()
{
	return $_SERVER['REQUEST_SCHEME'].'://';
}
function infra_view_getAgent()
{
	return $_SERVER['HTTP_USER_AGENT'];
}
function infra_view_getIP()
{
	return $_SERVER['REMOTE_ADDR'];
}
function infra_view_getRef()
{
	return $_SERVER['HTTP_REFERER'];
}
function infra_view_getCookie($name = null)
{
	if (is_null($name)) {
		return $_COOKIE;
	}

	return @$_COOKIE[$name];
}
function infra_view_setCookie($name, $val = null)
{
	$_COOKIE[$name] = $val;
	$root = infra_view_getRoot();
	if (is_null($val)) {
		$time = time() - 60 * 60 * 24 * 30 * 24;
	} else {
		$time = time() + 60 * 60 * 24 * 30 * 24;
	}

	return setcookie($name, $val, $time, '/'.$root);
}
function infra_view_getPath()
{
	return infra_view_getSchema().infra_view_getHost().'/'.infra_view_getRoot();
}
/**
 * Возвращает путь до сайта от корня сервера
 */
function infra_view_getRoot()
{
	$path=substr(infra_getcwd(), strlen(infra_realpath($_SERVER['DOCUMENT_ROOT'])));
}

/*function infra_view_setCOOKIE($name,$val){
	return infra_view_setCookie($name,$val);
}
function infra_view_getCOOKIE($name,$val){
	return infra_view_getCookie($name,$val);
}*/
