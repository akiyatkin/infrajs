<?php
use itlife\infrajs\Infrajs;
use itlife\infra;

$ans = array();
$ans['title'] = 'check3';

infra_require('*infrajs/make.php');

infra_html('<div id="main"></div>', true);

$layers=infra_loadJSON('*infrajs/tests/resources/check3.json');

infra\ext\Crumb::change("test");

Infrajs::check($layers);

$html=infra_html();
preg_match_all('/x/', $html, $matches);
$count=sizeof($matches[0]);
$countneed=4;

if ($count==$countneed) {
	return infra_ret($ans, 'ret');
}
return infra_err($ans, 'err');
