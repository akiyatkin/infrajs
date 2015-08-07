<?php

//Свойство layers
namespace itlife\infrajs\ext;

use itlife\infrajs\Infrajs;

class layers
{
	public static function init()
	{
		global $infrajs;
		infra_wait($infrajs, 'oninit', function () {
			infrajs::runAddList('layers');
		});
	}
}
