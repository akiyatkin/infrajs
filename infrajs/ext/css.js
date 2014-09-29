//Свойство css

	infra.listen(infra,'layer.onparse',function(){
		
	});
	infrajs.csscheck=function(layer){
			if(!layer.css)return;
			infra.fora(layer.css,function(css){
				infra.loadCSS(css);
			});
		}