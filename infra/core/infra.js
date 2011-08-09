var infra = {};
this.infra = infra;

/* Константы, определяются клиентом и браузером отдельно. Приведены дефолтные значения. */
infra.ROOT = ''; // Корень сайта, от которого читается запрашиваемый путь
infra.NODE = false; // Находимся ли мы сейчас на node.js или в браузере
infra.DEBUG = false; // Вывод отладочной информации

/* Общее */
Function.prototype.bind = function(object,arguments){
	var that = this;
	var func=function() {
		return that.apply(object,arguments);
	}
	func.toString=function(){
		return that.toString()+'\nbinded: '+object;
	};
	return func;
}
infra.bind=function(object,func){
	return function() {
		return func.apply(object,arguments)
	}
}
function bindReady(handler){
	var called = false
	function ready() {
		if (called) return
		called = true
		handler()
	}
	if ( document.addEventListener ) {
		document.addEventListener( "DOMContentLoaded", function(){
			ready()
		}, false )
		window.addEventListener( "load", ready(), false );
	} else if ( document.attachEvent ) {
		if ( document.documentElement.doScroll && window == window.top ) {
			function tryScroll(){
				if (called) return
				if (!document.body) return
				try {
					document.documentElement.doScroll("left")
					ready()
				} catch(e) {
					setTimeout(tryScroll, 0)
				}
			}
			tryScroll()
		}
		document.attachEvent("onreadystatechange", function(){

			if ( document.readyState === "complete" ) {
				ready()
			}
		})
	}
    if (window.addEventListener)
        window.addEventListener('load', ready, false)
    else if (window.attachEvent)
        window.attachEvent('onload', ready)
    /*  else window.onload=ready */
}
infra.last_unick=0;//time последней точки
infra.first_unick=new Date().getTime();//Начало отсчёта для всех точек
infra.getUnick=function(){//Возвращаем всегда уникальную метку, цифры
	var m=new Date().getTime();
	m-=infra.first_unick;//Отсчёт всего времени идёт с момента загрузки страницы в миллисекундах
	var last_unick=infra.last_unick||m;
	while(last_unick>=m)m++;
	infra.last_unick=m;
	return m;
}

/*
 * Обработка ошибок
 * */

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

