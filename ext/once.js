infrajs.onceLayer=function(layer,name,call,args){
	var store=infrajs.storeLayer(layer);
	if(!store.once_layer)store.once_layer={};
	data=store.once_layer;
	if(!data[name])data[name]={};
	data=data[name];
	var hash=JSON.stringify(args);
	if(data[hash])return data[hash].res;
	data[hash]={res:call.apply(this,args)};
	return data[hash].res;
}