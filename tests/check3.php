<?php
	use itlife\infrajs\Infrajs;
	use itlife\infra;
	require_once(__DIR__.'/../../infra/infra.php');
	$ans = array();
	$ans['title'] = 'check3';

	infra_require('*infrajs/initphp.php');

	infra_html('<div id="main"></div>',true);

	$layers=infra_loadJSON('*infrajs/tests/resources/check3.json');
	
	infra\ext\crumb::change("test");
	
	infrajs::check($layers);

	$html=infra_html();
	preg_match_all('/x/', $html, $matches);
	$count=sizeof($matches[0]);
	$countneed=4;

	if($count==$countneed)return infra_ret($ans,'ret');
	return infra_err($ans,'err');