<?php
namespace itlife\infrajs\infrajs\ext;

//autosave, autosaveclient, autosavename

//Из-за этого нельзя кэшировать снимок всей страницы
class autosave {
	function get(&$layer,$name='',$def=null){
		if(@is_null($layer['autosavename']))return $def;
		$val=infra_session_get($layer['autosavename'].'.'.$name);
		if(@is_null($val))return $def;
		return $val;
	}
}
