<?php

	$ans = array();
	$ans['title'] = 'Тест функции кэширующих функций infra_cache infra_admin_cache';


	$conf=infra_config();
	$ans['admin']=infra_admin();
	$ans['debug']=$conf['debug'];



	$r1=infra_cache_is();
	$r2=infra_cache_check(function () use (&$ans) {
		infra_cache_no();
	});
	$r3=infra_cache_is();
	
	if (infra_admin()) {
		if ($r1||$r2||$r3) {
			return infra_err($ans, 'infra_cache_check работает некорректно с авторизацией');
		}
	} else {
		if (!$r1||$r2||!$r3) {
			return infra_err($ans, 'infra_cache_check работает некорректно');
		}
	}
	
	$name='test';

	$ans['test']=false;
	infra_admin_cache($name.'!!', function () use (&$ans) {
		infra_cache_no();
		$ans['test'] = true;
	});
	if (!$ans['test']) {
		return infra_err($ans, 'infra_cache_no В кэшируемой функции запрет на кэширование... но этот запрет не сработал');
	}
	


	
	$ans['counter']=0;
	infra_admin_cache($name, function () use (&$ans) {
		$ans['counter']++;
	});
	infra_admin_cache($name, function () use (&$ans) {
		$ans['counter']++;
	});

	if (infra_admin()) {
		if ($ans['counter'] != 1) {
			return infra_err($ans, 'infra_admin_cache В с авторизацией должен был сработать один раз');
		}
	} else {
		if ($conf['debug']) {
			if ($ans['counter'] != 1) {
				return infra_err($ans, 'infra_admin_cache В отладочном режиме должен был сработать один раз, так как debug:true');
			}
		} else {
			if ($ans['counter'] != 0) {
				return infra_err($ans, 'infra_admin_cache В рабочем режиме должен работать кэш, требуется обновить страницу');
			}

		}
	}



	$ans['counter']=0;
	infra_cache(array('test'), $name, function () use (&$ans) {
		infra_cache_no();
		$ans['counter']++;
	});
	infra_cache(array('test'), $name, function () use (&$ans) {
		infra_cache_no();
		$ans['counter']++;
	});


	if (infra_admin()) {
		if ($ans['counter'] != 1) {
			return infra_err($ans, 'infra_cache с авторизацией должен был сработать один раз');
		}
	} else {
		if ($conf['debug']) {
			if ($ans['counter'] != 1) {
				return infra_err($ans, 'infra_cache В отладочном режиме должен был сработать один раз, так как debug:true');
			}
		} else {
			if ($ans['counter'] != 0) {
				return infra_err($ans, 'infra_cache В рабочем режиме должен работать кэш, требуется обновить страницу');
			}

		}
	}

	if (infra_admin()) {
		return infra_ret($ans, 'Тест пройден с авторизацией');
	} else {
		return infra_ret($ans, 'Тест пройден без авторизацией');
	}
