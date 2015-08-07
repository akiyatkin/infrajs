<?php

namespace itlife\infrajs\ext;

//unick:(number),//Уникальное обозначение слоя
//Нужно для уникальной идентификации какого-то слоя. Для хранения данных слоя в глобальной области при генерации слоя на сервере и его отсутствия на клиенте. Slide
use itlife\infrajs\Infrajs;

class unick
{
	public static $counter = 1;
	public static function init()
	{
		global $infra,$infrajs;
		infra_wait($infrajs, 'oninit', function () {
			//session и template
			global $infra_template_scope;
			$fn = function ($name, $value) {
				return unick::find($name, $value);
			};
			infra_seq_set($infra_template_scope, infra_seq_right('infrajs.find'), $fn);

infra_seq_set($infra_template_scope, infra_seq_right('infrajs.unicks'), unick::$unicks);
		});
	}
	public static $unicks = array();
	public static function check(&$layer)
	{
		if (@!$layer['unick']) {
			$layer['unick'] = self::$counter++;
		}
		self::$unicks[$layer['unick']] = &$layer;
	}
	public static function &find($name, $value)
	{
		$layers = infrajs::getAllLayers();
		$right = infra_seq_right($name);

		return infrajs::run($layers, function &(&$layer) use ($right, $value) {
			if (infra_seq_get($layer, $right) == $value) {
				return $layer;
			}
			$r = null;

			return $r;
		});
	}
}
