<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$db=infra_db(true);

	$f=!!$db;
	echo '<h1>Соединения с базой данных <span style="color:'.($f?'green">есть':'gray">нет').'</span></h1>';
?>
