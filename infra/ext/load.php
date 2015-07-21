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


function infra_toutf($str)
{
	if (!is_string($str)) {
		return $str;
	}
	if (preg_match('//u', $str)) {
		return $str;
	} else {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($str, 'UTF-8', 'CP1251');
		} else {
			return iconv('CP1251', 'UTF-8', $str);//Некоторые строки обрубаются на каком-то месте... замечено в mht
		}
	}
}
function infra_strtolower($str)
{
	if (!is_string($str)) {
		return $str;
	}

	if (preg_match('//u', $str)) {
		$r = false;
	} else {
		$r = true;

		if (function_exists('mb_convert_encoding')) {
			$str = mb_convert_encoding($str, 'UTF-8', 'CP1251');
		} else {
			$str = iconv('CP1251', 'UTF-8', $str);//Некоторые строки обрубаются на каком-то месте... замечено в mht
		}
	}
	$str = mb_strtolower($str, 'UTF-8');
	if ($r) {
		$str = infra_tofs($str);
	}

	return $str;
}

function infra_json_decode($json, $soft = false)
{
	//soft если об ошибке не нужно сообщать
	$json2 = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
	$data = json_decode($json2, true, 512);//JSON_BIGINT_AS_STRING в javascript тоже нельзя такие цифры... архитектурная ошибка.
	if (!$soft && $json2 && is_null($data) && !in_array($json2, array('null'))) {
		echo '<h1>json decode error</h1>';
		echo "\n".'<pre>'."\n";
		var_dump($json);
		var_dump($data);
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
function infra_json_encode($mix, $pretty = true)
{
	return json_encode($mix, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function infra_unload($path)
{
	//{status:200,value:''};
	$s = &infra_storeLoad('require');
	unset($s[$path]);
	$s = &infra_storeLoad('loadJSON');
	unset($s[$path]);
	$s = &infra_storeLoad('load');
	unset($s[$path]);
	$s = &infra_storeLoad('loadTEXT');
	unset($s[$path]);
}

function &infra_storeLoad($name)
{
	global $infra_load_store;
	if (!$infra_load_store) {
		$infra_load_store = array();
	}
	if (!$name) {
		return $infra_load_store;
	}
	if (!isset($infra_load_store[$name])) {
		$infra_load_store[$name] = array();
	}

	return $infra_load_store[$name];
}

function infra_require($path)
{
	$store = &infra_storeLoad('require');
	if (isset($store[$path])) {
		return $store[$path]['value'];
	}
	$store[$path] = array('value' => true);//Метку надо ставить заранее чтобы небыло зацикливаний
	$rpath = infra_theme($path);
	if (!$rpath) {
		throw new Exception('infra_require - не найден путь '.$path);
	}
	require_once $rpath;//Просто require позволяет загрузить самого себя. А мы текущий скрипт не добавляем в список подключённых
}
function infra_forFS($str)
{
	//Начинаться и заканчиваться пробелом не может
	//два пробела не могут идти подряд
	//символов ' " /\#&?$ быть не может удаляются som e будет some
	//& этого символа нет, значит не может быть htmlentities
	//символов <> удаляются из-за безопасности
	//Виндовс запрещает символы в именах файлов  \/:*?"<>|
	//% приводит к ошибке malfomed URI при попадании в адрес так как там используется decodeURI
	$str = preg_replace('/[%\*<>\'"\|\:\/\\\\#\?\$&]/', ' ', $str);
	$str = preg_replace('/^\s+/', '', $str);
	$str = preg_replace('/\s+$/', '', $str);
	$str = preg_replace('/\s+/', ' ', $str);

	return $str;
}
function infra_srcinfo($src)
{
	$p = explode('?', $src);
	$file = array_shift($p);
	if ($p) {
		$query = '?'.implode('?', $p);
	} else {
		$query = '';
	}

	$p = explode('/', $file);
	$file = array_pop($p);

	if (sizeof($p) == 0 && preg_match("/^\*/", $file)) {
		$file = preg_replace("/^\*/", '', $file);
		$p[] = '*';
	}
	$folder = implode('/', $p);
	if ($folder) {
		$folder .= '/';
	}

	$fdata = infra_nameinfo($file);

	$fdata['query'] = $query;

	$fdata['src'] = $src;
	$fdata['path'] = $folder.$file;
	$fdata['folder'] = $folder;

	return $fdata;
}
function infra_nameinfo($file)
{
	//Имя файла без папок// Звёздочки быть не может
	$p = explode('.', $file);
	if (sizeof($p) > 1) {
		$ext = array_pop($p);
		$name = implode('.', $p);
		if (!$name) {
			$name = $file;
			$ext = '';
		}
	} else {
		$ext = '';
		$name = $file;
	}
	$fname = $name;
	preg_match("/^(\d{6})[\s\.]/", $name, $match);
	$date = @$match[1];
	$name = preg_replace("/^\d+[\s\.]/", '', $name);
	$ar = explode('@', $name);
	$id = false;
	if (sizeof($ar) > 1) {
		$id = array_pop($ar);
		if (!$id) {
			$id = 0;
		}
		$idi = (int) $id;
		$idi = (string) $idi;//12 = '12 asdf' а если и то и то строка '12'!='12 asdf'
		if ($id == $idi) {
			$name = implode('@', $ar);
		} else {
			$id = false;
		}
	}
	$ans = array(
		'id' => $id,
		'name' => trim($name),
		'fname' => $fname,
		'file' => $file,
		'date' => $date,
		'ext' => $ext,
	);

	return $ans;
}
function infra_tofs($str)
{
	$conf = infra_config();
	if ($conf['infra']['fscharset'] != 'UTF-8') {
		$str = infra_toutf($str);
		$str = iconv('UTF-8', 'CP1251', $str);
	}

	return $str;
}
function infra_theme($str, $debug = false)
{
	//Небезопасная функция
	//Повторно для адреса не работает Путь только отностельно корня сайта или со звёздочкой
	$str = infra_tofs($str);
	$dirs = infra_dirs();
	if (!$str) {
		return;
	}
	//if($str=='*')return $dirs['data'];


	$q = explode('?', $str, 2);
	$str = $q[0];

	$is_fn = ($str{strlen($str) - 1} == '/') ? 'is_dir' : 'is_file';

	$query = '';
	if (isset($q[1])) {
		$query = '?'.$q[1];
	}

	if ($str{0} != '*') {
//Проверка что путь уже правильный... происходит когда нет звёздочки... Неопределённость может возникнуть только с явными путями
		//if($is_fn($str))return $str.$query;//Относительный путь в первую очередь, если повторный вызов для пути попадём сюда

		if ($is_fn($str)) {
			return $str.$query;
		}

		return;
	}

	$str = mb_substr($str, 1);
	foreach ($dirs['search'] as $dir) {
		if ($is_fn($dir.$str)) {
			return $dir.$str.$query;
		}
	}

	return;
}

function &infra_loadJSON($path)
{
	$store = &infra_storeLoad('loadJSON');
	global $infra;
	if (isset($store[$path])) {
		if ($store[$path]['com']) {
			$infra['com'] = $store[$path]['com'];
		}
		if (!$store[$path]['cache']) {
			infra_cache_no();
		}

		return $store[$path]['value'];
	}
	$store[$path] = array();

	$store[$path]['cache'] = infra_cache_check(function () use ($path, &$text) {
		$text = infra__load($path);
	});
	$store[$path]['com'] = infra_load_com();
	/*JSON_FORCE_OBJECT
	JSON_UNESCAPED_SLASHES
	JSON_UNESCAPED_UNICODE
	//json_encode*/
	if (is_string($text)) {
		$store[$path]['value'] = infra_json_decode($text);
	} else {
		$store[$path]['value'] = $text;
	}
	$store[$path]['status'] = true;

	if ($store[$path]['com']) {
		$infra['com'] = $store[$path]['com'];
	}

	return $store[$path]['value'];
}
function infra_load_com()
{
	$heads = headers_list();
	$headers = array();
	foreach ($heads as $v) {
		$v = explode(':', $v, 2);
		$headers[$v[0]] = $v[1];
	}
	if (isset($headers['infra-com'])) {
		$com = infra_json_decode($headers['infra-com']);
	} else {
		$com = false;
	}

	return $com;
}
function &infra_loadTEXT($path)
{
	$store = infra_storeLoad('loadTEXT');
	global $infra;
	if (isset($store[$path])) {
		if ($store[$path]['com']) {
			$infra['com'] = $store[$path]['com'];
		}
		if (!$store[$path]['cache']) {
			infra_cache_no();
		}

		return $store[$path]['value'];
	}
	$store[$path] = array();

	$store[$path]['cache'] = infra_cache_check(function () use ($path, &$text) {
		$text = infra__load($path);
	});
	$store[$path]['com'] = infra_load_com();

	/*JSON_FORCE_OBJECT
	JSON_UNESCAPED_SLASHES
	JSON_UNESCAPED_UNICODE
	//json_encode*/
	if (is_null($text)) {
		$text = '';
	}

	if (!is_string($text)) {
		$store[$path]['value'] = infra_json_encode($text);
	} else {
		$store[$path]['value'] = $text;
	}
	$store[$path]['status'] = true;

	if ($store[$path]['com']) {
		$infra['com'] = $store[$path]['com'];
	}

	return $store[$path]['value'];
}
/**
 * Функция возвращет находимся ли мы в исполнении скрипта запущенного из браузера или скрипта подключённого c помощью infra_loadJSON infra_loadTEXT другим php скриптом
 * можно установить false если ещё небыло никаких установок..
 * если кто-то подключает в php через theme.php или тп... сброс в theme.php уже не сработает
 */
function infra_isphp($val = null)
{
	global $FROM_PHP_PLUGIN;
	if (is_null($val)) {
		return !!$FROM_PHP_PLUGIN; //false или null = false
	} else {
		$FROM_PHP_PLUGIN = $val;
	}
}
function infra__load($path)
{
	$store = infra_storeLoad('load');
	if (isset($store[$path])) {
		return $store[$path]['value'];
	}
	//php файлы эмитация веб запроса
	//всё остальное file_get_content

	$load_path = infra_theme($path);
	$fdata = infra_srcinfo($load_path);
	if ($load_path) {
		$plug = infra_theme($fdata['path']);
		if ($fdata['ext'] == 'php') {
			$getstr = infra_toutf($fdata['query']);//get параметры в utf8, с вопросом
			$getstr = preg_replace("/^\?/", '', $getstr);
			parse_str($getstr, $get);
			if (!$get) {
				$get = array();
			}
			$GET = $_GET;
			$_GET = $get;
			$REQUEST = $_REQUEST;
			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

			$SERVER_QUERY_STRING = $_SERVER['QUERY_STRING'];
			$_SERVER['QUERY_STRING'] = $getstr;

			
			$from_php_old=infra_isphp();
			infra_isphp(true);

			ob_start();
			//headers надо ловить
			$rrr = include $plug;
			$result = ob_get_contents();
			$resecho = $result;
			ob_end_clean();
			
			infra_isphp($from_php_old);

			if ($rrr !== 1 && !is_null($rrr)) { //Есть возвращённый результат
				$result = $rrr;
				if ($resecho) { //Сообщение об ошибке... далее всё ломаем
					$result = $resecho.infra_json_encode($result); //Есть вывод в браузер и return
				}
			}

			$_SERVER['QUERY_STRING'] = $SERVER_QUERY_STRING;
			$_REQUEST = &$REQUEST;
			$_GET = &$GET;
			$data = $result;

//$data='php file';
		} else {
			$data = file_get_contents($plug);
		}
		$store['status'] = 200;
		$store['value'] = $data;
	} else {
		$data = '';
		$store['status'] = 404;
		$store['value'] = '';
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



function infra_ret($ans, $str = false)
{
	$ans['result'] = 1;
	if ($str) {
		$ans['msg'] = infra_template_parse(array($str), $ans);
	}

	return infra_ans($ans);
}
function infra_err($ans, $str = false)
{
	$ans['result'] = 0;
	if ($str) {
		$ans['msg'] = infra_template_parse(array($str), $ans);
	}

	return infra_ans($ans);
}
function infra_ans($ans)
{
	if (infra_isphp()) {
		return $ans;
	} else {
		header('Content-type:application/json');//Ответ формы не должен изменяться браузером чтобы корректно конвертирвоаться в объект js, если html то ответ меняется
		echo infra_json_encode($ans);
	}
}
function infra_echo($ans = array(), $msg = false, $res = null)
{
	//Окончание скриптов
	if ($msg !== false) {
		$ans['msg'] = $msg;
	}
	if (!is_null($res)) {
		$ans['result'] = $res;
	}

	return infra_ans($ans);
}
