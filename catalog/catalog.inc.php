<?php
	infra_require('*files/xls.php');
	function cat_init(){
		$conf=infra_config();
		$data=infra_cache(array($conf['catalog']['dir']),'cat_init',function(){
			$conf=infra_config();

			$data=&xls_init2($conf['catalog']['dir'],array('Имя файла'=>$conf['catalog']['Имя файла']));
			cat_prepareData($data);
			return $data;
		});
		return $data;
	}
	function cat_prepareData(&$data){
		xls_runGroups($data,function(&$gr){
			$gr['name']=$gr['descr']['Наименование'];
			if(!$gr['name'])$gr['name']=$gr['title'];
			infra_forr($gr['data'],function(&$pos) use(&$gr){
				$pos['group_name']=$gr['name'];
			});
		});
	}
?>