/*
 * Циклы
 * */

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
	while(el=ar[fn]()){
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

/*
 * Одинаковое api для загрузки слоев и расширений.
 * */
infra.buffer=[];
infra.buffer_load=[];
infra.bufferOn=function(){
	infra.buff=true;
}
infra.bufferAdd = function(type,path){
	infra.buffer.push({type:type,path:path,toString:function(){return path}});
	infra.buffer_load.push(path);
}
infra.bufferOff = function(){
	infra.buff=false;
	infra.loadMulti(infra.buffer_load);
	infra.buffer_load=[];
	infra.forr(infra.buffer,function eachbuffer(o){
		try{
			infra[o.type](o.path);
		}catch(e){
			if(infra.debug)alert('Ошибка infra.bufferOff\n'+o.type+' '+o.path+'\n'+e);
		}
	});
}
infra.prop = function(obj,prop,def){//Считываем из obj prop если нет вернётся def
	/*
		var p='asdf';
		prop={'have':1}[p];

		- Считывание в переменную с именем аргумента функции (var не важен)
		- неизвестного свойства объекта (об этом появляется notice в Консоли ошибок)
		- имя свойства указано в переменной
		0.003ms против 2.5ms
	*/
	if(!obj)return def;
	if(obj.hasOwnProperty(prop))return obj[prop];
	return def;
}
infra.replacepath = function(oldp,newp){//понадобилось для переноса core/lib/session/session.js в core/plugins/session/session.js (*session/session.js)
	var self=infra.replacepath;
	if(newp){
		self[oldp]=newp;
	}else{
		newp=infra.prop(self,oldp,oldp);//Считываем из self oldp если нет будет oldp 
	}
	return newp;
}
infra.theme = function(src){
	if(/^\*+/.test(src)){//Начинаемся со звёздочки... значит настоящий путь надо вычислить этим занимается файл theme.php
		//src=src.replace(/^\*+/,'*');//Оставляем одну звёздочку
		//src='core/infra/theme.php?'+encodeURIComponent(src);//Это нужно когда путь до php с несколькоими параметрами * передаётся через theme.php Без кодирования путь будет портится, так как автоматически этот путь второй раз кодироватьс яне будет, а надо бы.. 
		src=src.replace(/^\*+/,'infra/plugins/');//Оставляем одну звёздочку
		//src='core/infra/theme.php?'+src;
	}else{
		//src=encodeURI(src);
		src=src;
	}
	return infra.ROOT+src;
}
infra.load = function(save_path,func,async) {//func и async deprecated
	if(infra.buff){
		infra.bufferAdd('load',save_path);
		return;
	}
	save_path=infra.replacepath(save_path);
	if(typeof(save_path)!=='string')return;
	if(async==undefined){
		async=!!func;
	}
	if(infra.load[save_path]!==undefined){
		if(func){
			func(infra.load[save_path]);
			return;
		}else{
			return infra.load[save_path];
		}
	}
	var load_path=this.theme(save_path);
	if (!infra.NODE) {
		//var exts = load_path.split('.')[-2];
		//&& (exts[0] != 'node') && ((exts[1] != 'js') || (exts[1] != 'json'))) 
		var _transport = function(){
			var result = !1;
			var actions = [
				function() {return new XMLHttpRequest()},
				function() {return new ActiveXObject('Msxml2.XMLHTTP')},
				function() {return new ActiveXObject('Microsoft.XMLHTTP')}
			];
			for(var i = 0; i < actions.length; i++) {
				try{
					result = actions[i]();
					break;
				} catch (e) {}	
			}
			return result
		}
		transport = _transport();
		transport.open('GET', load_path, async);
		transport.setRequestHeader("Content-Type", "text/plain; charset=UTF-8");
		if(async){
			transport.onreadystatechange=function(){
				var state = transport.readyState;
				if(state==4){
					if(transport.status == 200){
						infra.load[save_path]=transport.responseText;
					}else{
						infra.load[save_path]=null;
					}
					if(func)func(infra.load[save_path]);
				}
			}
		}
		transport.send(null);
		if(!async){
			if(transport.status == 200){
				infra.load[save_path]=transport.responseText;
			}else{
				infra.load[save_path]=null;
			}
			if(func)func(infra.load[save_path]);
			return infra.load[save_path];
		}
	} else {
		var fs = require('fs');
		try {
			infra.load[save_path] = fs.readFileSync(load_path, 'utf-8');
		} catch (e) {}
		return infra.load[save_path];
	}
}
infra.globalEval = function(data) {
	if(!data)return;
	if (infra.NODE) {
		eval(data);
	} else {
		// Inspired by code by Andrea Giammarchi
		// http://webreflection.blogspot.com/2007/08/global-scope-evaluation-and-dom.html
		var head = document.getElementsByTagName("head")[0] || document.documentElement, script = document.createElement("script");
		script.type = "text/javascript";
		script.text = data;
		head.insertBefore( script, head.firstChild );
		head.removeChild( script );
	}
}
infra.loadJS = function(path,nocache,call) {//nocache используется для статистики на itlife-studio.ru
	if(infra.buff){
		infra.bufferAdd('loadJS',path);
		return;
	}
	path=infra.replacepath(path);
	var script=false;
	if(this.loadJS[path]==undefined){
		this.loadJS[path]=true;
		if(/^http:/.test(path)){//Это крос доменный запрос
			script=document.createElement('script');
			script.type='text/javascript';
			var head = document.getElementsByTagName("head")[0] || document.documentElement;
			if(call){
				var callback=function(){
					callback=function(){};
					call();
				};
				script.onreadystatechange= function () {     
				   if (this.readystate == 'complete' || this.readystate == 'loaded') {     
					  callback();
				   }
				}
				script.onload = script.onerror = callback 
			}
			script.src=path;
			head.insertBefore(script,head.firstChild);
			head.removeChild(script);
		}else{
			var code=infra.load(path);
			this.globalEval(code);
		}
	}
	if(!script&&call)call();
	if(nocache==='nocache')delete infra.load[path];//Удалили метку о загрузки файла... хотя метка о выполнение скрипта осталась.
	return;
}
infra.unload = function(path){
	delete this.load[path];
	delete this.loadJSON[path];
	delete this.loadCSS[path];
	delete this.loadJS[path];
	if(this.loadIMG.images){
		delete this.loadIMG.images[path];
	}
}
infra.loadJSON = function(path,r){//load, eval, nocache
	if(infra.buff){
		infra.bufferAdd('loadJSON',path);
		return;
	}
	path=infra.replacepath(path);
	//if(r=='reload'){//depricated
	//	infra.unload(path);
	//}
	if(typeof(path)!=='string') return;

	if(r=='load'){//Пофиг на всё просто загружаем снова
		infra.unload(path);
	}
	if(r=='eval'){//Нужно заного выполнить оригинал
		delete infra.loadJSON[path];
	}

	if(infra.loadJSON[path]==undefined){
		var data=infra.load(path);
		if(data){
			try{
				data=eval('('+data+')');
			}catch(e){
				if(this.debug)alert('JSON ошибка '+path+'\n'+e+'\n'+data);// Если json не получился будем считать что это просто строка
				data=data;
			}
		}else{
			data='';
		}
		infra.loadJSON[path]=data;
	}
	var res=infra.loadJSON[path];
	//if(r){//depricated
	//	delete infra.loadJSON[path];
	//}
	if(r=='copy'){//Возвращает копию, которую можно изменять, Будут изменяться и все предыдущие полученные объекты. А будущие уже не будут изменяться... 
		delete infra.loadJSON[path];
	}
	if(r=='nocache'){
		delete infra.load[path];
	}
	return res;
}
infra.style = function(code){
	if(infra.style[code])return;//Почему-то если это убрать после нескольких перепарсиваний стили у слоя слетают.. 
	infra.style[code]=true;
	var style=document.createElement('style');//создани style с css
	style.type = "text/css";
	if (style.styleSheet){//
		style.styleSheet.cssText=code;
	}else{
		style.appendChild( document.createTextNode(code) );
	}
	var head = document.getElementsByTagName("head")[0] || document.documentElement;
	head.insertBefore(style,head.lastChild);//добавили css на страницу
}
infra.loadCSS = function(path,link){//Ассинхронно нельзя иначе порядок собъётся
	if(infra.buff){
		infra.bufferAdd('loadCSS',path);
		return;
	}
	path=infra.replacepath(path);
	if(infra.loadCSS[path])return;
	infra.loadCSS[path]=true;

	if(link){
		var link=document.createElement('link');
		link.rel="stylesheet";
		link.href=infra.theme(path);
		var head = document.getElementsByTagName("head")[0] || document.documentElement;
		head.insertBefore(link,head.lastChild);//добавили css на страницу
	}else{
		var code=infra.load(path);
		infra.style(code);
	}
}
infra.loadIMG = function(path,func,func2){//Всегда ассинхронно
	path=infra.replacepath(path);
	path=infra.theme(path);
	//if(/^\*/.test(path)){
	//path='core/plugins/'+path.replace(/^\*/,'');
	//}
	if(!infra.loadIMG.check){
		infra.loadIMG.process={};
		infra.loadIMG.images={};
		infra.loadIMG.listen={};
		infra.loadIMG.listen2={};
		infra.loadIMG.check=function(path){
			if(infra.loadIMG.images[path]!==undefined){
				if(infra.loadIMG.images[path]){
					infra.loadIMG.listen2[path]=[];
					while(infra.loadIMG.listen[path].length){
						var f=infra.loadIMG.listen[path].shift();
						f(infra.loadIMG.images[path]);
					}
				}else{
					infra.loadIMG.listen[path]=[];
					while(infra.loadIMG.listen2[path].length){
						var f=infra.loadIMG.listen2[path].shift();
						f();
					}

				}
			}
		}
	}
	if(!infra.loadIMG.listen[path]){
		infra.loadIMG.listen[path]=[];
		infra.loadIMG.listen2[path]=[];
	}
	if(func)infra.loadIMG.listen[path].push(func);
	if(func2)infra.loadIMG.listen2[path].push(func2);
	if(infra.loadIMG.process[path]==undefined){
		infra.loadIMG.process[path]=1;
		var img=new Image();
		img.onload=function(){
			infra.loadIMG.process[path]=2;
			infra.loadIMG.images[path]=this;
			img.onload=function(){};//delete в ie6 приводит к ошибке
			infra.loadIMG.check(path);
		};
		img.src=path;
		return;
		setTimeout(function(){
			if(infra.loadIMG.process[path]!==2){
				infra.loadIMG.process[path]=0;
				infra.loadIMG.images[path]=false;
				infra.loadIMG.check(path);
			}
		},15000);
	}else{
		infra.loadIMG.check(path);
	}
}

/*
 * События
 * */
infra.fire = function(obj,fn,clsname,def,context){
	infra.isexec=true;
	context=context||obj;
	/*if(def!==undefined&&fn){//Только для cond
		if(context['exec_'+name]!==undefined){
			return context['exec_'+name];//События с cond в одном забеге два раза не выполняются
		}

	}*/
	var r=this.fire.execute.apply(this,arguments);
	if(def!==undefined&&fn){
		var parts=fn.split('.');
		if(parts.length==3){
			var type=parts[2];//cond, before, after
			var name=parts[1];
		}else{
			var name=fn;
		}
		context['exec_'+name]=r;
		/*setTimeout(function(){
			if(infrajs.isexec)setTimeout(arguments.callee,1);//alert или eval не оборвёт выполнение забега
			else delete context['exec_'+name];
		},1);*/
	}
	infra.isexec=false;
	return r;
};
infra.fire.execute = function(obj,fn,clsname,def,context,args){//args пользователь передавать не может
	//context - в каком пространстве выполняться обработчикам
	//clsname - имя класса объекта obj если есть.. в этом случае будет запущены события infrajs "obj.fn.before" и "obj.fn.after"
	context=context||obj;
	clsname=clsname||'';
	var r;
	
	if(clsname){
		if(def!==undefined){
			var res=this.fire(this,clsname+'.'+fn+'.cond',false,def,context,args);//Руками это никогда не генерируется, будет режим без clsname Но с def
			if(def===true){
				if(res===false)return res;//Если кто-то вернул false будет выход. Обработка заканчивается когда кто-то вернул false.
			}else if(def===false){
				if(res!==true)return res;//Если никто не вернул true будет выход. Обработка заканчивается когда кто-то вернул true.
			}
		}
		this.fire(this,clsname+'.'+fn+'.before',false,undefined,context,args);//Руками это никогда не генерируется
	}
	
	if(!obj)alert('Нет obj в infra.fire.execute '+arguments);
	if(obj[fn]){
		var callback=obj[fn];
		r=infra.exec(callback,' обработчике объекта',context,args,['fn:'+fn,'clsname:'+clsname]);
		if(!clsname&&r!==undefined)return r;
	}
	if(obj.listen&&obj.listen[fn]){
		r=this.forr(obj.listen[fn],function(callback){
			r=infra.exec(callback,' очереди обработчиков listen',context,args,['fn:'+fn,'clsname:'+clsname]);
			if(!clsname&&r!==undefined)return r;
		});
		if(r!==undefined)return r;
	}
	if(clsname){
		var allfn='';
		if(fn!==allfn){
			r=this.fire(infra,allfn,false,def,context,[fn,clsname,def]);
			if(!clsname&&r!==undefined)return r;
		}
	}
	if(clsname){
		this.fire(this,clsname+'.'+fn+'.after',false,undefined,context,args);
	}
	
	
	if(!clsname){
		return def;//Если cond и никто ничего не сказал возвращаем то чего не ждали
	}else{
		return true;
	}
};
infra.unlisten = function(obj,evt,callback){
	if(!obj)return;
	if(obj.listen===undefined)return;
	if(obj.listen[evt]===undefined)return;
	infra.forr(obj.listen[evt],function(call,i){
		if(call===callback){
			obj.listen[evt].splice(i,1);
			return false;
		}
	});
}
infra.listen = function(obj,evt,callback,instart){
	if(!obj)alert('Нет obj в infra.listen '+arguments);
	if(obj.listen===undefined)obj.listen={};
	if(obj.listen[evt]===undefined)obj.listen[evt]=[];
	obj.listen[evt][instart?'unshift':'push'](callback);//instart означает добавить в начало списка
};

/*
 * Подключение контролера (check)
 * */
infra.process = false;
infra.process_count = 0;
infra.layers=[];//Записываются только слои у которых нет родителя... 
infra.wait_timer=false;
infra.waits=[];

infra.run=function(layers,callback,back,parent){
	//return false - не продолжаем в текущем узле(run вернёт undefined)
	//return 0 - совсем не продолжаем (run вернёт 0)
	//return true - совсем не продолжаем (run вернёт true) / иначе (run вернёт undefined)
	var r;
	r=infra.fora(layers,function(layer){
		if(!back){
			//var r=this.exec(callback,layer,back,parent);
			var r=this.exec(callback,'Пробежка по слоям run',infra,[layer,parent],['Назад:'+back]);
			if(r===false)return;//Ситуация когда возвращённый false просто не позволяет углубляться дальше
			if(r!==undefined)return r;//выход
		}
		r=infra.foro(layer,function(val,name){
			if(this.run.props.array.hasOwnProperty(name)){
				var r=infra.run(val,callback,back,layer);
				if(r!==undefined)return r;
			}else if(this.run.props.object.hasOwnProperty(name)){
				var r=infra.foro(val,function(v,i){
					var r=infra.run(v,callback,back,layer);
					if(r!==undefined)return r;
				},back);
				if(r!==undefined)return r;
			}
		}.bind(this),back);
		if(r!==undefined)return r;
		if(back){
			//var r=this.exec(callback,layer,back,parent);
			var r=this.exec(callback,'Пробежка по слоям run',infra,[layer,parent],['Назад:'+back]);
			if(r!==undefined)return r;
		}
	}.bind(this),back);
	return r;
}

infra.run.props={//В callback все указанные списки слоёв обрабатываются после обработки слоя в котором они указаны. after и before актуально для callback2
	//Расширяется в env.js
	array:{},
	object:{}//divs:true
}

/* Используется для расширений */
infra.run.add=function(what,name){
	this.props[what][name]=true;
}

infra.checkNow = function() {
	this.fire(this,'oninit');//В этот момент в this.layers могут добавиться новые слои от функции check.//Во всех остальных обработчиках добавляемые слои обработаются при повторной пробежке
	this.ismainrun=false;
	this.run(this.wlayers,function(layer){//Функция для любова слоя, подслои обрабатываются
		delete layer.exec_onchange;//Обрабатываем слой или нет
		delete layer.exec_onshow;//Показывается
		delete layer.exec_onparse;//Перепарсиваеся слой или нетт
		delete layer.exec_onshow_savemybranch;//кроме exec_onshow может быть определена ещё и эта пременнаря, означающая что текущй слой скрыт но его ветку нужно показывать
		delete layer.fight_msg;
	});
	this.run(this.wlayers,function(layer,parent){//Функция для любова слоя, подслои обрабатываются
		if(layer.parent===undefined)layer.parent=parent||false;
		if(!layer.parent)this.ismainrun=true;//Метка о том что это пробежка начиная от корня 
		this.fire(layer,'oninit','layer');//Перед тем как запускать oninit должен быть устанолвен parent это единственное требование ядра. Нельзя делать только один раз для каждого слоя. Состояния определяются каждый раз тут и при повторных пробежках когда состояние динамическое в child
		if(!this.fire(layer,'onchange','layer',true))return false;//В глубь не идём //Добавляются дочернии слои, определяется data tpl is div(например показать сверху или снизу)
		var r=infra.fire(infra,'layer.onshow.cond',false,1,layer);//Если подписчики ничего не вернут, будет true, Если вернут false выход, если вернёт null - значит игнорируем				//В этот момент нельзя проверять есть див на старнице или нет.
		// if(!r) Мы не выходим даже если слой не показывается мы всё равно проверяем его детей, так как у них сработал onchange*/
	});
	this.fire(this,'onchange');
	this.fire(this,'onparse');
	this.run(this.wlayers,function(layer){
		if(!layer.exec_onchange)return false;
		if(!layer.exec_onshow)return;
		this.fire(infra,'layer.onparse.cond',false,false,layer);
	});
	this.run(this.wlayers,function(layer){//Если слой скрыт или слой должен перепарсится у него всегда! запускается onhide.
		
		if(layer.exec_onshow&&!layer.exec_onparse)return;
		this.fire(layer,'onhide','layer',true);//По умолчанию true то есть чтобы остановить событие нужно вернуть false в обработчике cond
			//Скрыть нужно непоказываемые слои и слои которые будут перепарсиваться
	},true);//скрываем в обратном порядке

	this.run(this.wlayers,function(layer){//Бежим в порядке свойств
		if(!layer.exec_onchange)return false;
		if(!layer.exec_onshow)return;
		if(!layer.exec_onparse)return;

		this.fire(layer,'onparse','layer'); //В onparse можно работать с данными 
		//Вложенный узел может использовать какие-то данные родителя.. потому что заведомо родитель уже обработан и данные у него готовы. Родитель же использовать что-то от вложенных узлов не может потмоу что вложенные узлы могут быть не говтовы.. да и не логично это.
		//В случае frames получается, что родитель ещё не показан и это приводит к ошибке с окнами.. title для frames окна устанавливается в onparse родителя, который на самом деле показывается после frames
		//получение html и показ должны быть в разных обработчиках.. парсится в обычном порядке.. и только показывается с учётом frames в ином порядке
		//frames - нужна для окон.. мы передали слой и этот слой показан и можно с эим слоем работатать. Подменить этот слой на какой-то родительский не льзя потмоу что слой используется как внутри popup так и снаружи и может быть повторно передан popup должна быть понятно это одно и тоже или разное..
	});
	this.run(this.wlayers,function(layer){
		//onparse делается в порядке описания слоёв
		//onshow делается в порядке свойств onparse может обращаться к родителям они к тому времени будут уже распаршены в том числе и в случае с frames
		if(!layer.exec_onchange)return false;
		if(!layer.exec_onshow)return;
		if(!layer.exec_onparse)return;//В случае если слой был скрыт из-за того что до него просто в onchange не дошли в нём остануться старные значения и нужно yes проверять
		var r=this.fire(layer,'oninsert','layer',true);//Свойство для вставки и чтобы перед вставкой можно было div проверить, если divа нет дальше onshow не запустится
		if(r)r=this.fire(layer,'onshow','layer'); //Нужен для шаблонов, там эта функция выполнится до перепарсивания, Внутри функции можно изменить data. 
	});
	this.fire(this,'onshow');//autosave, autofocus Плагины могут после показа что-то делать со слоём// Калькулятро введёных данны. считается после подстановки из autosave данных, по этому плагин autosave подписан на infrjs.listen('layer.onshow.before');
}

infra.check = function(layers, action) { //Пробежка по слоям, вызывается после загрузки расширений
	if(this.process&&!this.wait_timer){//Функция checkNow сейчас выполняется и в каком-то
		setTimeout(function(){//обработчике прошёл вызов пробежки...  Если мы добавим текущий слой в массив всех слоёв.. он начнёт участвовать в пробежке в операциях после той в которой был вызов создавший этот слой... короче не добавляем его
			infra.check(layers,action);
		},100);//Запоминаем всё в этой ловушке...
		return;
	}
	infra.fora(layers,function(layer){//Если layers undefined пробежки не будет
		if(action)layer.reparseone=true;//Если указан конкретный слой, отмечаем что его нужно перепарсить если он должен быть виден
		if(action=='reload'&&layer.data)infra.unload(layer.data);//Обновление данных слоя
		if(!layer.parent&&!infra.fora(this.layers,function(l){//Если parent есть значит слой уже где-то записан и будет обработан вместе с родителем
			if(layer===l)return true;
		}))this.layers.push(layer);//Только если рассматриваемый слой ещё не добавлен
	}.bind(this));

	if(this.waits===undefined)return;//уже пробежка по всем слоям выходим
	if(!layers){
		this.waits=undefined;
	}else{
		if(!infra.fora(layers,function(nl){//Отсеиваем повторы
			if(!infra.fora(infra.waits,function(l){
				if(l==nl)return true;
			}))infra.waits.push(nl);
		}));
	}
	if(this.wait_timer)return;
	this.process=true;
	this.process_count++;//Счётчик сколько раз перепарсивался сайт, посмотреть можно в firebug
	if(this.loader)this.loader.show();//Исключительный хак.. чтобы лоадер успел показаться
	this.wait_timer=setTimeout(infra.bind(this,function(){
		bindReady(function(){
			this.wait_timer=false;//Все новые слои будут ждать пока не станет false
			this.wlayers=this.waits||this.layers;//При запуске checkNow все ожидающие слои обнуляются
			this.waits=[];
			this.checkNow();
			this.process=false;
		}.bind(this));
	}),100);//Если вызывать infra.check() и вместе с этим переход по ссылке проверка слоёв сработает только один раз за счёт это паузы.. два вызова объединяться за это время в один.
}

/*
 * Загрузка расширений, могут быть разные для браузера и для клиента
 * */
