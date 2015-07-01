<?php
	infra_require('*files/xls.php');
	function cat_init(){
		$conf=infra_config();
		$data=infra_cache(array($conf['catalog']['dir']),'cat_init',function(){
			$conf=infra_config();

			$data=&xls_init($conf['catalog']['dir'],array('Имя файла'=>$conf['catalog']['Имя файла']));

			
			return $data;
		});
		return $data;
	}