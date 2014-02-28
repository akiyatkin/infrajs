//Требования к серверу -> повторять файловую систему и запускать для каждого соединения поток Fiber
//Чтобы скрипт запускался в консоли нужно его оборачивать в ещё один поток и дополнительно без потока пытаться использовать yield чтобы дождаться его выполнения если работа идёт в потоке сервера.

/*	var href=decodeURIComponent(location.href).replace('+',' ');
	if(href!==location.href){
		location.href=href;
	}*/

/*var undefined;
infra={};//объект infra создаётся для каждого view/ Расширяем всегда прототип а чтобы добавить переменные действительные только в созданном объекте infra проверяем их наличие в функциях где они нужны

if(!window.ROOT)window.ROOT='';
infra.NODE=(typeof(window)!=='object');

if(!infra.NODE){
	if(!window.console)window.console={};
	if(!window.console.log)window.console.log=function(){};
}

*/
/*Эммитируем module.exports*/
//if(typeof(module)=='undefined')module={};

/* Работа с файловой системой */
//if(infra.NODE)var fs=require('fs');


/* Обработка потоков */
//infra.stor=function(){//На клиенте stor хранится просто в глобальной области
//	if(!infra.stor.data)infra.stor.data={};
//	return infra.stor.data;
//}
/*infra.fiber=function(fn){ //Обернуть функцию в поток
	return fn;
};
infra.sync=function(obj,fn,er){
	return function(){
		return fn.apply(obj,arguments);
	}
}
*/

//if (!Function.prototype.bind){ Нам не подходит оригинальная функция. Нужно чтобы в alert выдался код оригинальной функции.. для этого подменяется toString
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
//}

/*Обработка ошибок*/
/*	infra.debug=false;
	infra.log=function(msg){
		if(console)console.log(msg);
		if(infra.debug){
			if(typeof(alert)!=='undefined')alert(msg);
		}
	}
	infra.error=function(e,name,code,more){//Запускается для ошибок. e - исключение, name - имя места, code - код, more - массив всяких данных
		if(this.IE&&typeof(e)!=='string')em=e.name+':'+e.message;
		else em=e;
		var inframsg='\n++++++++++++++++++\nОшибка: '+name+'\n'+em+'\n'+code+'\n\n';		
		if(typeof(more)!='object'||more.constructor!='Array'){
			if(more)more=[more];
			else more=[];
		}
		for(var i=0,l=more.length;i<l;i++){
			inframsg+='\n'+more[i];
		}
		inframsg+='\n++++++++++++++++++';
		if(console&&console.log)console.log('INFRAERROR: '+inframsg);
		if(this.debug&&!this.NODE)alert(inframsg);
		e.inframsg=inframsg;
		throw e;//на это рассчитывается, чтоб скрипт остановил свою работу catalog/groups.njs
	}
*/

/*Пробежки*/
	
//VIEW



//СОБЫТИЯ




