<?php
// Copyright http://itlife-studio.ru
/*
	infra_tofs - В кодировку файловой системы
	infra_toutf- в объект php
	infra_json_decode(string)
	infra_json_encode(obj)
	infra_require
	infra_theme
	infra_srcinfo
	infra_nameinfo
	infra_load
	infra_loadTEXT
	infra_loadJSON
*/
@define('ROOT','../../../../');
global $infra_fscp1251,$infra_fsruspath;
$infra_fscp1251=NULL;
$infra_fsruspath='infra/plugins/infra/Тест русского.языка';


function infra_tofs($name){
	global $infra_fscp1251,$infra_fsruspath;
	$name=infra_toutf($name);
	if($infra_fscp1251===NULL){
		if(is_file(ROOT.$infra_fsruspath)){
			$infra_fscp1251=false;
		}else if(is_file(ROOT.iconv('UTF-8','CP1251',$infra_fsruspath))){
			$infra_fscp1251=true;
		}else{
			echo '<h1>Проблемы с кодировкой!</h1>'.'<p>Файл <a href="'.$infra_fsruspath.'">'.$infra_fsruspath.'</a> Должен быть доступен</p>';
			exit;
		}
	}
	if($infra_fscp1251){
		$name=iconv('UTF-8','CP1251',$name);
	}
	return $name;
}
function infra_toutf($str){
	if(!is_string($str))return $str;
	if(preg_match('//u', $str)){
		return $str;
	}else{
		if(function_exists('mb_convert_encoding')){
			return mb_convert_encoding($str, 'UTF-8', 'CP1251');
		}else{
			return iconv('CP1251','UTF-8',$str);//Некоторые строки обрубаются на каком-то месте... замечено в mht
		}
	}
}
function infra_strtolower($str){

	if(!is_string($str))return $str;

	if(preg_match('//u', $str)){
		$r=false;
	}else{
		$r=true;

		if(function_exists('mb_convert_encoding')){
			$str=mb_convert_encoding($str, 'UTF-8', 'CP1251');
		}else{
			$str=iconv('CP1251','UTF-8',$str);//Некоторые строки обрубаются на каком-то месте... замечено в mht
		}
	}
	$str=mb_strtolower($str,'UTF-8');
	if($r)$str=infra_tofs($str);
	return $str;
}

function infra_json_decode($json){
	$json2 = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
	$data = json_decode($json2, true, 512);//JSON_BIGINT_AS_STRING в javascript тоже нельзя такие цифры... архитектурная ошибка.
	if($json2&&!$data&&!in_array($json2,array('false','{}','[]','""','0'))){
		echo '<h1>json decode error</h1>';
		echo "\n".'<pre>'."\n";
		var_dump($json);
		echo "\n".'</pre>';
		exit;
	}
	/*
	// the following strings are valid JavaScript but not valid JSON

	// the name and value must be enclosed in double quotes
	// single quotes are not valid 
	$bad_json = "{ 'bar': 'baz' }";
	json_decode($bad_json); // null

	// the name must be enclosed in double quotes
	$bad_json = '{ bar: "baz" }';
	json_decode($bad_json); // null

	// trailing commas are not allowed
	$bad_json = '{ bar: "baz", }';
	json_decode($bad_json); // null
	*/
    return $data;
}
function infra_json_encode($mix){
	$v=phpversion();
	$ver=explode('.',$v);
	if($ver[0]<5||($ver[0]==5&&$ver[1]<4)) return json_encode($mix);//для 5.2.2 и всего что старее 5.2.4
	else return json_encode($mix,JSON_UNESCAPED_UNICODE);
}


function infra_unload($path){//{status:200,value:''};
	$s=&infra_storeLoad('require');
	unset($s[$path]);
	$s=&infra_storeLoad('loadJSON');
	unset($s[$path]);
	$s=&infra_storeLoad('load');
	unset($s[$path]);
	$s=&infra_storeLoad('loadTEXT');
	unset($s[$path]);
}

function &infra_storeLoad($name){
	global $infra_load_store;
	if(!$infra_load_store)$infra_load_store=array();
	if(!$name)return $infra_load_store;
	if(!isset($infra_load_store[$name]))$infra_load_store[$name]=array();
	return $infra_load_store[$name];
}

