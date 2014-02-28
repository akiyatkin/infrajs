//preload
infrajs.preload={
	load:{
		before:{//Загружается в oninit
			'external':true,
			'js':true
		},
		after:{
			'data':true,
			'tpl':true,
			'css':true
		}
	},
	add:function(when,prop){
		this.load[when][prop]=true;
	},
	getLoad:function(layer,type,loads){
		var loads=loads||[];
		infra.fora(layer,function(layer){
			for(var prop in this.load[type]){
				infra.fora(layer[prop],function(f){
					if(typeof(f)=='string')loads.push(f);
				});
			}
		});
		return loads;
	}
}
infrajs.listen(infrajs,'oninit',function(){
	var loads=[];
	var stor=this.stor();
	this.run(stor.wlayers,function(layer,parent){//Обработка презагрузки, нельзя вынести из ядра, так как загрузка after происходит после определения списка показываемых слоёв и до их показа, а там нет никаких событий для этого, есть только onshow но там уже во всю идут единичные загрузки путей...
		//this.preload.getLoad(layer,'befo re',loads);//Ну подумаешь, вложенные external не будут предзагружены а так нашли все без разбора будет слой показан или нет
	});
	//js.loadMulti(loads);//Загрузили все External
});
infrajs.listen(infrajs,'onparse',function(){
	var loads=[];
	var stor=this.stor();
	for(var i in stor.divs){
		//this.preload.getLoad(this.divs[i],'after',loads);
	} //js.loadMulti(loads);
});
