<?php
	@define('ROOT','./');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	/*
		порядок подключения не должен иметь значения
	*/
		
	infra_require('*infra/ext/html.php');
	infra_require('*infra/ext/htaccess.php');
	infra_require('*infrajs/infrajs.php');//

	infra_require('*infrajs/ext/external.php');//
	infra_require('*infrajs/ext/state.php');//
	infra_require('*infrajs/ext/subs.php');//
	infra_require('*infrajs/ext/div.php');//
	infra_require('*infrajs/ext/tpl.php');//
	infra_require('*infrajs/ext/layers.php');//
	infra_require('*infrajs/ext/unick.php');//
	infra_require('*infrajs/ext/is.php');//
	infra_require('*infrajs/ext/env.php');//
	infra_require('*infrajs/ext/css.php');//
	infra_require('*infrajs/ext/autosave.php');//
	infra_require('*infrajs/ext/config.php');//
	infra_require('*infrajs/ext/parsed.php');//
	infra_require('*infrajs/ext/seojson.php');//
	infra_require('*seo/seo.ext.php');//

	infra_require('*session/session.php');

	infra_require('*infrajs/ext/session.php');//

	infra_require('*infrajs/make.php');

?>