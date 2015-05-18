<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$ans = array(
		'title'=>'infra getRoot'
	);
	$root=infra_view_getRoot(ROOT);

	$d = infra_loadJSON('*infra/tests/getRoot/getRoot.php');

	if($d['root']==$root){
		$ans['result'] = 1;
		return infra_ret($ans, "тест пройден");
	}
	else{
		$ans['result'] = 0;
		return infra_err($ans, "Не пройден");
	}