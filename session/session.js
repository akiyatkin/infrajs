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


//infra.load('*infra/default.js','r');

infra.require('*infra/ext/seq.js');
/*
	С помощью init получется объект сессии
	var session=infra.session;
	Типов сессий 5: data, base, face, tamp, temp
	Аналог $_SESSION из php это сессия base - не синхронизируется, сохраняется на диск, на клиенте хранится в локальном хранилище

* @param {string} type тип сессии
* @return {object}
*/
infra.session={
	init:function(){
		this.init=function(){};
		var list=this.storageLoad();
		this.data=this.make(list,{});
		this.sync();
	},
	getLink:function(){
		var id=infra.view.getCookie(this._getName('id'));
		if(!id)return '';
		var host=infra.view.getHost();
		var path=infra.view.getRoot(window.ROOT);
		var pass=infra.view.getCookie(this._getName('pass'));
		var link='http://'+host+'/'+path+'infra/plugins/session/login.php?id='+id+'&pass='+pass;
		return link;
	},
	_getName:function(name){
		return 'infra_session_'+name;
	},
	stor:(function(){//функции для работы с локальным хранилищем браузера
		var iestor=false;
		var localstor=false;

		var is=false;
		try{ is=!!window.localStorage; }catch(e){};
		try{ isses=!!window.sessionStorage; }catch(e){};
		if(!is&&!isses){
			var iestor=document.getElementsByTagName('head');
			if(iestor&&iestor[0]){
				iestor=iestor[0];
			}else{
				infra.error('Не найден элемент head для локального хранилища');
				return {};
			}
			if(iestor&&iestor.addBehavior){
				iestor.addBehavior("#default#userData");
			}else{
				localstor={};
			}
			try{
				iestor.load('namespace');
				iestor.getAttribute('test');
			}catch(e){//infra.error(e,'stor.load',arguments.callee,"bug в ieTester ie6 Object doesn't doesn't support this property or method на getAttribute хотя alert(this.iestor.getAttribute) показывает функцию");
				iestor=false;//Просто будем на кукисах
			}
		}
		return {
			load:function(name){
				if(is){
					var path=infra.view.getPath();
					var val=window.localStorage[path+'.'+name];
				}else if(isses){
					var path=infra.view.getPath();
					var val=window.sessionStorage[path+'.'+name];
				}else if(iestor){
					iestor.load('namespace');
					var val=iestor.getAttribute(name);
				}else{
					//var view=infra.View.init();
					//name=view.setCOOKIE(this._getName('time'),0);//Хранение локально невозможно
					var val=localstor[name];
				}
				//infra.exec(val,'session stor ar');
				try {
					if(val)val=eval('('+val+')');
				}catch(e){
					val=[];
				}
				return val;
			},
			save:function(name,list){
				list=infra.session.source(list);
				if(is){
					var path=infra.view.getPath();
					window.localStorage[path+'.'+name]=list;
				}else if(isses){
					var path=infra.view.getPath();
					window.sessionStorage[path+'.'+name]=list;
				}else if(iestor){
					iestor.setAttribute(name,list);
					iestor.save('namespace');
				}else{
					localstor[name]=list;
				}
			}
		}
	})(),
	storageLoad:function(){//get
		var res=this.stor.load(this._getName('data'));
		if(!res)res=[];
		return res;
	},
	dataSave:function(nlist,repl){
		if(repl){
			this.data=this.make(nlist,{});
		}else{
			this.data=this.make(nlist,this.data);
		}
	},
	storageSave2:function(nlist,repl){//set
		//nlist это корректный список {name:'',value:''}
		if(repl){
			var list=this.right(nlist);
		}else{
			var list=this.storageLoad();
			list.push(nlist);
			list=this.right(list);
		}
		var dataname=this._getName('data');
		this.stor.save(dataname,list);
	},
	storage_repl:false,
	storage_process:false,
	storage_wait:[],
	storageSave:function(nlist,repl){//set
		//nlist это корректный список {name:'',value:''}
		if(repl){
			this.storage_repl=true;
			this.storage_wait=[nlist];
		}else{
			this.storage_wait.push(nlist);
		}
		if(!this.storage_process){
			this.storage_process=true;
			var that=this;
			setTimeout(function(){
				that.storage_process=false;
				var repl=that.storage_repl;
				that.storage_repl=false;
				var nlist=that.storage_wait;
				that.storage_wait=[];
				if(repl){
					var list=that.right(nlist);
				}else{
					var list=that.storageLoad();
					list.push(nlist);
					list=that.right(list);
				}
				var dataname=that._getName('data');
				that.stor.save(dataname,list);
			},1);
		}
	},
	syncreq:function(list,sync,callback){//новое значение, //Отправляется пост на файл, который записывает и возвращает данные
		var cb=function(ans){
			if(!ans||!ans.result)return callback('error');
			if(ans.msg)alert(ans.msg);
			if(!ans.is.session_id){
				this.logout();
				return callback();
			}
			var timename=this._getName('time');
			infra.view.setCookie(timename,ans.time);//Время определяется на сервере, выставляется на клиенте
			
			//По сути тут set(news) но на этот раз просто sync вызываться не должен, а так всё тоже самое
			this.storageSave(ans.news);
			this.dataSave(ans.news);
			
			callback();
		}.bind(this);
		var data={//id и time берутся из кукисов на сервере
			list:this.source(list)
		}
		var load_path=infra.theme('*session/sync.php');

		$.ajax({
			url:load_path,
			timeout:120000,
			async: !sync,
			type:'POST',
			data:data,
			dataType: 'json',
			complete: function(req) {
				try{
					var ans=eval("("+req.responseText+")");
				}catch(e){
					var ans=false;
				}
				cb(ans);
			}
		});
	},
	logout:function(){
		var view=infra.view;
		this.storageSave([],true);
		this.data={};

		var sentname=this._getName('sent');
		var waitname=this._getName('wait');
		this.stor.save(waitname,false);
		this.stor.save(sentname,false);

		view.setCookie(this._getName('time'));//Время определяется на сервере, выставляется на клиенте
		view.setCookie(this._getName('id'));
		view.setCookie(this._getName('pass'));
	},
	getId:function(){
		var view=infra.view;
		return view.getCOOKIE(this._getName('id'));
	},
	getTime:function(){//Нужно для определения последнего сеанса связи с сервером
		var view=infra.view;
		return view.getCOOKIE(this._getName('time'));
	},
	right:function(list){
		var rsent=[]; 
		infra.fora(list,function(li){
			var short=infra.seq.short(li.name);
			if(infra.forr(rsent,function(rli){
				if(infra.seq.short(rli.name)==short)return true;
			}))return;
			rsent.unshift(li);
		},true);
		return rsent;
	},
	wait:[],
	callbacks:[],
	process:false,
	process_timer:false,
	sync:function(list,sync,callback){
		if(!callback)callback=function(){};
		if(!this.getId()&&(!list||(list.constructor==Array&&list.length==0))){//Если ничего не устанавливается и нет id то sync не делается
			return callback();
		}

		this.wait.push(list);
		if(typeof(callback)=='function')this.callbacks.push(callback);
		var that=this;
		if(sync){
			list=that.wait;
			that.wait=[];
			if(that.process){
				clearTimeout(that.process_timer);
				that.process=false;
			}
			that._sync(list,sync,function(){
				for(var i=0,l=that.callbacks.length;i<l;i++){
					that.callbacks[i]();
				}
				that.callbacks=[];
			});
		}else{
			if(that.process)return;
			that.process=true;
			clearTimeout(that.process_timer);
			that.process_timer=setTimeout(function(){
				that.process=false;
				var list=that.wait;
				that.wait=[];
				that._sync(list,sync,function(){
					for(var i=0,l=that.callbacks.length;i<l;i++){
						that.callbacks[i]();
					}
					that.callbacks=[];
				});
			},1);
		}
	},
	_sync:function(list,sync,callback){
		var sentname=this._getName('sent');
		var waitname=this._getName('wait');
		

		var wait=this.stor.load(waitname);//Задержка
		if(wait&&list)		wait.push(list);
		else if(wait&&!list) 	wait=wait;
		else if(!wait&&list)	wait=[list];
		else if(!wait&&!list)	wait=[];
		wait=this.right(wait);
		var conf=infra.config();
		if(conf.session.sync&&sync){//Если просто вызыван sync с одним параметром или без
			this.stor.save(sentname,wait);//Всё записалось в sent и после успешной отправки очистится
			this.stor.save(waitname,false);//wait становится пустым, но пока будет отправка он может наполняться
			return this.syncreq(wait,sync,function(err){
				if(err){
					this.stor.save(waitname,wait);
					this.stor.save(sentname,false);
					//this.syncreq(list,sync,arguments.callee);
					callback(err);
				}else{
					this.stor.save(sentname,false);//Всё записалось в sent и после успешной отправки очистится
					callback(err);
				}
			}.bind(this));//синхронно вызываем сразу, вразрез с асинхронными
		}

		
		this.stor.save(waitname,wait);

		if(!conf.session.sync){
			callback(false);
			return;
		}
		if(this.syncing){
			this.syncing.push(callback);//при ошибке сессия больше не обновляется.. обработчике копятся.. и тп..
			return;
		}else{
			this.syncing=[callback];
		}
	
		var next=function(){//Возвращается был новый запрос или нет.
			var sent=this.stor.load(sentname);//в sent хранится что уже в процессе отправления
			var wait=this.stor.load(waitname);//в wait скадывается всё новое что нужно отправить
			if(!sent&&!wait)return false;//Отправлять нечего. При пустой синхронизации будет true wait []
		
			if(!sent)sent=[];//Далее собираем всё в sent очищаем wait
			if(wait)sent.push(wait);//sent и wait могут быть одновременно если был разрыв связи при прошлом запросе

			sent=this.right(sent);
			
			this.stor.save(sentname,sent);//Всё записалось в sent и после успешной отправки очистится
			this.stor.save(waitname,false);//wait становится пустым, но пока будет отправка он может наполняться
		
			this.syncreq(sent,sync,function(err){
				this.stor.save(sentname,false);
				if(err){
					//setTimeout(next,5000);
					var wait=this.stor.load(waitname);
					if(wait)sent.push(wait);//добавили текущий sent в начало wait
					this.stor.save(waitname,this.right(sent));

					//фильтр натыкали а позиции показались без фильтра
					var calls=this.syncing;
					infra.forr(calls,function(ca){ ca(true) });
					this.syncing=false;
					conf.session.sync=false;//Ошибка отправка на сервер больше не будет работать пока не обновится страница
				}else{
					var r=next();
					if(!r){//А если был запрос, попадём сюда снова после его окончания
						var calls=this.syncing;//Чтоб небыло замыканий прежде чем запускать обработчики очищается syncing
						this.syncing=false;
						infra.forr(calls,function(ca){ ca(false) });
						infra.fire(this,'onchange');
					}
				}
			}.bind(this));
			return true;
		}.bind(this);
		setTimeout(next,1);
	},
	source:function(obj,exceptions,level){
		if(window.JSON){
			return JSON.stringify(obj);
		}
		exceptions=exceptions||{};
		level=level||0;
		if(level==11)obj='level>10';
		var str='';
		if(obj&&typeof(obj)=='object'){
			var arr=(obj.constructor===Array);
			str+=arr?'[':'{';
			var first=true;
			for(var i in obj){
				if(!obj.hasOwnProperty(i))continue;
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
			str='null';
		}
		return str;
	},
	make:function(list,data){
		infra.fora(list,function(li){
			data=infra.seq.set(data,li.name,li.value);
		}.bind(this));
		return data;
	},
	get:function(name,def){//data может быть undefined
		this.init();
		name=infra.seq.right(name);
		var val=infra.seq.get(this.data,name);
		if(val===undefined)return def;
		return val;
	},
	set:function(name,value,sync){
		//if(this.get(name)===value)return; //если сохранена ссылка то изменение её не попадает в базу данных и не синхронизируется
		var li={name:infra.seq.right(name),value:value};
		if(li.name[0]=='safe')return false;
		//При set делается 2 действия
		

		this.storageSave(li);//Задержка!!!!
		this.dataSave(li);
		var fn=sync;
		if(typeof(fn)!=='function')fn=function(){};
		this.sync(li,!!sync,fn);//2 true синхронно
	},
	getValue:function(name,def){//load для <input value="...
		var value=this.get(name);
		if(typeof(value)=='undefined')value=def;
		value=value.replace(/"/g,'&quot;');
		return value;
	},
	getText:function(name,def){//load для <texarea>...
		var value=this.get(name);
		if(typeof(value)=='undefined')value=def;
		value=value.replace(/</g,'&lt;');
		value=value.replace(/>/g,'&gt;');
		return value;
	}
};
