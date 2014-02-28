//onsubmit - обработка ответа формы. Ответ обработчика находится в layer.config.ans (обрабатываются параметры в ответе result, msg
//Проверки перед отправки формы не предусмотрено. Всё проверяет сервер и отвечает в msg.
//При изменении msg слой перепарсивается
infrajs.onsubmitinit=function(){
	infrajs.parsedAdd(function(layer){//parsed должен забираться после установки msg config-a
		if(!layer.onsubmit)return '';
		if(!layer.config||!layer.config.ans||!layer.config.ans.msg)return '';
		return layer.config.ans.msg;		
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
			infra.require('infra/lib/jquery/jquery.form.js');
			form.ajaxSubmit({
				dataType:'json',
				type:'post',
				complete:function(xhr){
					layer.config['onsubmit']=false;
					var ans=false,text=false,msg='';
					if(xhr){
						text=xhr.responseText;
						try{
							ans=eval('(function(a){return a})('+text+')');
						}catch(e){
							msg='Ошибка на сервере';
							if(infra.debug) msg+='<hr>'+e+'<hr>'+text;
						}
					}else{
						msg='Ошибка связи';
					}
					if(!ans)ans={result:0,msg:msg};
					if(!ans.result&&!ans.msg){
						//ans.msg='Ошибка на сервере';
					}
					layer.config.ans=ans;
					if(infra.loader)infra.loader.hide();
					//infra.fire(layer,'layer.onsubmit');
					if(typeof(layer.onsubmit)=='function'){
						layer.onsubmit(layer);
					}else{
					}
					infrajs.check();
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
