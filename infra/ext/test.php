<?php
	require_once(__DIR__.'/../infra.php');
	
	infra_admin(true);

	$plugin=$_SERVER['QUERY_STRING'];
	$code=infra_loadTEXT('*'.$plugin.'/.test.js');
	echo $code;