<?php
	require_once(__DIR__.'/../infra/infra.php');
	infra_require('*infra/ext/seq.php');
	infra_require('*session/session.php');

	$ans=array();
	$id=$_REQUEST['id'];
	$pass=$_REQUEST['pass'];//md5 пароля, чтобы авторизоваться не нужно знать пароль, хэша достаточно.
	$src=$_REQUEST['src'];
	if($pass&&$id){
		infra_session_change($id,$pass);
	}
	
	if(!$src)$src='';
	else $src='?'.$src;
	$conf=infra_config();
	$path='http://'.infra_view_getHost().'/';
	$dirs=infra_dirs();
	$path.=infra_view_getRoot($dirs['ROOT']).$src;
	@header('Location: '.$path);
	//return infra_echo($ans);
