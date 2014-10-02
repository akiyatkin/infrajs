<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*infra/ext/seq.php');
	infra_require('*session/session.php');

	$ans=array();
	$id=$_REQUEST['id'];
	$pass=$_REQUEST['pass'];
	$src=$_REQUEST['src'];
	if($pass&&$id){
		infra_view_setCookie(infra_session_getName('pass'),$pass);
		infra_view_setCookie(infra_session_getName('id'),$id);
		infra_view_setCookie(infra_session_getName('time'),1);
	}
	if(!$src)$src='';
	else $src='?'.$src;
	$conf=infra_config();
	$path='http://'.infra_view_getHost().'/';
	$path.=infra_view_getRoot(ROOT).$src;
	@header('Location: '.$path);
	//return infra_echo($ans);
?>
