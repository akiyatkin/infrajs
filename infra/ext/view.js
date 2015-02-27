infra.view={
	getPath:function(){//depricated плохо связано с такойже функцией на сервере
		return location.pathname;
	},
	/*getRoot:function(){//Дай путь до корня сайта от корня домена
		return location.pathname;
	},*/
	getRoot:function(){
		//Путь начинается без слэша svn/project/ например
		var p=this.getPath();
		p=p.split('/');
		p.pop();
		if(!p[0])p.shift();
		p=p.join('/');
		if(p)p+='/';
		return p;
	},
	getAGENT:function(){
		return navigator.userAgent;
	},
	getREF:function(){
		if(document.http_referrer)return document.http_referrer;
		return document.referrer
	},
	getREQUEST:function(name){
		if(this.REQUEST)return this.REQUEST;
		var REQUEST={};
		/*var DATA=this.getPOST();
		for(var i in DATA)REQUEST[i]=DATA[i];*/
		var DATA=this.getGET();
		for(var i in DATA)REQUEST[i]=DATA[i];
		this.REQUEST=REQUEST;
		return REQUEST;
	},
	getCookie:function(name){
		//if(!this.cookies){ асинхронно установленные кукисы сервером, должны обнаруживаться, для этого разбираем куки каждый раз
			this.cookies={};
			infra.forr(document.cookie.split(';'),function( cookie ) {
				var parts = cookie.split('=');
				var key=parts[0]||'';
				key=key.replace(/^\s+/,'');
				key=key.replace(/\s+$/,'');
				var val=parts[1]||'';
				val=val.replace(/^\s+/,'');
				val=val.replace(/\s+$/,'');
				this.cookies[key]=val;
			}.bind(this));
		//}
		if(name)return this.cookies[name];
		return this.cookies;
	},
	setCookie:function(name,val){
		if(val===undefined)val='';
		this.getCookie();
		
		var longdate=new Date();
		longdate.setFullYear(2020);
		
		
		if(val===''){
			var deldate=new Date();
			deldate.setFullYear(2000);
			var expire=deldate;
			delete this.cookies[name];
		}else{
			val=String(val);
			var expire=longdate;
			this.cookies[name]=val;
		}
		//var httproot=infra.plugin.getHTTPROOT();//Куки для домена который сейчас в адресной строке
		//var root=httproot?httproot.siteroot:'/';
		var root=infra.view.getRoot();
		var val=name + "=" + escape(val) + '; path=/'+root+'; expires=' + expire.toGMTString();
		document.cookie = val;
		return true;
	},
	setCOOKIE:function(name,val){
		return this.setCookie(name,val);
	},
	getCOOKIE:function(name){
		return this.getCookie(name);
	},
	getQuery:function(){
		var url=location.search;
		//url=decodeURIComponent(url);
		//try {
			url=decodeURI(url);
		//}catch(e){

		//}
		/*url=url.replace(/\+/g,' ');
		var m=url.split('?');
		m.shift();
		return m.join('?');*/
		return url;
	},
	getHost:function(){
		return location.host;
	},
	getGET:function(){
		if(this.GET)return this.GET;
		var query=this.getQuery();
		var query=decodeURI(query);
		var pars=query.split('&');
		var GET={};
		for(var i=0,l=pars.length;i<l;i++){
			var par=pars[i];
			if(!par)continue;
			par=par.split('=');
			var name=par[0];
			GET[name]=par[1];
			if(GET[name]&&Number(GET[name])==String(GET[name])){
				GET[name]=Number(GET[name]);
			}
		}
		return this.GET=GET;
	},
	setTitle:function(title){
		document.title=title;
	}
}