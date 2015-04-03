<?php
	//unick:(number),//Уникальное обозначение слоя
	//Нужно для уникальной идентификации какого-то слоя. Для хранения данных слоя в глобальной области при генерации слоя на сервере и его отсутствия на клиенте. Slide
	global $unick_counter;
	global $infra,$infrajs;
	infra_wait($infrajs,'oninit',function(){
		//session и template
		global $infra_template_scope;
		$fn=function($unick){
			return infrajs_getUnickLayer($unick);
		};
		infra_seq_set($infra_template_scope,infra_seq_right('infrajs.getUnickLayer'),$fn);
		$fn=function($name,$value){
			return infrajs_find($name,$value);
		};
		infra_seq_set($infra_template_scope,infra_seq_right('infrajs.find'),$fn);
	});
	$unick_counter=1;

	function infrajs_unickSet(&$layer){
		global $unick_counter;
		if(@!$layer['unick'])$layer['unick']=$unick_counter++;
	}
	function &infrajs_find($name,$value){
		$layers=infrajs_getAllLayers();
		$right=infra_seq_right($name);
		return infrajs_run($layers,function&(&$layer) use($right,$value){
			if(infra_seq_get($layer,$right)==$value)return $layer;
		});
	}
	function &infrajs_getUnickLayer($unick){//depricated
		return infrajs_find('unick',$unick);
	}
?>
