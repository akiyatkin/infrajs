<?php
//Свойство dyn, state, istate
//infra.load('*infrajs/ext/external.js');//Уже должен быть
global $infrajs;
infra_wait($infrajs,'oncheck',function(){
	infrajs_externalAdd('child','layers');
	infrajs_externalAdd('childs',function(&$now,&$ext){//Если уже есть значения этого свойства то дополняем
		if(!$now)$now=array();
		infra_forx($ext,function(&$now, &$n,$key){
			if(@$now[$key])return;
			//if(!now[key])now[key]=[];
			//else if(now[key].constructor!==Array)now[key]=[now[key]];
			//now[key].push({external:n});
			$now[$key]=array("external"=>&$n);
		},array(&$now));
		return $now;
	});
	infrajs_externalAdd('state',function(&$now,&$ext,&$layer,&$external,$i){//проверка external в onchange
		infrajs_setState($layer,'state',$ext);
		return $layer[$i];
	});

	infrajs_externalAdd('istate',function($now,$ext,$layer){
		die('istate в external быть не может потому что istate определяет когда запускается onchange и сейчас onchange уже сработал и проверяется externals где совсем будет не втему обнаружить изменение istate \n'+$layer['tplroot']);
	});	

	infrajs_runAddKeys('childs');
	infrajs_runAddList('child');
	/*infrajs_externalAdd('state',function&(&$now,&$ext,&$layer,&$external,&$i){//проверка external в onchange
		infrajs_setState($layer,'state',$ext);
		return $layer[$i];
	});*/
});
	/**
	* layer.istate=.. - так делать нельзя
	* нужно делать так infrajs.setState(layer,'istate','Компания'); - Компания это относительный путь от состояния родителя
	*/

function infrajs_setState(&$layer,$name,&$value){
	if(!isset($layer['dyn']))$layer['dyn']=array();
	$layer['dyn'][$name]=$value;
	if(isset($layer['parent'])){
		$root=&$layer['parent'][$name];
	}else{
		$root=&infra_State_getState();
	}
	$layer[$name]=&$root->getState(array($layer['dyn'][$name]));
}
/*
function infrajs_stateChilds(&$layer){//oncheck
	infra_forx($layer['childs'],function(&$l,$key){//У этого childs ещё не взять external
		if(!$l['state'])$l['state']=$key;
		if(!$l['istate'])$l['istate']=$key;
	});
}
/*
function infrajs_stateChild(&$layer){//oncheck
	if(@!$layer['child'])return;//Это услвие после setState 

	$st=&$layer['state']->child;
	if($st)$state=$st->name;
	else $state='###child###';
	infra_fora($layer['child'],function(&$state,&$l){
		$l['state']=$state;
		$l['istate']=$state;
	},array($state));
	//infrajs_setState($layer,'state',$state);//Функция setState определена в state.js
	//$layer['istate']=&$layer['state'];//В случае с child istate и state становятся одинаковыми и равны state - состоянию от которого считываются параметры
}*/
?>