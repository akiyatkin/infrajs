<?php

$dirs = infra_dirs();

$conf = infra_config();

if ($conf['infra']['cache'] == 'fs') {
	if (is_dir($dirs['cache'].'imager_resize/')) {
		mkdir($dirs['cache'].'imager_resize/');
	}
	if (is_dir($dirs['cache'].'imager_gray/')) {
		mkdir($dirs['cache'].'imager_gray/');
	}
	if (is_dir($dirs['cache'].'imager_remote/')) {
		mkdir($dirs['cache'].'imager_remote/');
	}
}
if ($conf['imager']['watermark']) {
	if (is_dir($dirs['data'].'imager/')) {
		mkdir($dirs['data'].'imager/');
	}
	if (is_dir($dirs['data'].'imager/.notwater//')) {
		mkdir($dirs['data'].'imager/.notwater//');
	}
	if (!is_dir($dirs['backup'])) {
		mkdir($dirs['backup']);
	}
	if (is_dir($dirs['backup'].'imager_orig/')) {
		mkdir($dirs['backup'].'imager_orig/');
	}
}
