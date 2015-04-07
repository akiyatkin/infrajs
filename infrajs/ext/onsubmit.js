//onsubmit - обработка ответа формы. Ответ обработчика находится в layer.config.ans (обрабатываются параметры в ответе result, msg
//Проверки перед отправки формы не предусмотрено. Всё проверяет сервер и отвечает в msg.
//При изменении msg слой перепарсивается
infrajs.onsubmitinit=function(){
	infrajs.parsedAdd(function(layer){//parsed должен забираться после установки msg config-a
		//После onsubmit слой должен перепарсится
		if(!layer.onsubmit)return '';
		if(!layer.config||!layer.config.ans)return '';
		var str=layer.config.ans.msg;
		if(!str)str='';
		if(layer.config.ans.time){
			str+=layer.config.ans.time;
		}
		return str;		
	});
}
infrajs.setonsubmit=function(layer){
	if(!layer['onsubmit'])return;
	
	if(!layer.config)layer.config={};

	var div=$('#'+layer.div);

	var form=div.find('form');

	form.find('.submit').click(function(){
		form.submit();
	});
	form.submit(function(e){
		e.preventDefault(); // <-- important

		if(layer.config['onsubmit'])return false;//Защита от двойной отправки
		layer.config['onsubmit']=true;

		if(infra.loader)infra.loader.show();
		setTimeout(function(){// Надо чтобы все обработчики повесились на onsubmit и сделали всё что нужно с отправляемыми данными и только потом отправлять
			//autosave должен примениться
			infra.require('vendor/malsup/form/jquery.form.js');
			form.ajaxSubmit({
				dataType:'json',
				async:false,
				type:'post',
				complete:function(xhr){
					layer.config['onsubmit']=false;
					var ans=false,text=false,msg='';
					if(xhr){
						text=xhr.responseText;
						try{
							if(!text)throw 'Empty response';
							ans=eval('(function(a){return a})('+text+')');
						}catch(e){
							msg='Ошибка на сервере';
							if(infra.debug) msg+='<hr>'+e+'<hr>'+text;
						}
					}else{
						msg='Ошибка связи';
					}
					if(layer.global)infrajs.global.set(layer.global);//Удаляет config.ans у слоёв
					if(!ans)ans={result:0,msg:msg};
					layer.config.ans=ans;
					infra.session.syncNow();
					
					if(infra.loader)infra.loader.hide();
					infra.fire(layer,'onsubmit');//в layers.json указывается onsubmit:true, а в tpl осуществляется подписка на событие onsubmit и обработка
					if(typeof(layer.onsubmit)=='function')layer.onsubmit(layer);
					if(ans.go)infra.State.go(ans.go);
					else infrajs.check();
				}
			});
		},1);
		return false;
	});
};


/*
infra.listen(infra,'layer.onsubmit',function(){
	var layer=this;
	var plugin='onsubmitpopup';
	if(!layer[plugin])return;
	infra.require('*popup/popup.js');
	infra.fora(layer[plugin],function(l){
		l.parent=layer;
		popup.open(l);
	})
});*/
