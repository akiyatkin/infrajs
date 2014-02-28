//Свойство css
	if(infrajs.external)infrajs.external.add('css','external');
	infra.listen(infra,'layer.onparse',function(){
		var layer=this;
		if(!layer.css)return;
		infra.fora(layer.css,function(css){
			infra.load(css,'c');
		});
	});
