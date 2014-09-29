/**
 * Расширение subs
 * Суть расширения сводится к ключам значения которых автоматически подставятся в ряд свойст слоя указанного в subs
 * а именно tpl установится такой же как у родителя, 
 * tplroot будет как ключ в subs
 * и будет div будет как ключ
 */
/**
 * Теперь infrajs.run(layers) будет бегать по слоям описанным и в свойстве subs
 * object значит что subs будет восприниматься как объект свойства которого это массивы слоёв
 */
infra.wait(infrajs,'oninit',function(){
	infrajs.runAddKeys('subs');
	infrajs.externalAdd('subs','divs');
});

/**
 * subs, указанное в external, объединяется с основным описанием также, как свойство divs (как именно не помню)
 */

/**
 * В onchange проверяется наличие этого свойства и предустанавливаются необходимые свойства слоёв если таковые будут найдены
 */

infrajs.subMake=function(layer){
	if(!layer.parent)return;

	if(layer.parent.subs){
		//forx бежим по свойствам объекта, как по массивам. Массивы могут быть вложенные
		var key=infra.forx(layer.parent.subs,function(l,key){//Такую пробежку у родителя сразу для всех детей делать не нельзя, так как external у детей ещё не сделан.
			if(layer===l)return key;//Ага, текущей слой описан у родителя в subs. Любой return останавливает цикл и возвращает иначе key был бы undefined.
		});
		if(key){//Так так теперь предопределяем свойства
			//div не круче external.(но в external div не указывается) в  tpl и tplroot не круче
			//div наследуется от родителя
			layer.div=key;
			layer.sub=true;
		}
	}
	if(layer.sub){
		//if(!layer.div)layer.div=key;
		if(!layer.tpl)layer.tpl=layer.parent.tpl;
		if(!layer.tplroot)layer.tplroot=layer.div;
	}
}