function infra_require($path){
	$store=&infra_storeLoad('require');
	if(isset($store[$path]))return $store[$path]['value'];
	
	$store[$path]=array('value'=>true);//Метку надо ставить заранее чтобы небыло зацикливаний
	$rpath=infra_theme($path);
	if(!$rpath)die('infra_require - не найден путь '.$path);
	require(ROOT.$rpath);

}
function _infra_src($orig){//Возвращает список адресов которые нужно проверить
	$store=infra_storeLoad('_infra_src');
	//Возвращает последний слэш так как было передано... если небыло то и не будет
	if(isset($store[$orig]))return $store[$orig];

	$ans=array();
	$ans['orig']=$orig;
	$ans['query']='';

	$src=$orig;
	
	if(preg_match('/^(.*)(\?.*)$/',$src,$match)){
		$src=$match[1];
		$ans['query']=$match[2];
	}else{
		$ans['query']='';
	}
	$ans['path']=$src;//Есть всегда

	$ans['secure']=preg_match('/\/\./',$src);
	if(!$ans['secure'])$ans['secure']=preg_match('/^\./',$src);
	if(!$ans['secure'])$ans['secure']=preg_match('/\*\./',$src);

	$ans['isfolder']=preg_match('/\/+$/',$src);

	
	$ans['isstar']=preg_match('/\*/',$src);
	if(!$ans['isstar']){
		$ans['paths']=array($src);
		$ans['path']=$src;
		$ans['find']=preg_replace('/^infra\/data\//','*',$src);
	}else{
		$ans['paths']=array();

		if($ans['isfolder'])$src=preg_replace('/\/+$/','',$src);//Удалили последний слэш, временно
		$src=preg_replace('/^\*/','',$src);
		$p=explode('/',$src);
		$file=array_pop($p);//Не может быть пустой строкой
		$plugin=array_shift($p);//Может быть пустой строкой
		if($plugin)$plugin.='/';
		$folder=implode('/',$p);//Путь от плагина до файла
		if($folder)$folder.='/';
		$file=$folder.$file;//Есть обязательно
		if($ans['isfolder'])$file=$file.'/';

		$ans['path']='infra/data/'.$plugin.$file;
		$ans['paths'][]='infra/data/'.$plugin.$file;
		$ans['paths'][]='infra/layers/'.$plugin.$file;
		$ans['paths'][]='infra/plugins/'.$plugin.$file;
		$ans['find']='*'.$plugin.$file;//Этот путь можно вывести со звёздочкой и по нему опять будет файл найден при необходимости.		
	}
	if($orig=='*')$ans['isfolder']=true;
	return $ans;
}
function infra_srcinfo($src){

	$p=explode('?',$src);
	$file=array_shift($p);
	if($p)$query='?'.implode('?',$p);
	else $query='';

	$p=explode('/',$file);
	$file=array_pop($p);

	if(sizeof($p)==0&&preg_match("/^\*/",$file)){
		$file=preg_replace("/^\*/",'',$file);
		$p[]='*';
	}
	$folder=implode('/',$p);
	if($folder)$folder.='/';
	
	



	$fdata=infra_nameinfo($file);

	$fdata['query']=$query;

	$fdata['src']=$src;
	$fdata['path']=$folder.$file;
	$fdata['folder']=$folder;
	return $fdata;
}
function infra_nameinfo($file){//Имя файла без папок// Звёздочки быть не может
	$p=explode('.',$file);
	if(sizeof($p)>1){
		$ext=array_pop($p);
		$name=implode('.',$p);
		if(!$name){
			$name=$file;
			$ext='';
		}
	}else{
		$ext='';
		$name=$file;
	}
	preg_match("/^(\d{6})[\s\.]/",$name,$match);
	$date=@$match[1];
	$name=preg_replace("/^\d+[\s\.]/",'',$name);
	$ar=explode("@",$name);
	$id=false;
	if(sizeof($ar)>1){
		$id=array_pop($ar);
		if(!$id)$id=0;
		$idi=(int)$id;
		$idi=(string)$idi;//12 = '12 asdf' а если и то и то строка '12'!='12 asdf'
		if($id==$idi){
			$name=implode('@',$ar);
		}else{
			$id=false;
		}
	}
	$ans=array(
		'id'=>$id,
		'name'=>trim($name),
		'file'=>$file,
		'date'=>$date,
		'ext'=>$ext
	);
	return $ans;
}

