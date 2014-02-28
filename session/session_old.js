/*
Copyright 2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
	
	var ses=infra.Session.init('base',view);

view объект - на клиенте создаваемый, как view=infra.View.init(); на сервере view=infra.View.init([request,response])
или infra.View.get(); если view до этого уже создавался
	
	//Основной приём работы с сессией
	ses.set('name','value');
	ses.get('name');

Данные сессии это объект и можно добавлять значения в иерархию этого объекта

	ses.set('basket.list.DF2323','12'); //В данном случае объект сессии если до этого был пустой 
	//примет вид {basket:{list:{DF2323:'12'}}}
	ses.get('basket'); //Вернётся объект {list:{DF2323:'12'}}

В данном случае точка специальный символ определяющий уровень вложенность для сохраняемого значения. Так как точка также может быть в имени свойства для этого используется следующий синтаксис.
	
	ses.set(['basket','list','KF.56','1');
	ses.get('basket.list'); //или
	ses.get(['basket','list']); //Вернёт объект {'KF.56':'1'}
*
**/
/**/
if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');


//infra.load('*infra/default.js','r');

infra.Session=function(type){//Класс работает в node и javascript
	this.type=type;
	this.opt=infra.Session.options[type];

	if(infra.NODE&&!infra.Session.storage[this.type])infra.Session.storage[this.type]={};

	this.restore();
}
infra.storage={};
infra.Session.storage={};
infra.Session.options={//seri - возможно ли сериализовывать данные сессии
	'data':{save:1,sync:1,seri:1},
	'base':{save:1,sync:0,seri:1},
	'face':{save:0,sync:1,seri:1},
	'tamp':{save:0,sync:0,seri:1},//на клиенте хранить функции нельзя, испортятся при сохранении в localStorage и связь потеряется. На сервере можно
	'temp':{save:0,sync:0,seri:0}//Можно хранить функции, либо они будут либо нет, но не испортятся. На клиенте сессия теряется при обновлении. temp это аналог infra.stor с разницей что на сервере temp сохраняется при обновлениях и теряется при рестарте.
}
/*
	С помощью init получется объект сессии
	var session=infra.Session.init(type)
	Типов сессий 5: data, base, face, tamp, temp
	Аналог $_SESSION из php это сессия base - не синхронизируется, сохраняется на диск, на клиенте хранится в локальном хранилище

* @param {string} type тип сессии
* @return {object}
*/
infra.Session.init=function(type){
	var stor=infra.stor();
	if(!stor.sessions)stor.sessions={};
	if(!stor.sessions[type])stor.sessions[type]=new infra.Session(type);
	return stor.sessions[type];
}
infra.Session.prototype={
	getName:function(name){
		return 'session-'+this.type+'-'+name;
	},
	/*Устанавливается новый id*/
	setId:function(id){
		var er=function(msg){ console.log(msg||'Некорректный session id:'+id);};
		var nowid=this.getId();
		if(nowid==id)return er('Сессия уже установлена c id:'+id);
		if(!this.opt.sync)return er('Менять id можно только у синхронизируемых сессий');
		if(!id)return er();
		var s=id.split('-');
		if(s.length!=2)return er();
		if(s[0].length!=6)return er();
		if(s[1].length!=6)return er();


		var view=infra.View.get();

		var cookname=this.getName('id');
		view.setCOOKIE(cookname,id);

		var timename=this.getName('time');
		view.setCOOKIE(timename,0);//Время последней синхронизации сбрасывается, чтобы сервер передал все данные

		if(!infra.NODE){
			this.storageSave([],true);//Очистили локальное хранилище
		}
		this.restore();//Синхронно
		if(!infra.NODE)if(typeof(infrajs)!='undefined')infrajs.check();//Если запуски закончились запускается контроллер
	},
	getId:(function(){
		var 
		Chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
		Length=Chars.length,
		RndChar=function(){
			return Chars.charAt(Math.floor(Math.random() * Length));
		},
		gen=function(len){
			for(var s='',i=0;i<len;i++)s+=RndChar();
			return s;
		};
		return function(){
			var cookname=this.getName('id');
			var view=infra.View.get();
			var id=view.getCOOKIE(cookname);
			if(id)return id;
			var d=new Date();
			var XXXXXX=gen(6);
			var YY=String(d.getFullYear()).substr(2,2);
			var MM=String(d.getMonth()+101).substr(1,2);
			var DD=String(d.getDate()+100).substr(1,2);		
			var id=YY+MM+DD+'-'+XXXXXX;
			view.setCOOKIE(cookname,id);
			return id;
		}
	})(),
	restore:function(){//restore делается каждый view
		this.data={};
		
		var id=this.getId();
		var list=this.storageLoad();
		this.data=this.make(list,this.data);

		if(infra.NODE){
			var sesadm=infra.load('*session/sesadm.sjs');
			var fs=require('fs');
			var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+sesadm.folder+this.type+'/'+id+'/');
			if(this.opt.save&&!(stat&&stat.isDirectory())){
				this.storageSave(list,true);//Это нужно чтобы на сервере востанавливалась папка если её удалить
			}
		}
		if(!infra.NODE&&this.opt.sync){//Если при создании сессии мы видимо что она синхронизирована.. делаем запрос
			this.sync();
		}
	},
	storageMerge:function(){
		this.storageSave([{value:this.data}],true);
	},
	last_time:0,
	getUnickTime:function(){
		var t=new Date().getTime();
		while(t<=this.last_time)t++;
		this.last_time=t;
		return t;
	},
	storageSave:function(nlist,repl){//add
		if(repl){
			var list=nlist;
		}else{
			var list=this.storageLoad();//Всегда возвращает одномерный массив
			infra.fora(nlist,function(li){
				infra.forr(list,function(li2,i){//Нужно стереть упоминание о предыдущем установленном значении
					if(li2.str==li.str)return list.splice(i,1);
				});
				list.push(li);//и добавляем новое значение
			}.bind(this));
		}

		if(!list||typeof(list)!=='object') list=[];
		else if(list.constructor!==Array) list=[list];
		
		var id=this.getId();
		
		
		if(!infra.NODE){
			var dataname=this.getName('data');
			if(this.opt.seri)this.stor.setAr(dataname,list);
		}else{
			var sesadm=infra.load('*session/sesadm.sjs');
			
			infra.fora(list,function(li){
				if(!li.time)li.time=this.getUnickTime();
			}.bind(this));
			
			infra.Session.storage[this.type][id]=list;
			if(this.opt.save){
				if(repl){
					sesadm.rmfulldir(this.type,id)
				}
				infra.fora(nlist,function(li){
					sesadm.writeNew(this.type,id,li);
				}.bind(this));
			}
		}
	},
	stor:(function(){//функции для работы с локальным хранилищем браузера
		if(infra.NODE)return {};
		var iestor=false;
		if(!window.localStorage){
			var iestor=document.getElementsByTagName('head');
			if(iestor&&iestor[0]){
				iestor=iestor[0];
			}else{
				infra.error('Не найден элемент head для локального хранилища');
			}
			if(iestor&&iestor.addBehavior){
				iestor.addBehavior("#default#userData");
			}
			try{
				iestor.load('namespace');
				iestor.getAttribute('test');
			}catch(e){//infra.error(e,'stor.load',arguments.callee,"bug в ieTester ie6 Object doesn't doesn't support this property or method на getAttribute хотя alert(this.iestor.getAttribute) показывает функцию");
				iestor=false;//Просто будем на кукисах
			}
		}
		return {
			get:function(name){
				if(window.localStorage){
					name=window.localStorage[name];
				}else if(iestor){
					iestor.load('namespace');
					name=iestor.getAttribute(name);
				}else{
					var view=infra.View.init();
					name=view.getCOOKIE(name);
				}
				if(typeof(name)!=='string')name='';
				return name;
			},
			set:function(name,val){
				if(window.localStorage){
					window.localStorage[name]=val;
				}else if(iestor){
					this.iestor.setAttribute(name,val);
					this.iestor.save('namespace');
				}else{
					var view=infra.View.init();
					view.setCOOKIE(name,val);
				}
			},
			getAr:function(name){			
				var wait=this.get(name);
				if(wait)wait=infra.exec(wait,'session stor ar');
				if(!wait||wait.constructor!=Array)wait=false;
				return wait;
			},
			setAr:function(name,val){
				if(!val||val.constructor!=Array)val='';
				this.set(name,infra.Session.prototype.source(val));
			}
		}
	})(),
	storageLoad:function(){
		if(infra.NODE){
			var sesadm=infra.load('*session/sesadm.sjs');
			return sesadm.loadNew(this.type,this.getId());
		}else{
			if(!this.opt.seri)return [];
			var res=this.stor.get(this.getName('data'));
			if(res)res=infra.exec(res,'storageLoad');
			if(!res||res.constructor!=Array)res=[];
			return res;
		}
		
	},
	/*sort_time:function(a,b){
		a=a['time'];
		b=b['time'];

		if(a==b)return 0;
		return (a < b) ? -1 : 1;
	},
	
	serverPut:function(){//Отправляем все данные клиента как новые
		this.storageMerge();
		this.sync(this.storageLoad());
	},
	serverTake:function(){//Клиент забирает все данные сервера как новые
		var li={};
		this._set(li);
		this.storageSave(li);
		this.sync();
	},*/
	syncreq:function(list,sync,callback){//новое значение
		//Отправляется пост на файл, который записывает и возвращает данные
		var data={//id и time берутся из кукисов на сервере
			isempty:(!infra.foro(this.data,function(){return true}))?1:'',//Даёт возможность серверу запросить все данные
			type:this.type,
			list:this.source(list)
		}
		var set='jpx';
		if(sync=='async')set+='a';
		infra.load('*session/sync.njs',set,data,function(err,ans){
			if(!ans)return;
			if(ans.msg)alert(ans.msg);
			var timename=this.getName('time');
			var view=infra.View.get();
			
			view.setCOOKIE(timename,ans.time);
			
			//По сути тут set(news) но на этот раз просто sync вызываться не должен, а так всё тоже самое
			this.data=this.make(ans.news,this.data);
			this.storageSave(ans.news);
			
			if(ans.sentall){
				if(!this.get('__verify__')){
					var list=this.pname('__verify__');
					list.value=1;
					this.data=this.make(list,this.data);
					this.storageSave(list);
				}
				this.syncreq(this.storageLoad(),sync,callback);
				//callback();
			}else{
				callback();
			}
		}.bind(this));
	},
	sync:function(list,sync,callback){
		if(!callback)callback=function(){};
		if(sync!='async'){//Если синхронно, то не делается check, Если просто вызыван sync с одним параметром или без
			return this.syncreq(list,sync,callback);//синхронно вызываем сразу, вразрез с асинхронными
		}
	
		var sentname=this.getName('sent');
		var waitname=this.getName('wait');
	
		var wait=this.stor.getAr(waitname);
		if(wait&&list)			wait.push(list);
		else if(wait&&!list) 	wait=wait;
		else if(!wait&&list)	wait=[list];
		else if(!wait&&!list)	wait=[];
		this.stor.setAr(waitname,wait);
	
		if(this.syncing)return this.syncing.push(callback);
		else this.syncing=[callback];
	
		var next=function(){//Возвращается был новый запрос или нет.
			var sent=this.stor.getAr(sentname);//в sent хранится что уже в процессе отправления
			var wait=this.stor.getAr(waitname);//в wait скадывается всё новое что нужно отправить
			if(!sent&&!wait)return false;//Отправлять нечего. При пустой синхронизации будет true wait []
		
			if(!sent)sent=[];//Далее собираем всё в sent очищаем wait
			if(wait)sent.push(wait);//sent и wait могут быть одновременно если был разрыв связи при прошлом запросе
			
			this.stor.setAr(sentname,sent);//Всё записалось в sent и после успешной отправки очистится
			this.stor.setAr(waitname,false);//wait становится пустым, но пока будет отправка он может наполняться
		
			this.syncreq(sent,sync,function(){
				this.stor.setAr(sentname,false);
				var r=next();
				if(!r){//А если был запрос, попадём сюда снова после его окончания
					var calls=this.syncing;//Чтоб небыло замыканий прежде чем запускать обработчики очищается syncing
					this.syncing=false;
					infra.forr(calls,function(ca){ ca() });
				}
			}.bind(this));
			return true;
		}.bind(this);
		setTimeout(next,1);
	},
	source:function(obj,exceptions,level){
		exceptions=exceptions||{};
		level=level||0;
		if(level==11)obj='level>10';
		var str='';
		if(obj&&typeof(obj)=='object'){
			var arr=(obj.constructor===Array);

			str+=arr?'[':'{';
			var first=true;
			for(var i in obj){
				if(exceptions[i])continue;
				level++;
				var r=this.source(obj[i],false,level);
				level--;
				if(r!==''){
					if(!first)str+=',';
					first=false;
					if(arr){
						str+=r;
					}else{
						str+='"'+i.replace(/\"/g,'\"')+'"'+':'+r;
					}
				}
			}
			str+=arr?']':'}';
		}else if(typeof(obj)=='string'){
			str+='"'+obj.replace(/"/g,'\\"').replace(/[\n\r]/g,'\\n')+'"';//
		}else if(typeof(obj)=='number'||typeof(obj)=='boolean'){
			str+=obj;
		}else{//function, null и undefined игнорируются вместо них возвращается null что будет значить что этих элементов нет
			str='undefined';
			//str='' Это решает проблему обновления когда файл был удалён, дело в том что он не будет найден при пробежке. По этому теперь файлы удаляюстя только если что-то добавилось на уровень выше иначе, будет записан null в файл js на севере что и будет означать его удаление.
		}
		return str;
	},
	names:function(names){//Функция принимает строку-путь а возвращает массив-путь
		if(typeof(names)!='string')return names||[];
		if(!names)return [];
		var ar=names.split('.');
		for(var i=0;i<ar.length;i++){
			if(ar[i]==''){
				ar.splice(i,1);
			}else{
				ar[i]=ar[i].replace(this.sign,'.');			
			}
		}
		return ar;
	},
	namestr:function(names){
		var str=[];
		infra.fora(names,function(val){
			val=val.replace('.',this.sign);
			str.push(val);
		}.bind(this));
		return str.join('.');
	},
	sign:'?',
	pname:function(list){
		//Приводит к стандартному виду путь/и к значению  
		/*
			Разбор имени name происходит в двух режимах. Строка и массив. Если массив разделителем является ?. Если строка разделитель точка.
			Всё приводится к виду массива - это нормализованный вид.
			'name.name'=['name','name']
			'name?name'=['name?name']
			['name?name']=['name','name']
			[] - []
			
			[''] - [{name:[],str:''}]
			'some' - {name:['some'],str:'some'},
			['som.e','one'] - [['som.e'],'one'] - {name:['som.e','one'],str:'som?e.one'},
			
			['som?e','one'] - ['som','e','one'] - {name:['som.e','one'],str:'som?e.one'},
			[['som','e'],'one'] - [['som,'e','one'] - {name:['som','e','one'],str:'som?e?one'},
			[{name:'some'},{name:'some'}] - [{name:['some'],str:'some'},{name:['some'],str:'some'}]
			'some.one' - {name:['some','one'],str:'some.one'}
			'some?one' - {name:['some.one'],str:'some?one'}
			
		*/
		
		if(typeof(list)=='string'){
			var res={name:list};//name тип строка
		}else if(list&&typeof(list)=='object'){
			var first=infra.fora(list,function(val){return val;});
			if(list.constructor==Array){
				if(typeof(first)=='string'||(first&&typeof(first)=='object'&&first.constructor==Array)){//name тип массив
					res={name:[]};
					infra.fora(list,function(n){
						n=n.split(this.sign);
						infra.forr(n,function(n){
							res.name.push(n);
						});
					}.bind(this));
				}else if(first&&typeof(first)=='object'){//list это список li
					infra.fora(list,function(val,key,group){
						group[key]=this.pname(val);
					}.bind(this));
					res=list;
				}else{
					res=[];
				}
			}else{//объект
				res=list;
			}
		}else{
			res=[];
		}
		infra.fora(res,function(val){
			val.name=this.names(val.name);
			if(!val.str)val.str=this.namestr(val.name);
		}.bind(this));
		return res;
	},
	make:function(list,data){
		if(!data)data={};
		if(!list)return data;
		infra.fora(list,function(li){
			data=this._set(li,data);
		}.bind(this));
		return data;
	},
	set:function(list,value,sync){
		var list=this.pname(list);
		infra.fora(list,function(li){
			li.value=value;
		});
		
		//При set делается 3 действия
		this.data=this.make(list,this.data);//1
		this.storageSave(list);//2
		if(!infra.NODE&&this.opt.sync)this.sync(list,sync||'async',function(){
			if(typeof(infrajs)!='undefined')infrajs.check();//Если запуски закончились запускается контроллер
		});//3
		
	},
	clear:function(){
		this.data={};
		this.storageSave([],true);//Очистили локальное хранилище
	},
	isSet:function(list,nameneed){//Найти новое устанавливаемое занчение для nameneed командой li {name:.., value:..}
		return infra.fora(list,function(li){
			nameset=this.names(li.name);
			nameneed=this.names(nameneed);
			var r=!infra.forr(nameset,function(set,i){
				if(!nameneed[i])return false;//Путь до переменной за которой следим уже закончился, как например (следим)config.sync (меняем)config
				if(nameneed[i]!==nameset[i])return true;//Если не совпадают значит переменная nameset не влияет на nameneed (следим)config.save (меняем)config.sync
			});
			if(r)return r;
		}.bind(this));
	},
	//Какое значение устанавливает данный list c таким-то именем = get(
	isSetVal:function(list,nameneed){//Сначало вызывается isSet
		//var newname=nameneed.replace(li.name,'');
		var data=this.make(list);
		return this._get(nameneed,data);//Например, undefined будет значить что не ставится или ставится undefined
		
		//this.get(nameneed,list);//означет дай name с учётом значений переданных в list
	},
	_set:function(li,data){//Сохраняет в переменную data
		if(!data)data={};
		var names=this.names(li.name);
		if(arguments.length>1){
			var ses=arguments[1];
			if(names.length&&typeof(ses)!=='object')ses={};
		}else{
			if(names.length&&typeof(data)!=='object')data={};
			var ses=data;
		}
		for(var i=0,l=names.length-1;i<l;i++){
			var n=names[i];
			if(typeof(ses[n])!='object'){
				if(typeof(li.value)=='undefined')return;//Как-то нужно избавляться от удалённых значений
				ses[n]={};
			}
			ses=ses[n];
		}
		if(l+1){
			var n=names[names.length-1];
			if(typeof(li.value)=='undefined'){
				if(typeof(ses[n])!='undefined')delete ses[n];		
			}else{
				ses[n]=li.value;
			}
		}else{
			data=li.value;
		}
		if(typeof(data)!=='object')data={};
		return data;
	},
	_get:function(names,data){
		var names=this.names(names);
		if(names.length&&typeof(data)!=='object')return;
		for(var i=0,l=names.length-1;i<l;i++){
			var n=names[i];
			if(!data.hasOwnProperty(n)||typeof(data[n])!='object')return;
			data=data[n];
		}
		if(l+1){
			var n=names[l];
			if(data.hasOwnProperty(n))return data[n];
			else return;
		}else{
			return data;
		}
	},
	get:function(name,data){
		if(arguments.length>1){//data может быть undefined
			var ses=data;
		}else{
			var ses=this.data;
		}
		return this._get(name,ses);
	},
	/*get:function(name,list){
		if(arguments.length>1){//list может быть undefined
			var list=[this.storageLoad(),list];
			var ses=this.make(list);
		}else{
			var ses=this.data;
		}
		return this._get(name,ses);
	},*/
	
	
	
	//Считывает из переменной data
	/*copy:function(){},//Создаётся не синхронизированная копия, на сервере созданная копия не сохранена
	//create:function(){},//Создаётся новая пустая сессия не сохранённая на сервере
	load:function(){},//Загрузить существующую сессию
	save:function(){},//Начать синхронизировать сессию с сервером. На сервере данные из классической сессии начинаются записываться напостоянно в хранилище на жёстком.
	agentSave:function(){},//Вызывается при set. На сервере это будет означать запись в переменную сессии
	agentLoad:function(){},//Вызывается при set. Загрузка из серверной сессии. На сервере ещё нужно идентифицировать каждого агента... наверно
	connect:function(){},//Когда сессия синхронизирована вызывается каждые x секунд, и вызывается при set. Передаёт и получает обновления. Передаваемые обновления приоритетней получаемых. Если сессия является сохранённой то на сервере также каждые x секунд идёт проверка изменений.
	time:0 //Время последней синхронизации
	*/
	
	//depricated
	save:function(name,value,callback){
		this.set(name,value);
		if(callback)alert('session callback');
	},
	load:function(name,def,valuetrue){
		var value=this.get(name);
		if(typeof(valuetrue)!=='undefined'&&value)return valuetrue;
		if(typeof(value)=='undefined')return def;
		return value;
	},
	loadValue:function(name,def){//load для <input value="...
		var value=this.get(name);
		if(typeof(value)=='undefined')value=def;
		value=value.replace(/"/g,'&quot;');
		return value;
	},
	loadText:function(name,def){//load для <texarea>...
		var value=this.get(name);
		if(typeof(value)=='undefined')value=def;
		value=value.replace(/</g,'&lt;');
		value=value.replace(/>/g,'&gt;');
		return value;
	}
};


