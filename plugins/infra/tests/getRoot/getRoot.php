<?php
	@define('ROOT','../../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$d=infra_view_getRoot(ROOT);
	$ans=array('root'=>$d);
	return infra_echo($ans);
?>
