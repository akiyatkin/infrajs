<?php


$ans = array();
$ans['title'] = 'Проверка доступности сервера';

$conf=infra_config();
if ($conf['infra']['cache'] != 'mem') {
	return infra_ret($ans, 'memcache не используется');
}

if (!class_exists('Memcache')) {
	return infra_err($ans, 'Нет класса Memcache');
}
$mem = infra_memcache();
if (!$mem) {
	return infra_err($ans, 'Сервер не доступен');
}

$val=infra_mem_get('test');
if (!$val) {
	infra_mem_set('test', true);
	return infra_err($ans, 'Неудалось восстановить значение. Требуется F5');
}



return infra_ret($ans, 'сервер доступен');
