<?php
//Свойство external
//
global $infrajs_external_props;
$infrajs_external_props=array( //Расширяется в env.js
	'div'=>function(&$now,&$ext){
		return $ext;
	},
	'layers'=>function(&$now,&$ext){
		if(!$now)$now=array();
		else if(infra_isAssoc($now)!==false)$now=array($now);
		
		infra_fora($ext,function($j) use(&$now){
			//array_unshift($now,array('external'=>&$ext));
			array_push($now,array('external'=>&$j));
		});
		return $now;
	},
	'external'=>function(&$now,&$ext){//Используется в global.js, css
		if(!$now)$now=array();
		else if(infra_isAssoc($now)!==false)$now=array(&$now);
		array_push($now,$ext);
		return $now;
	},
	'config'=>function(&$now,&$ext,&$layer){//object|string any
		if(infra_isAssoc($ext)===true){
			if(!$now)$now=array();
			foreach($ext as $j=>$v){
				if(!is_null(@$now[$j]))continue;
				$now[$j]=&$ext[$j];
			}
		}else{
			if(is_null($now))$now=&$ext;
		}
		return $now;
	}
);
function infrajs_externalAdd($name,$func){
	global $infrajs_external_props;
	$infrajs_external_props[$name]=$func;
}

function infrajs_externalCheck(&$layer){
	while(@$layer['external']&&(!isset($layer['onlyclient'])||!$layer['onlyclient'])){
		$ext=&$layer['external'];
		infrajs_externalCheckExt($layer,$ext);
	}
}
function infrajs_externalMerge(&$layer,&$external,$i){//Используется в configinherit
	global $infrajs_external_props;
	if(infra_isEqual($external[$i],$layer[$i])){//Иначе null равено null но null свойство есть и null свойства нет разные вещи

	}else if(isset($infrajs_external_props[$i])){
		$func=$infrajs_external_props[$i];
		while(is_string($func)){//Указана не сама обработка а свойство с такойже обработкой
			$func=$infrajs_external_props[$func];
		}
		$layer[$i]=call_user_func_array($func,array(&$layer[$i],&$external[$i],&$layer,&$external,$i));
	/*}else if(is_callable($external[$i])){//Функции вызываются сначало у описания потом у external потому что external добавляется потом
		//Имя может совпать с какой-нибудь функцией php
		if(isset($external['debug'])){
			var_dump($external[$i]);
			echo '<Pre>';
			var_dump($i);
		}
		infra_listen($layer,$i,$external[$i]);*/
	}else{
		
		if(is_null($layer[$i]))$layer[$i]=$external[$i];
	}
}
function infrajs_externalCheckExt(&$layer,&$external){
	if(!$external)return;
	unset($layer['external']);

	/*
	//---- Управляем порядком свойств в слое
		$tlayer=array();
		foreach($layer as $i=>&$v){
			if($i=='external'){
				unset($layer[$i]); //Всё что до external остаётся в томже порядке, всё что после будет после свойств external
			}else if(@!$layer['external']){
				$tlayer[$i]=&$layer[$i];
				unset($layer[$i]);
			}
		}
		infra_fora($external,function(&$layer,&$exter){
			if(is_string($exter)) $external=&infra_loadJSON($exter);
			else $external=$exter;
			if($external)foreach($external as $i=>&$v){
				if(isset($layer[$i]))continue;//Свойство было указано до external и не удалялось
				$layer[$i]=null;//создали пустые свойства в новом порядке
			}

		},array(&$layer));
		
		foreach($tlayer as $i=>&$v){//Вернули родные свойства обратно но уже в нужном порядке
			$layer[$i]=&$v;
		}
		
	//------
	*/

	infra_fora($external,function(&$exter) use(&$layer){
		if(is_string($exter)) $external=&infra_loadJSON($exter);
		else $external=$exter;

		
		if($external)foreach($external as $i=>&$v){
			infrajs_externalMerge($layer,$external,$i);
		}

	});
}
?>
