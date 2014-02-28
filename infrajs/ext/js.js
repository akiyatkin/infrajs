//Свойство js
	if(infrajs.external)infrajs.external.add('js',function(now,ext){
		infra.fora(ext,function(script){
			infra.load(script,'e');
		});
	});
	infra.listen(infra,'layer.oninit',function(){
		var layer=this;
		if(layer.js){//Загружаем внешние обработки свойств
			infra.fora(layer.js,function(script){
				infra.load(script,'e');
			});
			delete layer.js;
		}
	});
