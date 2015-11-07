//Свойство div divcheck

infrajs.div_init=function(layer){
	infrajs.runAddKeys('divs');
	infrajs.externalAdd('divs',function(now,ext){//Если уже есть пропускаем
		if(!now)now={};
		for(var i in ext){
			if(now[i])continue;
			now[i]=[];
			infra.fora(ext[i],function(l){
				now[i].push({external:l});
			});
		}
		return now;
	});
}
infrajs.divtpl=function(layer){
	if(!layer['divtpl'])return;
	layer['div']=infra.template.parse([layer['divtpl']],layer);
}
infrajs.divCheck=function(layer){
	var start=false;
	if(infrajs.run(infrajs.getWorkLayers(),function(l){//Пробежка не по слоям на ветке, а по всем слоям обрабатываемых после.. .то есть и на других ветках тоже
		if(!start){
			if(layer===l)start=true;
			return;
		}
		if(l.div!==layer.div)return;//ищим совпадение дивов впереди
		if(infrajs.is('show',l)){
			infrajs.isSaveBranch(layer,infrajs.isParent(l,layer))
			return true;//Слой который дальше показывается в томже диве найден
		}
	}))return false;
}
