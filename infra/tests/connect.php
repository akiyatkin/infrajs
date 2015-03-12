<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$db=infra_db(true);
	$ans=array();
	$ans['title']='Проверка соединения с базой данных';
	if($db)return infra_ret($ans,'Есть соединение с базой данных');
	else return infra_err($ans,'Нет соединения с базой данных');
	//echo '<h1>Соединения с базой данных <span style="color:'.($f?'green">есть':'gray">нет').'</span></h1>';
?>
