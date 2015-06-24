<?php
	
	require_once(__DIR__.'/../../infra/infra.php');
	$ans = array(
		'title'=>'Тест на корректность пути'
	);
	$root=infra_view_getRoot(ROOT);

	$d = infra_loadJSON('*infra/tests/getRoot/getRoot.php');

	if($d['root']!=$root) return infra_err($ans, "Путь задан не корректно");
	return infra_ret($ans, "Путь задан корректно");
