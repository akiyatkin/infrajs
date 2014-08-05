//Скрипт. Точка это разделитель. Могут проблемы когда имя свойства файл расширением,
//autosavename - путь где сохраняются данные,
//
//Атрибуты 
//autosave="0" не использовать автосохранение для данного слоя
//autosavebreak="1" позволять у поля сбрасывать автососхранение

infra.wait(infrajs,'oninit',function(){
	infrajs.externalAdd('autosave',function(now,ext,layer,external){
		if(external.inheritance){
			now=ext;//Если есть метка о наследовании, то просто сохраняем указатель
		}else{//Иначе копируем и при изменении одного другой изменяться не будет 
			if(!now)now={};
			for(var v in ext){//Объект autosave не наследуется а копируется
				now[v]=ext[v];
			}
		}
		return now;
	});
	/*infrajs.parsedAdd(function(layer){
		//Работы в itlife выбранная работа сохраняется но дальше слой не должен перепарсиваться... всё обрабатывается на кликах
		if(!layer.autosavename)return '';
		if(!window.JSON)return '';
		return JSON.stringify(layer.autosave);
		//сохранённое значение формы, а на сервере стоит из-за этого запрет на кэширование, так как обращение к сессии
	});*/

});

(function(){

	var autosave={
		getInps:function(div){
			return $('#'+div).find('select, [type=search], [type=tel], [type=email], [type=password], [type=text], [type=radio], [type=checkbox], textarea').filter('[autosave!=0]').filter('[name!=]');
		},
		/**
		* слой у которого нужно очистить весь autosave, например после отправки формы на сервер, нужно сбросить сохранённые в инпутах данные
		* exc массив свойств которые очищать не нужно и нужно сохранить.. 
		*/
		clear:function(layer){//Если autosave у двух слоёв одинаковый нельзя нарушать связь
			if(!layer.autosavename)return;
			layer.autosave={};
			infra.session.set(layer.autosavename);
		},
		get:function(layer,name,def){//blinds
			if(!layer.autosavename)return def;
			if(!name)name='';
			var val=infra.session.get(layer.autosavename+'.'+name);
			if(val===undefined)return def;
			return val;
		},
		logout:function(){//нет возможности востановить значения по умолчанию указанные в слоях.
			infra.session.logout();
			location.href=location.href;//Чтобы сбросить autosave в слоях
		},
		set:function(layer,name,val){//skoroskidka, rte.layer.js
			if(!layer.autosavename)return;
			infra.session.set(layer.autosavename+'.'+name,val);

			var right=infra.seq.right(name);
			layer.autosave=infra.seq.set(layer.autosave,right,val);
		},
		//-----------
		loadAll:function(layer){
			var inps=autosave.getInps(layer.div).filter('[autosave]');
			inps.each(function(){
				var inp=$(this);
				var name=inp.attr('name');
				var val=autosave.getVal(inp);
				var valsave=autosave.get(layer,name);
				if(valsave!==undefined){
					autosave.setVal(inp,valsave);
					autosave.bracket(inp,true);
					inp.change();
				}
			});
			
		},
		saveAll:function(layer){
			if(!layer.autosavename)return;
			var inps=autosave.getInps(layer.div).filter('[autosave]');

			inps.each(function(){
				var inp=$(this);
				var name=inp.attr('name');
				if(!name)return;
				//this.removeAttribute('notautosaved');//должно быть отдельное событие которое при малейшем измееннии поля ввода будет удалять это свойство //Если свойства этого нет, то сохранять ничего не нужно
				var val=autosave.getVal(inp);
				var nowval=autosave.get(layer,name);
				if(!nowval)nowval='';
				if(val==nowval)return;
				autosave.bracket(inp,true);
				autosave.set(layer,name,val);
			});
		},
		getVal:function(inp){
			inp=$(inp);
			if(inp.attr('type')=='checkbox'){
				var val=inp.is(':checked');
			}else if(inp.is('radio')){
				var val=inp.is(':checked');
			}else if(inp.is('select')){
				var val=inp.find('option:selected').val();
			}else{
				var val=inp.val();
			}
			return val;
		},
		setVal:function(inp,valsave){
			inp=$(inp);
			if(inp.attr('type')=='checkbox'){
				inp.attr('checked',valsave);
			}else if(inp.attr('type')=='radio'){
				var sel=inp.filter('[value="'+valsave+'"]');
				if(sel.length){
					inp.attr('checked',true);
				}
			}else if(inp.is('select')){
				var sel=inp.find('option[value="'+valsave+'"]');
				if(!sel.length){
					sel=inp.find('option:contains("'+valsave+'")');
				}
				if(sel.length){
					inp.find('option').removeAttr('selected');
				}
				sel.attr('selected','selected');
			}else{
				inp.val(valsave);
			}
		},
		bracket:function(inp,is){
			if(!is){
				$(inp).prevAll('.autosavebreak:first').css('display','none');
			}else{
				$(inp).prevAll('.autosavebreak:first').css('display','');
			}
		},
		fireEvent:function(element,event){
		  if (document.createEventObject) {
		    // dispatch for IE
		    var evt = document.createEventObject();
		    return element.fireEvent('on'+event,evt)
		  } else {
		    // dispatch for others
		    var evt = document.createEvent("HTMLEvents");
		    evt.initEvent(event, true, true ); // event type,bubbling,cancelable
		    return !element.dispatchEvent(evt);
		  }
		}
	};

	infrajs.autosaveRestore=function(layer){
		if(layer.autosavenametpl)layer.autosavename=infra.template.parse([layer.autosavenametpl],layer);
		var defautosave={};
		if(layer.autosavename){
			if(typeof(layer.autosave)=='object'){
				defautosave=layer.autosave;
			}
			layer.autosave={};
		}
		
		if(!layer.autosavename)return;

		var val=infrajs.autosave.get(layer)||{};//Загружается сессия и устанавливается в слой в текущий вкладке
		
		layer.autosave=val;//В обработчиках onchange уже можно использовать данные из autosave
		for(var i in defautosave){
			if(typeof(layer.autosave[i])=='undefined'){
				layer.autosave[i]=defautosave[i];
			}
		}
	}
	infrajs.autosaveHand=function(layer){
		if(!layer.autosavename)return;
		var inps=autosave.getInps(layer.div).not('[autosave]').attr('autosave',1);//Берём input тольо не обработанные
		inps.each(function(){
			var inp=this;
			var html='<div class="autosavebreak" title="Отменить изменения" style="display:none; position:absolute; width:9px; height:3px; cursor:pointer; background-color:gray;"onmouseout="this.style.backgroundColor=\'gray\'" onmouseover="this.style.backgroundColor=\'red\'"></div>';
			if(inp.getAttribute('autosavebreak')){
				inp.removeAttribute('autosavebreak');
				$(inp).before(html);
				var def=autosave.getVal(inp);
				$(inp).prevAll('.autosavebreak:first').click(function(){
					autosave.setVal(inp,def);
					//$(inp).change();//Применится для визуального редактора
					autosave.fireEvent(inp,'change');
					autosave.set(layer,inp.name,undefined);//В сессии установится null 
					autosave.bracket(inp,false);//Скрываем пипку сбороса сохранённого
				});
			}
		});
		//Функция сохраняет все значение, а не только того элемента на ком она сработала

		autosave.loadAll(layer);//Востанавливаем то что есть в autosave, При установки нового занчения срабатывает change

		//change может программно вызываться у множества элементов. это приводит к тормозам.. нужно объединять
		inps.change(function(){//Всё на change.. при авто изменении нужно вызывать событие change
			//autosave.saveAll(layer);
			var inp=$(this);
			var name=inp.attr('name');//getInps проверяет чтобы у всех были name
			//this.removeAttribute('notautosaved');//должно быть отдельное событие которое при малейшем измееннии поля ввода будет удалять это свойство //Если свойства этого нет, то сохранять ничего не нужно

			var val=autosave.getVal(inp);
			//var nowval=autosave.get(layer,name);
			//if(!nowval)nowval='';
			//if(val===nowval)return;
			
			autosave.bracket(inp,true);
			autosave.set(layer,name,val);
		});//Подписались на события inputов onchange
	}
	


	
	/*
	* При скрытии слоя сохраняем изменения в его полях 
	* 
	*/
	/*infra.listen(infra,'layer.onhide',function(){//rte.layer.js сохраняется autosave в onhide - подставляетяс texarea после редактора
		var layer=this;
		if(!layer.autosavename)return;
		if(layer.autosaveonhide===false)return;
		autosave.saveAll(layer);
	});*/




	
		


	infrajs.autosave=autosave;//Это нужно из за метода clear который может вызываться кем угодно. и localSave
	

	//infra.seq.set(infra.template.scope,['autosave','get'],autosave.get);
	//infra.seq.set(infra.template.scope,['infra','session','get'],infra.session.get);
	
})();
