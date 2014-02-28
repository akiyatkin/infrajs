<?php
infra_require('*infra/ext/seq.php');

class infra_State{
	function __construct($name='',$parent=false){
		$this->name=$name;
		$this->parent=$parent;
		$this->childs=array();
		$this->child=null;
		$this->obj=null;
		$this->old=null;
		if(!$parent)$this->link='';
		else if(!$parent->parent)$this->link=$parent->link.$name;
		else $this->link=$parent->link.'/'.$name;
	}
	function &getState($state_mix=''){//Относительное получение вложенных State 
		//Если передаётся просто имя, оно должно быть в массиве ['asd.asd'] иначе оно распознается как ['asd','asd']
		if(is_object($state_mix))return $state_mix;
		$state_right=infra_seq_right($state_mix);

		if(sizeof($state_right)==0){
			return $this;
		}

		$state_name=array_shift($state_right);
		if(!@$this->childs[$state_name]){
			

			/*$s=infrajs_store();
			$root=$s['first'];
			var_dump($this===$root);
			var_dump(sizeof($root->childs));

			echo '<b>('.$state_name.')</b>';*/
			$this->childs[$state_name]=new infra_State($state_name,$this);
		}
		return $this->childs[$state_name]->getState($state_right);
	}
	function toString(){
		return $this->getName();
	}
	function getName(){
		$state=&$this;
		$s=array();
		while($state->name){
			array_unshift($s,$state->name);
			$state=&$state->parent;
		}
		return infra_seq_short($s,'/');
	}
	function run($cl,$args=array()){
		$obj=&$this->obj;
		$old=&$this->old;

		infra_foro($old,function($cl,&$obj,&$old,&$args, &$val,$s){//Сначало по тем которых нету

			call_user_func_array($cl,array_merge($args,array($s,$obj[$s],$old[$s])));

		},array($cl,&$obj,&$old,&$args));

		infra_foro($obj,function($cl,&$obj,&$old,&$args, &$val,$s){//Теперь по тем которые есть
			if(!is_null(@$old[$s]))return;//уже забегали значит
			call_user_func_array($cl,array_merge($args,array($s,@$obj[$s],@$old[$s])));
		},array($cl,&$obj,&$old,&$args));
	}
	function prepare(&$obj,&$old){//obj и old текущего объекта
		$this->old=&$old;
		$this->obj=&$obj;
		

		$this->child=&infra_foro($obj,function&(&$that,$val,$s){//Первый случайный child
			return $that->getState(array($s));
		},array(&$this));



		$this->run(function(&$that,$s,$obj,$old){
			$state=&$that->getState(array($s));
			$state->prepare($obj,$old);
		},array(&$this));
	}
	function notify(){//Восходящая система событий от родителя к детям / потом /asdf потом /asdf/asdf
		if(is_null($this->obj)){
			//infra_fire($this,'onhide');
		}else if(is_null($this->old)){
			//infra_fire($this,'onshow');
			//infra_fire($this,'onchange');
		}else{
			//infra_fire($this,'onchange');
		}
		$this->run(function($that,$s,$obj,$old){
			$state=$that->getState(array($s));
			$state->notify();
		},array(&$this));
	}
	function &getRight($parsed=false){
		if(!$parsed)return false;
		$state=$parsed['state'];
		$param=$parsed['param'];

		$f=($parsed['ind'][0]=='$');
		$s=($parsed['ind'][1]=='$');


		if($f){
			$obj1=array();
		}else{
			$obj1=&$this->obj;//Актуальный объект, копия
			if(!is_array($obj1)){
				$obj1=array();
			}
		}

		if($s){
			/*var ro=obj1;//Надо в obj1 найти и грохнуть то что сейчас уже есть от указанного состояния
			for(var i=0,l=state.length;i<l-1;i++){
				ro[state[i]]={};
				ro=ro[state[i]];
			}
			if(l){
				ro[state[l-1]]={};
			}else{
				obj1={};
			}*/
			$ro=&$obj1;
			for($i=0,$l=sizeof($state);$i<$l-1;$i++){
				$ro[$state[$i]]=array();
				$ro=$ro[$state[$i]];
			}

			if($l){
				$ro[$state[$l-1]]=&$real;
			}else{
				$obj1=&$real;
			}



			$obj2=array();//Грохнуть всё что сейчас в текущем состоянии
		}else{

			$r=$this->getState($state)->obj;
			$real=$r;
			$obj2=array();//сохранить всё то сейчас в текщуем состоянии

		/*$ar=array('one'=>array('two'=>array('three'=>array('a'=>'a','b'=>'b'))));
		echo '<pre>';
		print_r($ar);
		$state=array('one','two','three');
		$value='asdf';
		$t=&$ar;
		while(sizeof($state)!==0){
			$n=array_shift($state);
			$t=&$t[$n];
			if(is_null($value)&&!is_array($t))break;
			if(!is_array($t))$t=array();
		}
		$t['a']='b';
		print_r($t);
		print_r($ar);*/

			$ro=&$obj2;
			for($i=0,$l=sizeof($state);$i<$l-1;$i++){
				$ro[$state[$i]]=array();
				$ro=$ro[$state[$i]];
			}

			if($l){
				$ro[$state[$l-1]]=&$real;
			}else{
				$obj2=&$real;
			}
		}


		if(is_array($param)){
			$this->merge($obj2,$param);
		}else{
			$obj2=&$param;
		}
		$obj1=&$this->merge($obj1,$obj2); //obj1 сейчас есть объект правильной адресной строки с учётом состояния и указаний 1$ 2$
		return $obj1;
	}
	function &merge(&$obj1,&$obj2){//Объединяет два объекта  в третий... obj1 меняется
		if(infra_isAssoc($obj2)){
			if(!infra_isAssoc($obj1)){
				$obj1=array();
			}
			foreach($obj2 as $i=>$v){
				$obj1[$i]=&$this->merge($obj1[$i],$obj2[$i]);
			}
		}else{
			$obj1=&$obj2;
		}
		return $obj1;
	}
	/*go:function(obj,replace){//obj или str в адресную строку.. и переходим
		if(typeof(obj)=='string'){
			var parsed=infra.State.parser.parse(obj);
			obj=this.getRight(parsed);
		}
		var s=this;
		while(s.parent){
			var nobj={};
			nobj[s.name]=obj;
			var st=s.name;
			s=s.parent;
			for(var i in s.obj){
				if(i==st)continue;
				nobj[i]=s.obj[i];
			}
			obj=nobj;
		}
		var q=infra.State.parser.getQuery(obj);
		var r=this.write(q,replace);
		return r;
	},
	replace:function(hash){
		hash=decodeURIComponent(hash);
		var righturl=location.protocol+'//'+location.hostname+location.pathname;
		righturl+='#'+hash;
		location.replace(righturl);
	},
	read:function(){
		var hash=decodeURIComponent(location.hash);
		hash=hash.replace('#','');
		return hash;
	},
	write:function(hash,replace){
		hash=hash.replace(/^#/,'');
		if(hash==this.read())return false;//Адрес повторяется
		$.history.load(hash);
		//$.historyLoad(hash);
		return true;
	}*/
};






