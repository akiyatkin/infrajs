<?php
//Свойство css
	if(function_exists('infrajs_external_add'))infrajs_external_add('css','external');
	global $infra;
	infra_listen($infra,'layer.oninsert',function($layer){
		if(@!$layer['css'])return;
		infra_fora($layer['css'],function(&$layer, $css){
			$code=infra_load($css,'ft');
			infra_html('<style>'.$code.'</style>',$layer['div']);
		},array(&$layer));
	});
?>
