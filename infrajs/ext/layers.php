<?php
//Свойство layers
namespace itlife\infrajs\infrajs\ext;
use itlife\infrajs\infrajs;
class layers {
	static function init(){
		global $infrajs;
		infra_wait($infrajs,'oninit',function(){
			infrajs::runAddList('layers');	
		});		
	}
}
