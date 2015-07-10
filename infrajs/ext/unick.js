//unick:(number),//Уникальное обозначение слоя
//Нужно для уникальной идентификации какого-то слоя. Для хранения данных слоя в глобальной области при генерации слоя на сервере и его отсутствия на клиенте. Slide

(function(){
	var counter=1;
	infrajs.unickInit=function(){
		infra.seq.set(infra.template.scope,infra.seq.right('infrajs.find'),function(){
			return infrajs.find.apply(infrajs,arguments);
		});
		infra.seq.set(infra.template.scope,infra.seq.right('infrajs.unicks'),infrajs.unicks);
	}
	infrajs.unicks={};
	infrajs.unickCheck=function(layer){
		if(!layer.unick)layer.unick=counter++;
		infrajs.unicks[layer.unick]=layer;
	}
	infrajs.find=function(name,value){
		var right=infra.seq.right(name);
		var r=infrajs.run(infrajs.getAllLayers(),function(layer){
			if(infra.seq.get(layer,right)==value)return layer;
		});
		if(r)return r;
		return infrajs.run(infrajs.getWorkLayers(),function(layer){ //В работе могут быть слои которые небыли добавлены к общему списку
			if(infra.seq.get(layer,right)==value)return layer;
		});
	}
	infrajs.getUnickLayer=function(unick){//depricated infrajs.find('unick',unick);
		return infrajs.find('unick',unick);
	}
	
})();
