//Обработка - перепарсиваем слой если изменились какие-то атрибуты
	infra.parsed=function(layer){//Функция возвращает строку характеризующую настройки слоя 
		var str='';
		for(var prop in this.parsed.props){
			if(typeof(this.parsed.props[prop])=='function'){
				try{
					var val=this.parsed.props[prop].apply(layer);
				}catch(e){
					var val='error';
					if(infra.DEBUG)alert('Ошибка в infra.parsed(layer) '+prop+'\n'+e+'\n'+this.parsed.props[prop]+'\nlayer:'+layer);
				}
			}else{
				try{
					var val=layer[prop];
					if(val&&val.toString)val=val.toString();
				}catch(e){
					var val='error-toString';
					if(infra.DEBUG)alert('Ошибка toString в infra.parsed(layer) '+prop+'\n'+e+'\n'+val.toString+'\nlayer:'+layer);
				}
			}
			str+='|'+val;
		}
		return str;
	}
	infra.parsed.props={
		//Расширяется в global.js
		'data':true,
		'tpl':true,
		'is':true,
		'config':true//Добавлено для окна.. Когда is занят
	};
	infra.parsed.add=function(prop,fn){
		this.props[prop]=fn||true;
	}

	infra.listen(infra,'layer.onparse.cond',function(){
		var layer=this; //Проверяем не установлены ли новые данные для слоя
		if(layer.parsed!==infra.parsed(layer))return true;	
	});
	infra.listen(infra,'layer.onparse.before',function(){
		this.parsed=infra.parsed(this);	//Выставляется после обработки шаблонов в которых в событиях onparse могла измениться data
	});
