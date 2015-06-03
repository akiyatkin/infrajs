<?php
/*
Copyright 2008-2013 ITLife, Ltd. http://itlife-studio.ru

infrajs.php Общий инклуд для всех скриптов



----------- functions.php Библиотека ---------------
infra_toutf - перевести строку в кодировку UTF8 если строка ещё не в кодировке UTF8
infra_toFS- перевести строку в кодировку файловой системы
infra_tojs - объект php в строку json
infra_fromjs - строка json в объект php
infra_getBrowser - строка ie ie6 gecko safari opera и тп...
infra_getUrl - синхронный кроссдоменный get запрос работающий на хостингах с ограничением file_get_contents


----------- plugins.php Плагины --------------
infra_plugin - Подключает функции какого-то плагина, возвращает вывод плагина в браузер, или если плагином предусмотренно возвращает объект php ответ плагина
infra_theme - (*some/path/to/file) возвращает пусть от корня сайта до файла согласно системе плагинов

----------- cache.php Плагины --------------
infra_cache - ($conds,$fn,$args); conds - файлы или метки.

----------- login.php Авторизация ---------------
infra_admin(true);//bool, //если true, выкидывает окно авторизации если не авторизирован


----------- не реализовано --------------
modified - будет как-нибудь
state - серверная обработка адреса сайта
?1/12/213 - это корректная ссылка... но вот куда она ведёт.. должен быть редирект чтобы поисковики понимали что это ?Форум/Имя Раздела/Имя Темы
?openid, session - генерируемые при переходах по ссылкам get параметры
statist - интегрировать как-нибудь

*/



	@define('ROOT','../../../');
	
	ini_set('allow_call_time_reference', true);//http://forum.dklab.ru/viewtopic.php?t=19975 Ошибка Deprecated: Call-time pass-by-reference has been deprecated в PHP 5
	if(!is_dir(ROOT.'infra/'))die('ROOT выставлен неправильно');

	if(ini_get('register_globals')&&ini_get('register_globals')!='Off')die('<h1>Вам нужно установить register_globals Off</h1>');

	if(!is_dir(ROOT.'infra/data/')){
		mkdir(ROOT.'infra/data/',0755);//Создаём если нет папку infra/cache
		if(!is_dir(ROOT.'infra/data/')){
			die('Не удалось создать папку infra/data/, прав нехватает наверно :(');
		}
	}
	
	$v=phpversion();
	$ver=explode('.',$v);
	if($ver[0]<5||($ver[0]==5&&$ver[1]<4))die('Требуется более новая версия php от 5.3 сейчас '.$v);
	/*
		5.4 - json_encode($data,JSON_UNESCAPED_UNICODE);
		5.3 - используются анонимные функции
		5.3 - не всегда ставится закрывающие тег php
	*/
	


	//error_reporting(E_ALL & ~E_NOTICE);
	if(function_exists('mb_internal_encoding')){
		mb_internal_encoding('UTF-8');//ХЗ зачем очень давно появилось...
	}



	//Убираем магически появляющийся ниоткуда кавычки
	if (get_magic_quotes_gpc()) {
		die('get_magic_quotes_gpc() должны быть отключены');
		/*if(!function_exists('stripslashes_deep')){
			function stripslashes_deep($value){
				$value = is_array($value)?array_map('stripslashes_deep',$value):stripslashes($value);
				return $value;
			}
		}
	    $_POST = array_map('stripslashes_deep', $_POST);
	    $_GET = array_map('stripslashes_deep', $_GET);
	    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
	    */
	}

/*

*/

require_once(ROOT.'infra/plugins/infra/ext/load.php');
$_SERVER['QUERY_STRING']=infra_toutf($_SERVER['QUERY_STRING']);




infra_require('*infra/ext/config.php');
//Продакшин должен быть таким же как и тестовый сервер, в том числе и с выводом ошибок. Это упрощает поддержку. Меньше различий в ошибках.
//ini_set('error_reporting',E_ALL ^ E_STRICT ^ E_NOTICE);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
//Strict Standards: Only variables should be assigned by reference
//Notice: Only variable references should be returned by reference
//Notice: Undefined index: 
ini_set('display_errors',1);



infra_require('*infra/ext/admin.php');
infra_require('*infra/ext/cache.php');

$conf=infra_config();
if($conf['debug']){
	@header('Infrajs-Debug:true');
	infra_cache_no();
}

infra_require('*infra/ext/once.php');



infra_require('*infra/ext/mail.php');
infra_require('*infra/ext/forr.php');


infra_require('*infra/ext/mem.php');
infra_require('*infra/ext/events.php');
infra_require('*infra/ext/connect.php');
infra_require('*infra/ext/view.php');



infra_require('*infra/ext/seq.php');
infra_require('*infra/ext/template.php');
infra_require('*infra/ext/state.php');
?>