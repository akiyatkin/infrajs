<?php
use itlife\infrajs\Infrajs;

itlife\infra\ext\crumb::change('test');
infra_require('*infrajs/make.php');

$ans = array();
$ans['title'] = 'проверка чек';

infra_html('<div id="main"></div>');

$layers=infra_loadJSON('*infrajs/tests/resources/check2.json');
Infrajs::check($layers);

$layer=&$layers['layers'];

$html=infra_html();

preg_match_all('/x/', $html, $matches);
$count=sizeof($matches[0]);

if ($count!=4) {
		return infra_err($ans, 'нууль '.$count);
}
return infra_ret($ans, 'daa');
