<?php
	$dirs=infra_dirs();
	@mkdir($dirs['cache'].'admin_takefiles/');
	@mkdir($dirs['backup'].'admin_deletedfiles/');