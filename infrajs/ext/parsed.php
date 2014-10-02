<?php
	//parsed
	global $infrajs;
	
	function infrajs_parsedinit(){
		infrajs_parsedAdd('dataroot');
		infrajs_parsedAdd('tplroot');
		infrajs_parsedAdd('envval');
		infrajs_parsedAdd('json');
		infrajs_parsedAdd('tpl');
		infrajs_parsedAdd('is');
		infrajs_parsedAdd('parsed');
		infrajs_parsedAdd(function($layer){
			if(!isset($layer['parsedtpl']))return '';
			return infra_template_parse(array($layer['parsedtpl']),$layer);
		});
	};

	//Обработка - перепарсиваем слой если изменились какие-то атрибуты
	function infrajs_parsed($layer){//Функция возвращает строку характеризующую настройки слоя 
		global $infrajs_parsed_props;
		$str='';
		for($i=0,$l=sizeof($infrajs_parsed_props);$i<$l;$i++){
			$val=$infrajs_parsed_props[$i]($layer);
			if(!is_null($val)){
				$str.='|'.$val;
			}
		}
		return $str;
	}
	global $infrajs_parsed_props;
	$infrajs_parsed_props=array();//Расширяется в global.js
	
	function infrajs_parsedAdd($fn){
		global $infrajs_parsed_props;
		if(is_string($fn))$func=function($layer) use($fn){
			if(!isset($layer[$fn]))return '';
			return print_r($layer[$fn],true);
		};
		else $func=$fn;
		$infrajs_parsed_props[]=$func;
	}
?>