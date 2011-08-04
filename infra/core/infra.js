var infra = {};
this.infra = infra;

/* Константы, определяются клиентом и браузером отдельно. Приведены дефолтные значения. */
infra.ROOT = ''; // Корень сайта, от которого читается запрашиваемый путь
infra.NODE = false; // Находимся ли мы сейчас на node.js или в браузере
infra.DEBUG = false; // Вывод отладочной информации

/* Обработка ошибок */

/* Вывод ошибки */
infra.error = function(error, callback, name, context, args, msgs, test) {
	if (infra.DEBUG) {
		if(!callback) callback=''; if(!name) name='';
		if(!context) context=''; if(!args) args=''; if(!msgs) msgs=[];
		var em = 'Ошибка в '+name+'\n'+error.name+':'+error.message+'\ncallback:\n'+callback+'\nargs:\n'+args+'\ncontext:'+context+'\nИНФО:\n'+msgs.join('\n')
		if (!infra.NODE) {
			if (!test) alert(em);
		} else {
			if (!test) console.error(em);
		}
		throw error;
	}
}

/* Запуск функции, в которой может быть ошибка */
infra.exec = function(callback, name, context, args, msgs, test) {
	args=args||[];
	try {
		var r=callback.apply(context,args);
		return r;
	} catch(e) {
		infra.error(e, callback, name, context, args, msgs, test)
	}
}

/* Циклы */
/*
	fory - Бежим рекурсивно по массиву объектов, а потом по свойствам объектов y - Oo
	forx - Бежим по объекту а потом по его свойствам как по массивам рекурсивно x - multi
	
	fori - Бежим по объекту рекурсивно - for (var i 
	fora - Бежим по массиву рекурсивно (for Array)
	
	foru - Бежим без разницы объекту или массиву нерекурсивно
	
	//Низкий уровень
	foro - Бежим по объекту (for Object)
	forr - Бежим по массиву (for aRRay)
	
	val,key,group,i
	
	undefined везде пропускается, любой return обрывает цикл
*/
infra.foroa=function(){//depricated
	return infra.forx.apply(this,arguments);
}
infra.fory=function(obj,callback,back){
	return infra.fora(obj,function(v,i){
		return infra.foro(v,function(el,key,group){
			return infra.exec(callback,'infra.fory',this,[el,key,group,i],['back:'+back]);
		},back);
	},back);
}
infra.forx=function(obj,callback,back){//Бежим сначало по объекту а потом по его свойствам как по массивам
	return infra.foro(obj,function(v,key){
		return infra.fora(v,function(el,i,group){
			return infra.exec(callback,'infra.forx',this,[el,key,group,i],[back]);//callback,name,context,args,more
		},back);
	},back);
}
infra.foru=function(obj,callback,back){//Бежим без разницы объекту или массиву
	if(obj&&typeof(obj)=='object'&&obj.constructor==Array){
		return infra.forr(obj,callback,back);//Массив
	}else{
		return infra.foro(obj,callback,back);//Объект
	}
}

infra.fori=function(obj,callback,back,key,group){//Бежим по объекту рекурсивно
	var r,i;
	if(obj&&typeof(obj)=='object'){
		r=infra.foro(obj,function(v,key){
			r=infra.fori(v,callback,back,key,obj)
			if(r!==undefined)return r;
		},back);
		if(r!==undefined)return r;
	}else if(obj!==undefined){
		r=infra.exec(callback,'infra.fori',this,[obj,key,group],[back]);//callback,name,context,args,more
		if(r!==undefined)return r;
	}
}

infra.fora=function(el,callback,back,group,key){//Бежим по массиву рекурсивно
	var r,i;
	if(el&&el.constructor===Array){
		r=infra.forr(el,function(v,i){
			r=this.fora(v,callback,back,v,i);
			if(r!==undefined)return r;
		},back);
		if(r!==undefined)return r;
	}else if(el!==undefined){//Если undefined callback не вызывается, Таким образом можно безжать по переменной не проверя определена она или нет.
		r=infra.exec(callback,'infra.fora',this,[el,key,group],[back]);//callback,name,context,args,more
		if(r!==undefined)return r;
	}
}

infra.forr=function(el,callback,back){//Бежим по массиву
	if(!el)return;
	var r,i;
	if(back){
		for(i=el.length-1;i>=0;i--){
			r=infra.exec(callback,'infra.forr',this,[el[i],i],[back]);//callback,name,context,args,more
			if(r!==undefined)return r;
		}
	}else{
		for(i=0;i<el.length;i++){
			r=infra.exec(callback,'infra.forr',this,[el[i],i],[back]);//callback,name,context,args,more
			if(r!==undefined)return r;
		}
	}
}
infra.foro=function(obj,callback,back){//Бежим по объекту
	if(!obj)return;
	var r,ar=[],key,el,fn=back?'pop':'shift';
	for(key in obj){
		if(obj.hasOwnProperty(key))ar.push({key:key,val:obj[key]});
	}
	whileinfra=ar[fn]()){
		r=infra.exec(callback,'infra.foro',this,[el.val,el.key],[back]);//callback,name,context,args,more
		if(r!==undefined)return r;
	}
}

infra.each=function(elem,callback,back,group,key){//Возвращает undefined или то что было возвращено callback
	var r;//depricated
	if(elem&&elem.constructor===Array){
		if(back){
			for(var i=elem.length-1;i>=0;i--){
				r=this.each(elem[i],callback,back,elem,i);
				if(r!==true)return r;
			}
		}else{
			for(var i=0;i<elem.length;i++){
				r=this.each(elem[i],callback,back,elem,i);
				if(r!==true)return r;
			}
		}
	}else if(elem!==undefined){//Если undefined callback не вызывается, Таким образом можно безжать по переменной не проверя определена она или нет.
		try{
			r=callback.apply(this,[elem,group,key]);
			if(r!==undefined)return r;
		}catch(e){
			if(infra.debug)alert('Ошибка в infra.each\n'+e+'\nelem:\n'+elem+'\ncallback:\n'+callback+'\nback:\n'+back);
			throw e;
		}
	}
	return true;
}

/* Одинаковое api для загрузки слоев и расширений. */

/* События */

/* Подключение контролера (check) */

/* Загрузка расширений, могут быть разные для браузера и для клиента */
