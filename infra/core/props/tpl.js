(function(){
		//Обработка onshow и onhide, tpl, data
	infra.html = function(el,html){
		//var ie=(navigator.userAgent.indexOf("MSIE")!==-1);
		var type='html';
		//if(ie){
			var type='htmlcssjs';
		//}
		//var type='jquery';

		//var start=js.getUnick();


		if(type=='jquery'){
			var res=$(el).html(html);
		}else if(type=='html'){
			if(html!==undefined){
				var res=(el.innerHTML=html);
			}else{
				var res=el.innerHTML;
			}
		}else if(type=='htmlcssjs'){
			if(html!==undefined){
				this.scriptautoexec=false;
				this.styleautoexec=false;
				var tempid='infrahtml'+infra.getUnick();//Одинаковый id нельзя.. если будут вложенные вызовы будет ошибка

				html='<span id="'+tempid+'" style="display:none">'+
				'<style>#'+tempid+'{ width:3px }</style>'+
				'<script type="text/javascript">infra.scriptautoexec=true;</script>'+
				'1</span>'+html;

				try{
					var res=(el.innerHTML=html);
				}catch(e){
					el.innerHTML='Ошибка, Возможно из-за вставки блочного элемента в строчный или другое какое-то не логичное действие';
				}


				if(!this.scriptautoexec){
					var scripts = el.getElementsByTagName("script");
					for (var i = 1,script; script = scripts[i]; i++){
						this.exec(script);
					}
				}

				var bug=document.getElementById(tempid);
				if(bug){
					var b=this.getStyle(bug,'width');
					if(b!=='3px'){
						var csss= el.getElementsByTagName("style");
						for (var i = 0,css;css=csss[i];i++){
							var t=css.cssText;//||css.innerHTML; для IE будет Undefined ну и бог с ним у него и так работает а сюда по ошибке поподаем
							infra.style(t); 
						}
					}
					try{
						el.removeChild(bug);
					}catch(e){
						if(infra.DEBUG)alert('Ошибка при удалении временного элемента в infra.html\n'+ e);
					}
				}

			}else{
				var res=el.innerHTML;
			}
		}
		//var end=js.getUnick();
		//var time=end-start;
		//this.timer+=time;
		return res;
	};
	infra.getStyle = function(el, cssprop){
		if (el.currentStyle) //IE
			return el.currentStyle[cssprop]
		else if (document.defaultView && document.defaultView.getComputedStyle) //Firefox
			return document.defaultView.getComputedStyle(el, "")[cssprop]
		else //try and get inline style
			return el.style[cssprop]
	};

	infra.listen(infra,'layer.onshow.cond',function(){
		if(!this.tpl){
			this.exec_onshow_savemybranch=true;
			this.exec_onshow_msg='Нет шаблона';
			return null;//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
		}
	});
	infra.getData=function(layer){
		//Используется в propcheck.js
		var data=layer.data;//Может быть и undefined
		if(data&&data.constructor==Array){//Если массив то это просто строка в виде данных
			data=infra.load(data[0]);
		}else if(typeof(data)=='string'){
			data=infra.loadJSON(data);
		}else if(data&&typeof(data)=='object'){//data может быть объектом данных.. хм.. 
			//depricated
		}
		return data;
	}
	infra.isDataParse=function(obj){

		if(!obj)alert('Нет obj в infra.isDataParse '+arguments);
		if(!obj.tpl)return false;
		if(obj.data||obj.tpls||obj.tplroot)return true;
		if(this.process_show){
			var tpl=this.getTpl(obj);
			if(tpl.charAt(0)=='{')return true;
		}
		return false;
	}
	infra.getTpl=function(layer){
		var tpl=layer.tpl;
		if(typeof(tpl)=='string'){
			tpl=infra.load(tpl);
		}else if(tpl&&tpl.constructor==Array){
			tpl=tpl[0];
		}else{
			tpl='';
		}
		if(!tpl)tpl='';
		return String(tpl);
	}
	infra.getHtml=function(layer){//Вызывается как для основных так и для подслойв tpls frame. Расширяется в tpltpl.prop.js
		var tpl=this.getTpl(layer);
		if(this.isDataParse(layer)){
			var tpls={};
			var t=this.template.getTpls(layer.tpl);//С кэшем перепарсивания
			
			for(var i in t)tpls[i]=t[i];
			
			var repls={};//- подшаблоны для замены, Важно, что оригинальный распаршеный шаблон не изменяется
			infra.fora(layer.tplsm,function(tm){//mix tpl
				var t=this.template.getTpls(tm);//С кэшем перепарсивания
				for(var i in t){
					repls[i]=t[i];//Нельзя подменять в оригинальном шаблоне, который в других местах может использоваться без подмен
				}
			}.bind(this));
			
			var strdata=layer.data;
			layer.data=this.getData(layer);//подменили строку data на объект data
			
			var tplroot=layer.tplroot||'root';
			var html=this.template.make(tpls,layer,tplroot,repls,layer.dataroot);

			layer.data=strdata;
		}else{
			var html=tpl;
		}
		return html;
	}
	infra.listen(infra,'onparse',function(){
		this.process_show=true;//ключ когда события tpl начинают выполняться
	});
	infra.listen(infra,'onshow',function(){
		this.process_show=false;
	});
	infra.listen(infra,'',function(fn,clsname,def){//Вызво обработчиков из шаблона
		if(clsname!=='layer')return;
		var layer=this;
		if((infra.process_show||layer.showed)&&infra.isDataParse(layer)){
			var tpls=infra.template.getTpls(layer.tpl);//С кэшем перепарсивания
			if(tpls[fn]){
				if(typeof(tpls[fn][0])=='string'){
					try{
						if(tpls[fn].length!=1){
							throw 'Найдена вставка {переменной} в обработчике';
						}
						tpls[fn][0]=new Function(tpls[fn][0]);
					}catch(e){
						alert('Ошибка в обработчике в шаблоне не смогли создать функцию \n'+e+'\n------\n'+clsname+' '+fn+'\n'+callback+'\n'+layer);
					}
				}

				var callback=tpls[fn][0];
				try{
					r=callback.apply(layer);
				}catch(e){
					if(infra.DEBUG){
						alert('Ошибка в обработчике в шаблоне\n'+e+'\n------\n'+clsname+' '+fn+'\n'+callback+'\n'+layer);
					}
				}
				if(!clsname&&r!==undefined)return r;
			}
		}
	});


	infra.listen(infra,'layer.onparse.cond',function(){
		var layer=this;
		var data=infra.getData(layer);
		/*
			Должен ли dataisnew выставляться если у слоя был установлен путь до данных которые ранее уже загружались но после был новый путь устанолен и сейчас возврат к старому
			По этому необходимо изменения делать в data чтобы они остались без повторного запуска.. если изменения будут в config то они там останутся прежними так как в указанном случае dataisnew не запустится
		*/
		if(layer.dataoo[layer.data]!==data||!layer.counter){//counter определяется тоже в layer.onparse.before.. и так как подключён до tpl.js уже определена 1 для первого перепарсивания
			return true;//Нужно перепарсивать
			//Не можем здесь использовать dataisnew так как по временной цепочке оно определяется дальше и здесь определяться не может
			//Так как cond условие могло выйти на предыдущих проверках и dataisnew осталось бы не определённым если бы определялось тут
			//По этому dataisnew опредляется дальше а здесь мы проверяем так же как если бы dataisnew определяли,.. только без определения
		}
	})
	/**
	* onparse слоя запускается всегда когда слой парсится, если слой не парсится о чём есть событие layer.onparse.cond которое вернуло false то onparse не запустится
	*/
	infra.listen(infra,'layer.onparse.before',function(){//Для dataisnew
		//Устанавливается свойство dataisnew - новые это данные или нет, что можно проверить в onparse. Таким образмо в Onparse можно разрулить обе ситуации. Нужно изменить данные и чтобы они применились и нужно работать с данными после того как они были обработаны как вновь загруженные.
		var layer=this;
		if(!layer.dataoo)layer.dataoo={};
		var data=infra.getData(layer);
		layer.dataisnew=(layer.dataoo[layer.data]!==data||layer.counter===1);//counter определяется тоже в layer.onparse.before.. и так как подключён до tpl.js уже определена 1 для первого перепарсивания
		layer.dataoo[layer.data]=data;
		layer.$data=data;//depricated
		//У Слоя может подменяться data и тогда данные не грузятся по вторно но для слоя будут новые и обработчик отработает повторно
		//Нужно сохранять загруженный путь вместе с адресом
		//Мы могли бы это сделать сквозным, но нет .. правильней для каждого обработчика при первом запуске с этими данными должена быть метка первый это раз или второй.. 
	});

	/*infra.listen(infra,'layer.onparse.cond',function(){//externals уже должны обработаться
		var layer=this;
		if(!layer.tpl&&(layer.tplroot||layer.tplroottpl)){
			var parent=layer;
			while(parent=parent.parent){
				if(parent.tpl){
					layer.tpl=parent.tpl;
					break;
				}
			}
		}
	},false,true);*/

	infra.listen(infra,'layer.onhide.after',function(){//onhide запускается когда слой ещё виден
		var layer=this;
		//Нужно для?? удаления мега css класса у body определяющего ширину всей страницы
		if(!infra.divs[layer.div]){//значит другой слой щас в этом диве покажется и реальное скрытие этого дива ещё впереди. Это чтобы не было скачков
			//Нужно проверить не будет ли див заменён самостоятельно после показа. Сейчас мы знаем что другой слой в этом диве прямо не показывается. Значит после того как покажутся все слои и див останется в вёрстке только тогда нужно его очистить.
			var div=document.getElementById(layer.div);
			if(div){
				div.innerHTML='';
			}
		}
	});
	infra.listen(infra,'layer.onparse.after',function(){//До того как сработает событие самого слоя в котором уже будут обработчики вешаться
		var layer=this;
		var html=infra.getHtml(layer);
		if(!html)html='';
		layer.html=html;
	});

	
	/*infra.listen(infra,'layer.oninsert.cond',function(){
		var layer=this;
		if(!layer.div)return false;
	
	});*/
	
	infra.listen(infra,'layer.oninsert.after',function(){//До того как сработает событие самого слоя в котором уже будут обработчики вешаться
		var layer=this;
		var div=document.getElementById(layer.div);
		if(!div){//Мы не можем проверить это в isshow так как для проверки надо чтобы например родитель показался, Но показ идёт одновременно уже без проверок.. сейчас.  По этому сейчас и проверяем. Пользователь не должне допускать таких ситуаций.
			if(infra.DEBUG){//Также мы не можем проверить в layer.oninsert.cond так как ситуация когда див не найден это ошибка, у слоя должно быть определено условие при которых он не показывается и это совпадает с тем что нет родителя. В конце концов указываться divparent
				alert('Не найден контейнер для слоя:'+'\ndiv:'+layer.div+'\ntpl:'+layer.tpl+'\ntplroot:'+layer.tplroot+'\nparent.tpl:'+(layer.parent?layer.parent.tpl:''));
			}
			return false;
		}
		infra.html(div,layer.html);
		delete layer.html;//нефиг в памяти весеть
	});
	infra.parsed.add('dataroot');
	//
	document.writeold=document.write;
	document.write=function(html){// нужно указывать document.write.div где нибудь в шаблоне и тогда фукнция сработает
		var sdiv=document.write.div||'documentwrite';
		var div=document.getElementById(sdiv);
		if(div){
			if(div.id=='documentwrite')div.id='';
			infra.html(div,html);
		}else{
			if(infra.DEBUG){
				alert('Нужен document.write.div указать или создать ещё элемент с id '+sdiv);
			}
		}
	}
	document.write.div='documentwrite';
})();
