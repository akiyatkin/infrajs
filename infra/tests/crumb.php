<?php
	
	require_once(__DIR__.'/../../infra/infra.php');
	infra_require('*infra/ext/crumb.php');
	use itlife\infrajs\infra\ext\crumb;
	crumb::init();

	$ans = array();
	$ans['title'] = 'Хлебные крошки';

	crumb::change('');
	$crumb=crumb::getInstance('');
	$f=$crumb->query;

	crumb::change('test');

	$s=&crumb::getInstance('some');
	$s2=&crumb::getInstance('some');
	$r=infra_isEqual($s,$s2);

	$s=crumb::$childs;
	$r2=infra_isEqual($s[''],crumb::getInstance());

	$r=$r&&$r2;

	$crumb=crumb::getInstance('test');
	$crumb2=crumb::getInstance('test2');

	if($f==Null&&$r&&!is_null($crumb->query)&&is_null($crumb2->query)) return infra_ret($ans, 'ret');
	else return infra_err($ans, 'ret');