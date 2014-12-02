<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	
	infra_admin(true);

	$plugin=$_SERVER['QUERY_STRING'];
	$code=infra_loadTEXT('*'.$plugin.'/.test.js');
	echo $code;