<?php
@define('ROOT','../../../../');


//autosave, autosaveclient, autosavename

//Из-за этого нельзя кэшировать снимок всей страницы

	function infrajs_autosave_get(&$layer,$name='',$def=null){
		if(@is_null($layer['autosavename']))return $def;
		$val=infra_session_get($layer['autosavename'].'.'.$name);
		if(@is_null($val))return $def;
		return $val;
	}
	/*
	function infrajs_autosaveRestore(&$layer){
		if(@$layer['autosavename'])$layer['autosave']=array();
		if(@$layer['autosaveclient'])return;//autosave только на клиенте
		if(@is_null($layer['autosave']))return;
		if(@!$layer['autosavename'])$layer['autosavename']='autosave';
		$val=infrajs_autosave_get($layer);//Загружается сессия и устанавливается в слой в текущий вкладке
		if(!$val)$val=array();
		$layer['autosave']=$val;//В обработчиках onchange уже можно использовать данные из autosave
	}*/
?>
