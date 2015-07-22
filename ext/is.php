<?php
namespace itlife\infrajs\ext;
class is{
	function check(&$layer){
		if(!isset($layer['is'])||is_null($layer['is'])){
			$is=true;
		}else{
			$is=$layer['is'];
		}
		if($is=='0')$is=false;//В шаблоне false не удаётся вернуть
		return $is;
	}
	function istpl(&$layer){
		$prop='is';
		$proptpl=$prop.'tpl';
		if(!isset($layer[$proptpl]))return;
		$p=$layer[$proptpl];
		$p=infra_template_parse(array($p),$layer);
		$layer[$prop]=$p;
	}
}