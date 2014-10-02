<?php
/*
	config
	configinherit:(bool)
*/
function infrajs_configtpl(&$layer){
	$name='config';//stencil//
	$nametpl=$name.'tpl';
	if(isset($layer[$nametpl])){
		if(!isset($layer[$name]))$layer[$name]=array();
		foreach($layer[$nametpl] as $i=>$v){
			$layer[$name][$i]=infra_template_parse(array($layer[$nametpl][$i]),$layer);
		}
	}
}
function infrajs_configinit(){
	infrajs_externalAdd('configtpl',function&(&$now,&$ext,&$layer,&$external,$i){
		//if(!isset($layer['configtpl']))return $now;
		//if(isset($layer['config']))return $now;
		if(!$now)return $ext;
		return $now;
	});
}
function infrajs_configinherit($layer){
	if(isset($layer['configinherit'])){
		$layer['config']=$layer['parent']['config'];
		unset($layer['configinherit']);
	}
}

?>
