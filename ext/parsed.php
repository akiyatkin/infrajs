<?php
namespace itlife\infrajs\infrajs\ext;
//parsed
//Обработка - перепарсиваем слой если изменились какие-то атрибуты
class parsed {
	//Расширяется в global.js
	static $props=array();
	static function init(){
		parsed::add('dataroot');
		parsed::add('tplroot');
		parsed::add('envval');
		parsed::add('json');
		parsed::add('tpl');
		parsed::add('is');
		parsed::add('parsed');
		parsed::add(function($layer){
			if(!isset($layer['parsedtpl']))return '';
			return infra_template_parse(array($layer['parsedtpl']),$layer);
		});
	}
	
	static function check($layer){//Функция возвращает строку характеризующую настройки слоя 
		$str=array();
		for($i=0,$l=sizeof(parsed::$props);$i<$l;$i++){
			$call=parsed::$props[$i];
			$val=$call($layer);
			if(!is_null($val))$str[]=$val;
		}
		return implode('|',$str);
	}
	static function add($fn){
		if(is_string($fn))$func=function($layer) use($fn){
			if(!isset($layer[$fn]))return '';
			return print_r($layer[$fn],true);
		};
		else $func=$fn;
		parsed::$props[]=$func;
	}
}