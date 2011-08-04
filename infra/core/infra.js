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

/* Одинаковое api для загрузки слоев и расширений. */
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

/* События */
infra.fire = function(obj,fn,clsname,def,context){
	infra.isexec=true;
	context=context||obj;
	/*if(def!==undefined&&fn){//Только для cond
		if(context['exec_'+name]!==undefined){
			return context['exec_'+name];//События с cond в одном забеге два раза не выполняются
		}

	}*/
	var r=this.execute.apply(this,arguments);
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
infra.execute = function(obj,fn,clsname,def,context,args){//args пользователь передавать не может
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
	
	if(!obj)alert('Нет obj в infra.execute '+arguments);
	if(obj[fn]){
		
		var callback=obj[fn];
		//js.exec=function(callback,name,context,args,back){
		
		r=infra.exec(callback,' обработчике объекта',context,args,['fn:'+fn,'clsname:'+clsname]);
		
		/*try{
			r=callback.apply(context,args||[]);
		}catch(e){
			if(js.debug){
				if(js.IE)e=e.name+':'+e.message;	
				alert('Ошибка в обработчике объекта\n'+e+'\n------\n'+clsname+' '+fn+'\n'+callback+'\n'+context);
			}
		}*/
		if(!clsname&&r!==undefined)return r;
		
	}
	if(obj.listen&&obj.listen[fn]){
		for(var i=0,l=obj.listen[fn].length;i<l;i++){
			var callback=obj.listen[fn][i];
			
			r=infra.exec(callback,' очереди обработчиков listen',context,args,['fn:'+fn,'clsname:'+clsname]);
			/*try{
				r=callback.apply(context,args||[]);
			}catch(e){
				if(js.debug){
					alert('Ошибка в очереди обработчиков listen\n'+e+'\n------\n'+clsname+' '+fn+'\n'+callback+'\n'+context);
				}
			}*/
			if(!clsname&&r!==undefined)return r;
		}
	}
	
	
	if(clsname){
		var allfn='';
		if(fn!==allfn){
			r=this.fire(infrajs,allfn,false,def,context,[fn,clsname,def]);
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
infra.listen = function(obj,evt,callback,instart){
	if(!obj)alert('Нет obj в infrajs.listen '+arguments);
	if(obj.listen===undefined)obj.listen={};
	if(obj.listen[evt]===undefined)obj.listen[evt]=[];
	obj.listen[evt][instart?'unshift':'push'](callback);//instart означает добавить в начало списка
};

/* Подключение контролера (check) */
infra.check = function(layers,action){//Пробежка по слоям
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
			if(!infra.fora(infrajs.waits,function(l){
				if(l==nl)return true;
			}))infrajs.waits.push(nl);
		}));
	}
	if(this.wait_timer)return;
	this.process=true;
	this.process_count++;//Счётчик сколько раз перепарсивался сайт, посмотреть можно в firebug
	if(this.loader)this.loader.show();//Исключительный хак.. чтобы лоадер успел показаться
	this.wait_timer=setTimeout(infra.bind(this,function(){
		$(function(){
			this.wait_timer=false;//Все новые слои будут ждать пока не станет false
			this.wlayers=this.waits||this.layers;//При запуске checkNow все ожидающие слои обнуляются
			this.waits=[];
			this.checkNow();
			this.process=false;
		}.bind(this));
	}),100);//Если вызывать infrajs.check() и вместе с этим переход по ссылке проверка слоёв сработает только один раз за счёт это паузы.. два вызова объединяться за это время в один.
}

/* Загрузка расширений, могут быть разные для браузера и для клиента */
