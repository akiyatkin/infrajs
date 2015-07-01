<?php
	$dirs=infra_dirs();
	@mkdir($dirs['data'].'imager/');
	@mkdir($dirs['data'].'imager/.notwater/');
	@mkdir($dirs['backup'].'imager_orig/');