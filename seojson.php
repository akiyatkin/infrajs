<?php

	use itlife\infrajs\ext\seojson;

$src = $_SERVER['QUERY_STRING'];
	$seo = seojson::load($src);

return infra_ans($seo);
