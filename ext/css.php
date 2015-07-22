<?php
//Свойство css	
namespace itlife\infrajs\ext;
use itlife\infrajs;
class css{
	function check(&$layer){
		if(!isset($layer['css']))return;
		$sotre=infrajs::store();
		if(!$store['css'])$store['css']=array();
		if($store['css'][$css])return;
		infra_fora($layer['css'],function($css) use(&$layer,&$store){			
			$store['css'][$css]=true;
			$code=infra_loadTEXT($css);
			infra_html('<style>'.$code.'</style>',$layer['div']);
		});
	}
}