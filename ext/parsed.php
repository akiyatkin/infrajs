<?php

namespace itlife\infrajs\ext;

//parsed
//Обработка - перепарсиваем слой если изменились какие-то атрибуты
class parsed
{
	//Расширяется в global.js
	public static $props = array();
	public static function init()
	{
		self::add('dataroot');
		self::add('tplroot');
		self::add('envval');
		self::add('json');
		self::add('tpl');
		self::add('is');
		self::add('parsed');
		self::add(function ($layer) {
			if (!isset($layer['parsedtpl'])) {
				return '';
			}

			return infra_template_parse(array($layer['parsedtpl']), $layer);
		});
	}

	public static function check($layer)
	{
		//Функция возвращает строку характеризующую настройки слоя 
		$str = array();
		for ($i = 0, $l = sizeof(self::$props);$i < $l;++$i) {
			$call = self::$props[$i];
			$val = $call($layer);
			if (!is_null($val)) {
				$str[] = $val;
			}
		}

		return implode('|', $str);
	}
	public static function add($fn)
	{
		if (is_string($fn)) {
			$func = function ($layer) use ($fn) {
			if (!isset($layer[$fn])) {
				return '';
			}

			return print_r($layer[$fn], true);
		};
		} else {
			$func = $fn;
		}
		self::$props[] = $func;
	}
}
