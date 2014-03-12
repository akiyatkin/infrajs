<?php
//========================
	global $infrajs,$infra;
	$query=$_SERVER['QUERY_STRING'];
	$query=urldecode($query);
	
	infra_State_set($query);

//========================
//infrajs oninit
//========================
	//=======wait=====//
	infra_wait($infrajs,'oninit',function(){

		//div
		infrajs_div_init();
	});
	
	infra_wait($infrajs,'oninit',function(){	
		//config
		infrajs_configinit();
	});
	infra_wait($infrajs,'oninit',function(){	
		//parsed
		infrajs_parsedinit();
	});
	//=======listen=====//
	infra_listen($infrajs,'oninit',function(&$layer){
		//seo
		infrajs_seo_init();
	});
//========================
//layer oninit
//========================
	infra_listen($infra,'layer.oninit',function(&$layer){
		//external

		infrajs_externalCheck($layer);
	});
	infra_listen($infra,'layer.oninit',function(&$layer){
		//config
		infrajs_configinherit($layer);
	});
	infra_listen($infra,'layer.oninit',function(&$layer){
		//infrajs
		$store=&infrajs_store();
		$layer['store']=array('counter'=>$store['counter']);
	});
	infra_listen($infra,'layer.oninit',function(&$layer){
		//unick
		infrajs_unickSet($layer);
	});
	infra_listen($infra,'layer.oninit',function(&$layer){//это из-за child// всё что после child начинает плыть. по этому надо state каждый раз определять, брать от родителя.
		//state
		if(!isset($layer['dyn'])){//Делается только один раз
			infrajs_setState($layer,'state',$layer['state']);
			infrajs_setState($layer,'istate',$layer['istate']);
		}
		
	});
	infra_listen($infra,'layer.oninit',function(&$layer){
		//state
		if(!isset($layer['parent']))return;
		
		infrajs_setState($layer,'istate',$layer['dyn']['istate']);//Возможно у родителей обновился state из-за child у детей тоже должен обновиться хотя они не в child
		infrajs_setState($layer,'state',$layer['dyn']['state']);
	});

	infra_listen($infra,'layer.oninit',function(&$layer){	

		//state child
		if(@!$layer['child'])return;//Это услвие после setState 

		$st=&$layer['state']->child;
		if($st)$state=$st->name;
		else $state='###child###';

		infra_fora($layer['child'],function(&$state,&$l){
			infrajs_setState($l,'state',$state);
			infrajs_setState($l,'istate',$state);
		},array($state));
	});
	infra_listen($infra,'layer.oninit',function(&$layer){//Должно быть после external, чтобы все свойства у слоя появились
		//state childs
		infra_forx($layer['childs'],function(&$l,$key){//У этого childs ещё не взять external
			if(!@$l['state'])infrajs_setState($l,'state',$key);
			if(!@$l['istate'])infrajs_setState($l,'istate',$key);
		});
	});	




	/*infra_listen($infra,'layer.oninit',function(&$layer){
		//state link
		if(!isset($layer['link'])&&!isset($layer['linktpl']))$layer['linktpl']='{istate}';
	});*/
	infra_listen($infra,'layer.oninit',function(&$layer){
		//seo
		infrajs_seo_checkseolinktpl($layer);
		infrajs_seo_collectLayer($layer);
	});
//========================
//layer is check
//========================
	infrajs_isAdd('check',function(&$layer){//может быть у любого слоя в том числе и у не iswork, и когда нет старого значения

		//infrajs это исключение
		if(!$layer)return false;//Может быть когда вернулись с check к родителю который ещё ниразу небыл в работе
		if(!infrajs_isWork($layer))return false;//Нет сохранённого результата, и слой не в работе, если работа началась с infrajs.check(layer) и у layer есть родитель, который не в работе
	});
	infrajs_isAdd('check',function(&$layer){
		//state
		if(is_null($layer['istate']->obj))return false;
	});
	
	

//========================
//layer oncheck
//========================
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//autosave
		if(infrajs_tplonlyclient($layer))return;
		infrajs_autosaveRestore($layer);
	});
	/*infra_listen($infra,'layer.oncheck',function(&$layer){//onchange вызывается только у слоёв у которых есть соответствующий state, до проверки external
		//external
		//infrajs_externalCheck($layer);
	});*/
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//counter

		if(@!$layer['counter'])$layer['counter']=0;
	});
	/*infra_listen($infra,'layer.oncheck',function(&$layer){	
		//state link
		if(isset($layer['linktpl']))$layer['link']=infra_template_parse(array($layer['linktpl']),$layer);
	});	*/
	



	
	infra_listen($infra,'layer.oncheck',function(&$layer){//Заменяем пустые слои иначе они считаются пустыми массивами в которых слоёв нет
		//subs
		if(@!$layer['subs'])return;
		infra_foro($layer['subs'],function(&$val){
			if(!$val||!is_array($val))$val=array('_'=>'notempty');
		});
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){//external уже проверен
		//subs
		infrajs_subMake($layer);
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){//external уже проверен
		//config
		infrajs_configtpl($layer);
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//div
		infrajs_divtpl($layer);

	});
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//tpl
		infrajs_tplrootTpl($layer);
		infrajs_tpldatarootTpl($layer);
		infrajs_tplTpl($layer);
		infrajs_tplJson($layer);
	});
	
	infra_listen($infra,'layer.oncheck',function(&$layer){//external то ещё не применился у вложенных слоёв, по этому используется свойство envtochild
		//env envs
		infrajs_envEnvs($layer);
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){//external то ещё не применился нельзя
		//env envtochild
		infrajs_envtochild($layer);

	});
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//env envframe
		infrajs_envframe($layer);
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//env envframe
		infrajs_envframe2($layer);
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){//external уже есть 
		//env myenvtochild
		infrajs_envmytochild($layer);
	});
