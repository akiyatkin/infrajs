<?php
	/*
		Пустой шаблон также содержи подшаблон root, ошибка что возвращается слово root
	*/
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$ans=array();

	$ans['res']=infra_template_parse(array(''),true);
	if($ans['res']!=='')return infra_err($ans,'Непройден тест 1 {res}');
	
	$ans['res']=infra_template_parse(array(''));
	if($ans['res']!=='')return infra_err($ans,'Непройден тест 2 {res}');

	return infra_ret($ans,'Все теcты пройдены');
?>