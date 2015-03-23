/*
URL control - http://itlife-studio.ru
Copyright 2008-2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru

//Механизм записи через символ или массивом некую последовательность (state в адресе, путь в сессии до свойства)
a - запись разделитель (.) заменитель (*)
	'as*df.asdf' - краткая форма, 
	['as.df','asdf'] - правильая форма
	'as*df.asdf'
	['as.df','asdf'] - правильая форма

	['as.df','as*df'] - правильая форма, при нахождении символа * в правильной форме ошибка
	as*df.as*df - краткая форма
	['as.df','as.df'] seldom символ в краткой форме превратится в точку в правильной форме.
	as*df.as*df - краткая форма


*/

infra.require('*infra/ext/events.js');
infra.require('*infra/ext/seq.js');
infra.State=function(name,parent){
	this.name=name;
	this.parent=parent;
	this.childs={};
	if(!parent)this.link='./';
	else if(!parent.parent)this.link=parent.link+name;
	else this.link=parent.link+'/'+name;

};
infra.State.forFS=function(str){
	//Начинаться и заканчиваться пробелом не может
	//два пробела не могут идти подряд
	//символов ' " /\#&?$ быть не может заменяются на пробел
	//& этого символа нет, значит не может быть htmlentities
	//символов <> удаляются из-за безопасности
	//Виндовс запрещает символы в именах файлов  \/:*?"<>|
	str=str.replace(/[\*<>\'"\|\:\/\\\\#\?\$&]/g,' ');
	str=str.replace(/^\s+/g,'');
	str=str.replace(/\s+$/g,'');
	str=str.replace(/\s+/g,' ');
	return str;
}
/*infra.State.normalHref=function(href){//?asdf.asdf/asdf/adf
	var r=href.split('?');
	if(r.length>1){
		try{//error malfomed URI
			href='?'+decodeURI(r[1]);
		}catch(e){
			href='?'+r[1];
		}
	}else{
		return '?';
	}
	var parsed=infra.State.parser.parse(href);
	var state=this.getState(parsed.state);
	var obj=state.getRight(parsed);
	href=infra.State.parser.getQuery(obj);
	return href;
}*/
infra.State.setA=function(div){
	if(typeof(div)=='string'){
		div=document.getElementById(div);
	}
	if(!div)return;
	var as=div.getElementsByTagName('a');

	for(var i=0,len=as.length; i<len; i++){
		var a = as[i];
	

		var notweblife=a.getAttribute('notweblife');//depricated
		if(notweblife == 'true') continue;//У ссылки может быть запрет на проверку

		var weblife=a.getAttribute('weblife');//depricated
		if(weblife)continue;

		/*var weblife_refresh=a.getAttribute('weblife_refresh');
		if(weblife == 'true'&&!weblife_refresh) continue;//Ссылка проверена обновлять её не нужно */


		var ainfra=a.getAttribute('infra');
		var ainfra_refresh=a.getAttribute('infra_refresh');
		if(ainfra == 'true'&&!ainfra_refresh) continue;//Ссылка проверена обновлять её не нужно

		a.setAttribute('infra','true');

		var href=a.getAttribute('weblife_href');//Повторно заходим если мягкое изменение адреса
		var isfirst=!href;

		
		if(isfirst){
			href=a.getAttribute('href');
			if(typeof(href)=='undefined'||href==null)continue;//У ссылки нет ссылки
			if(/^javascript:/.test(href))continue;
		}

		var r=href.split('?');
		if(r.length>1){
			var quest=true;
			var beforequest=r[0];
			try{//error malfomed URI
				var href='?'+decodeURI(r[1]);
			}catch(e){
				var href='?'+r[1];
			}
		}else{
			var quest=false;
			var beforequest=href;
			var href='?';
		}
		var t=beforequest.split('/');
		
		if(t.length>=3){
			var method=t.shift();
			if(method=='http:'||method=='https:'){
				t.shift();//слэш пустой
				sitehost=t.shift();
				siteroot=t.join('/');
				beforequest=siteroot;
				if(isfirst){
					var islocal=location.host=='127.0.0.1'||location.host=='localhost';
					if((method=='http:'&&sitehost==location.host&&('/'+siteroot==location.pathname))){
						//Домен есть но он совпадает с текущим включая siteroot. Значит просто домен не учитывается.
					}else{
						var target=a.getAttribute('target');
						//if(!target&&!infra.forr(infra.conf.http.honeydomains,function(d){ //Проверка открывать в текущем окне или в новом
						//	if(sitehost==d)return true;
						//}))
						a.setAttribute('target','_blank');//Если target не установлен
						continue;
					}
				}
			}
		}
	
		if(isfirst){
			if(beforequest)continue;//В ссылке есть что-то до вопроса
		}
	
		/*
		if(isfirst){
			href=a.getAttribute('href');
			if(typeof(href)=='undefined'||href==null)continue;//У ссылки нет ссылки
			if(/^javascript:/.test(href))continue;

			var r=href.split('?');
			if(r.length>1){
				var h=r[0];
				var href='?'+decodeURI(r[1]);
			}else{
				var h=href;
				var href='?';
			}
			var t=h.split('/');
			var sitehost=infra.conf.http.sitehost;
			var siteroot=infra.conf.http.siteroot;
			if(t.length>=3){
				var method=t.shift();
				t.shift();//слэш пустой

				sitehost=t.shift();
				siteroot=t.join('/');

				if(isfirst){
					var islocal=location.host=='127.0.0.1'||location.host=='localhost';
					if((method=='http:'&&sitehost==location.host&&('/'+siteroot==location.pathname))){
						//t.shift();//домен выкинули
					}else{
						var r=infra.forr(infra.conf.http.honeydomains,function(d){
							if(sitehost==d)return true;
						});
						if(!r){
							var target=a.getAttribute('target');
							if(!target)a.setAttribute('target','_blank');//Если target не установлен
						}
						continue;
					}
				}
			}
		}*/
		var parsed=this.parser.parse(href);
		var target=a.getAttribute('target');
		if(target||!parsed){//Это какая-то левая ссылка
			if(!target)a.setAttribute('target','_blank');//Если target не установлен
			continue;
		}
	
		if(typeof(a.onclick)==='function'){
			var old_func=a.onclick;
		}else{
			var old_func=function(){};
		}
		
		
		if(isfirst)a.setAttribute('weblife_href',href);//Признак того что эта ссылка внутренняя и веблайфная... так сохраняется первоначальный адрес
		if(parsed.ind[0]=='&'||parsed.ind[1]=='&') a.setAttribute('ainfra_refresh','true');//Признак того что эта ссылка внутренняя и веблайфная... 
		
		var state=this.getState(parsed.state);
		var obj=state.getRight(parsed);
		href=this.parser.getQuery(obj);
		//if(href&&!history.pushState)href=encodeURI(href); Проблемы в node ie передаёт не закодированный адрес и сервер не видит этого
		if(href){
			try{
				href=decodeURI(href);//Нужно для печати чтобы ссылки были без процентов
			}catch(e){ }
		}

		var siteroot=infra.view.getRoot();
		
		var sethref=href?('http://'+location.host+'/'+siteroot+'?'+encodeURI(href)):('http://'+location.host+'/'+siteroot);
		a.setAttribute('href',sethref);//Если параметров нет то указывам путь на главную страницу

		if(isfirst){

			a.onclick=function(old_func,a){
				return function(){
					
					if(typeof(event)!=='undefined'&&event.returnValue===false)return false;
					var re=old_func.apply(a);
					if(re===false){
						if(typeof(event)!=='undefined')event.returnValue=false;
						return false;
					}
					var nohref=a.getAttribute('nohref');
					if(nohref)return false;
					if(/\?/.test(a.href)){
						//var param=a.href.substring(a.href.indexOf('?')+1);
						var h=a.href;
						//try {
							h=decodeURI(h);
						//}catch(e){

						//}
						var ar=h.split('?');
						ar.shift();
						var param=ar.join('?');
					}else{
						var param='';
					}
					if(!/^=/.test(param)){
						param='/'+param;
					}else{
						param=''+param;
					}
					//setTimeout(function(){//Текущий процесс выполнения скрипта закончится с показом всех слоёв
						try{
							infra.State.set(param);
						}catch(e){
							console.error(e);
						}
					//},1);
					if(typeof(event)!=='undefined')event.returnValue=false;
					return false;
				}
			}(old_func,a);
		}
	}
}
infra.State.init=function(){
	this.init=function(){};
	var listen=function(){		
		var query=infra.view.getQuery();
		if(infra.State.get()==query)return;//chrome при загрузки запускает собыите а FF нет. Первый запуск мы делаем сами по этому отдельно для всех а тут игнорируются совпадения.
		infra.State.set(query,'back or forward or first');
	}
	if(history.pushState){ //Первый запус должен проходить после того как все слои подключились все кому интересно подписались на события и потом проходит событие и всё работает
		window.addEventListener('popstate',listen, false); //Генерировать заранее нельзя
	}
	listen();//Даже если html5 не поддерживается мы всё равно считаем первую загрузку а дальше уже будут полные переходы и всё повториться
}

infra.State.go=function(href){
	this.set(href);
}
infra.State.getState=function(state_mix){
	var store=this.store();
	if(!store.first)store.first=new infra.State();
	return store.first.getState(state_mix);
}



infra.State.get=function(){
	var store=this.store();
	return store.query;
}
infra.State.set=function(href,auto){//href без # ? типа asdf/asdf. auto означает что это движение по истории и записывать это движения в конец истории не надо так как оно уже там
	var parsed=this.parser.parse(href);
	var state=this.getState();
	var obj=state.getRight(parsed);
	infra.State.popstate=auto;//Метка о том новый переход или движение по истории
	var query=this.parser.getQuery(obj);
	var store=this.store();
	if(!auto&&store.query!==query){//typeof чтобы не зацикливались когда нет pushState
		var path=query?('?'+encodeURI(query)):location.pathname;
		document.http_referrer=location.href;

		if(history.pushState){
			history.pushState(null,null,path);//При переходе назад этой записи не должно быть
		}else{
			if(window.console)console.log('Нет history.pushState');
			return location.href=query?('?'+query):location.pathname;	
		} 
	}
	var view=infra.view;
	//console.log(''+(new Date()).toLocaleTimeString()+' '+query+'\nAgent: '+view.getAGENT()+'\nRefer: '+decodeURI(view.getREF()));
	store.query=query;
	state.prepare(obj,state.obj);
	state.notify();
	//setTimeout(function(){//Текущий процесс выполнения скрипта закончится с показом всех слоёв
		//это нужно чтобы цепочка Infrajs.check встала друг за другом, и не врывалась по середине
	//if(!infra.session.isSync()){
		infra.fire(infra.State,'onchange');//слушаем и запускаем infrajs.check
	//}else{
	//	infra.when(infra.session,'onsync',function(){
	//		infra.fire(infra.State,'onchange');
	//	});
	//}
	//},1);//Клик по ссылке должен быть обратан после всех click обработчиков
}
infra.State.store=function(name){
	if(!this.store.data)this.store.data={};
	if(!name)return this.store.data;
	if(!this.store.data[name])this.store.data[name]={};
	return this.store.data[name];
}
infra.State.getQuery=function(){
	var store=this.store();
	return store.query;
}

infra.State.parser={};
infra.State.parser.afterDomain=function(href){//Проверка является ли указанный адрес адресом сборки, ссылается на страницу этого сайта
	/*if(href&&/^#/.test(href)==false&&/^\?/.test(href)==false){
		return false;
	}*/
	var view=infra.view;
	var mydomain=view.getPath();

	var dom=/^http\:\/\/([a-zA-Z0-9\-\.\\\/]+)([\/#\?]*)/.exec(href);
	
	if(dom){
		var domain=dom[1];
		if(domain!==mydomain){
			return false;
		}else{
			domain+=dom[2];
		}
		var h=href.replace('http:/'+'/'+domain,'');
	}else{
		var h=href;
	}
	if(!h)return '/';
	h=h.replace(/^[\?#]+/,'');
	return h;
}
infra.State.parser.getObj=function(strurl,state){
	state=state||[];

	/*Принимает строку которая переводится в объект... 
		/some/foo - some объект foo свойство
		=some/foo=moo - foo свойство, moo значение значит свойство foo простое
		/some/foo/moo - foo свойство, moo тоже свойство.. соответственно свойство foo есть объект
		Строка может начинаться со / или c = по умолчанию /
	*/

	var surl=strurl;
	if(/^=/.test(surl)){
		surl=surl.replace(/^=/,'');
		if(!surl||surl=='null'){
			surl=undefined;
		}
		obj={};
		var ro=obj;
		for(var i=0,l=state.length;i<l-1;i++){
			ro[state[i]]={};
			ro=ro[state[i]];
		}
		if(l){
			ro[state[l-1]]=surl;
		}else{
			obj=surl;
		}

		return obj;
	}else if(/^\//.test(surl)){
		//surl=surl.replace(/^\//,'');//Убрали слэш который в начале
	}else if(/^&/.test(surl)){
		//surl=surl.replace(/^&/,'');
		surl='/$'+surl;
	}else{
		surl='/'+surl;
	}

	surl=surl.replace(/[\$\/]$/,'');//Убрали слэш которые в конце

	var qfind=surl.match(/\//g); // ["/", "/", "/"...]
	var rfind=surl.match(/\$/g); // ["$", "$",..]
	var qnum=(qfind)?qfind.length:0;
	var rnum=(rfind)?rfind.length:0;

	for (var i=0; i < qnum-rnum; i++){
		surl += '$';
	}

	//todo: number string Типы значений должны быть правильными в результате а пока Number(s)!=s - значит s строка
	surl=surl.replace(/%20/g,' '); // проблема с пробелом в сафари.

	var simbol='[\'"\\\\А-Яа-яёЁ~\\w\\s\\(\\)\\-—…\\.°•’Є\\–,`“”\\?!_:\\[\\];№*%©®@™«»\\+]';
	


	var regg=new RegExp('((^|[\\$&\\/])'+simbol+'+)(?=$|[&\\$])','g');
	surl=surl.replace(regg,'$1={}');	//type -> type:{}
	var regg=new RegExp('('+simbol+'+)','g');
	surl=surl.replace(/"/g,'\\"');
	surl=surl.replace(regg,'"$1"');  // 123 str ->  "123 str"

	//$surl=str_replace('=&','=""&',$surl);
	//$surl=str_replace('=$','=""$',$surl);

	surl=surl.replace(/=&/g,'=""&');
	surl=surl.replace(/=$/g,'=""$');

	//surl=surl.replace(/=((?!\bnull\b|\btrue\b|\bfalse\b|\bundefined\b|\b\d+\b)[А-Яа-яёЁ\w\s\-\.\,]+)/g, ':"$1"');	// type=str -> type:"str"
	//surl=surl.replace('"null"','null');
	surl=surl.replace(/"undefined"/g,'undefined');
	surl=surl.replace(/"null"/g,'null');
	surl=surl.replace(/"true"/g,'true');
	surl=surl.replace(/"false"/g,'false');
	surl=surl.replace(/\$(?!(&|$|\$))/g,'$&');
	surl=surl.replace(/\$/g,'}');
	surl=surl.replace(/\//g, ':{');
	surl=surl.replace(/&/g,',');
	surl=surl.replace(/=/g,':');	// type=3 -> type:3 or type=null -> type:null

	//Если в качестве значения передана пустая строка будут склееное :}  :,
	surl=surl.replace(/:}/g,':undefined}');
	surl=surl.replace(/:,/g,':undefined}');



	surl=surl.replace(/^:/,'');

	obj='(';
	for(var i=0,l=state.length;i<l;i++){
		obj+='{"'+state[i]+'":';
	}
	if(surl){
		obj+=surl;
	}else{
		obj+='{}';
	}
	for(var i=0;i<l;i++){
		obj+='}';
	}
	obj+=')';
	try{
		var obj=eval(obj);
		return obj;
	}catch(e){
		if(window.console)console.log('Адрес не распознан\n'+strurl+'\n'+surl);
		return {error:'url'};
	}
}
infra.State.parser.getQuery=function(obj,r){//Возвращает строку запроса из объекта obj  r- служебный параметр
	if(typeof(obj)!='object')return '='+obj;
	if(!obj)return '';
	var p='';
	for(var i in obj){
		if(typeof obj[i] ==='string'
			||typeof obj[i] ==='number'
			||typeof obj[i] ==='boolean'
		){
			p+='&'+i;
			var value=obj[i].toString();
			if(value.length>0){
				p+='='+value;
			}
		}else if(obj[i] === null){
			//p+='&'+i+'=null';
		}else if(typeof obj[i] == 'object'){
			p+='&'+i+'\/';
			p+=this.getQuery(obj[i],true);
			p+='$';
		}	
	}
	p=p.substr(1);
	p=p.replace(/\/\$/,'');
	if(!r){
		p=p.replace(/\$+$/,'');
		p=p.replace(/\/\$\&/,'&');
		p=p.replace(/\/$/,'');
		//p=p.replace(/\+/,' ');
		//p=p.replace(/\s/g,'+');
		//p=p.replace(/\s/g,'~');
	}
	
	return p;
}
infra.State.parser.cache={};
infra.State.parser.def_ind=['$','$'];
infra.State.parser.parse=function(href){
	if(this.cache[href])return this.cache[href];
	var param=this.afterDomain(href);
	if(param===false){
		this.cache[href]=false
		return this.cache[href];
	}

	var ind=/(^[\$&]+)(.*$)/.exec(param);//$$asdf/asdf, $$asdf.asdf/asdf, $$/asdf/asdf $$asdf.asdf&asdf
	if(ind){
		param=ind[2];
		ind=ind[1].split('');
	}else{
		ind=this.def_ind;
	}
	if(ind.length==1){
		ind[1]=ind[0];
	}
	
	function getStateEnd(param){//Определяем где заканчивается указанное состояние
		var r=0;
		var e=param.indexOf('=')+1;
		var s=param.indexOf('/')+1;
		var a=param.indexOf('&')+1;
		var l=param.length+1;
		
		if(!s){
			r=a;
		}else if(!a){
			r=s;
		}else if(a<s){
			r=a;
		}else{
			r=s;
		}
		if(e&&e<r)r=e;
		if(!r)r=l;

		if(r)r--;
		return r;
	}
	var r=getStateEnd(param);
	var state=param.slice(0,r);
	var value=param.slice(r);
	/*	
	if(r==e){
		var value=param;
		var state='';
	}else if(r){//значит где-то но заканчивается
		var state=param.slice(0,r-1);
		var value=param.slice(r-1);
	}else{
		var value='';
		var state=param;
	}*/
	state=infra.seq.right(state);
	
	param=this.getObj(value,state);
	
	this.cache[href]={param:param,state:state,ind:ind}
	return this.cache[href];
}
infra.State.parser.test=function(parsed){
	var p='';
	if(typeof(parsed.param)=='object'){
		for(var i in parsed.param){
			p+='\n\t'+i+':'+parsed.param[i];
		}
	}else{
		p+=parsed.param;
	}
	alert('param:'+p+'\nstate:'+parsed.state+'\nind:'+parsed.ind);
}






infra.State.prototype={
	toString:function(){
		return this.getName();
	},
	getName:function(){
		var state=this;
		var s=[];
		while(state.name){
			s.unshift(state.name);
			state=state.parent;
		}
		return infra.seq.short(s,'/');
	},
	getState:function(state_mix){//Относительное получение вложенных State 
		//Если передаётся просто имя, оно должно быть в массиве ['asd.asd'] иначе оно распознается как ['asd','asd']
		if(state_mix&&typeof(state_mix)=='object'&&state_mix.constructor!==Array)return state_mix;
		var state_right=infra.seq.right(state_mix);
		if(state_right.length==0)return this;
		state_right=state_right.concat();//Делаем копию что бы не изменять оригинальный переданный state_mix
		var state_name=state_right.shift();
		if(!this.childs[state_name])this.childs[state_name]=new infra.State(state_name,this);
		return this.childs[state_name].getState(state_right);
	},
	run:function(cl){
		var obj=this.obj||false;
		var old=this.old||false;

		infra.foro(old,function(val,s){//Сначало по тем которых нету
			cl(s,obj[s],old[s]);
		});
		infra.foro(obj,function(val,s){//Теперь по тем которые есть
			if(old[s]!==undefined)return;//уже забегали значит
			cl(s,obj[s],old[s]);
		});
	},
	prepare:function(obj,old){//obj и old текущего объекта
		this.old=old;
		this.obj=obj;
		var that=this;
		this.child=infra.foro(obj,function(val,s){//Первый случайный child
			return that.getState([s]);
		});


		this.run(function(s,obj,old){
			var state=that.getState([s]);
			state.prepare(obj,old);
		});
	},
	notify:function(){//Восходящая система событий от родителя к детям / потом /asdf потом /asdf/asdf
		if(this.obj===undefined){
			infra.fire(this,'onhide');
		}else if(this.old===undefined){
			infra.fire(this,'onshow');
			infra.fire(this,'onchange');
		}else{
			infra.fire(this,'onchange');
		}
		var that=this;
		this.run(function(s,obj,old){
			var state=that.getState([s]);
			state.notify();
		});
	},
	getRight:function(parsed){
		if(!parsed)return false;
		var state=parsed.state;
		var param=parsed.param;
		var $1=(parsed.ind[0]=='$');
		var $2=(parsed.ind[1]=='$');
		if($1){
			var obj1={};
		}else{
			var obj1=this.merge({},this.obj);//Актуальный объект, копия
			if(typeof(obj1)!=='object'){
				obj1={};
			}
		}
		if($2){
			var ro=obj1;//Надо в obj1 найти и грохнуть то что сейчас уже есть от указанного состояния
			for(var i=0,l=state.length;i<l-1;i++){
				ro[state[i]]={};
				ro=ro[state[i]];
			}
			if(l){
				ro[state[l-1]]={};
			}else{
				obj1={};
			}
			var obj2={};//Грохнуть всё что сейчас в текущем состоянии
		}else{
			var real=this.getState(state).obj;
			if(typeof(real)=='object'){
				var real=this.merge({},real);//Копия
			}
			var obj2={};//сохранить всё то сейчас в текщуем состоянии
			var ro=obj2;
			for(var i=0,l=state.length;i<l-1;i++){
				ro[state[i]]={};
				ro=ro[state[i]];
			}
			if(l){
				ro[state[l-1]]=real;
			}else{
				obj2=real;
			}
		}

		if(typeof(param)=='object'){
			this.merge(obj2,param);
		}else{
			obj2=param;
		}
		var obj=this.merge(obj1,obj2);

		//obj1 сейчас есть объект правильной адресной строки с учётом состояния и указаний 1$ 2$
		return obj;
	},
	merge:function(obj1,obj2){//Объединяет два объекта  в третий... obj1 меняется
		if(obj2&&typeof(obj2)=='object'&&obj2.constructor!=Array){
			if(!obj1||typeof(obj1)!='object'||obj1.constructor==Array){
				obj1={};
			}
			for(var i in obj2){
				obj1[i]=this.merge(obj1[i]||undefined,obj2[i]);
			}
		}else{
			obj1=obj2;
		}
		return obj1;
	}
	/*go:function(obj,replace){//obj или str в адресную строку.. и переходим
		if(typeof(obj)=='string'){
			var parsed=infra.State.parser.parse(obj);
			obj=this.getRight(parsed);
		}
		var s=this;
		while(s.parent){
			var nobj={};
			nobj[s.name]=obj;
			var st=s.name;
			s=s.parent;
			for(var i in s.obj){
				if(i==st)continue;
				nobj[i]=s.obj[i];
			}
			obj=nobj;
		}
		var q=infra.State.parser.getQuery(obj);
		var r=this.write(q,replace);
		return r;
	},
	replace:function(hash){
		hash=decodeURIComponent(hash);
		var righturl=location.protocol+'//'+location.hostname+location.pathname;
		righturl+='#'+hash;
		location.replace(righturl);
	},
	read:function(){
		var hash=decodeURIComponent(location.hash);
		hash=hash.replace('#','');
		return hash;
	},
	write:function(hash,replace){
		hash=hash.replace(/^#/,'');
		if(hash==this.read())return false;//Адрес повторяется
		$.history.load(hash);
		//$.historyLoad(hash);
		return true;
	
	}*/
}


/**/
