<?php
namespace itlife\infrajs\infrajs\ext;
use itlife\infrajs\infrajs;
use itlife\infrajs\infrajs\ext\external;
class env {
	function init(){
		global $infra,$infrajs;
		infra_wait($infrajs,'oninit',function(){
			//Обработка envs, envtochild, myenvtochild, envframe
			external::add('myenv','config');//Обрабатывается также как config
			//infrajs_externalAdd('env','');//Никак не обрабатывается.. будет установлено только если нечего небыло
			external::add('envs','childs');//Объединяется так же как childs

			infrajs::runAddKeys('envs');//Теперь бегаем и по envs свойству
		});
	}
	function check(&$layer){
		if(@!$layer['env'])return;
		$store=&infrajs::store();
		$r=null;
		//Слои myenv надо показывать тогдаже когда и показывается сам слой
		$myenv=null;
		$ll=null;
		infrajs::run(infrajs::getWorkLayers(),function(&$l) use(&$layer,&$myenv,&$ll){//Есть окружение и мы не нашли ни одного true для него
			if(!isset($l['myenv']))return;

			
			if(!infrajs::is('check',$l))return;//В back режиме выйти нельзя.. смотрятся все слои

			

			if(infra_isEqual($l,$layer))return;//Значение по умолчанию смотрится отдельно 
			
			if(!isset($l['myenv'][$layer['env']]))return;
			if(is_null($l['myenv'][$layer['env']]))return;

			if(infrajs::is('show',$l)){//Ищим последнюю установку на счёт env
				$myenv=$l['myenv'][$layer['env']];
				$ll=&$l;
				
			}
		});

		


		if(!is_null($myenv)){//Если слой скрываем слоем окружения который у него в родителях числиться он после этого сам всё равно должен показаться
			if($myenv){//Значение по умолчанию смотрим только если myenv undefined
				$r=true;
			}else{
				$r=false;
				infrajs::isSaveBranch($layer,!!infrajs::isParent($ll,$layer));
				//infrajs_isSaveBranch($layer,false);
			}
		}
		if(is_null($r)&&@$layer['myenv']){//Значение по умолчанию
			$myenv=$layer['myenv'][$layer['env']];
			if(!is_null($myenv)){//Оо есть значение по умолчанию для самого себя
				if($myenv){
					$r=true;
				}else{//Если слой по умолчанию скрыт его детей не показываем
					$r=false;
					infrajs::isSaveBranch($layer,false);
				}
			}
		}
		$layer['envval']=$myenv;
		if($r) return !!$myenv;	
		return false;
	}
	

	//myenv:(object),//Перечислены env которые нужно показать и значения которые им нужно передать в envval
	//env:(string),//Имя окружения которое нужно укзать чтобы слой с этим свойством показался
	//envval:(mix),//Значение, которое было установленое в myenv. envval устанавливается автоматически, в ручную устанавливать его нельзя



/*
 	//когда есть главная страница и структура вложенных слоёв, но вложенные показываются не при всех состояниях и иногда нужно показать главную страницу. Это не правильно. Адреса должны автоматически нормализовываться.
	//Если такого состояния нет нужно сделать редирект на главную и по этому задачи показывать главную во внутренних состояниях отпадает
	//при переходе на клиенте должно быть сообщение страницы нет, а при обновлении постоянный редирект на главную или на страницу поиска
	infra.listen(infra,'layer.oncheck',function(){
		//myenv Наследуется от родителя только когда совсем ничего не указано. Если хоть что-то указано от родителя наследования не будет.
		var layer=this;
		if(layer.myenv)return;
		if(!layer.parent||!layer.parent.myenv)return;
		layer.myenv={};
		infra.foro(layer.parent.myenv,function(v,k){
			layer.myenv[k]=v;
		});
	});
	*/

	function checkinit(&$layer){
		if(@!$layer['envs'])return;
		infra_forx($layer['envs'],function(&$l,$env){//Из-за забегания вперёд external не применился а в external могут быть вложенные слои
			$l['env']=$env;
			$l['envtochild']=true;
		});
	}
	function envtochild(&$layer){
		$parent=$layer;
		while(@$parent['parent']&&@$parent['parent']['env']){
			$parent=$parent['parent'];
			if(@$parent['envtochild']){
				$layer['env']=$parent['env'];
				return;
			}
		}
	}
	function envframe(&$layer){
		if(@!$layer['envframe'])return;
		if(@$layer['env'])return;

		$stor=infra_stor();
		if(@!$stor['envcouter'])$stor['envcouter']=0;
		$stor['envcouter']++;
		$layer['env']='envframe'.$stor['envcouter'];
	}
	function envframe2(&$layer){
		$parent=@$layer['parent'];
		if(!$parent)return;
		if(@!$parent['envframe'])return;
		if(@!$layer['myenv'])$layer['myenv']=array();
		$layer['myenv'][$parent['env']]=true;
		$layer['myenvtochild']=true;
	}
	function envmytochild(&$layer){
		$parent=$layer;
		while(@$parent['parent']&&@$parent['parent']['myenv']){
			$parent=$parent['parent'];
			if(@$parent['myenvtochild']){
				if(!isset($layer['myenv']))$layer['myenv']=array();
				foreach($parent['myenv'] as $i=>$v){
					$layer['myenv'][$i]=$parent['myenv'][$i];
				}
				return;
			}
		}
	}
}