<?php
	require_once(__DIR__.'/../infra/infra.php');
	infra_require('*session/session.php');
	infra_admin(true);
	$ans=array();
	$name=$_REQUEST['name'];
	$val=$_REQUEST['val'];

	infra_session_set($name,$val);

	$ans['data']=infra_session_get();
	return infra_ret($ans);