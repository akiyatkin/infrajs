<?php

//Свойство dyn, state, crumb
//infra.load('*infrajs/ext/external.js');//Уже должен быть
namespace itlife\infrajs\ext;

use itlife\infrajs\Infrajs;
use itlife\infra;

class Crumb
{
	public static function init()
	{
		global $infra,$infrajs;

		infra_wait($infrajs, 'oninit', function () {

			$root = infra\ext\Crumb::getInstance();
			global $infra_template_scope;
			infra_seq_set($infra_template_scope, infra_seq_right('infra.Crumb.query'), $root->query);
			infra_seq_set($infra_template_scope, infra_seq_right('infra.Crumb.params'), infra\ext\Crumb::$params);
			infra_seq_set($infra_template_scope, infra_seq_right('infra.Crumb.get'), infra\ext\Crumb::$get);

			$cl = function ($mix = null) {
				return infra\ext\Crumb::getInstance($mix);
			};
			infra_seq_set($infra_template_scope, infra_seq_right('infra.Crumb.getInstance'), $cl);
			external::add('child', 'layers');
			external::add('childs', function (&$now, &$ext) {
				//Если уже есть значения этого свойства то дополняем
				if (!$now) {
					$now = array();
				}
				infra_forx($ext, function (&$n, $key) use (&$now) {
					if (@$now[$key]) {
						return;
					}
					//if(!now[key])now[key]=[];
					//else if(now[key].constructor!==Array)now[key]=[now[key]];
					//now[key].push({external:n});
					$now[$key] = array('external' => &$n);
				});

				return $now;
			});
			external::add('crumb', function (&$now, &$ext, &$layer, &$external, $i) {//проверка external в onchange
				Crumb::set($layer, 'crumb', $ext);

				return $layer[$i];
			});
			infrajs::runAddKeys('childs');
			infrajs::runAddList('child');

});
	}
	public static function set(&$layer, $name, &$value)
	{
		if (!isset($layer['dyn'])) {
			$layer['dyn'] = array();
		}
		$layer['dyn'][$name] = $value;
		if (isset($layer['parent'])) {
			$root = &$layer['parent'][$name];
		} else {
			$root = &infra\ext\Crumb::getInstance();
		}
		if ($layer['dyn'][$name]) {
			$layer[$name] = &$root->getInst($layer['dyn'][$name]);
		} else {
			$layer[$name] = &$root;
		}
	}
}
