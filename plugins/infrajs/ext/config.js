/*
	configinherit:(bool)
*/
infrajs.configinit=function(){
	infrajs.externalAdd('configtpl',function(now,ext,layer,external,i){
		if(!now)return ext;
		return now;
	});
}
infrajs.configtpl=function(layer){
	var name='config';//stencil//
	var nametpl=name+'tpl';
	if(layer[nametpl]){
		if(!layer[name])layer[name]={};
		for(var i in layer[nametpl]){
			layer[name][i]=infra.template.parse([layer[nametpl][i]],layer);
		}
	}
}
infrajs.configinherit=function(layer){
	if(layer.configinherit){
		layer.config=layer.parent.config;
		delete layer.configinherit;
	}
}