//========================
// infrajs oncheck
//========================

//========================
//layer is show
//========================
	infrajs_isAdd('show',function(&$layer){
		//infrajs
		if(!infrajs_is('check',$layer))return false;
	});
	infrajs_isAdd('show',function($layer){
		//is
		return infrajs_isCheck($layer);
	});
	infrajs_isAdd('show',function(&$layer){
		//tpl
		if(@$layer['tpl'])return;
		//infrajs_isSaveBranch($layer,true);//Когда нет шаблона слой скрывается, но не скрывает свою ветку

		$r=true;
		if($layer['parent']){//Пустой слой не должен обрывать наследования если какой=то родитель скрывает всю ветку
			$r=infrajs_isSaveBranch($layer['parent']);
			if(is_null($r))$r=true;
		}
		infrajs_isSaveBranch($layer,$r);

		return false;
	});
	infrajs_isAdd('show',function(&$layer){//Родитель скрывает ребёнка если у родителя нет опции что ветка остаётся целой
		//infrajs
		
		if(@!$layer['parent'])return;
		if(infrajs_is('show',$layer['parent']))return;
		if(infrajs_isSaveBranch($layer['parent']))return;//Какой-то родитель таки не показывается, например пустой слой, теперь нужно узнать скрывает родитель свою ветку или нет
		//echo $layer['tplroot'].':'.$layer['parent']['tplroot'].'<br>';

		return false;		
	});
	infrajs_isAdd('show',function(&$layer){
		//div
		return infrajs_divcheck($layer);
	});
	infrajs_isAdd('show',function(&$layer){
		//div
		if(@!$layer['div'])return false;//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
	});
	
	infrajs_isAdd('show',function(&$layer){
		//tpl depricated
		if(is_string(@$layer['tpl'])&&@$layer['tplcheck']){//Мы не можем делать проверку пока другой плагин не подменит tpl
			$res=infra_loadTEXT($layer['tpl']);
			if(!$res)return false;
		}
	});
	infrajs_isAdd('show',function(&$layer){
		//tpl depricated
		if(infrajs_tplonlyclient($layer))return;
		return infrajs_tplJsonCheck($layer);
	});
	
	
	infrajs_isAdd('show',function(&$layer){
		//env

		if(@!$layer['env']){
			infrajs_getHtml($layer);
			global $infrajs;
			if(isset($infrajs['com']['env'])){
				$vals=$infrajs['com']['env'];
				if(!isset($layer['myenv']))$layer['myenv']=array();
				infra_forr($vals,function($val) use(&$layer){
					$layer['myenv'][$val]=true;
				});
			}
			return;
		}
		return infrajs_envCheck($layer);
	});
	//infrajs_isAdd('show',function(&$layer){
		//tpl
		//if(@$layer['onlyclient'])return false;
	//});
//========================
//layer onshow
//========================
	infra_listen($infra,'layer.oncheck',function(&$layer){
		//counter
		$layer['counter']++;
	});
	infra_listen($infra,'layer.oncheck',function(&$layer){//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя	
		//div
		if(@!$layer['div']&&@$layer['parent'])$layer['div']=$layer['parent']['div'];
	});

	infra_listen($infra,'layer.oncheck',function(&$layer){//Без этого не показывается окно cо стилями.. только его заголовок.. 
		//div
		infra_forx($layer['divs'],function(&$l,$div){
			if(@!$l['div'])$l['div']=$div;
		});	
	});
	infra_listen($infra,'layer.onshow',function(&$layer){
		//tpl
		if(infrajs_tplonlyclient($layer))return;
		$layer['html']=infrajs_getHtml($layer);
	});
	infra_listen($infra,'layer.onshow',function(&$layer){
		//tpl
		if(infrajs_tplonlyclient($layer))return;
		global $infrajs;
		
		$r=infra_html($layer['html'],$layer['div']);
		if(!$r&&(!isset($layer['divcheck'])||!$layer['divcheck']))echo 'Не найден div '.$layer['div'].' infra_html<br>';
		unset($layer['html']);//нефиг в памяти весеть
	});



	infra_listen($infra,'layer.onshow',function(&$layer){
		//seo
		infrajs_seo_now($layer);
	});
	
//========================
//infrajs onshow
//========================
	infra_listen($infrajs,'onshow',function(){
		//seo		
		infrajs_seo_save();
		infrajs_seo_apply();
		
	});
?>