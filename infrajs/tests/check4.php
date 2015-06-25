<?php
	use itlife\infrajs\infrajs;
	use itlife\infrajs\infra;
	require_once(__DIR__.'/../../infra/infra.php');
	$ans = array();
	$ans['title'] = 'check4';
	infra_require('*infrajs/initphp.php');

	infra_html('<div id="main1"></div><div id="main2"></div>',true);
	$layers=infra_loadJSON('*infrajs/tests/resources/check4.json');
	infra\ext\crumb::change("test");
	infrajs::check($layers);


	$html=infra_html();
	preg_match_all('/x/', $html, $matches);
	$count=sizeof($matches[0]);
	$countneed=2;

	if($count==$countneed)return infra_ret($ans,'ret');
	return infra_err($ans,'err');