function _infra_sortfile($src1,$setd){//starpath infra/data
	$p=explode('/',$src1);
	//$srcext=preg_match('/\.\w{0,4}$/',$src1);//Расширение при поиске не учитываем
	//$src='infra/data';
	$src='';
	//$src=preg_replace('/\/$/','',$starpath);
	

	for($i=0,$l=sizeof($p);$i<$l;$i++){
		$name=$p[$i];//Критерий поиска id,name,полное имя файла
		$last=($i==$l-1);
		if(!$name)continue;
		
		
		if($i)$src.='/';	
		if(
			((!$last||$setd)&&is_dir(ROOT.$src.$name))
			||($last&&!$setd&&is_file(ROOT.$src.$name))
		){
			$src.=$name;	
			continue;
		}
		$namer=infra_strtolower($name);
	
		$res=false;
		if (is_dir(ROOT.$src)&&$dh = opendir(ROOT.$src)) {		
			while (($file = readdir($dh)) !== false) {
				if($file=='..'||$file=='.')continue;
				if((!$setd&&$last&&is_file(ROOT.$src.'/'.$file))||(($setd||!$last)&&is_dir(ROOT.$src.'/'.$file))){

					$r=infra_nameinfo($file);
					if($namer==$r['id']
						||$namer==infra_strtolower($r['name'])
						||$namer==infra_strtolower($r['name']).'.'.infra_strtolower($r['ext'])//Это надо чтобы определённое расширение взялось
						||$namer==infra_strtolower($r['file'])){
							$src.=$file;
							$res=true;
							break;
					}
				}
			}
			closedir($dh);
		}
		if(!$res)return false;
	}

	if($setd) $src.='/';
	return $src;
}
function _infra_theme($src){
//Функция возвращает корректный путь до файла в нужной теме, без *. путь начинается после адреса ROOT
	//Если путь содержит * но файл не найден возвращается false иначе если файла нет возвращается false или переданный $src
	//Путь возвращается в кодировкe файловой системы.

	//d,f,s,h,u,n
	//$setp=false;
	//$infra_src_cache[$orig]=$ans;
	$psrc=_infra_src($src);

	$path=$psrc['path'];
	$query=$psrc['query'];
	
	//if($psrc['secure']&&(!$sets&&!infra_admin()))return false;
	//if($psrc['secure']&&!$sets)return false;
	
	//Самая быстрая проверка
	if(!$psrc['isfolder']&&is_file(ROOT.$path))return $path.$query;
	if($psrc['isfolder']&&is_dir(ROOT.$path))return $path.$query;

	


	
	foreach($psrc['paths'] as $path){
		if(!$psrc['isfolder']&&is_file(ROOT.$path))return $path.$query;
		if($psrc['isfolder']&&is_dir(ROOT.$path))return $path.$query;
	}
	if($psrc['find']){//find это значит infra/data подойдёт потому что ищим только там
		foreach($psrc['paths'] as $path){

			$path=_infra_sortfile($path,$psrc['isfolder']);

			if($path){
				if(!$psrc['isfolder']&&is_file(ROOT.$path))return $path.$query;
				if($psrc['isfolder']&&is_dir(ROOT.$path))return $path.$query;//Если path это папка слэш у неё обязан уже быть
				//if($path)return false;
			}
		}
	}
	
	//Бежим по всем возможным местам расположения файла... по темам, плагинам и тп...
	
	return false;
}
function infra_theme($src){
	$src=infra_tofs($src);
	$res=_infra_theme($src);
	return $res; 
}

