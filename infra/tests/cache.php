<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');

	echo 2;
	infra_admin_cache('asdf',function(){
		infra_cache_no();
		echo 1;
	});