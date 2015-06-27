<?php
//Свойство external
//
namespace itlife\infrajs\infrajs\ext;
class external {
	static $props;
	static function init(){
		external::$props=array( //Расширяется в env.js
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
	}
	static function add($name,$func){
		external::$props[$name]=$func;
	}
	static function check(&$layer){
		while(@$layer['external']&&(!isset($layer['onlyclient'])||!$layer['onlyclient'])){
			$ext=&$layer['external'];
			external::checkExt($layer,$ext);
		}
	}
	static function merge(&$layer,&$external,$i){//Используется в configinherit
		if(infra_isEqual($external[$i],$layer[$i])){//Иначе null равено null но null свойство есть и null свойства нет разные вещи

		}else if(isset(external::$props[$i])){
			$func=external::$props[$i];
			while(is_string($func)){//Указана не сама обработка а свойство с такойже обработкой
				$func=external::$props[$func];
			}
			$layer[$i]=call_user_func_array($func,array(&$layer[$i],&$external[$i],&$layer,&$external,$i));
		}else{
			
			if(is_null($layer[$i]))$layer[$i]=$external[$i];
		}
	}
	static function checkExt(&$layer,&$external){
		if(!$external)return;
		unset($layer['external']);
		infra_fora($external,function(&$exter) use(&$layer){
			if(is_string($exter)) $external=&infra_loadJSON($exter);
			else $external=$exter;

			
			if($external)foreach($external as $i=>&$v){
				external::merge($layer,$external,$i);
			}

		});
	}
}