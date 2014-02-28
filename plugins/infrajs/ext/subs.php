<?php
/**
 * Расширение subs
 * Суть расширения сводится к ключам значения которых автоматически подставятся в ряд свойст слоя указанного в subs
 * а именно tpl установится такой же как у родителя, 
 * tplroot будет как ключ в subs
 * и будет div будет как ключ
 * После external
 */
/**
 * Теперь infrajs.run(layers) будет бегать по слоям описанным и в свойстве subs
 * object значит что subs будет восприниматься как объект свойства которого это массивы слоёв
 */
global $infrajs;
infra_wait($infrajs,'oncheck',function(){
	infrajs_runAddKeys('subs');
	/**
	 * subs, указанное в external, объединяется с основным описанием также, как свойство divs (как именно не помню)
	 */
	infrajs_externalAdd('subs','divs');
	/**
	 * В onchange проверяется наличие этого свойства и предустанавливаются необходимые свойства слоёв если таковые будут найдены
	 */
});

function infrajs_subMake(&$layer){
	if(@!$layer['parent'])return;
	if(@!$layer['parent']['subs'])return;
	//forx бежим по свойствам объекта, как по массивам. Массивы могут быть вложенные
	//var_dump($layer['parent']['subs']);
	$key=infra_forx($layer['parent']['subs'],function(&$layer, &$l,$key){//Такую пробежку у родителя сразу для всех детей делать не нельзя, так как external у детей ещё не сделан.
		if(infra_isEqual($layer,$l))return $key;//Ага, текущей слой описан у родителя в subs. Любой return останавливает цикл и возвращает иначе key был бы undefined.
	},array(&$layer));
	if($key){//Так так теперь предопределяем свойства
		//div не круче external.(но в external div не указывается) в  tpl и tplroot не круче
		if(@!$layer['div'])$layer['div']=$key;
		if(@!$layer['tpl'])$layer['tpl']=$layer['parent']['tpl'];
		if(@!$layer['tplroot'])$layer['tplroot']=$key;
	}
}
?>
