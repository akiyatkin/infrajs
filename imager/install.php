<?php
	$dirs=infra_dirs();

	@mkdir($dirs['cache']);
	@mkdir($dirs['cache'].'imager_resize/');

	$conf=infra_config();
	if($conf['imager']['watermark']){
		@mkdir($dirs['data'].'imager/');
		@mkdir($dirs['data'].'imager/.notwater/');
		@mkdir($dirs['backup'].'imager_orig/');
	}