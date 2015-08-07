<?php

use itlife\infrajs\Infrajs;

$ans = array();
$ans['title'] = 'isEqual';

$l = array('tpl' => 'asdf','test' => 'bad');

$layers = array(&$l);
$msg = 'Maybe good';

infra_require('*infrajs/make.php');
$layer = &Infrajs::run($layers, function &(&$layer) use ($msg) {
	$layer['test'] = $msg;

	return $layer;
});

$l['test'] = 'Good';
if ($l['test'] != $layer['test']) {
	return infra_err($ans, 'err');
}

return infra_ret($ans, 'ret');
