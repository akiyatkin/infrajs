//Свойство reload используется в session/list.layer.js
infra.listen(infra,'layer.onchange.before',function(){
	var layer=this;
	if(layer.reload&&layer.data)infra.unload(layer.data);
});