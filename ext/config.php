<?php

/*
	config
	configinherit:(bool)
*/

namespace itlife\infrajs\ext;

class config
{
	public static function configtpl(&$layer)
	{
		$name = 'config';//stencil//
		$nametpl = $name.'tpl';
		if (isset($layer[$nametpl])) {
			if (!isset($layer[$name])) {
				$layer[$name] = array();
			}
			foreach ($layer[$nametpl] as $i => $v) {
				$layer[$name][$i] = infra_template_parse(array($layer[$nametpl][$i]), $layer);
			}
		}
	}
	public static function init()
	{
		external::add('configtpl', function &(&$now, &$ext, &$layer, &$external, $i) {
			//if(!isset($layer['configtpl']))return $now;
			//if(isset($layer['config']))return $now;
			if (!$now) {
				return $ext;
			}

			return $now;
		});
	}
	public static function configinherit($layer)
	{
		if (isset($layer['configinherit'])) {
			$layer['config'] = $layer['parent']['config'];
			unset($layer['configinherit']);
		}
	}
}
