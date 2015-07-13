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
	//namespace itlife\infrajs\infra;

//Скрипт не должен управлять этими опциями
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', 1);
	


	
/*
 игнор цифр, и расширения infra/infra
*/

require_once(__DIR__.'/../infra/ext/config.php');

require_once(__DIR__.'/../infra/ext/load.php');





//Продакшин должен быть таким же как и тестовый сервер, в том числе и с выводом ошибок. Это упрощает поддержку. Меньше различий в ошибках.
//ini_set('error_reporting',E_ALL ^ E_STRICT ^ E_NOTICE);
//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
//Strict Standards: Only variables should be assigned by reference
//Notice: Only variable references should be returned by reference
//Notice: Undefined index: 
//ini_set('display_errors',1);


infra_require('*infra/ext/admin.php');


infra_require('*infra/ext/cache.php');


$conf=infra_config();

if ($conf['debug']) {
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

infra_install();

itlife\infrajs\infra\ext\crumb::init();