function &infra_State_getState($state_mix=''){
	$stor=&infra_State_store();
	if(!isset($stor['first']))$stor['first']=new infra_State();
	$s=&$stor['first']->getState($state_mix);
	return $s;
}
function infra_State_get(){//что уже установлено
	$stor=&infra_State_store();
	return $stor['query'];
}


function infra_State_set($href,$auto=false){//href без # ? типа asdf/asdf. auto означает что это движение по истории и записывать это движения в конец истории не надо так как оно уже там

	$parsed=infra_State_parser_parse($href);

	$state=&infra_State_getState();

	$obj=&$state->getRight($parsed);

	$query=infra_State_parser_getQuery($obj);


	$stor=&infra_State_store();
	$stor['query']=$query;
	$state->prepare($obj,$state->obj);
	$state->notify();
}
function &infra_State_store(){
	global $infra_store_state;
	if(!$infra_store_state)$infra_store_state=array();
	return $infra_store_state;
}
function infra_State_getQuery(){
	$stor=&infra_State_store();
	return $stor['query'];
}

function infra_State_parser_afterDomain($href){//Проверка является ли указанный адрес адресом сборки, ссылается на страницу этого сайта
	$mydomain=$_SERVER['HTTP_HOST'];

	$r=preg_match("/^http\:\/\/([a-zA-Z0-9\-\.\/]+)([\/#\?]*)/",$href,$dom);
	if($r){
		$domain=$dom[1];
		if($domain!==$mydomain){
			return false;
		}else{
			$domain.=$dom[2];
		}
		$h=str_replace('http:/'.'/'.domain,'',$href);
	}else{
		$h=$href;
	}
	if(!$h)return '/';
	$h=preg_replace("/^[\?#]+/",'',$h);
	return $h;
}
function infra_State_parser_getObj($strurl,$state=array()){
	/*Принимает строку которая переводится в объект... 
		/some/foo - some объект foo свойство
		=some/foo=moo - foo свойство, moo значение значит свойство foo простое
		/some/foo/moo - foo свойство, moo тоже свойство.. соответственно свойство foo есть объект
		Строка может начинаться со / или c = по умолчанию /
	*/

	
	$surl=$strurl;
	if(preg_match("/^=/",$surl)){
		$surl=preg_replace("/^=/",'',$surl);
		if(!$surl||$surl=='null'){
			$surl=null;
		}
		$obj=$surl;
		//todo state нужно учесть
		return $obj;
	}else if(preg_match("/^\//",$surl)){
	}else if(preg_match("/^&/",$surl)){
		$surl='/$'.$surl;
	}else{
		$surl='/'.$surl;
	}
	$surl=preg_replace('/[\$\/]$/','',$surl);//Убрали слэш которые в конце
	$qnum=preg_match_all("/\//",$surl,$r); // ["/", "/", "/"...]
	$rnum=preg_match_all('/\$/',$surl,$r); // ["$", "$",..]

	for($i=0;$i<$qnum-$rnum;$i++){
		$surl.='$';
	}


	//todo: number string Типы значений должны быть правильными в результате а пока Number(s)!=s - значит s строка
	$surl=preg_replace("/%20/",' ',$surl); // проблема с пробелом в сафари.

	$symbol='[\'"\\\\А-Яа-яёЁ~\\w\\s\\(\\)\\-—…\\.°•’Є\\–,`“”\\?!_:\\[\\];№*%©®@™«»\\+]';
	$symbol=infra_toutf($symbol);

	$regg='/((^|[\\$&\\/])'.$symbol.'+)(?=$|[&\\$])/u';
	$regg=infra_toutf($regg);
	$surl=preg_replace($regg,'$1={}',$surl);	//type -> type:{}

	//var regg=new RegExp('('+symbol+'+)','g');
	$surl=preg_replace('/"/','\\"',$surl);
	$regg="/(".$symbol."+)/u";
	$surl=preg_replace($regg,'"$1"',$surl); // 123 str ->  "123 str"


	$surl=str_replace('=&','=""&',$surl);
	$surl=str_replace('=$','=""$',$surl);

	$surl=str_replace('"undefined"','undefined',$surl);
	$surl=str_replace('"null"','null',$surl);
	$surl=str_replace('"true"','true',$surl);
	$surl=str_replace('"false"','false',$surl);

	$surl=preg_replace('/\$(?!(&|$|\$))/','$&',$surl);
	$surl=preg_replace('/\$/','}',$surl);


	$surl=preg_replace('/\//',':{',$surl);
	$surl=preg_replace('/&/',',',$surl);
	$surl=preg_replace('/=/',':',$surl);	// type=3 -> type:3 or type=null -> type:null

	//Если в качестве значения передана пустая строка будут склееное :}  :,
	$surl=preg_replace('/:}/',':undefined}',$surl);
	$surl=preg_replace('/:,/',':undefined}',$surl);
	$surl=preg_replace('/^:/','',$surl);
	//$surl=infra_toutf($surl);

	//if($strurl=='/')$surl='{}';//hack

	$obj='';
	for($i=0,$l=sizeof($state);$i<$l;$i++){
		$obj.='{"'.$state[$i].'":';
	}
	if($surl){
		$obj.=$surl;
	}else{
		$obj.='{}';
	}
	for($i=0;$i<$l;$i++){
		$obj.='}';
	}
	//$obj.=')';
	$obj=infra_json_decode($obj);
	return $obj;
}
function infra_State_parser_getQuery(&$obj,$r=false){//Возвращает строку запроса из объекта obj  r- служебный параметр
	if(!is_array($obj))return '='.$obj;
	if(is_null($obj))return '';
	$p='';
	foreach($obj as $i=>$v){
		if(is_string($obj[$i])
			||is_integer($obj[$i])
			||is_bool($obj[$i])
		){
			$p.='&'.$i;
			$value=(string)$obj[$i];
			if(strlen($value)>0){
				$p.='='.$value;
			}
		}else if(is_null($obj[$i])){
			//p+='&'+i+'=null';
		}else if(is_array($obj[$i])){
			$p.='&'.$i.'/';
			$p.=infra_State_parser_getQuery($obj[$i],true);
			$p.='$';
		}	
	}
	$p=substr($p,1);
	$p=preg_replace("/\/\$/",'',$p);
	if(!$r){
		$p=preg_replace('/\$+$/','',$p);
		$p=preg_replace('/\/\$\&/','&',$p);
		$p=preg_replace('/\/$/','',$p);
		//$p=preg_replace('/\+/',' ',$p);
		//p=p.replace(/\s/g,'+');
		//p=p.replace(/\s/g,'~');
	}
	return $p;
}
global $infra_State_parser_def_ind;
$infra_State_parser_cache=array();
$infra_State_parser_def_ind=array('$','$');
function infra_getStateEnd($param){//Определяем где заканчивается указанное состояние
	$r=0;
	$e=strpos($param,'=');
	if($e===false)$e=-1;
	$e+=1;
	$s=strpos($param,'/');
	if($s===false)$s=-1;
	$s+=1;
	$a=strpos($param,'&');
	if($a===false)$a=-1;
	$a+=1;
	$l=strlen($param)+1;
	
	if(!$s){
		$r=$a;
	}else if(!$a){
		$r=$s;
	}else if($a<$s){
		$r=$a;
	}else{
		$r=$s;
	}
	if($e&&$e<$r)$r=$e;
	if(!$r)$r=$l;

	if($r)$r--;
	return $r;
}
function infra_State_parser_parse($href=''){

	$param=infra_State_parser_afterDomain($href);
	
	$r=preg_match("/(^[\$&]+)(.*$)/",$param,$ind);//$$asdf/asdf, $$asdf.asdf/asdf, $$/asdf/asdf $$asdf.asdf&asdf
	if($r){
		$param=$ind[2];
		$ind=explode('',$ind[1]);
	}else{
		global $infra_State_parser_def_ind;
		$ind=$infra_State_parser_def_ind;
	}
	if(sizeof($ind)==1){
		$ind[1]=$ind[0];
	}
	
	$r=infra_getStateEnd($param);

	$state=substr($param,0,$r);
	$value=substr($param,$r);
	/*	
	if(r==e){
		var value=param;
		var state='';
	}else if(r){//значит где-то но заканчивается
		var state=param.slice(0,r-1);
		var value=param.slice(r-1);
	}else{
		var value='';
		var state=param;
	}*/
	$state=infra_seq_right($state);
	
	$param=infra_State_parser_getObj($value,$state);
	$parsed=array('href'=>$href,'param'=>&$param,'state'=>&$state,'ind'=>$ind);

	return $parsed;
}
//ВЗР План Функция Космос
function infra_State_forFS($str){
	//Начинаться и заканчиваться пробелом не может
	//два пробела не могут идти подряд
	//символов ' " /\#&?$ быть не может удаляются som e будет some
	//& этого символа нет, значит не может быть htmlentities
	//символов <> удаляются из-за безопасности
	//Виндовс запрещает символы в именах файлов  \/:*?"<>|
	$str=preg_replace('/[\*<>\'"\|\:\/\\\\#\?\$&]/',' ',$str);	
	$str=preg_replace('/^\s+/','',$str);
	$str=preg_replace('/\s+$/','',$str);
	$str=preg_replace('/\s+/',' ',$str);
	return $str;
}
  

