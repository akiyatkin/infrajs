<?php
define('INFRA_SEQ_SELDOM','·');
define('INFRA_SEQ_OFFEN','.');


function infra_seq_short($val,$offen=INFRA_SEQ_OFFEN,$seldom=INFRA_SEQ_SELDOM){//Возвращает строку - короткая запись последовательности
	if(is_string($val)=='string')return $val;
	if(!is_array($val))$val=array();
	$nval=array();
	infra_forr($val,function($s) use(&$nval,$offen,$seldom){ 
		$nval[]=str_replace($offen,$seldom,$s);
	});
	return implode($offen,$nval);
}

function infra_seq_right($val,$offen=INFRA_SEQ_OFFEN,$seldom=INFRA_SEQ_SELDOM){//Возвращает массив - правильную запись последовательности

	if(!is_array($val)){
		if(!is_string($val))$val='';
		$val=explode($offen,$val);

		infra_forr($val,function(&$s,$i) use($seldom,$offen){
			$s=str_replace($seldom,$offen,$s);
		});
		if($val[sizeof($val)-1]==='')array_pop($val);
		if($val[0]==='')array_shift($val);

		$val=array_values($val);
	}
	$res=array();
	for($i=0,$l=sizeof($val);$i<$l;$i++){
		$s=$val[$i];
		if($s===''&&sizeof($res)!=0&&$res[$i-1]!==''){//Сами себя не должны отменять
			array_pop($res);
		}else{
			$res[]=$s;
		}
	}
	return $res;
}
function &infra_seq_set(&$obj,$right,&$val){
	$make=is_null($val)?false:true;
	$i=sizeof($right)-1;
	$need=&infra_seq_get($obj,$right,0,$i,$make);
	if(!$make&&is_array($need))unset($need[$right[$i]]);
	if($make) $need[$right[$i]]=$val;
	return $obj;
}
function &infra_seq_get(&$obj,&$right,$start=0,$end=NULL,$make=false){//получить из obj значение right до end(не включая) брать начинаем с start
	if(is_null($end))$end=sizeof($right);
	$r=null;
	if($end===$start)return $obj;
	if(is_null($obj))return $r;//Даже если make мы не изменим ссылку null на obj в javascript так что и тут так

	if(is_array($obj)){
		if($make&&(@!is_array($obj[$right[$start]])))$obj[$right[$start]]=array();
		if($make||@!is_null($obj[$right[$start]])){//Если передать несуществующее свойство в функцию принимающую ссылку то это свойство начнёт существовать
			return infra_seq_get($obj[$right[$start]],$right,++$start,$end,$make);
		}
	}else if(is_object($obj)){
		if($make&&!is_array($obj->$$right[$start]))$obj->$$right[$start]=array();
		if(property_exists($obj,$right[$start])){//К методам объектов обращаться не можем
			return infra_seq_get($obj->$right[$start],$right,++$start,$end,$make);
		}
	}else{
		return $r;
	}
	return $r;
		/*
	if(is_null($end))$end=sizeof($right);
	if($end===$start)return $obj;
	if(is_null($obj))return;
	
	if(is_array($obj)){
		if($make&&!is_array($obj[$right[$start]]))$obj[$right[$start]]=array();
		if(array_key_exists($right[$start],$obj)){
			return infra_seq_get($obj[$right[$start]],$right,++$start,$end,$make);
		}
	}else if(is_object($obj)){
		if($make&&!is_array($obj->$$right[$start]))$obj->$$right[$start]=array();
		if(property_exists($obj,$right[$start])){//К методам объектов обращаться не можем
			return infra_seq_get($obj->$right[$start],$right,++$start,$end,$make);
		}
	}else{
		return NULL;
	}*/
}
?>
