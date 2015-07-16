<?php


$ans = array();
$ans['title'] = 'Проверка доступности сервера';

$conf=infra_config();
if ($conf['infra']['cache'] == 'mem') {
	if (!class_exists('Memcache')) {
		return infra_err($ans, 'Нет класса Memcache');
	}
	$mem = infra_memcache();
	if (!$mem) {
	    return infra_err($ans, 'Сервер не доступен');
	}
	return infra_ret($ans, 'сервер доступен');
} else {
	return infra_ret($ans, 'memcache не используется');
}
