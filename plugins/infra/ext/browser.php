<?php
/*
Copyright 2008-2010 ITLife, Ltd. http://itlife-studio.ru

Функции
	infra_toutf - В utf
	infra_tofs - В кодировку файловой системы
	infra_tojs - в строку json
	infra_tophp- в объект php

	infra_browser - возвращает строку характеризующую браузер
	infra_url - синхронный кроссдоменный get запрос работающий на хостингах с ограничением file_get_contents

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


global $infra_config;
$infra_config=array();
function &infra_config($sec=false){
	$sec=$sec?'secure':'unsec';
	global $infra_config;
	if(isset($infra_config[$sec]))return $infra_config[$sec];
	if(!is_file(ROOT.'infra/data/.config.json')){
		die('<h1>Вам нужно создать файл infra/data/.config.json</h1>{"admin":{"login":"логин","password":"секрет","email":"admin@email.ru"}}');
		$data=array();
	}
	$atime=infra_admin_time();
	/*
	 Когда изменяется debug с 0 на 1 нужно последний раз авторизоватсья чтобы это изменение применилось... и потом так как debug 1 уже все изменения пройдут без авторизаций. Всё и так считывается каждый раз.
	 */
	if(!$atime||!$data||$data['debug']||$data['time']<$atime){//Если была новая авторизация конфиг считываем снова
		$src='infra/plugins/';
		$list=scandir(ROOT.$src);
		foreach($list as $name){
			if($name[0]=='.')continue;
			if(!is_dir(ROOT.$src.$name))continue;
			if(!is_file(ROOT.$src.$name.'/.config.json'))continue;

			$d=file_get_contents(ROOT.$src.$name.'/.config.json');
			$d=infra_tophp($d);
			if(is_array($d))foreach($d as $k=>$v) {
				if(!is_array($data[$k]))$data[$k]=array();
				if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
				else $data[$k]=$v;
			}
		}
		if(is_file(ROOT.'infra/layers/.config.json')){
			$d=file_get_contents(ROOT.'infra/layers/.config.json');
			$d=infra_tophp($d);
			if(is_array($d))foreach($d as $k=>$v) {
				if(!is_array($data[$k]))$data[$k]=array();
				if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
				else $data[$k]=$v;
			}
		}

		$d=file_get_contents(ROOT.'infra/data/.config.json');
		$d=infra_tophp($d);
		if(is_array($d))foreach($d as $k=>$v) {
			if(!is_array($data[$k]))$data[$k]=array();
			if(is_array($v)) foreach($v as $kk=>$vv)$data[$k][$kk]=$vv;
			else $data[$k]=$v;
		}
		/*
		if(!$data['http'])$data['http']=array();
		if(!$data['http']['sitehost'])$data['http']['sitehost']=$_SERVER['HTTP_HOST'];
		if(!$data['http']['siteroot'])$data['http']['siteroot']='';//Абсолютный путь до папки корня системы от корня домена. На сервере это никак не используется. или для web запросов самому к себе 'svn/x5service/'
		*/
		
		//$data['time']=$data['debug']?0:$atime;
		$data['time']=$atime;//Таже секунда не подойдёт не обновится. для обновления конфига авторизация должна пройти после изменения хотябы на секунду
		//infra_mem_set('infra_config',$data);
	}
	$infra_config['unsec']=$data;
	foreach($data as $i=>$part){
		$pub=$part['pub'];
		if(is_array($pub)){
			foreach($part as $name=>$val){
				if(!in_array($name,$pub)){
					unset($data[$i][$name]);
				}
			}
		}else{
			unset($data[$i]);
		}
	}
	//unset($data['admin']);
	//unset($data['mysql']);
	$infra_config['secure']=$data;
	return $infra_config[$sec];
}





function infra_hash($args){
	$a=array();
	foreach($args as $k=>$v){
		if(is_callable($v))$a[$k]='func!';
		else if(is_array($v))$a[$k]=infra_hash($v);
		else $a[$k]=$v;
	}
	return md5(serialize($a));
}
global $infra_once;
$infra_once=array();

function infra_once($name,$call,$args=array(),$re=false){
	global $infra_once;

	$strargs=infra_hash($args);
	$name=$name.$strargs;

	if($infra_once[$name]&&!$re)return $infra_once[$name]['result'];
	$infra_once[$name]=array('exec'=>true);
	
	$v=array_merge($args,array($re));
	
	$v=call_user_func_array($call,$v);
	
	$infra_once[$name]['result']=$v;
	return $infra_once[$name]['result'];
}


global $ibrowser;
$ibrowser=array();
function infra_browser($agent=false){
	global $ibrowser;
	if(!$agent)$agent=$_SERVER['HTTP_USER_AGENT'];
	$agent=strtolower($agent);
	if(isset($ibrowser[$agent]))return $ibrowser[$agent];
	
	if (preg_match('/msie (\d)/', $agent,$matches)) {
		$name = 'ie ie'.$matches[1];
	}elseif (preg_match('/opera/', $agent)) {
		$name = 'opera';
		if(preg_match('/opera\/9/', $agent)) {
			$name.=' opera9';
		}else if(preg_match('/opera (\d)/', $agent,$matches)){
			$name.=' opera'.$mathces[1];
		}
		if(preg_match('/opera\smini/', $agent)) {
			$name.=' opera_mini';
		}
	}elseif (preg_match('/gecko\//', $agent)){
		$name='gecko';
		if (preg_match('/firefox/', $agent)){
			$name .= ' ff';
			if (preg_match('/firefox\/2/', $agent)){
				$name .= ' ff2';
			}elseif (preg_match('/firefox\/3/', $agent)){
				$name .= ' ff3';
			}
		}
	}elseif (preg_match('/webkit/', $agent)) {
		$name = 'webkit';
		if (preg_match('/chrome/', $agent)) {
			$name .= ' chrome';
		}else{
			$name .= ' safari';
		}
	}elseif (preg_match('/konqueror/', $agent)) {
		$name='konqueror';
	}elseif (preg_match('/flock/', $agent)) {
		$name='flock';
	}else{
		$name='stranger';
	}
	if (!preg_match('/ie/', $name)){
		$name.=' noie';
	}
	if (preg_match('/linux|x11/', $agent)) {
	   $name.=' linux';
	}elseif (preg_match('/macintosh|mac os x/', $agent)) {
	    $name.=' mac';
	}elseif (preg_match('/windows|win32/', $agent)) {
	    $name.=' win';
	}
	if(preg_match('/stranger/',$name)){
		$name='';
	}
	$ibrowser[$agent]=$name;
	return $name;
}


