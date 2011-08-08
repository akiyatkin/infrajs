/*
URL control - http://itlife-studio.ru

Copyright 2008 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru

history 
01.04.2010
	Свойство equal отменено.. .на itlf.ru была ошибка при листании примеров
04.04.2010
	href='http://www.1c.ru/rus/partners/solutions/partners.jsp?city=%D2%EE%EB%FC%FF%F2%F2%E8';
	href=decodeURI(href);//если href такой Происходит ошибка в функции parse=
	deoceURI опущен ниже по коду... только для внутренних адресов (без http)
18.04.2010
	infra.state.parser.def_ind=['$','$']; можно переопределять дефолтные знаки

*/
(function(){
	infra.State=function(name,parent){
		this.state=name||'';//depricated
		this.name=name||'';
		this.parent=parent||false;
		this.childs={};
		//this.listener={};
	};

	infra.State.prototype={
		toString:function(){
			return this.getName();
		},
		decodeName:function(st,right){
			if(!right&&infra.cache_save){
				if(/\./.test(st)){
					alert('Имя состояния содержит запрещённый символ .:'+st);
				}
			}
			st=st.replace(/·/g,'.');
			return st;
		},
		encodeName:function(st){
			if(infra.cache_save){
				if(/·/.test(st)){
					alert('Имя состояния содержит запрещённый символ ·:'+st);
				}
			}
			st=st.replace(/\./g,'·');
			return st;
		},
		getName:function(state,just){
			if(state==undefined){
				var state=this;
				var s=[];
				while(state.state){
					var st=this.encodeName(state.state);
					s.unshift(st);
					state=state.parent;
				}
				return s.join('.');
			}else{
				var s=state.replace(/^\.+/,'');
				s=state.replace(/\.+$/,'');//Убираем лишние точки
				s=s.split('.');
				var state=[];
				for(var i=0,l=s.length;i<l;i++){
					var st=this.decodeName(s[i]);
					if(st)state.push(st);
				}
				return state;
			}
		},
		timer:0,
		merge:function(obj1,obj2){//Объединяет два объекта  в третий... obj1 меняется
			if(obj2&&typeof(obj2)=='object'){
				if(typeof(obj1)!='object'){
					obj1={};
				}
				for(var i in obj2){
					obj1[i]=this.merge(obj1[i]||undefined,obj2[i]);
				}
			}else{
				obj1=obj2;
			}
			return obj1;
		},
		go:function(obj,replace){//obj или str в адресную строку.. и переходим
			if(typeof(obj)=='string'){
				var parsed=infra.state.parser.parse(obj);
				obj=this.getRight(parsed);
			}
			var s=this;
			while(s.parent){
				var nobj={};
				nobj[s.state]=obj;
				var st=s.state;
				s=s.parent;
				for(var i in s.obj){
					if(i==st)continue;
					nobj[i]=s.obj[i];
				}
				obj=nobj;
				
			}
			
			var q=infra.state.parser.getQuery(obj);
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
		},
		getState:function(s,split){//Относительное получение вложенных State, spit true значит передан адресостояния через точку
			var state2='';
			if(s&&typeof(s)=='object'&&s.construct===Array){//Уже массив
				var sorig=s;
				var s=[];//Делаем копию
				for(var i=0,l=sorig.length;i<l;i++){
					s.push(sorig[i]);
				}
				
				var state=s.shift();
				state2=s;
			}else if(typeof(s)=='object'){
				return s;
			}else if(typeof(s)=='string'){
				if(split){
					state2=this.getName(s);
					var state=state2.shift();//взяли самый первый
				}else{
					state2=[];
					state=s;
				}
			}else{
				return this;
			}
			if(state2.length==0)state2='';
			if(!state){
				return this;
			}else{//Если что-то осталось в s идём дальше
				if(!this.childs[state])this.childs[state]=new infra.State(state,this);
				state=this.childs[state];
			}
			return state.getState(state2);
		},
		/*listen:function(type,func){
			if(!this.listener[type])this.listener[type]=[];
			this.listener[type].push(func);
		},*/
		/*run:function(type,type2){
			type2=type2||type;
			var ar=this.listener[type];
			if(!ar)return;
			for(var i=0;i<ar.length;i++){
				try{
					var r=ar[i](this,type2);
				}catch(e){
					if(infra.cache_save){
						alert('Ошибка state.notify '+type+' '+e+'\n'+ar[i]);
					}
				}
				if(r==='off'){
					ar.splice(i,1);
					i--;
				}
			}
		},*/
		prepare:function(obj,old){//obj и old текущего объекта
			this.old=old;
			this.obj=obj;
			this.child=false;
			if(typeof(this.obj)=='object'){
				for(var i in this.obj)break;
				if(i)this.child=this.getState(i);
			}
			obj=obj||false;
			old=old||false;
			if(typeof(old)=='object'){
				for(var s in old){//Сначало по тем которых нету
					var state=this.getState(s);
					state.prepare(obj[s],old[s]);
				}
			}
			if(typeof(obj)=='object'){
				for(var s in obj){//Теперь по тем которые есть
					if(old[s]!==undefined)continue;
					var state=this.getState(s);
					state.prepare(obj[s],old[s]);
				}
			}
		},
		/*refresh:function(){
			if(this.parent){
				delete this.parent.obj[this.state];
				this.parent.prepare(this.parent.obj,this.parent.old);
			}else{
				this.prepare({},this.old);
			}
			this.notify();
		},*/
		notify:function(){
			infra.state.level++;
			this.callback('');
			if(typeof(this.old)=='object'){
				for(var s in this.old){//Сначало по тем которых нет
					var state=this.getState(s);
					state.notify();
				}
			}
			if(typeof(this.obj)=='object'){
				for(var s in this.obj){//Теперь по тем которые есть
					if(this.old&&this.old[s]!==undefined)continue;
					var state=this.getState(s);
					state.notify();
				}
			}
			this.callback('ready');
			infra.state.level--;
		},
		callback:function(some){
			if(this.obj==undefined){
				infra.fire(this,'onhide'+some);
			}else if(this.old==undefined){
				infra.fire(this,'onshow'+some);
				infra.fire(this,'onchange'+some);
			}else{
				//if(!this.isEqual(this.obj,this.old)){
					infra.fire(this,'onchange'+some);
				//}
			}
		},
		/*isEqual:function(obj1,obj2){
			if(obj1==obj2)return true;
			if(typeof(obj1)=='object'&&typeof(obj2)!='object')return false;
			if(typeof(obj2)=='object'&&typeof(obj1)!='object')return false;
			if(!obj1||!obj2||(typeof(obj1)!='object'&&typeof(obj2)!='object'))return false;
			for(var i in obj1){
				if(!this.isEqual(obj1[i],obj2[i])){
					return false;
				}
			}
			for(var i in obj2){
				if(obj1[i]==undefined)return false
			}
			return true;
		},*/
		//Установить
		set:function(value){
			if(this.obj&&typeof(this.obj)!=='object'){
				return;//Если текущее значение есть и оно не объект выходим не изменяя его
			}
			if(this.parent){
				var pobj=this.parent.obj;
				if(!pobj){
					pobj={};
				}
				this.parent.obj=pobj;
				if(!pobj[this.state]||typeof(pobj[this.state])!=='object'||typeof(value)!=='object'){
					pobj[this.state]=value;
				}else{
					for(var i in value){
						pobj[this.state][i]=value[i];
					}
				}
				
				/*if(!this.parent.obj[this.state]
					||typeof(this.parent.obj)!=='object'
					||typeof(value)!=='object'){
						if(typeof(this.parent.obj)!=='object')){
							this.parent.obj={};
							this.parent.obj[this.state]=value;
						}else{
							this.parent.obj[this.state]=value;
						}
				}else{
					for(var i in value){
						this.parent[this.state][i]=value[i];
					}
				}*/
				var obj=pobj;
				var old=this.parent.old;
				this.parent.prepare(obj,old);
			}else{
				var old=this.old;
				var obj=value;
				this.prepare(obj,old);
			}
		},
		getRight:function(parsed,href){
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
		}
	}
	infra.state=new infra.State();
	
	infra.state.toHash=function(str){
		str.replace('/[\?&#\$]/','');
		return str;
	}
	infra.state.level=0;

	infra.state.getA=function(a){//Используется в itlife-studio.ru для меню в шапке, для поиска ссылок совпадающих с адресной строкой
		var href=a.getAttribute('weblife_href');
		if(!href)href=a.getAttribute('href');
		var h=href.split(/[#\?]/);
		if(h.length>1){
			href=h[1];
		}else{
			href='';
		}
		return href;
	}
	infra.state.setA=function(div){
		/*if(typeof(div)==='object'&&typeof(div.show)==='object'&&typeof(div.show.div)!=='undefined'){
			div=div.show.div;
		}else if(typeof(div)==='object'&&typeof(div[0])!=='undefined'&&typeof(div[0].arg)!=='undefined'){
			if(div[0].arg.show){
				div=div[0].arg.show.div;
			}else{
				div=div[0].arg;
			}
		}*/
		if(typeof(div)=='string'){
			div=document.getElementById(div);
			/*for(var i=0; i<$(div).length;i++){
				infra.state.setA($(div).get(i));
			}
			return;*/
		}
		if(!div)return;


		var as=div.getElementsByTagName('a');
		for(var i=0,len=as.length; i<len; i++){
			var a = as[i];
			var notweblife=a.getAttribute('notweblife');
			if(notweblife == 'true') continue;//У ссылки может быть запрет на проверку
			var weblife=a.getAttribute('weblife');
			var weblife_refresh=a.getAttribute('weblife_refresh');
			if(weblife == 'true'&&!weblife_refresh) continue;

			
			a.setAttribute('weblife','true');
			var href=a.getAttribute('weblife_href');//Повторно заходим если мягкое изменение адреса

			var isfirst=!href;
			if(isfirst){
				href=a.getAttribute('href');
				if(typeof(href)=='undefined'){//У ссылки нет ссылки
					continue;
				}
				if(/^javascript:/.test(href)){
					continue;
				}
			}
			var parsed=this.parser.parse(href);
			if(!parsed){
				var target=a.getAttribute('target');
				if(!target){//Если target не установлен
					a.setAttribute('target','_blank');
				}
				continue;
			}
			
			if(typeof(a.onclick)==='function'){
				var old_func=a.onclick;
			}else{
				var old_func=function(){};
			}
			
			
			if(isfirst){
				a.setAttribute('weblife_href',href);//Признак того что эта ссылка внутренняя и веблайфная... 
			}
			if(parsed.ind[0]=='&'||parsed.ind[1]=='&'){
				a.setAttribute('weblife_refresh','true');//Признак того что эта ссылка внутренняя и веблайфная... 
			}
			var param=this.getRight(parsed,href);
		
			href=this.parser.getQuery(param);
			a.setAttribute('href',href?((a.getAttribute('infrajs_hide')?'#':'?')+href):'http://'+this.parser.domain);//Если параметров нет то указывам путь на главную страницу
			if(isfirst){	
				a.onclick=function(old_func,a){
					return function(){
						var re=old_func.bind(a)();
						if(re===false){
							if(typeof(event)!=='undefined')event.returnValue=false;
							return false;
						}
						var nohref=a.getAttribute('nohref');
						if(nohref){
							return false;
						}
						if(/\?/.test(a.href)){
							//var param=a.href.substring(a.href.indexOf('?')+1);
							var h=decodeURI(a.href);
							var ar=h.split('?');
							ar.shift();
							var param=ar.join('?');
						}else if(/#/.test(a.href)){
							var h=decodeURI(a.href);
							var ar=h.split('#');
							ar.shift();
							var param=ar.join('#');
						}else{
							var param='';
						}
						if(!/^=/.test(param)){
							param='#/'+param;
						}else{
							param='#'+param;
						}
						infra.state.go(param);
						if(typeof(event)!=='undefined')event.returnValue=false;
						return false;
					}
				}(old_func,a);
			}
		}
	}
	infra.state.setHash=function(hash){
		var parsed=this.parser.parse(hash);
		var obj=this.getRight(parsed);
		var href=this.parser.getQuery(obj);
		hash=location.hash;
		hash=hash.replace(/^#/,'');	
		dehash=decodeURI(hash);
		
		if(infra.state.hash!==undefined){
			document.http_referrer=location.protocol+'//'+location.host+location.pathname;
			if(infra.state.hash)document.http_referrer+='?'+infra.state.hash;
		}
		infra.state.hash=href;
		
		document.http_now=location.protocol+'//'+location.host+location.pathname;
		if(infra.state.hash)document.http_now+='?'+infra.state.hash;//Используется в *metrika.layer.js
		
		/*if(!infra.opera){//Без этого в opera не работают кнопки вперёд назад
			if(
				(dehash!=hash)//В адресе недекодированный адрес
				||(hash!==href&&hash!==infra.state.hash)){
				location.replace(location.protocol+'//'+location.host+location.pathname+'#'+href);
				if(!infra.safari&&!infra.IE||infra.IE>8) return;
			}
		}*/
		
		
		
		
		infra.state.prepare(obj,infra.state.obj);
		
		infra.state.notify();
	}



	infra.state.parser={};
	infra.state.parser.domain=location.host+location.pathname;
	infra.state.parser.is=function(href){//Проверка является ли указанный адрес адресом сборки, ссылается на страницу этого сайта
		/*if(href&&/^#/.test(href)==false&&/^\?/.test(href)==false){
			return false;
		}*/
		var domain=/^http\:\/\/([a-zA-Z0-9\-\.\\\/]+)[\/#\?]*/.exec(href);
		
		if(domain){
			domain=domain[1];
			if(domain!==this.domain){
				return false;
			}
			var h=href.replace('http:/'+'/'+domain,'');
		}else{
			var h=href;
		}
		if(!h)return true;
		if(!/^[#\?]/.test(h))return false;
		return true;
	}
	infra.state.parser.getObj=function(strurl,state){
		state=state||[];
		
		/*Принимает строку которая переводится в объект... 
			/some/foo - some объект foo свойство
			=some/foo=moo - foo свойство, moo значение значит свойство foo простое
			/some/foo/moo - foo свойство, moo тоже свойство.. соответственно свойство foo есть объект
			Строка может начинаться со / или c =
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
		
		var simbol='[А-Яа-яёЁ~\\w\\s\\(\\)\\-—…\\.•\\–,`“”\\?!_:\\[\\];*%©®@™«»\\+]';
		
		var regg=new RegExp('((^|[\\$&\\/])'+simbol+'+)(?=$|[&\\$])','g');
		surl=surl.replace(regg,'$1={}');	//type -> type:{}
		var regg=new RegExp('('+simbol+'+)','g');
		surl=surl.replace(regg,'"$1"');  // 123 str ->  "123 str"
			
		
		//surl=surl.replace(/=((?!\bnull\b|\btrue\b|\bfalse\b|\bundefined\b|\b\d+\b)[А-Яа-яёЁ\w\s\-\.\,]+)/g, ':"$1"');	// type=str -> type:"str"
		//surl=surl.replace('"null"','null');
		surl=surl.replace('"undefined"','undefined');
		surl=surl.replace('"null"','null');
		surl=surl.replace('"true"','true');
		surl=surl.replace('"false"','false');
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
			if(infra.cache_save){
				alert('Ошибка... адрес не распознан\n'+strurl+'\n'+surl);
			}
			return {error:infra.getUnick()};//Просто уникальная ссылка на выходе... во время отладки будет видно на какой странице найдена эта ссылка
		}
	}
	infra.state.parser.getQuery=function(obj,r){//Возвращает строку запроса из объекта obj  r- служебный параметр
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
			p=p.replace(/\s/g,'+');
			//p=p.replace(/\s/g,'~');
		}
		
		return p;
	}
	infra.state.parser.cache={};
	infra.state.parser.def_ind=['$','$'];
	infra.state.parser.parse=function(href){
		try{
			href=decodeURI(href);
		}catch(e){
			href="#";
			//href=href;//В адресе может быть проце %
		}
		href=href.replace(/\+/g,' ');
		if(this.cache[href])return this.cache[href];
		if(!this.is(href)){
			this.cache[href]=false
			return this.cache[href];
		}
		//href=href.replace(/~/g,' ');

		if(/#/.test(href)){
			var param=href.substring(href.indexOf('#')+1);
		}else if(/\?/.test(href)){
			var param=href.substring(href.indexOf('?')+1);
		}else{
			var param=href;
		}
		
		/*//var param=a.href.substring(a.href.indexOf('?')+1);
							var h=decodeURI(a.href);
							var ar=h.split('?');
							ar.shift();
							var param=ar.join('?');*/
		
		var ind=/(^[\$&]+)(.*$)/.exec(param);
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
			if(r){//Если после равно что-то идёт то то что до равно это не состояние
				if(e&&e<r)r=0;
			}else{//Если только равно то то что до равно состояние
				if(e)r=e;
				else r=l;
			}
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
		state=infra.state.getName(state);
		
		param=this.getObj(value,state);
		
		this.cache[href]={param:param,state:state,ind:ind}
		return this.cache[href];
	}
	infra.state.parser.test=function(parsed){
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

	infra.listen(infra.state,'onchange',function(){
		infra.check();
	});
})();
