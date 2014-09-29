<?php
	@define('ROOT','../../../../');

	require_once(ROOT.'infra/plugins/infra/infra.php');

	$root=infra_view_getRoot(ROOT);
	
	
	$d=infra_loadJSON('*infra/tests/getRoot/getRoot.php');
	echo 'Определение корня сервера на разных уровнях вложенности в разных файлах';
	
	if($d['root']==$root){
		echo '<h1 style="color:green">PASS</h1>';
	}else{
		echo '<h1 style="color:red">ERROR</h1>';
	}
?>
