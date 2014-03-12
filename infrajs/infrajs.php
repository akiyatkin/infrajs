<?php
/*//Функции для написания плагинов
infrajs_store();
infrajs_storeLayer(layer)
infrajs_getWorkLayers();
infrajs_getAllLayers();

infrajs_run(layers,callback);
infrajs_runAddList('layers')
infrajs_runAddKeys('divs');

infrajs_isSaveBranch(layer,val);
infrajs_isParent(layer,parent);
infrajs_isWork(layer);

infrajs_is('rest|show|check',layer);
infrajs_isAdd('rest|show|check',callback(layer));

infrajs_check(layer);
infrajs_checkAdd(layer);


*/
global $infrajs;
$infrajs=array();

function &infrajs_storeLayer(&$layer){
	if(@!$layer['store'])$layer['store']=array('counter'=>0);//Кэш используется во всех is функциях... iswork кэш, ischeck кэш используется для определения iswork слоя.. путём сравнения ))
	return $layer['store'];//Очищается кэш в checkNow	
}
function &infrajs_store(){//Для единобразного доступа в php, набор глобальных переменных
	global $infrajs_store;
	if(!$infrajs_store)$infrajs_store=array(
			"timer"=>false,
			"run"=>array('keys'=>array(),'list'=>array()),
			"waits"=>array(),
			"process"=>false,
			"counter"=>0,//Счётчик сколько раз перепарсивался сайт, посмотреть можно в firebug
			"alayers"=>array(),//Записываются только слои у которых нет родителя... 
			"wlayers"=>array()//Записываются обрабатываемые сейчас слои
	);
	return $infrajs_store;
};

function infrajs_getAllLayers(){
	$store=&infrajs_store();
	return $store['alayers'];	
}
function &infrajs_getWorkLayers(){
	$store=&infrajs_store();
	return $store['wlayers'];
};
/*
	в check вызывается check// игнор
	два check подряд будет два выполнения.

	###mainrun всегда check всегда один на php, но для совместимости..., для тестов.. нужно помнить что каждый check работает с одним и тем же infra_html

	Гипотетически можем работать вне клиента.. дай один html дай другой... выдай клиенту третий
	без mainrun мы не считаем env
*/
function infrajs_check(&$layers=null){//Пробежка по слоям
	$store=&infrajs_store();
	global $infrajs;
	//if($store['process'])return;//Уже выполняется
	//$store['process']=true;
	//процесс характеризуется двумя переменными process и timer... true..true..false.....false
	$store['counter']++;		
	$store['ismainrun']=is_null($layers);
	
	if(!is_null($layers)){
		$store['wlayers']=array(&$layers);
	}else{
		$store['wlayers']=$store['alayers'];
	}

	infra_fire($infrajs,'oninit');//сборка событий



	

	infrajs_run(infrajs_getWorkLayers(),function(&$store, &$layer,&$parent){//Запускается у всех слоёв в работе которые wlayers
		if($parent)$layer['parent']=&$parent;
		infra_fire($layer,'layer.oninit');
		if(infrajs_is('check',$layer)){
			infra_fire($layer,'layer.oncheck');
		}	
		
	},array(&$store));//разрыв нужен для того чтобы можно было наперёд определить показывается слой или нет. oncheck у всех. а потом по порядку.

	infra_fire($infrajs,'oncheck');//момент когда доступны слои по getUnickLayer
	
	infrajs_run(infrajs_getWorkLayers(),function(&$layer){//С чего вдруг oncheck у всех слоёв.. надо только у активных
		if(infrajs_is('show',$layer)){			
			
			//Событие в котором вставляется html		
			infra_fire($layer,'layer.onshow');//при клике делается отметка в конфиге слоя и слой парсится... в oncheck будут подстановки tpl и isRest вернёт false
			infra_fire($layer,'onshow');
			//onchange показанный слой не реагирует на изменение адресной строки, нельзя привязывать динамику интерфейса к адресной строке, только черещ перепарсивание
		}
	});//у родительского слоя showed будет реальное а не старое

	
	infra_fire($infrajs,'onshow');//loader, setA, seo добавить в html, можно зациклить check
	//$store['process']=false;
};
function infrajs_checkAdd(&$layers){//Два раза вызов добавит слой повторно
	//Чтобы сработал check без аргументов нужно передать слои в add
	//Слои переданные в check напрямую не сохраняются
	$store=&infrajs_store();
	$store['alayers'][]=&$layers;//Только если рассматриваемый слой ещё не добавлен
};

