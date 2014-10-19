
if (!Function.prototype.bind){
	Function.prototype.bind = function(object,arguments){
		var that = this;//функция у которой нужно сделать this как object

		var func=function() {
			return that.apply(object,arguments);
		}
		if(!func.binded){
			func.binded=true;//Первый bind важней второго
			func.toString=function(){
				return that.toString()+'\nbinded: '+object;//Два бинда не должны приводить к рекурсии
			};
		}
		return func;
	}
}
infra.foro=function(obj,callback,back){//Бежим по объекту
	if(!obj||typeof(obj)!=='object')return;
	var r,ar=[],key,el,fn=back?'pop':'shift';
	for(key in obj){
		if(((!obj.hasOwnProperty||obj===location)&&obj[key])||obj.hasOwnProperty(key))ar.push({key:key,val:obj[key]});
	}
	while(el=ar[fn]()){
		if(infra.isNull(el.val))continue;
		//r=infra.exec(callback,'infra.foro',[el.val,el.key,obj],[back]);//callback,name,context,args,more
		r=callback.apply(infra,[el.val,el.key,obj]);
		if(infra.isNull(r))continue;
		if(r instanceof infra.Fix){
			if(r.opt.del){
				delete obj[el.key];
			}
			if(!infra.isNull(r.ret))return r.ret;
		}else{
			return r;
		}
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
	if(el instanceof Array){
		r=infra.forr(el,function(v,i){
			r=infra.fora(v,callback,back,el,i);
			if(!infra.isNull(r))return r;
		},back);
		if(!infra.isNull(r))return r;
	}else if(!infra.isNull(el)){
		r=callback.apply(infra,[el,key,group]);
		return r;
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
infra.Fix=function(opt,ret){
	if(typeof(opt)=='string'){
		if(opt=='del'){
			opt={
				del:true,
				ret:ret
			}
		}
	}
	this.opt=opt;//Класс сиганала об изменении массива
}
infra.isNull=function(r){
	if(r===undefined)return true;
	if(r===null)return true;
	return false;
}
infra.forr=function(el,callback,back){//Бежим по массиву
	if(!(el instanceof Array))return;
	var r,i,l;

	if(back){
		for(i=el.length-1;i>=0;i--){
			if(infra.isNull(el[i]))continue;
			r=callback.apply(infra,[el[i],i,el]);//callback,name,context,args,more
			if(infra.isNull(r))continue;
			if(r instanceof infra.Fix){
				if(r.opt.del){
					el.splice(i,1);
				}
				if(!infra.isNull(r.ret))return r.ret;
			}else{
				return r;
			}
		}
	}else{
		for(i=0,l=el.length;i<l;i++){//В callback нельзя удалять... так как i сместится
			if(infra.isNull(el[i]))continue;
			r=callback.apply(infra,[el[i],i,el]);//callback,name,context,args,more
			if(infra.isNull(r))continue;
			if(r instanceof infra.Fix){
				if(r.opt.del){
					el.splice(i,1);
					l--;
					i--;
				}
				if(!infra.isNull(r.ret))return r.ret;
			}else{
				return r;
			}
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