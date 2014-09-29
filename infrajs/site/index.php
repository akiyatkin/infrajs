<?php
	@define('ROOT','./');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*infrajs/init.php');
	infrajs('index.tpl','base_content','infra/layers.json');	
?>