function infrajs_isAdd($name,$callback){//def undefined быть не может
	$store=&infrajs_store();
	if(!isset($store[$name]))$store[$name]=array();//Если ещё нет создали очередь
	return $store[$name][]=$callback;
}
function &infrajs_is($name,&$layer=null){

	$store=&infrajs_store();
	if(!$store[$name])$store[$name]=array();//Если ещё нет создали очередь
	//Обновлять с новым check нужно только результат в слое, подписки в store сохраняются, Обновлять только в случае когда слой в работе
	if(!is_array($layer)&&!$layer)return $store[$name];//Без параметров возвращается массив подписчиков
	$cache=&infrajs_storeLayer($layer);//кэш сбрасываемый каждый iswork
	
	
	if(!infrajs_isWork($layer)){
		
		$cache[$name]=$oldval;
		if(!is_null($cache[$name])){//Результат уже есть
			return $cache[$name];//Хранить результат для каждого слоя
		}else{
			return;
			//die('Слой ни разу не был в работе и у него запрос is');
		}
	}
	//слой проверили по всей очередь

	if(@!is_null($cache[$name])){//Результат уже есть
		return $cache[$name];//Хранить результат для каждого слоя
	}

	$cache[$name]=true;//взаимозависимость не мешает, Защита от рекурсии, повторный вызов вернёт true как предварительный кэш
	for($i=0,$l=sizeof($store[$name]); $i<$l; $i++){
		$r=$store[$name][$i]($layer);
		if(!is_null($r)&&!$r){
			$cache[$name]=$r;
			break;
		}
	}
	return $cache[$name];
}
// is подписка, результат
//решение
// - две функции
// - массив как с аргументами





//run
function &infrajs_run(&$layers,$callback,$args=array(),&$parent=null){
	//$store=&infrajs_store('run_array');//$r, $props=$infrajs_run_props;
	//if($layers===true)$layers=&infrajs_getWorkLayers();
	//if($layers===false)$layers=&infrajs_getAllLayers();
	$r=&infra_fora($layers,function&(&$parent,&$callback,&$args,&$layer){

		//$r=call_user_func_array($callback,array_merge($args,array(&$layer,&$parent)));

		$aargs=array_values(array_merge($args,array(&$layer,&$parent)));		
		$r=&$callback($aargs[0],$aargs[1],@$aargs[2],@$aargs[3],@$aargs[4],@$aargs[5],@$aargs[6],@$aargs[7],@$aargs[8],@$aargs[9]);


		if(!is_null($r))return $r;
		$r=&infra_foro($layer,function&(&$layer,&$callback,&$args,&$val,$name){
			
			$store=&infrajs_store();
			if(!$store['run'])$store['run']=array();
			$props=&$store['run'];
			
			if(isset($props['list'][$name])){

				$r=&infrajs_run($val,$callback,$args,$layer);
				if(!is_null($r))return $r;
			}else if(isset($props['keys'][$name])){
				$r=&infra_foro($val,function&(&$layer,&$callback,&$args,&$v,$i){

					$r=&infrajs_run($v,$callback,$args,$layer);
					if(!is_null($r))return $r;
				},array(&$layer,&$callback,&$args));
				if(!is_null($r))return $r;
			}
		},array(&$layer,&$callback,&$args));


		if(!is_null($r))return $r;

	},array(&$parent,&$callback,&$args));
	return $r;
}
function infrajs_runAddKeys($name){
	$store=&infrajs_store();
	$store['run']['keys'][$name]=true;
}
function infrajs_runAddList($name){
	$store=&infrajs_store();
	$store['run']['list'][$name]=true;
}


function infrajs_isWork($layer){//val для отладки, делает метку что слой в работе
	$store=&infrajs_store();
	$cache=&infrajs_storeLayer($layer);//work
	return $cache['counter']&&$cache['counter']==$store['counter'];//Если слой в работе метки будут одинаковые
}
function infrajs_isParent(&$layer,&$parent){
	 while($layer){
		 if(infra_isEqual($parent,$layer))return true;
		 $layer=&$layer['parent'];
	 }
	 return false;
}
function infrajs_isSaveBranch(&$layer,$val=null){
	$cache=&infrajs_storeLayer($layer);
	if(!is_null($val))$cache['is_save_branch']=$val;	
	return @$cache['is_save_branch'];
}
/*function &infrajs_getParent(&$layer){//пробежка по infrajs_getWorkLayers не гарантирует правильного родителя
	if(!is_null($layer['parent']))return $layer['parent'];
	$ls=array(infrajs_getAllLayers(),infrajs_getWorkLayers());
	$layer['parent']=&infrajs_run($ls,function(&$layer,&$l,&$parent){
		if(infra_isEqual($layer,$l))return $parent;
	},array(&$layer));	
	if(@!$layer['parent'])$layer['parent']=false;
	return $layer['parent'];
}*/

function infrajs_checkNow(){
	
};
/**/
?>