function &infra_loadJSON($path){
	$store=infra_storeLoad('loadJSON');
	global $infra;
	if(isset($store[$path])){	
		if($store[$path]['com'])$infra['com']=$store[$path]['com'];
		return $store[$path]['value'];
	}
	$store[$path]=array();
	$text=infra__load($path);
	$store[$path]['com']=infra_load_com();
	
	/*JSON_FORCE_OBJECT
	JSON_UNESCAPED_SLASHES
	JSON_UNESCAPED_UNICODE
	//json_encode*/
	if(is_string($text)){
		$store[$path]['value']=infra_json_decode($text);
	}else{
		$store[$path]['value']=$text;
	}
	$store[$path]['status']=true;

	if($store[$path]['com'])$infra['com']=$store[$path]['com'];
	return $store[$path]['value'];
}
function infra_load_com(){
	$heads=headers_list();
	$headers=array();
	foreach($heads as $v){
		$v=explode(':',$v,2);
		$headers[$v[0]]=$v[1];
	}
	if(isset($headers['infra-com'])){
		$com=infra_json_decode($headers['infra-com']);
	}else{
		$com=false;
	}
	return $com;
}
function &infra_loadTEXT($path){
	$store=infra_storeLoad('loadTEXT');
	global $infra;
	if(isset($store[$path])){	
		if($store[$path]['com'])$infra['com']=$store[$path]['com'];
		return $store[$path]['value'];
	}
	$store[$path]=array();
	
	$text=infra__load($path);
	$store[$path]['com']=infra_load_com();

	/*JSON_FORCE_OBJECT
	JSON_UNESCAPED_SLASHES
	JSON_UNESCAPED_UNICODE
	//json_encode*/
	if(is_null($text))$text='';
		
	if(!is_string($text)){
		$store[$path]['value']=infra_json_encode($text);
	}else{
		$store[$path]['value']=$text;
	}
	$store[$path]['status']=true;

	if($store[$path]['com'])$infra['com']=$store[$path]['com'];
	return $store[$path]['value'];
}
function infra__load($path){
	$store=infra_storeLoad('load');
	if(isset($store[$path]))return $store[$path]['value'];
	//php файлы эмитация веб запроса 
	//всё остальное file_get_content

	$load_path=infra_theme($path);
	$fdata=infra_srcinfo($load_path);
	if($load_path){

		$plug=infra_theme($fdata['path']);
		if($fdata['ext']=='php'){
			//$r=preg_split('/\?/',$plugin,2);
			$getstr=infra_toutf($fdata['query']);//get параметры в utf8, с вопросом
			$getstr=preg_replace("/^\?/","",$getstr);
			parse_str($getstr,$get);
			if(!$get)$get=array();
			

			/*if (get_magic_quotes_gpc()){
				$get = array_map('stripslashes_deep', $get);
			}*/
			
			//foreach($GLOBALS as $k=>&$v){
			//	if($k[0]=='_'||$k=='GLOBALS')continue;
			//	global $$k;
			//}
			//unset($v);
		


			$GET=$_GET;
			$_GET=$get;

			//$POST=$_POST;
			//$_POST=array();
			
			$REQUEST=$_REQUEST;
			$_REQUEST=array_merge($_GET, $_POST, $_COOKIE);

			$SERVER_QUERY_STRING=$_SERVER['QUERY_STRING'];
			$_SERVER['QUERY_STRING']=$getstr;
			global $FROM_PHP;
			$FROM_PHP_OLD=$FROM_PHP;
			$FROM_PHP=true;

			ob_start();
			//headers надо ловить
			$rrr=include(ROOT.$plug);
			$result=ob_get_contents();
			$resecho=$result;
			ob_end_clean();
			$FROM_PHP=$FROM_PHP_OLD;

			//if($rrr&&$rrr!==1&&!is_string($rrr)){//в include небыло return.. он просто выполнился и всё
			if($rrr!==1&&!is_null($rrr)){//в include небыло return.. он просто выполнился и всё
				$result=$rrr;
				//if(!$setj) $result=infra_json_encode($result);
				if($resecho){
					$result=$resecho.infra_json_encode($result);
				}
			}else{
				//if($setj) $result=infra_tophp($result);
			}
			
			$_SERVER['QUERY_STRING']=$SERVER_QUERY_STRING;
			$_REQUEST=&$REQUEST;
			$_GET=&$GET;
			$data=$result;
			
			//$data='php file';
		}else{
			$data=file_get_contents(ROOT.$plug);
		}
		$store['status']=200;
		$store['value']=$data;
	}else{
		$data='';
		$store['status']=404;
		$store['value']='';
	}
	return $data;
}
/*
//Мультизагрузка нет, используется script.php


//Что такое store
//store пошёл из node где при каждом запросе страницы этот store очищался. и хранился для каждого пользователя в отдельности. 
//store нужен чтобы синтаксис в javascript и в php был одинаковый без global
//Без store нужно заводить переменную перед функцией, в нутри функции забирать её из global, придумывать не конфликтующие имена
//всё что хранится в store не хранится в localStorage
//store не специфицируется... если надо отдельно в объекте заводится...

//Много вещей отличающих node ещё и fibers

//Личный кабинет, авторизация пользователя?

//user.php (no-cache) заголовок getResponseHeader('no-cache')
//Опция global для обновления связанных файлов

//require('no-cache') не сохраняется в localStorage??
//require('no-cache') не сохраняется в localStorage



*/
function infra_echo($ans=array(),$msg=false,$res=null){//Окончание скриптов
	if(!is_string($html)&&$msg!==false){
		$ans['msg']=$msg;
	}
	if(!is_null($res)){
		$ans['result']=$res;
	}
	global $FROM_PHP;
	if(!$FROM_PHP){
		@header('Content-type:text/plain');//Ответ формы не должен изменяться браузером чтобы корректно конвертирвоаться в объект js, если html то ответ меняется
		echo infra_json_encode($ans);
	}
	return $ans;
}
?>