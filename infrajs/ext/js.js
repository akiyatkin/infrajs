//Свойство js
infrajs.jscheck=function(layer){
	if(!layer.js) return;
	//Загружаем внешние обработки свойств
	infra.fora(layer.js,function(script){
		infra.require(script);
	});
	delete layer.js;
}