<?php
//Свойство div
//infrajs_layerindiv


/* Это нужно чтобы скрывать слой.. а на php слои не скрываются
$store=&infrajs_store();
$store['divs']=array();
function infrajs_layerindiv($div,&$layer=null){//Функция в любой момент говорит правду какой слой находится в каком диве
	$store=&infrajs_store();
	if($layer)$store['divs'][$div]=&$layer;	
	return $store['divs'][$div];
}
global $infra;
infra_listen($infra,'layer.onshow',function(&$layer){
	if(!infrajs_is('show',$layer))return;
	infrajs_layerindiv($layer['div'],$layer);
});
*/

function infrajs_div_init(){
	infrajs_runAddKeys('divs');
	infrajs_externalAdd('divs',function(&$now,$ext){//Если уже есть пропускаем
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
function infrajs_divtpl(&$layer){
	if(!isset($layer['divtpl']))return;
	$layer['div']=infra_template_parse(array($layer['divtpl']),$layer);
}
function infrajs_divcheck(&$layer){
	$start=false;
	if(infrajs_run(infrajs_getWorkLayers(),function(&$l) use(&$layer,&$start){//Пробежка не по слоям на ветке, а по всем слоям обрабатываемых после.. .то есть и на других ветках тоже
		if(!$start){
			if(infra_isEqual($layer,$l))$start=true;
			return;
		}
		if(@$l['div']!=@$layer['div'])return;//ищим совпадение дивов впереди
		if(infrajs_is('show',$l)){
			infrajs_isSaveBranch($layer,infrajs_isParent($l,$layer));
			return true;//Слой который дальше показывается в томже диве найден
		}
	}))return false;
}




/*global $infra;
infra_listen($infra,'layer.onchange.after',function(&$layer){//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя
	if(@!$layer['div']&&$layer['parent'])$layer['div']=$layer['parent']['div'];
});
infrajs_isAdd('show',function(&$layer){
	if(@!$layer['div']){
		$layer['exec_onshow_msg']='Нет дива';
		return false;//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
	}
});
*/



/*
infra_listen($infra,'layer.oncheck',function(&$layer){//в onchange могли добавиться в divs новые дивы, которые остануться не обработанными в divs
	if(@$layer['div'])return;
	if(@!$layer['parent'])return;
	if(@!$layer['parent']['divs'])return;
	$div=infra_forx($layer['parent']['divs'],function(&$layer,&$l,$div){
		if(infra_isEqual($l,$layer))return $div;
	},array(&$layer));
	if($div)$layer['div']=$div;
});
*/


?>
