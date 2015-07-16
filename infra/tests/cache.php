<?php

	$ans = array();
	$ans['title'] = 'Тест функции infra_admin_cache';
	$ans['test'] = false;
	infra_admin_cache('asdf', function () use (&$ans) {
		infra_cache_no();
		$ans['test'] = true;
	});
	if (!$ans['test']) {
		return infra_ret($ans, 'В кэшируемой функции запрет на кэширование... но этот запрет не сработал');
	}

	return infra_ret($ans, 'Тест пройден');
