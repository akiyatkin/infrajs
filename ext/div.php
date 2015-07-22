<?php
//Свойство div
//div::layerindiv


/* Это нужно чтобы скрывать слой.. а на php слои не скрываются
$store=&infrajs::store();
$store['divs']=array();
function layerindiv($div,&$layer=null){//Функция в любой момент говорит правду какой слой находится в каком диве
	$store=&infrajs::store();
	if($layer)$store['divs'][$div]=&$layer;	
	return $store['divs'][$div];
}
global $infra;
infra_listen($infra,'layer.onshow',function(&$layer){
	if(!infrajs::is('show',$layer))return;
	layerindiv($layer['div'],$layer);
});
*/
namespace itlife\infrajs\ext;
use itlife\infrajs;
use itlife\infrajs\ext\external;
class div {
	static function init(){
		infrajs::runAddKeys('divs');
		external::add('divs',function(&$now,$ext){//Если уже есть пропускаем
			if(!$now)$now=array();
			foreach($ext as $i=>$v){
				if(isset($now[$i]))continue;
				$now[$i]=array();
				infra_fora($ext[$i],function(&$l) use(&$now,$i){
					array_push($now[$i],array('external'=>$l));
				});
			}
			return $now;
		});
	}
	static function divtpl(&$layer){
		if(!isset($layer['divtpl']))return;
		$layer['div']=infra_template_parse(array($layer['divtpl']),$layer);
	}
	static function divcheck(&$layer){
		$start=false;
		if(infrajs::run(infrajs::getWorkLayers(),function(&$l) use(&$layer,&$start){//Пробежка не по слоям на ветке, а по всем слоям обрабатываемых после.. .то есть и на других ветках тоже
			if(!$start){
				if(infra_isEqual($layer,$l))$start=true;
				return;
			}
			if(@$l['div']!=@$layer['div'])return;//ищим совпадение дивов впереди
			if(infrajs::is('show',$l)){
				infrajs::isSaveBranch($layer,infrajs::isParent($l,$layer));
				return true;//Слой который дальше показывается в томже диве найден
			}
		}))return false;
	}
}