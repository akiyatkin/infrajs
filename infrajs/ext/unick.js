//unick:(number),//Уникальное обозначение слоя
//Нужно для уникальной идентификации какого-то слоя. Для хранения данных слоя в глобальной области при генерации слоя на сервере и его отсутствия на клиенте. Slide
infra.wait(infrajs,'oninit',function(){
	//session и template
	infra.seq.set(infra.template.scope,infra.seq.right('infrajs.getUnickLayer'),function(unick){
		return infrajs.getUnickLayer(unick);
	});
});
(function(){
	var counter=1;
	infrajs.unickSet=function(layer){
		if(!layer.unick)layer.unick=counter++;
	}
	
	infrajs.getUnickLayer=function(unick){
		var r=infrajs.run(infrajs.getAllLayers(),function(layer){
			if(layer.unick==unick)return layer;
		});
		if(r)return r;
		return infrajs.run(infrajs.getWorkLayers(),function(layer){
			if(layer.unick==unick)return layer;
		});
	}
	
})();
