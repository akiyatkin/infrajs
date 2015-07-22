<?php
	
	require_once(__DIR__.'/../../infra/infra.php');
	infra_require('*infrajs/initphp.php');
	use itlife\infrajs\Infrajs;
	$ans = array();
	$ans['title'] = 'проверка функции infrajs::check';

	infra_html('<div id="oh"></div>');

	$layer=array('tpl'=>array('хой'),"div"=>"oh");
	infrajs::check($layer);

	$html=infra_html();

	if($html!='<div id="oh">хой</div>') return infra_err($ans, 'ошибка');
	return infra_ret($ans, 'работает');