/*function infra_State_normalHref($href){//?asdf.asdf/asdf/adf
	$r=explode('?',$href);
	if(sizeof($r)>1){
		$href='?'+$r[1];
	}else{
		return '';
	}
	$parsed=infra_State_parser_parse($href);
	$state=&infra_State_getState($parsed['state']);
	$obj=$state.getRight($parsed);
	$href=infra_State_parser_getQuery($obj);
	return $href;
}*/
function infra_State_setA($html){

	/*
	var view=infra.View.get();
	if(typeof(div)=='string'){
		div=document.getElementById(div);
	}
	if(!div)return;
	var as=div.getElementsByTagName('a');

	for(var i=0,len=as.length; i<len; i++){
		var a = as[i];


		var notweblife=a.getAttribute('notweblife');
		if(notweblife == 'true') continue;//У ссылки может быть запрет на проверку

		var weblife=a.getAttribute('weblife');
		var weblife_refresh=a.getAttribute('weblife_refresh');
		if(weblife == 'true'&&!weblife_refresh) continue;//Ссылка проверена обновлять её не нужно

		
		a.setAttribute('weblife','true');
		var href=a.getAttribute('weblife_href');//Повторно заходим если мягкое изменение адреса
		var isfirst=!href;
		if(isfirst){
			href=a.getAttribute('href');
			if(typeof(href)=='undefined'||href==null)continue;//У ссылки нет ссылки
			if(/^javascript:/.test(href))continue;

			var r=href.split('?');
			if(r.length>1){
				var h=r[0];
				var href='?'+decodeURI(r[1]);
			}else{
				var h=href;
				var href='?';
			}
			var t=h.split('/');
			if(t.length>=3){
				var method=t.shift();
				t.shift();
				var domainpath=t.join('/');
				if(method=='http:'&&domainpath==location.host+location.pathname){
					//t.shift();//домен выкинули
					//href=t.join('/');
				}else{
					var target=a.getAttribute('target');
					if(!target)a.setAttribute('target','_blank');//Если target не установлен
					continue;
				}
			}
			//if(href='?')href='./';
			//if(href&&href!='#'&&!/^\?/.test(href))continue;//Чёйта
		}
		var parsed=this.parser.parse(href);
		var target=a.getAttribute('target');
		if(target||!parsed){//Это какая-то левая ссылка
			if(!target)a.setAttribute('target','_blank');//Если target не установлен
			continue;
		}
		
		if(typeof(a.onclick)==='function'){
			var old_func=a.onclick;
		}else{
			var old_func=function(){};
		}
		
		
		if(isfirst)a.setAttribute('weblife_href',href);//Признак того что эта ссылка внутренняя и веблайфная... так сохраняется первоначальный адрес
		if(parsed.ind[0]=='&'||parsed.ind[1]=='&') a.setAttribute('weblife_refresh','true');//Признак того что эта ссылка внутренняя и веблайфная... 
		
		var state=this.getState(parsed.state);
		var obj=state.getRight(parsed);
		href=this.parser.getQuery(obj);
		//if(href&&!history.pushState)href=encodeURI(href); Проблемы в node ie передаёт не закодированный адрес и сервер не видит этого
		if(href)href=decodeURI(href);//Нужно для печати чтобы ссылки были без процентов
		var sethref=href?('?'+href):('http://'+view.getPath());
		a.setAttribute('href',sethref);//Если параметров нет то указывам путь на главную страницу

		if(isfirst){	
			a.onclick=function(old_func,a){
				return function(){
					if(typeof(event)!=='undefined'&&event.returnValue===false)return false;
					var re=old_func.bind(a)();
					if(re===false){
						if(typeof(event)!=='undefined')event.returnValue=false;
						return false;
					}
					var nohref=a.getAttribute('nohref');
					if(nohref)return false;

					if(/\?/.test(a.href)){
						//var param=a.href.substring(a.href.indexOf('?')+1);
						var h=a.href;
						var h=decodeURI(h);
						var ar=h.split('?');
						ar.shift();
						var param=ar.join('?');
					}else{
						var param='';
					}
					if(!/^=/.test(param)){
						param='/'+param;
					}else{
						param=''+param;
					}
					try{
						infra.State.set(param);
					}catch(e){
						console.log(e);
					}
					if(typeof(event)!=='undefined')event.returnValue=false;
					return false;
				}
			}(old_func,a);
		}
	}
	 */
}
/*infra.State.parser.test=function(parsed){
	var p='';
	if(typeof(parsed.param)=='object'){
		for(var i in parsed.param){
			p+='\n\t'+i+':'+parsed.param[i];
		}
	}else{
		p+=parsed.param;
	}
	alert('param:'+p+'\nstate:'+parsed.state+'\nind:'+parsed.ind);
}*/






/*infra.State.prototype={
}

/**/
?>
