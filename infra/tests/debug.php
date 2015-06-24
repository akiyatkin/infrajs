<?php
	
	require_once(__DIR__.'/../../infra/infra.php');

	$ans = array();
	$ans['title'] = 'Тест на значение debug';

	$conf = infra_config();
	if(!$conf['debug'])return infra_err($ans,'Значение debug = false');
	return infra_ret($ans,'Значение debug = true');