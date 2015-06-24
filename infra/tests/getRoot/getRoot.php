<?php
	require_once(__DIR__.'/../../../infra/infra.php');
	$d=infra_view_getRoot(ROOT);
	$ans=array('root'=>$d);
	return infra_echo($ans);
?>
