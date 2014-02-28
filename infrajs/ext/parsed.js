
	//parsed
	infrajs.parsedinit=function(){
		infrajs.parsedAdd('dataroot');
		infrajs.parsedAdd('tplroot');
		infrajs.parsedAdd('envval');
		infrajs.parsedAdd('json');
		infrajs.parsedAdd('tpl');
		infrajs.parsedAdd('is');
		infrajs.parsedAdd(function(layer){
			if(!layer.parsedtpl)return '';
			return infra.template.parse([layer.parsedtpl],layer);
		});
		infrajs.parsedAdd('parsed');
	};
	
	//Обработка - перепарсиваем слой если изменились какие-то атрибуты
	infrajs.parsed=function(layer){//Функция возвращает строку характеризующую настройки слоя 
		var str='';
		for(var i=0,l=this.parsed.props.length;i<l;i++){
			var val=this.parsed.props[i](layer);
			if(typeof(val)=='undefined'){
				val='';
			}
			str+='|'+val;
		};
		return str;
	}
	infrajs.parsed.props=[];//Расширяется в global.js
	
	infrajs.parsedAdd=function(fn){
		if(typeof(fn)=='string')var func=function(layer){return layer[fn]};
		else var func=fn;
		infrajs.parsed.props.push(func);
	}