<?php
use itlife\infrajs\Infrajs;
use itlife\infra;

$ans = array();
$ans['title'] = 'check_ext_childs';
infra_require('*infrajs/make.php');

infra_html('<div id="main1"></div><div id="main2"></div>', true);
$layers=infra_loadJSON('*infrajs/tests/resources/check_ext_childs.json');
infra\ext\crumb::change("test");
Infrajs::check($layers);

$html=infra_html();
preg_match_all('/x/', $html, $matches);
$count=sizeof($matches[0]);
$countneed=2;

if ($count==$countneed) {
	return infra_ret($ans, 'ret');
}
return infra_err($ans, 'err');
