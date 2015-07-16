<?php

$ans = array();

$ans['title'] = 'Проверка наличия папок';

$conf=infra_config();

if ($conf['infra']['cache']=='fs') {
	$dirs = infra_dirs();

	if (!infra_theme($dirs['cache'])) {
	    return infra_err($ans, 'Нет папки '.$dirs['cache']);
	}
	if (!infra_theme($dirs['data'])) {
	    return infra_err($ans, 'Нет папки '.$dirs['data']);
	}
	if (!infra_theme($dirs['backup'])) {
	    return infra_err($ans, 'Нет папки '.$dirs['backup']);
	}

	return infra_ret($ans, 'Обязательные папки есть');
} else {
	return infra_ret($ans, 'Используется memcache. Папки не создаются.');
}
