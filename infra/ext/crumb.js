
infra.Crumb=function(){};
infra.Crumb.childs={};
infra.Crumb.prototype={
	
	getInstance:function(name){
		//static public
		//Запускается у объектов и класса
		if(!name)name='';
		var right=[];
		if(this instanceof infra.Crumb)right=this.path;
		right=this.right(right.concat(this.right(name)));
		if(right[0]==='')right=[];
		var short=this.short(right);
		if(!infra.Crumb.childs[short]){
			var that=new infra.Crumb();	
			that.path=right;
			that.name=right[right.length-1]?right[right.length-1]:'';
			that.value=that.query=that.is=that.counter=null;
			infra.Crumb.childs[short]=that;
			if(that.name)that.parent=that.getInstance('//');
		}
		return infra.Crumb.childs[short];
	},
	right:function(short){
		//static
		//Запускается у класса
		return infra.seq.right(short,'/');
	},
	short:function(right){
		//static
		//Запускается у класса
		return infra.seq.short(right,'/');
	},
	toString:function(){
		//public
		return this.short(this.path);
	}
}

infra.Crumb.change=function(query){
	//static
	//Запускается паблик у класса

	var amp=query.split('&');
	if(amp.length>1)amp=[amp.shift(),amp.join('&')];

	var eq=amp[0].split('=',2);

	var sl=eq[0].split('/',2);


	if( eq.length!==1&&sl.length===1 ){
		//В первой крошке нельзя использовать символ "="
		var params=query;
		var query='';
	}else{
		var params=amp[1]?amp[1]:'';
		var query=amp[0];
	}
	infra.Crumb.params=params;

	var ar = params.split('&');
	var get = {};
	for(var tmp, x=0; x<ar.length; x++){
		tmp = ar[x].split('=');
		get[unescape(tmp[0])] = unescape(tmp[1]).replace(/[+]/g, ' ');
	}
	infra.Crumb.get=get;

	var right=infra.Crumb.right(query);
	var counter=++infra.Crumb.counter;
	var old=infra.Crumb.path;
	infra.Crumb.path=right;

	infra.Crumb.value=right[0]?right[0]:'';
	infra.Crumb.query=infra.Crumb.short(right);
	infra.Crumb.child=infra.Crumb.getInstance(infra.Crumb.value);

	var that=infra.Crumb.getInstance(infra.Crumb.path);
	var child=null;
	while(that){
		that.counter=counter;
		that.is=true;
		that.child=child;
		that.value=right[that.path.length]?right[that.path.length]:'';
		that.query=infra.Crumb.short(right.slice(that.path.length));
		child=that;
		that=that.parent;
	};
	that=infra.Crumb.getInstance(old);
	if(!that)return;
	while(that){
		if(that.counter==counter)break;
		that.is=that.child=that.value=that.query=null;
		that=that.parent;
	};
}
infra.Crumb.init=function(){
	//static
	//infra.Crumb.child=infra.Crumb.getInstance();

	var listen=function(){		
		var query=decodeURI(location.search.slice(1));
		if(query[0]=='*'){
			var q=query.split('?');
			infra.Crumb.prefix='?'+q.shift();
			query=q.join('?');
		}

		if(infra.Crumb.query===query)return;//chrome при загрузки запускает собыите а FF нет. Первый запуск мы делаем сами по этому отдельно для всех а тут игнорируются совпадения.
		infra.Crumb.popstate=true;
		infra.Crumb.change(query);
		infra.fire(infra.Crumb,'onchange');
		if(infra.Crumb.prefix){
			infra.Crumb.setA(document);
		}
	}
	if(document.readyState === "complete") return listen();
	document.addEventListener("DOMContentLoaded",function(){
		if(history.pushState){//Первый запус должен проходить после того как все слои подключились все кому интересно подписались на события и потом проходит событие и всё работает
			//Вперёд назад
			window.addEventListener('popstate',listen, false); //Генерировать заранее нельзя
		}
		listen();//Даже если html5 не поддерживается мы всё равно считаем первую загрузку а дальше уже будут полные переходы и всё повториться
	});
}
infra.Crumb.go=function(query){
	var q=query.split('?',2);
	if(q.length>1)query=q[1];
	//if(infra.Crumb.query===query)return;
	if(!infra.Crumb.prefix&&history.pushState){
		var path=(query?('?'+encodeURI(query)):location.pathname);
		document.http_referrer=location.href;
		history.pushState(null,null,path);//При переходе назад этой записи не должно быть
	}else{
		if(query&&query[0]=='*')infra.Crumb.prefix='';
		var path=(query?(infra.Crumb.prefix+'?'+query):location.pathname+infra.Crumb.prefix);
		location.href=path;	
	}
	infra.Crumb.popstate=false;
	infra.Crumb.change(query);
	infra.fire(infra.Crumb,'onchange');
}
infra.Crumb.setA=function(div){
	
	if(typeof(div)=='string')div=document.getElementById(div);
	if(!div)return;

	var as=div.getElementsByTagName('a');

	for(var i=0,len=as.length; i<len; i++){
		var a = as[i];

		var ainfra=a.getAttribute('infra');
		if(ainfra) continue;//Ссылка проверена обновлять её не нужно

		a.setAttribute('infra','true');

		
		var href=a.getAttribute('href');
		if(typeof(href)=='undefined'||href==null)continue;//У ссылки нет ссылки
		if(/^javascript:/.test(href))continue;
		if(/^mailto:/.test(href))continue;

		
		if (href=='.') { //Правильная ссылка на главную страницу
			var beforequest='';
			var href='';
		} else {
			var r=href.split('?');
			var beforequest=r.shift();
			if(r.length>0){
				try{ //error malfomed URI
					//Пытаемся убрать проценты из адреса
					var href=decodeURI(r.join('?'));
				}catch(e){
					var href=r.join('?');
				}
			}else{
				var href='';
			}
		}
		if(beforequest) {
			var t=beforequest.split('/');
			if(t.length>=3){
				//разобрали строчку вида http://yandex.ru/site/?test
				var method=t.shift();
				if(method=='http:'||method=='https:'){
					t.shift();//слэш пустой
					sitehost=t.shift();
					siteroot=t.join('/');
					beforequest=siteroot;
					
					if((method=='http:'&&sitehost==location.host&&('/'+siteroot==location.pathname))){
						//Домен есть но он совпадает с текущим включая siteroot. Значит просто домен не учитывается.
					}else{
						a.setAttribute('target','_blank');//Если target не установлен
						continue;
					}
				}
			}
			continue;//В ссылке есть что-то до вопроса
		}
	
		if(typeof(a.onclick)==='function'){
			var old_func=a.onclick;
		}else{
			var old_func=function(){};
		}
		
		a.setAttribute('weblife_href','?'+href);//Признак того что эта ссылка внутренняя и веблайфная... так сохраняется первоначальный адрес
		
		var crumb=infra.Crumb.getInstance(href);
		href=crumb.toString();

		var siteroot=infra.view.getRoot();
		if(href[0]=='*'){
			var sethref=href?('http://'+location.host+'/'+siteroot+'?'+encodeURI(href)):('http://'+location.host+'/'+siteroot);
		}else{
			var sethref=href?('http://'+location.host+'/'+siteroot+infra.Crumb.prefix+'?'+encodeURI(href)):('http://'+location.host+'/'+siteroot+infra.Crumb.prefix);
		}
		a.setAttribute('href',sethref);//Если параметров нет, то указывам путь на главную страницу

		a.onclick=function(old_func,a,crumb){
			
			return function(event){

				setTimeout(function(){//Сначало должны выполниться все другие подписки а это как дефолтное поведение в самом конце
					
					var re=old_func.apply(a);

					if(re===false){
						if(typeof(event)!=='undefined')event.returnValue=false;
						return false;
					}
					var nohref=a.getAttribute('nohref');
					if(nohref)return false;

					infra.Crumb.go(crumb.toString());
				},1);
				return false;
			}
		}(old_func,a,crumb);
	}
}
/*public $name;
	public $parent;
	static $child;
	static $value;//Строка или null значение следующей кроки
	static $query;//Строка или null значение следующей и последующих крошек
	static $childs=array();
	static $counter=0;
	static $path;//Путь текущей крошки
	static $params;//Всё что после первого амперсанда
	static $get;
	public $is;*/
infra.Crumb.prefix='';
infra.Crumb.value='';
infra.Crumb.query=null;
infra.Crumb.path=[];
infra.Crumb.counter=0;
infra.Crumb.getInstance=infra.Crumb.prototype.getInstance;
infra.Crumb.right=infra.Crumb.prototype.right;
infra.Crumb.short=infra.Crumb.prototype.short;
