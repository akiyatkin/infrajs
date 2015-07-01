<?php

	$dirs=infra_dirs();

	@mkdir($dirs['cache'].'docx/');
	@mkdir($dirs['cache'].'pages_mht/');
	@mkdir($dirs['cache'].'pages_cache/');