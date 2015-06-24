<?php
	
	require_once(__DIR__.'/../../infra/infra.php');
	$_SERVER['QUERY_STRING']="?test";
	infra_require('*infrajs/initphp.php');
	use itlife\infrajs\infrajs;
	$ans = array();
	$ans['title'] = 'проверка чек';

	infra_html('<div id="main"></div>');

	$layers=infra_loadJSON('*infrajs/tests/resources/check2.json');
	infrajs::checkAdd($layers);
	infrajs::check();

	$layer=&$layers['layers'];

	$html=infra_html();
	preg_match_all('/x/', $html, $matches);
	$count=sizeof($matches[0]);

	if($count!=4) return infra_err($ans, 'нууль');
	return infra_ret($ans, 'daa');