function infra_isAssoc(&$array){//(c) Kohana http://habrahabr.ru/qa/7689/
	if(!is_array($array))return null;
	$keys = array_keys($array);
	return array_keys($keys) !== $keys;
}
function infra_isEqual(&$a, &$b){
    $t = $a;
    if($r=($b===($a=1))){ $r = ($b===($a=0)); }
    $a = $t;
    return $r;
}

function infra_tophp($d,$slow=false){
	if(!$slow){
		$d=trim($d,')(');
		$d=preg_replace("/[\r\n\t]/","",$d);//Если будут эти символы падаем почему-то	
		$data=json_decode($d,true);
		if($data||is_array($data)){
			return $data;
		}else{
			$slow=true;
		}
	}
	if($slow){
		require_once(ROOT.'infra/plugins/infra/JSON.php');
		$ser=new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$res=$ser->decode($d);
		return $res;
	}
}

global $iu2r; 
$iu2r = array (
'\u0430' => 'а', '\u0410' => 'А',
'\u0431' => 'б', '\u0411' => 'Б',
'\u0432' => 'в', '\u0412' => 'В',
'\u0433' => 'г', '\u0413' => 'Г',
'\u0434' => 'д', '\u0414' => 'Д',
'\u0435' => 'е', '\u0415' => 'Е',
'\u0451' => 'ё', '\u0401' => 'Ё',
'\u0436' => 'ж', '\u0416' => 'Ж',
'\u0437' => 'з', '\u0417' => 'З',
'\u0438' => 'и', '\u0418' => 'И',
'\u0439' => 'й', '\u0419' => 'Й',
'\u043a' => 'к', '\u041a' => 'К',
'\u043b' => 'л', '\u041b' => 'Л',
'\u043c' => 'м', '\u041c' => 'М',
'\u043d' => 'н', '\u041d' => 'Н',
'\u043e' => 'о', '\u041e' => 'О',
'\u043f' => 'п', '\u041f' => 'П',
'\u0440' => 'р', '\u0420' => 'Р',
'\u0441' => 'с', '\u0421' => 'С',
'\u0442' => 'т', '\u0422' => 'Т',
'\u0443' => 'у', '\u0423' => 'У',
'\u0444' => 'ф', '\u0424' => 'Ф',
'\u0445' => 'х', '\u0425' => 'Х',
'\u0446' => 'ц', '\u0426' => 'Ц',
'\u0447' => 'ч', '\u0427' => 'Ч',
'\u0448' => 'ш', '\u0428' => 'Ш',
'\u0449' => 'щ', '\u0429' => 'Щ',
'\u044a' => 'ъ', '\u042a' => 'Ъ',
'\u044b' => 'ы', '\u042b' => 'Ы',
'\u044c' => 'ь', '\u042c' => 'Ь',
'\u044d' => 'э', '\u042d' => 'Э',
'\u044e' => 'ю', '\u042e' => 'Ю',
'\u044f' => 'я', '\u042f' => 'Я',
);

function infra_json($data){
	require_once(ROOT.'infra/plugins/infra/JSON.php');
	$ser=new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

	$res=$ser->decode('{asdf:1}');
	print_r($res);
	
	$obj=(object)null;
	$obj->asdf=2;

	$res=$ser->encode($obj);
	print_r($res);
}
function infra_tojs($data,$head=false){
	global $iu2r;

	require_once(ROOT.'infra/plugins/infra/JSON.php');
	$ser=new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	$data=$ser->encode($data);

	/*$data=json_encode($data);*/
	/*if($head==='header'){//Форма контактов использует
		//if(!$_SERVER['HTTP_X_REQUESTED_WITH'])return;
		@header('Content-type: application/javascript; charset=UTF-8');
	}*/
	$data = strtr($data,$iu2r);
	return $data;
}
function infra_echo($ans=array(),$msg=false,$res=null,$msgdeb=null){//Окончание скриптов
	if($msg!==false){
		$ans['msg']=$msg;
	}
	if(!is_null($res)){
		$ans['result']=$res;
	}
	if(!is_null($msgdeb)){
		$conf=infra_config();
		if($conf['debug']){
			if(!is_string($msgdeb))$msgdeb=print_r($msgdeb,true);
			$ans['msg']=$msg.'. '.$msgdeb;
		}
	}
	global $FROM_PHP;
	if(!$FROM_PHP){
		@header('Content-type:text/plain');//Ответ формы не должен изменяться браузером чтобы корректно конвертирвоаться в объект js
		echo infra_tojs($ans);
	}
	return $ans;
}
?>