/*
	разделение меток загрузки файла и его интерпретации
	Если состояия у флага два то одна буква и не позволяет предустановить значения, автоустановка не сможет понять была установка или нет.
	Если нет автоустановки для двух состояний одной буквы достаточно
	Если состояний больше двух для каждого состояния предусматривается отдельная буква
	==СЕРВЕР===
	f - файл
	d - дирректория
	h - host удалённый - берётся из инета

	s - secure - файлы с точкой в начале подходят, также этот ключ позволяет обращаться к файлу напрямую без вебсервера (Файл в кэше клиенту не попадает)

	S - файл только для сервера (файлы с точкой в начале подходят, в кэш любые файлы с этим ключём не попадают) ставится автоматом для sjs файлов
	B - файл может выполняться где угодно (но sjs например всё равно не обработается node)

	m - эмитация запроса через веб сервер
	w - запрос через веб севрер
	r - require
	k - тупо readFile
	P - Передать текущие POST данные, который есть сейчас в запросе
	 - Всегда. Эмитировать работу с кукисами, который есть в этом запросе. Передать их и установить какие будут в ответе.
	
	==ВЕЗДЕ===
	o - global кэшируется до следующего рестарта и для всех пользователей. 
	v - Кэшируется на сессию пользователя. загрузку и выполнение.
	x - загружать при каждом запросе, кэш в infra.stor
	T - проверяется дата изменения файла
	
	n - Номер вначале имени файла не играет роли и расширение откидывается.
	
	! - выкидывать исключения в случае ошибки
	. - возвращать false в случае ошибки
	
	M - mix после двоеточия в строке адреса указываются и ключи

	a - асинхронно - последний переданный аргумент это callback						
	y - синхронная загрузка

	j - json данные
	X(J depricated) - интерпретировать данные каждый раз
	e - exec - код который нужно выполнить	
	t - текст
	i - Картинка (на сервере проверка расширения)

	g - третий параметр воспринимается как get данные (не кэшируется)
	p - третий параметр воспринимается как пост данные (не кэшируется)
	b - устанавливается результат в кэш load
	z - устанавливается результат в кэш exec
	Z - третий параметр не обрабатывается 
	==БРАУЗЕР===

	c - css стиль
	
	============
	3 и 4 параметр могут быть post callback всегда последний infra.load(path,mod,data,callback);

	
	web
	P - транслировать окружение при w

	error
	.! - обработка ошибок (.)

	load	
	imwrkr - способ загрузки (w)

	sense 
	icjet - что делать с загруженными данными (по рассширению)

	sync
	ay - асинхронно или синхронно (y)

	third parameter 
	pgbzZ - post или get (Z,p)
	
	secure
	s - можно ли файлы с точкой брать

	side	
	SB - можно всем или только серверу

	cache
	XvxoT - какой кэш (o) X как x но относится только к интерпретации

	target
	dhf1 - что именно подойдёт (f)
	
*/
	infra.plugin={//Ядро начинается с логики подключения расширений
		mod:function(strmod,path){
			if(!strmod)strmod='';
			if(typeof(strmod)!=='string')return strmod;//Если mod уже был мы его не перепроверяем
			
			var mod={};
			for(var i=0,l=strmod.length;i<l;i++)mod[strmod.charAt(i)]=true;

			if(path){//Нужен для автоматического определения параметров
				var ext=path.match(/\.(\w{0,4})($|\?)/);
				if(ext)mod.ext=(ext[1]||'').toLowerCase();
				mod.query=/\?/.test(path);//Если есть вопрос значит есть параметры, значит k и r уже не подохдят
			}
			this.modCheck(mod);
			return mod;//Объект с ключами как свойствами
		},
		modCheck:function(mod){//Функция расставляет значения по умолчанию и очевидные значения на основе наличия или нет других модификаторов.
			var inArray=function(need,ar){ return infra.forr(ar,function(e){if(need==e)return true}); }

			if(mod.J){
				infra.log('mod.J depricated');
				mod.X=true;//depricated
			}

			//target
			if(!mod.h&&!mod.d&&!mod.f&&!mod['1']){
				mod.f=true;
			}

			//third parameter
			if(!mod.g&&!mod.p&&!mod.b&&!mod.z){
				mod.Z=true;
			}

			//load, r - способ загрузки, который действует только на сервере
			if(!mod.i&&!mod.w&&!mod.k&&!mod.m&&!mod.r){
				if(mod.ext=='sjs')mod.r=true;
				else if(!mod.query&&inArray(mod.ext,['txt','css','tpl','json','tpl','js']))mod.k=true;
				else if(mod.ext=='njs')mod.m=true;//Эмитация сработает только на сервере
				else mod.w=true;
			}

			//sense
			if(mod.m)mod.r=true;//Эмитация делается с помощью require
			if(mod.r)mod.e=true;//с require может быть только execute
			if(!mod.i&&!mod.c&&!mod.j&&!mod.e&&!mod.t){
				if(!mod.ext);
				else if(inArray(mod.ext,['tpl','txt']))mod.t=true;
				else if(inArray(mod.ext,['jpg','gif','png']))mod.i=true;
				else if(inArray(mod.ext,['njs','json','php']))mod.j=true;
				else if(mod.ext=='css')mod.c=true;
				else if(mod.ext=='js')mod.e=true;
			}

			//side
			if(!mod.S&&!mod.B){
				if(mod.ext=='sjs')mod.S=true;
				if(!mod.S)mod.B=true;
			}

			//error
			if(!mod['.']&&!mod['!']){
				mod['.']=true;
			}
			
			//cache
			if(!mod.x&&!mod.o&&!mod.v&&!mod.T){
				if(mod.g||mod.p)mod.x=true;
				else if(inArray(mod.ext,['php','njs']))mod.v=true;
				else if(inArray(mod.ext,['js','tpl','json']))mod.T=true;
				else mod.o=true;
			}
			
			//Для глобального кэша нужен механизм его обновления для ситуаций изменения файлов.. такие файлы должны браться с меткой v
			if(!mod.d&&!mod['1']&&!mod.h&&!mod.f){
				mod.f=true;//Если не дирректория и не хост значит это обращение к файлу
			}
			
			//sync
			if(!mod.a&&!mod.y)mod.y=true;

			if(mod.r)mod.e=true;//Надо для кэша и вообще логика таже выполнить, но способом r. r это сопособ загрузки и способ выполнения e.
			if(mod.m)mod.e=true;//делается init как от веб сервера.. тоже и загрузка и выполнение
			if(mod.r&&!mod.m)mod.k=true;//Нужно для кэша чтобы require считывались для клиента быстро

			/*//load
			if(!mod.m&&!mod.r&&!mod.k){
				if(!mod.query&&inArray(mod.ext,['txt','css','tpl','json','js'])) mod.k=true;
				else mod.w=true;//load
			}*/
		},
		/*
		 * Функция возвращает массив только тех символов которые сейчас true из списка переданного в arr проверяя  их в mod. one значит вернуть только один символ строкой
		 */
		getSymbols:function(mod,arr,one){
			var r=[];
			for(var i=0,l=arr.length;i<l;i++){
				if(mod[arr[i]])r.push(arr[i]);//Берём буквы которые true
			}
			if(one)return r[0];
			return r;
		},
		theme:function(origpath,origmod){
			if(typeof(origmod)=='object'){
				var mod=origmod;
			}else{
				var mod=this.mod(origmod,origpath);
			}
			var ans=this.parse(origpath,mod.M);
			var path=ans.path;
			var cache=this.cache('theme',mod);
			var c=path+':'+this.getSymbols(mod,['h','f','d','s','n']);
			if(cache[c])return cache[c];
			if(mod.h){
				cache[c]=origpath;
				return cache[c];
			}

			
			if(/^\*/.test(path)){	
				path=infra.theme.hand+'?'+encodeURI(path);
			}
			return cache[c]=path;
		},
		parse:function(path,M){//используется в тестах load
			if(typeof(path)=='undefined')path='undefined';
			if(this.parse[path])return this.parse[path];
			var ans={};
			ans['mod']='';
			if(M){
				var s=path.split(':');
				if(s.length>1){
					ans['mod']=s.pop();
					path=s.join(':');
				}
			}
			//ans['orig']=path;
			ans['path']=path;
			ans['paths']=[];
			ans['opt']=path;//оптимальный путь со звёздочкой
			if(/^https?:\/\//.test(path)){
				ans['ishost']=true;
				ans['paths'].push(path);
				return ans;
			}
			if(/^\//.test(path)){//Путь от корня - проблема в том что у нас 2 корня. Такие пути просто запрещены.
				infra.error('Путь от корня использовать нельзя');
				return ans;
			}
			ans['secure']=/\/\./.test('\\'+path);// ищется \. и для совпадения и с первой точкой добавляется слэш в путь
			var p=path.split('?');
			if(p.length>1){
				path=p.shift();
				ans['query']=p.join('?');
			}
			
			ans['isfolder']=/\/+$/.test(path);
			if(path=='*')ans['isfolder']=true;			
			
			if(/^\*/.test(path)){//'*imager/imager.njs' '*pages/ *catalog/lib/test.js
				path=path.replace(/^\*\/?/,'');
				var p=path.split('/');


				var name=p.pop();//Может быть пустой строкой imager.njs '' test.js

				var plugin=p.shift();// imager pages catalog
				if(plugin)plugin+='/';
				else plugin='';

				var folder=p.join('/');// '' '' 'lib'
				if(folder)folder+='/';
				else folder='';
				
				var file=folder+name;//Есть обязательно imager.njs '' lib/test.js

				
				ans['paths'].push('infra/data/'+plugin+file);

				ans['paths'].push('infra/layers/'+plugin+file);
				
				ans['paths'].push('infra/plugins/'+plugin+file);
				
				ans['opt']='*'+plugin+file;
				//if(ans['query'])ans['opt']+='?'+ans['query'];//Этот путь можно вывести со звёздочкой и по нему опять будет файл найден при необходимости.
			}else{//Нет звёздочки
				ans['paths'].push(path);
				ans['opt']=path;
			}
			if(ans['query'])ans['?query']='?'+ans['query'];
			else ans['?query']='';

			ans['opt']+=ans['?query'];

			return ans;
		},		
		/*
		 * mod только строкой
		 */
		_getLoadArg:function(a){//path,mod,data,callback
			if(typeof(a[0])=='object')return a[0];//Для buffer там уже готовый а передался

			var arg={};
			
			//1 path, 2 mod
			for(var i=2,l=a.length;i<l;i++){
				var val=a[i];
				if(typeof(val)=='function'){
					arg.callback=val;
				}else if(typeof(val)=='object'&&infra.View&&(val instanceof infra.View)){
				}else{
					arg.data=val;
				}
			};
			var mod=(typeof(a[1])=='string'?a[1]:'');

			var M=/M/.test(mod);
			var ppath=this.parse(a[0],M);//Смотрим или нет в адресе :mod
			mod+=ppath.mod;

			arg.pathopt=ppath['opt'];
			arg.mod=this.mod(mod,arg.pathopt);

			var path=infra.theme(ppath.path,arg.mod);
			arg.path=path;

			return arg;
		},
		_getTransport:function(){
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
		},
		/*
			Путь моежт быть загружен и выполнен и ключи тут не играют роли. 
			Если путь выполнился как css так тому и быть. 
			Если потом спросить JSON вернётся true от выполненного CSS
		*/
		stor:function(){
			var stor=infra.stor();
			if(!stor.load)stor.load={};
			return stor.load;
		},
		getHTTPROOT:function(HTTP){
			var stor=this.stor();
			if(!stor.root){
				var p=location.pathname.split('/');
				var l=p.pop();
				if(l)var root=p.join('/')+'/';
				else var root=location.pathname;
				httproot={server:'php',evident:false,siteroot:root,sitehost:location.host,siteport:80};

				stor.root=httproot;
			}
			return stor.root;
		},
		_load:function(a,callback){// В php js загрузка происходит при каждом обновлении страницы. В node require делается один раз навсегда для всех пользователей
			
			var k=a.pathopt;
			
			var cache=this.cache('load',a.mod);

			if(a.mod.b)cache[k]={r:a.data};

			if(cache[k]){
				return callback(false,cache[k].r);
			}
			

			var http=infra.conf.http;
			var h='http://';

			/*var now=infra.plugin.getHTTPROOT();
			if(http.sitehost&&http.datahost&&(now.sitehost!==http.sitehost)){
				h+=http.datahost+'.'+now.sitehost+'/'+now.dataroot;
			}else if(http.sitehost){
				h+=http.sitehost+'/'+http.siteroot;
			}else{
				h+=now.sitehost+'/'+now.siteroot;
			}*/


			//var t='http://'+infra.conf.http.sitehost+'/'+infra.conf.http.siteroot;//Если сайт покажется на другом домене картинки загрузятся нормально а скрипты нужно предзагружать
			//var load_path=t+a.path;	
			
			//var load_path=infra.conf.http.siteroot+a.path;	
			var load_path='http://'+infra.conf.http.sitehost+'/'+infra.conf.http.siteroot+a.path;	//root нужен чтобы html мог быть в любой папке
			if(a.mod.p){
				this.load('infra/lib/jquery/jquery.js','e');
				$.ajax({
					url:load_path,
					timeout:120000,
					async: !!a.mod.a,
					type:'POST',
					data:a.data,
					dataType: 'json',
					complete: function(req) {
						cache[k]={r:req.responseText};
						callback(false,cache[k].r);
					}
				});
			}else{
				var transport = this._getTransport();
				transport.open('GET', load_path, !!a.mod.a);
				transport.setRequestHeader("Content-Type", "text/plain; charset=UTF-8");
				if(a.mod.a){
					transport.onreadystatechange=function(){
						var state = transport.readyState;
						if(state==4){
							if(transport.status == 200){
								cache[k]={r:transport.responseText};
							}else{
								cache[k]={r:''};
							}
							callback(false,cache[k].r);
						}
					}
				}
				transport.send(null);
				if(!a.mod.a){
					var state = transport.readyState;
					if(state==4){
						if(transport.status == 200){
							cache[k]={r:transport.responseText};
						}else{
							cache[k]={r:''};
						}
						callback(null,cache[k].r);
					}
				}
			}
		},
		/*
		 Список значений {}
		 - узнать значение
		 - установить значение
		 - получить все значения или пробежаться по всем значениям
		 - установить все значения или удалить всё чтобы установить новые по одному
		*/

		/*
			Кэш в разных местах
			Кэш загрузки и выполнения
			Если кэш загрузки и выполнения одинаковые то, кэш хранится только в загрузке это исключение для t

			x - без кэша
			o - кэш для всех
			v - кэш только для текущего пользователя и только для этого запроса

			- с обоими типами кэшей
			- прочитать кэш по ключу
			- установить кэш по ключу
			- пробежаться по кэшу

		*/
		cache:function(type,mod,path){//Два типа sense и load согласно имён групп модификаторов, и кэш может хранится в разных местах
					//o T v
			var stor=this.stor();
			if(mod=='clear'){
				//delete this['cache_'+type];
				//delete stor['cache_'+type];
				infra.fory([this['cache_'+type],stor['cache_'+type]],function(val,src,group){
					//бежим по свойствам объектов в массиве
					if(/\.js$/.test(src))return;
					if(/\.css$/.test(src))return;
					delete group[src];
				});
				return true;
			}
			var mod=this.mod(mod);
			if(infra.NODE&&path&&!mod.S){//если переда path это фиксируется для кэша клиента
				/*if(!stor.cache)stor.cache={
					repeat:{},
					c:{ k:[],w:[],m:[] },
					t:{ k:[],w:[],m:[] },
					j:{ k:[],w:[],m:[] },
					e:{ k:[],w:[],m:[] }
				};
				if(!stor.cache.repeat[path]){//Защита чтобы небыло повторений, такие ошибки хрен поймаешь потом
						var sense=this.getSymbols(mod,['c','t','j','e'],'one');
						if(sense){
							var load=this.getSymbols(mod,['k','w','m'],'one');
							if(load){
								stor.cache.repeat[path]=true;
								stor.cache[sense][load].push(path);
							}
						}
				}*/
			}
			if(mod.o||((mod.T||mod.v)&&!infra.NODE)){//Глобальный
				if(!this['cache_'+type])this['cache_'+type]={};
				return this['cache_'+type];
			}else if(mod.T){//Это глобальный кэш с проверкой на дату
				if(!stor['cache_'+type])stor['cache_'+type]={};
				return stor['cache_'+type];
			}else if(mod.v){//Кэш в stor 
				var stor=this.stor();
				if(!stor['cache_'+type])stor['cache_'+type]={};
				return stor['cache_'+type];
			}else if(mod.x){//нет кэша
				return {};
			}
		},
		unload:function(){
			var a=this._getLoadArg(arguments);
			//if(!a)return null;//возвращается null
			var k=a.pathopt;

			var cache=this.cache('sense',a.mod);
			cache[k]=false;
			
			var cache=this.cache('load',a.mod);
			delete cache[k];

			var cache=this.cache('theme',a.mod);
			delete cache[k];
		},
		buffer:{
			is:false,
			load:[]
		},
		loadBufferOff:function(callback){//TODO данные для view хранятся, скрипты сразу выполняются... сейчас не рабтает
			this.buffer.is=false;
			var paths=[];
			for(var i=0,l=this.buffer.load.length;i<l;i++){
				var p=this.buffer.load[i].pathopt;
				paths.push(p);
			}
			this.load('infra/plugins/infra/load.php?'+paths.join('|'),'j',function(err,data){
				for(var i=0,l=this.buffer.load.length;i<l;i++){
					var p=this.buffer.load[i].pathopt;
					this.load(p,'ftb',data[p]);
				}
				for(var i=0,l=this.buffer.load.length;i<l;i++){
					var a=this.buffer.load[i];
					this.load(a);
				}
				if(callback)callback();
			}.bind(this));
		},
		loadBufferOn:function(){
			this.buffer.is=true;
		},
		copy:function(obj2){
			var obj1=obj2;//Функции данные в себе не содержат. Функции возвращаются ссылками и если при работе функция устанавливает на себя данные, эти данные передадуться и следующим копиям
			if(obj2&&obj2.constructor===Array){
				obj1=[];
				for(var i=0,l=obj2.length;i<l;i++)obj1[i]=this.copy(obj2[i]);
			}else if(obj2&&typeof(obj2)==='object'){
				obj1={};
				for(var i in obj2)obj1[i]=this.copy(obj2[i]);
			}
			return obj1;
		},
		load:function(){
			var a=this._getLoadArg(arguments);

			//if(!a.path)return;//Значит нет переданного файла
			
			var k=a.pathopt;
			//voxl - кэши v o - global, l - в сессии temp (до перезагрузки), 
			var cache=this.cache('sense',a.mod,a.pathopt);
			//тут path уже может быть theme.njs?... и будет распознан иначе
			
			if(a.mod.z){
				a.mod.e=true;
				cache[k]={r:a.data};//Устанавливается кэш выполнения
			}
			
			var r;
			if(cache[k]&&(a.mod.j||a.mod.e||a.mod.i)){//Есть кэш
				r=cache[k].r;
				if(a.mod.X){
					r=this.copy(r);
				}
				if(a.callback)a.callback(false,r);
				return r;//кэш выполнения
			}

			if(a.mod.i&&!infra.NODE){
				this.loadIMG(a.path,function(img){
					cache[k]={r:img,type:'img'};
					if(a.callback)a.callback(false,img);
				});
				return;
			}else{
				if(this.buffer.is){
					this.buffer.load.push(a);
				}else{
					var result;
					this._load(a,function(err,text){//Возможна ситуация у одного и тогоже хотим и текст получить и выполнить...
						var c={},r;
						if(a.mod.e||a.mod.j||a.mod.c){
							c.r=r;
							cache[k]=c;//сохранили в кэше выполнения предварительно чтобы небыло зацикливаний
						}
						if(a.mod.r&&infra.NODE){
						}else if(a.mod.e){
							r=eval(text);
							if(module.exports){
								r=module.exports;
								delete module.exports;
							}
							c.type='exec';
						}else if(a.mod.j){
							try{
								r=eval('('+text+')');
							}catch(e){
								r=undefined;
							}
							c.type='json';
						}else if(a.mod.c){
							r=infra.style(text);
							c.type='css';
						}else{
						 	r=text;
						}

						if(a.mod.e||a.mod.j||a.mod.c){
							c.r=r;
							cache[k]=c;//сохранили в кэше выполнения

							result=cache[k].r;
							if(a.mod.X) result=this.copy(result);
						}else{
							result=r;
						}
						if(a.callback)a.callback(false,result);
						
					}.bind(this));//Там ещё есть кэш загрузки
					return result;
				}
				
			}
		},
		loadIMG:function(path,func){//Всегда ассинхронно
			var obj=this.loadIMG;
			if(!obj[path])obj[path]={
				process:0,
				image:undefined,
				listen:[]
			};

			var objimg=obj[path];
			if(!obj.check){
				obj.check=function(path){
					var objimg=obj[path];
					if(objimg.image!==undefined){//Либо картинка либо false
						while(objimg.listen.length){
							var fu=objimg.listen.shift();
							fu(objimg.image);
						}
					}
				}
			}

			
			if(func)objimg.listen.push(func);
			if(objimg.process===0){
				objimg.process=1;
				img=infra.img=new Image();
				img.onload=function(){
					objimg.process=2;
					objimg.image=this;
					img.onload=function(){};//delete в ie6 приводит к ошибке
					obj.check(path);
				};
				img.src=path;
			}else{
				obj.check(path);
			}
		},
		style:function(code){
			var stor=infra.stor();
			if(!stor.style_cache)stor.style_cache={};
			var cache=stor.style_cache;
			if(cache[code])return;//Почему-то если это убрать после нескольких перепарсиваний стили у слоя слетают.. 
			cache[code]=true;
			if(infra.NODE){
				//infra.html('<style>'+code+'</style>');
			}else{
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
		}
	}
	infra.style=function(){
		return this.plugin.style.apply(this.plugin,arguments);
	}
	infra.theme=function(){
		return this.plugin.theme.apply(this.plugin,arguments);
	}
	infra.loadBufferOn=function(){
		return this.plugin.loadBufferOn.apply(this.plugin,arguments);
	}
	infra.loadBufferOff=function(){
		return this.plugin.loadBufferOff.apply(this.plugin,arguments);
	}
	infra.load=function(){
		if(!infra.load.ed){
			infra.load.ed=true;
			var ss=document.getElementsByTagName('script');
			infra.forr(ss,function(s){
				if(!s.src)return;
				var view=infra.View.get();
				var src=s.src.replace('http://'+view.getPath(),'');
				infra.load(src,'ez',true);
			});
		}
		return this.plugin.load.apply(this.plugin,arguments);
	}
	infra.unload=function(){
		return this.plugin.unload.apply(this.plugin,arguments);
	}

infra.conf={};
infra.theme.hand='infra/plugins/infra/theme.php';