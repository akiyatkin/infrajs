infra.foro=function(obj,callback,back){//Бежим по объекту
	if(!obj||typeof(obj)!=='object')return;
	var r,ar=[],key,el,fn=back?'pop':'shift';
	for(key in obj){
		if(((!obj.hasOwnProperty||obj===location)&&obj[key])||obj.hasOwnProperty(key))ar.push({key:key,val:obj[key]});
	}
	while(el=ar[fn]()){
		if(el.val===undefined)continue;
		//r=infra.exec(callback,'infra.foro',[el.val,el.key,obj],[back]);//callback,name,context,args,more
		r=callback.apply(infra,[el.val,el.key,obj]);
		if(r!==undefined)return r;
	}
};
infra.fori=function(obj,callback,back,key,group){//Бежим по объекту рекурсивно
	var r,i;
	if(obj&&typeof(obj)==='object'){
		r=infra.foro(obj,function(v,key){
			r=infra.fori(v,callback,back,key,obj)
			if(r!==undefined)return r;
		},back);
		if(r!==undefined)return r;
	}else if(obj!==undefined){
		//r=infra.exec(callback,'infra.fori',[obj,key,group],[back]);//callback,name,context,args,more
		r=callback.apply(infra,[obj,key,group]);//callback,name,context,args,more
		if(r!==undefined)return r;
	}
};
infra.fora=function(el,callback,back,group,key){//Бежим по массиву рекурсивно
	var r,i;

	if(el&&el.constructor===Array){
		r=infra.forr(el,function(v,i){
			r=infra.fora(v,callback,back,el,i);
			if(r!==undefined)return r;
		},back);
		if(r!==undefined)return r;
	}else if(el!==undefined){//Если undefined callback не вызывается, Таким образом можно безжать по переменной не проверя определена она или нет.
		//r=infra.exec(callback,'infra.fora',[el,key,group],[back]);//callback,name,context,args,more
		r=callback.apply(infra,[el,key,group]);//callback,name,context,args,more
		if(r!==undefined)return r;
	}
};
infra.forc=function(obj,path,callback,i){// 'layer'  ['childs']
	/*
	 * Бежим в глубь объекта по пути переданному в path и для каждого уровня запускаем обработчик
	 * */
	if(typeof(obj)==='undefined')return;

	//var r=infra.exec(callback,'infra.forc',[obj,i],[back]);//callback,name,context,args,more
	var r=callback.apply(infra,[obj,i]);//callback,name,context,args,more
	if(r!==undefined)return r;

	if(typeof(i)==='undefined')i=0;
	else i++;

	if(typeof(path[i])==='undefined')return;
	infra.forc(obj[path[i]],path,callback,i);
}
infra.forr=function(el,callback,back){//Бежим по массиву
	if(!el)return;
	var r,i;
	var l=el.length;
	if(back){
		for(i=l-1;i>=0;i--){
			if(el[i]===undefined)continue;
			//r=infra.exec(callback,'infra.forr',[el[i],i,el],[back]);//callback,name,context,args,more
			r=callback.apply(infra,[el[i],i,el]);//callback,name,context,args,more
			if(r!==undefined)return r;
		}
	}else{
		for(i=0;i<l;i++){//В callback нельзя удалять... так как i сместится
			if(el[i]==undefined)continue;
			//r=infra.exec(callback,'infra.forr',[el[i],i,el],[back]);//callback,name,context,args,more
			r=callback.apply(infra,[el[i],i,el]);//callback,name,context,args,more
			if(r!==undefined)return r;
		}
	}
};
infra.foru=function(obj,callback,back){//Бежим без разницы объекту или массиву
	if(obj&&typeof(obj)=='object'&&obj.constructor===Array){
		return infra.forr(obj,callback,back);//Массив
	}else{
		return infra.foro(obj,callback,back);//Объект
	}
};
infra.forx=function(obj,callback,back){//Бежим сначало по объекту а потом по его свойствам как по массивам
	return infra.foro(obj,function(v,key){
		return infra.fora(v,function(el,i,group){
			//return infra.exec(callback,'infra.forx',[el,key,group,i],[back]);//callback,name,context,args,more
			return callback.apply(infra,[el,key,group,i]);//callback,name,context,args,more
		},back);
	},back);
};
infra.fory=function(obj,callback,back){//Бежим по свойствам объектов в массиве
	return infra.fora(obj,function(v,i){
		return infra.foro(v,function(el,key,group){
			//return infra.exec(callback,'infra.fory',[el,key,group,i],['back:'+back]);
			return callback.apply(infra,[el,key,group,i]);
		},back);
	},back);
}