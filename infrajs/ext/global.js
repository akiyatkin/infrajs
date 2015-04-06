//global:(bool);// проверка есть tpl или нет. Если tpl будет загружен пустой слой не покажется
//globalignoredata:(bool); загружать повторно данные или нет
infra.wait(infrajs,'oninit',function(){
	infrajs.externalAdd('global','external');
	infrajs.parsedAdd(function(layer){
		if(!layer.global)return '';
		var s='';
		infra.fora(layer.global,function(g){
			g=infrajs.global.get(g);
			s+=g.value+':';
		});
		return s;
	});
});

infrajs.global={
	globals:{},
	get:function(name){
		if(!this.globals[name]){
			this.globals[name]={
				value:0,
				unloads:{},
				layers:[]
			};
		}
		return this.globals[name];
	},
	unload:function(name,path){
		infra.fora(name,function(n){
			var g=this.get(n);
			g.unloads[path]=true;
		}.bind(this));
	},
	counter:1,
	set:function(names){
		infra.fora(names,function(name){
			var g=this.get(name);
			g.value=this.counter++;
			for(var path in g.unloads){
				infra.unload(path);
			}
			
			for(var i=0,l=g.layers.length;i<l;i++){
				var layer=g.layers[i];
				if(!layer.onsubmit)continue;
				if(!layer.config)continue;
				delete layer.config.ans;
			}
			
		}.bind(this));
		
	}
	//,onsubmit:[]
}
infrajs.checkGlobal=function(layer){
	if(!layer.global)return;
	//if(layer.globaled)return;
	//layer.globaled=true;
	/*if(layer.onsubmit){
		infrajs.global.onsubmit.push(layer);
	}*/
	var json='';
	if(layer.json){
		if(layer.json.constructor==Array){
			json=layer.json[0];
		}else{
			json=layer.json;
		}
	}
	infra.fora(layer.global,function(n){
		var g=infrajs.global.get(n);
		if(json){
			g.unloads[json]=true;
		}
		//if(layer.onsubmit){
			g.layers.push(layer);
		//}
	});
}