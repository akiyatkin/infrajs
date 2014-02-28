/*
1 будущее событие
*/

infra.fire=function(obj,clsfn,argso){
	if(obj!==infra) clsfn=clsfn.split('.');
	else clsfn=[clsfn];

	var cls=(clsfn.length>1)?clsfn.shift():'';
	var fn=clsfn.join('.');

	if(cls){
		var depot=infra.fire.depot(infra,cls+'.'+fn);	
	}else{
		var depot=infra.fire.depot(obj,fn);
	}
	depot.evt={
		context:obj,
		args:[obj].concat(argso||[])//Аргументы которые передаются в callback
	};
	//Если класс, то у непосредственно объекта вообще ничего не храниться
	for(var i=0,l=depot.listen.length;i<l;i++){
		var r=depot.exec(depot.listen[i]);
	}
}

infra.listen=function(obj,fn,callback){
	var depot=infra.fire.depot(obj,fn);
	depot.listen.push(callback);
}
/*
infra.fire(layer1,'layer.onshow');
infra.fire(layer2,'layer.onshow');
infra.wait(infra,'layer.onshow',function(layer2){

});*/
infra.when=function(obj,fn,callback){ //При первом следующем
	var depot=infra.fire.depot(obj,fn);
	depot.listen.push(function(){
		infra.unlisten(obj,fn,arguments.callee);
		return callback.apply(this,arguments);
	});
}
infra.wait=function(obj,fn,callback){//depricated, для классов не подходит
	var depot=infra.fire.depot(obj,fn);
	if(depot.evt){
		depot.exec(callback);
	}else{
		depot.listen.push(function(){
			infra.unlisten(obj,fn,arguments.callee);
			return callback.apply(this,arguments);
		});
	}
}
infra.handle=function(obj,fn,callback){//depricated, для классов не подходит
	var depot=infra.fire.depot(obj,fn);
	if(depot.evt){
		depot.exec(callback);
	}
	depot.listen.push(callback);
}
infra.fire.empty=function(){};
infra.unlisten=function(obj,fn,callback){
	var depot=infra.fire.depot(obj,fn);
	for(var i=0,l=depot.listen.length;i<l;i++){
		if(depot.listen[i]===callback){
			depot.listen[i]=infra.fire.empty;
		}
	}
}
infra.fire.depot=function(obj,fn){
	var n='__infra_fire_depot__';
	if(!obj[n])obj[n]={};
	if(!obj[n][fn])obj[n][fn]={//При повторном событии этот массив уже будет создан
		listen:[],//Массив всех подписчиков
		evt:undefined,//Событие ещё не состоялось, обновляется при каждом событии
		exec:function(callback){//выполняем подписчика
			var depot=this;
			var r=callback.apply((depot.evt.context||obj),depot.evt.args);
			if(r!==undefined){
				depot.free=false;//Метка что событие оборвалось
				return r;
			}
		}
	};
	return obj[n][fn];
}