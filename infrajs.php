<?php
namespace itlife\infrajs;
require_once(__DIR__.'/infra/infra.php');

/*//Функции для написания плагинов
infrajs::store();
infrajs::storeLayer(layer)
infrajs::getWorkLayers();
infrajs::getAllLayers();

infrajs::run(layers,callback);
infrajs::runAddList('layers')
infrajs::runAddKeys('divs');

infrajs::isSaveBranch(layer,val);
infrajs::isParent(layer,parent);
infrajs::isWork(layer);

infrajs::is('rest|show|check',layer);
infrajs::isAdd('rest|show|check',callback(layer));

infrajs::check(layer);
infrajs::checkAdd(layer);


*/

global $infrajs;
$infrajs=array();
class infrajs {
	static function &storeLayer(&$layer){
		if(@!$layer['store'])$layer['store']=array('counter'=>0);//Кэш используется во всех is функциях... iswork кэш, ischeck кэш используется для определения iswork слоя.. путём сравнения ))
		return $layer['store'];//Очищается кэш в checkNow	
	}
	static function &store(){//Для единобразного доступа в php, набор глобальных переменных
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
	}

	static function getAllLayers(){
		$store=&infrajs::store();
		return $store['alayers'];	
	}
	static function &getWorkLayers(){
		$store=&infrajs::store();
		return $store['wlayers'];
	}
	/*
		в check вызывается check// игнор
		два check подряд будет два выполнения.

		###mainrun всегда check всегда один на php, но для совместимости..., для тестов.. нужно помнить что каждый check работает с одним и тем же infra_html

		Гипотетически можем работать вне клиента.. дай один html дай другой... выдай клиенту третий
		без mainrun мы не считаем env
	*/
	static function check(&$layers=null){//Пробежка по слоям
		$store=&infrajs::store();
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



		
		
		infrajs::run(infrajs::getWorkLayers(),function(&$layer,&$parent) use(&$store){//Запускается у всех слоёв в работе которые wlayers
			if($parent)$layer['parent']=&$parent;
			infra_fire($layer,'layer.oninit');
			if(!infrajs::is('check',$layer))return;
			infra_fire($layer,'layer.oncheck');
		});//разрыв нужен для того чтобы можно было наперёд определить показывается слой или нет. oncheck у всех. а потом по порядку.
		
		infra_fire($infrajs,'oncheck');//момент когда доступны слои по getUnickLayer
		
		infrajs::run(infrajs::getWorkLayers(),function(&$layer){//С чего вдруг oncheck у всех слоёв.. надо только у активных
			if(infrajs::is('show',$layer)){			
				
				//Событие в котором вставляется html		
				infra_fire($layer,'layer.onshow');//при клике делается отметка в конфиге слоя и слой парсится... в oncheck будут подстановки tpl и isRest вернёт false
				infra_fire($layer,'onshow');
				//onchange показанный слой не реагирует на изменение адресной строки, нельзя привязывать динамику интерфейса к адресной строке, только черещ перепарсивание
			}
		});//у родительского слоя showed будет реальное а не старое

		
		infra_fire($infrajs,'onshow');//loader, setA, seo добавить в html, можно зациклить check
		//$store['process']=false;
	}
	static function checkAdd(&$layers){//Два раза вызов добавит слой повторно
		//Чтобы сработал check без аргументов нужно передать слои в add
		//Слои переданные в check напрямую не сохраняются
		$store=&infrajs::store();
		$store['alayers'][]=&$layers;//Только если рассматриваемый слой ещё не добавлен
	}

