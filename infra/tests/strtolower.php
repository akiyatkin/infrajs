<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	


	$ans=array('result'=>0,'title'=>"Проверка strtolower");
	
	$s1=infra_tofs('Кирилица utf8');
	$s2=infra_tofs('кирилица utf8');

	if(infra_strtolower($s1)!=$s2)return infra_err($ans,'infra_strtolower не работает');
	


	return infra_ret($ans);
?>