<?php
	//unick:(number),//Уникальное обозначение слоя
	//Нужно для уникальной идентификации какого-то слоя. Для хранения данных слоя в глобальной области при генерации слоя на сервере и его отсутствия на клиенте. Slide
	global $unick_counter;
	global $infra,$infrajs;
	infra_wait($infrajs,'oncheck',function(){
		//session и template
		global $infra_template_scope;
		$fn=function($unick){
			return infrajs_getUnickLayer($unick);
		};
		infra_seq_set($infra_template_scope,infra_seq_right('infrajs.getUnickLayer'),$fn);
	});
	$unick_counter=1;

	function infrajs_unickSet(&$layer){
		global $unick_counter;
		if(@!$layer['unick'])$layer['unick']=$unick_counter++;
	}

	function &infrajs_getUnickLayer($unick){
		$layers=infrajs_getAllLayers();
		return infrajs_run($layers,function&(&$layer) use($unick){
			if(isset($layer['unick'])&&$layer['unick']==$unick)return $layer;
		});
	}
?>