	static function isAdd($name,$callback){//def undefined быть не может
		$store=&infrajs::store();
		if(!isset($store[$name]))$store[$name]=array();//Если ещё нет создали очередь
		return $store[$name][]=$callback;
	}
	static function &is($name,&$layer=null){

		$store=&infrajs::store();
		if(!$store[$name])$store[$name]=array();//Если ещё нет создали очередь
		//Обновлять с новым check нужно только результат в слое, подписки в store сохраняются, Обновлять только в случае когда слой в работе
		if(!is_array($layer)&&!$layer)return $store[$name];//Без параметров возвращается массив подписчиков
		$cache=&infrajs::storeLayer($layer);//кэш сбрасываемый каждый iswork
		
		
		if(!infrajs::isWork($layer)){
			
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
	static function &run(&$layers,$callback,&$parent=null){
		//$store=&infrajs::store('run_array');//$r, $props=$infrajs_run_props;
		//if($layers===true)$layers=&infrajs_getWorkLayers();
		//if($layers===false)$layers=&infrajs_getAllLayers();
		$r=&infra_fora($layers,function&(&$layer) use(&$parent,$callback){
			
			$r=&$callback($layer,$parent);


			if(!is_null($r))return $r;
			$r=&infra_foro($layer,function&(&$val,$name) use(&$layer,$callback){
				
				$store=&infrajs::store();
				if(!$store['run'])$store['run']=array();
				$props=&$store['run'];
				$r=null;
				if(isset($props['list'][$name])){

					$r=&infrajs::run($val,$callback,$layer);
					if(!is_null($r))return $r;
				}else if(isset($props['keys'][$name])){
					$r=&infra_foro($val,function&(&$v,$i) use(&$layer,$callback){

						$r=&infrajs::run($v,$callback,$layer);
						//if(!is_null($r))
						return $r;
					});
					if(!is_null($r))return $r;
				}
				return $r;
			});


			//if(!is_null($r))
			return $r;

		});
		return $r;
	}
	static function runAddKeys($name){
		$store=&infrajs::store();
		$store['run']['keys'][$name]=true;
	}
	static function runAddList($name){
		$store=&infrajs::store();
		$store['run']['list'][$name]=true;
	}


	static function isWork($layer){//val для отладки, делает метку что слой в работе
		$store=&infrajs::store();
		$cache=&infrajs::storeLayer($layer);//work
		return $cache['counter']&&$cache['counter']==$store['counter'];//Если слой в работе метки будут одинаковые
	}
	static function isParent(&$layer,&$parent){
		 while($layer){
			 if(infra_isEqual($parent,$layer))return true;
			 $layer=&$layer['parent'];
		 }
		 return false;
	}
	static function isSaveBranch(&$layer,$val=null){
		$cache=&infrajs::storeLayer($layer);
		if(!is_null($val))$cache['is_save_branch']=$val;	
		return @$cache['is_save_branch'];
	}



	static function init($index,$div,$src){
		
		if(!empty($_SERVER['QUERY_STRING'])){
			$query=urldecode($_SERVER['QUERY_STRING']);
			if($query{0}=='*'){
				$theme=infra_theme('*infra/theme.php');
				return include($theme);
			}
		}

		infra_admin_modified();//Здесь уже выход если у браузера сохранена версия
		$html=infra_admin_cache('index.php',function($index,$div,$src,$query){
			@header("infrajs-cache: Fail");//Афигенный кэш, когда используется infrajs не подгружается даже
			infra_require('*infrajs/initphp.php');
			global $infrajs;

			$h=infra_loadTEXT($index);

			infra_html($h);//Добавить снизу
			
			$layers=&infra_loadJSON($src);
			
			infra_fora($layers,function&(&$layer) use($div){
				$layer['div']=$div;
				$r=null; return $r;
			});
			
			//$crumb=infra\ext\crumb::getInstance();
			
			infrajs::checkAdd($layers);

			infrajs::check();//В infra_html были добавленыs все указаные в layers слои
			
			$html=infra_html();
			
			$conf=infra_config();
			if(!$conf['infrajs']['onlyserver']){
				$script='<script src="?*infrajs/initjs.php?loadJSON='.$src.'"></script>';
				$html=str_replace('<head>','<head>'.$script,$html);
			}
			
			
			if(!$conf['infrajs']['onlyserver']){
				$script=<<<END
<script type="text/javascript">
		var layers=infra.loadJSON('{$src}');
		infra.fora(layers,function(layer){
			layer.div='{$div}';
		});
		infrajs.checkAdd(layers);
		infra.listen(infra.Crumb,'onchange',function(){
			infrajs.check();
		});
</script>
END;
				$html.=$script;
			}

			return $html;
		},array($index,$div,$src,$query));//Если не кэшировать то будет reparse

		@header("HTTP/1.1 200 Ok");
		echo $html;
	}
}