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

/*function infra_json($data){
	require_once(ROOT.'infra/plugins/infra/JSON.php');
	$ser=new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

	$res=$ser->decode('{asdf:1}');
	print_r($res);
	
	$obj=(object)null;
	$obj->asdf=2;

	$res=$ser->encode($obj);
	print_r($res);
}*/
function infra_tojs($data){
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