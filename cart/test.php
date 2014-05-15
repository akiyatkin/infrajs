<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_admin(true);
	infra_require('*session/session.php');
	$data=infra_session_get();
	echo '<pre>';
	print_r($data);
?>