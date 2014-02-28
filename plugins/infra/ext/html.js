/*
 * Вставить элемент с id строку str всё это в сроке html
 * DOM не используется
 */
(function(){
	/*
	 * Без аргументов вернуть текущий html
	 * Только первый, добавить снизу новый html
	 * Второй вставить в id
	 * id true  заменить весь html
	 **/
	infra.htmlclear=function(id){
		var el=document.getElementById(id);
		if(!el)return;
		el.innerHTML='';
		el.style.display='none';
	}
	infra.html=function(html,id){
		if(!arguments.length)return document.body.innerHTML;

		this.html.scriptautoexec=false;
		this.html.styleautoexec=false;
		var tempid='jslibhtml'+htmlGetUnick();//Одинаковый id нельзя.. если будут вложенные вызовы будет ошибка

		html='<span id="'+tempid+'" style="display:none">'+
		'<style>#'+tempid+'{ width:3px }</style>'+
		'<script type="text/javascript">infra.html.scriptautoexec=true;</script>'+
		'1</span>'+html;

		if(arguments.length==1){
			var el=document.body;
		}else if(id===true){
			var el=document.body;
		}else if(typeof(id)=='object'){
			var el=id;
		}else{
			var el=document.getElementById(id);
		}
		if(!el){
			console.log('Не найден div id');
			return;
		}
		try{
			var res=(el.innerHTML=html);
			el.style.display='';
		}catch(e){
			el.innerHTML='Ошибка, Возможно из-за вставки блочного элемента в строчный или другое какое-то нелогичное действие';
		}

		if(!this.html.scriptautoexec){

			var scripts = el.getElementsByTagName("script");
			//for (var i = 0,script; script = scripts[i]; i++){
			//подмена script через document.write или innerHTML изменяет и этот массив scripts
			for (var i=0,l=scripts.length;i<l;i++){
				(function(){
					var script=scripts[i];
					//setTimeout(function(){
						   htmlexec(script);
					//},1);
				})()
			}
		}

		var bug=document.getElementById(tempid);
		if(bug){
			var b=htmlGetStyle(bug,'width');
			if(b!=='3px'){
				var csss= el.getElementsByTagName("style");
				for (var i = 0,css;css=csss[i];i++){
					var t=css.cssText;//||css.innerHTML; для IE будет Undefined ну и бог с ним у него и так работает а сюда по ошибке поподаем


					var style=document.createElement('style');
					//style.innerHTML='@import url("'+href+'")';
					style.innerHTML=t;
					document.getElementsByTagName('head')[0].appendChild(style);
					//infra.style(t);
				}
			}
			try{
				el.removeChild(bug);
			}catch(e){
				if(infra.debug)alert('Ошибка при удалении временного элемента в infra.html\n'+ e);
			}
		}
		return res;
	}
	var htmlexec=function(script){
		if(!script)return;
		//if(htmlexec.busy){
		//	setTimeout(function(){
		//		htmlexec(script);
		//	}.bind(this),1);
		//	return;
		//}
		//htmlexec.busy=true;
		if(script.src){

			//stencill
			//(function() { 
				//var src='http://counter.rambler.ru/top100.jcn?{config.id}';
				var src=script.src;
				var ga = document.createElement('script'); ga.type = 'text/javascript'; 
				ga.async = script.async; 
				ga.src = src;
				var s = document.getElementsByTagName('script')[0]; 
				s.parentNode.insertBefore(ga, s); 
			//})(); 
			//htmlexec.busy=false;

			/*infra.load(script.src,'ea',function(){
				htmlexec.busy=false;
			});*/
		}else{ 
			//try{
				htmlGlobalEval(script.innerHTML); 
			//}catch(e){
			//	var conf=infra.config();
			//	if(conf.debug){
			//		alert('Ошибка в скрипте из шаблона\n'+e+'\n------\n'+script.innerHTML);
			//	}
			//}
			//htmlexec.busy=false;
		}
	}
	var htmlGlobalEval=function(data) {
		if(!data)return;
		var b={};
		if(typeof(window)!=='undefined'){
			b.IE=(function (){if(window.opera)return false; var rv = 0;if (navigator.appName == 'Microsoft Internet Explorer') {var ua = navigator.userAgent;var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");if (re.exec(ua) != null)rv = parseFloat(RegExp.$1);}return rv;})();
			b.opera=/opera/.test(navigator.userAgent)||window.opera;
			b.chrome=/Chrome/.test(navigator.userAgent)
			b.webkit=/WebKit/.test(navigator.userAgent);
			b.safari=(b.webkit&&!b.chrome);
		}
		// Inspired by code by Andrea Giammarchi
		// http://webreflection.blogspot.com/2007/08/global-scope-evaluation-and-dom.html
	
		if(b.IE==false&&b.safari==false){
			window.eval(data);
		}else{
			var head = document.getElementsByTagName("head")[0] || document.documentElement, script = document.createElement("script");
			script.type = "text/javascript";
			script.text = data;
			head.insertBefore( script, head.firstChild );
			head.removeChild( script );
		}
	}

	var htmlGetStyle=function(el, cssprop){
		if (el.currentStyle) //IE
			return el.currentStyle[cssprop]
		else if (document.defaultView && document.defaultView.getComputedStyle) //Firefox
			return document.defaultView.getComputedStyle(el, "")[cssprop]
		else //try and get inline style
			return el.style[cssprop]
	};
	var htmlGetUnick=function(){
		var t=new Date().getTime();
		while(t<=htmlGetUnick.last_time)t++;
		htmlGetUnick.last_time=t;
		return t;
	};
	htmlGetUnick.last_time=0;
	
	infra.html.scriptautoexec=undefined;//Флаг выполняется ли скрипт сам при вставке html
	infra.html.styleautoexec=undefined;//Флга применяется ли <style> при вставке html

	if(typeof(document)=='object'){
		document.writeold=document.write;
		document.write=function(html){// нужно указывать document.write.div где нибудь в шаблоне и тогда фукнция сработает
			var sdiv=document.write.div||'documentwrite';
			var div=document.getElementById(sdiv);
			if(div){
				if(div.id=='documentwrite')div.id='';
				infra.html(html,div);
			}else{
				if(infra.debug){
					alert('Нужен document.write.div указать или создать ещё элемент с id '+sdiv);
				}
			}
		}
		document.write.div='documentwrite';
	} 
})();
