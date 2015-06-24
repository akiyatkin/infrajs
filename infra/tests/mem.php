<?php
	
	require_once(__DIR__.'/../../infra/infra.php');
	$ans = array();
	$ans['title'] = 'Проверка доступности сервера';
	$mem=infra_memcache();
	if(!$mem)return infra_err($ans, 'Сервер не доступен');
	return infra_ret($ans, 'сервер доступен');
