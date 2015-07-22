	//Свойство reload используется в session/list.layer.js
	infra.listen(infra,'layer.oncheck',function(layer){
		if(layer.reload&&typeof(layer.json)=='string')infra.unload(layer.json);
	});