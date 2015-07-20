<?php
/*
	install безопасная обработка доступная всем. Работа с файлами и постоянные изменения

	И в install редактируются data/.config.json
	весь конфиг сохраняется в data/.config.json.. 
	интерфейс показывает сравнение собранного конфига всей системы с файлом data/.config.json
*/
namespace itlife\infrajs\infra;

function checkParentDir($name)
{
	$dirs = infra_dirs();
	$test=explode('/', $dirs[$name]);
	$test=array_slice($test, 0, sizeof($test)-2);
	$test=implode('/', $test).'/';
	if (!is_dir($test)) {
		die('Not Found folder '.$test.' for '.$name);
	}
}
$dirs = infra_dirs();
$conf = infra_config();

if ($conf["infra"]["cache"] == "fs") {
	checkParentDir('cache');
	if (!is_dir($dirs['cache'])) {
		mkdir($dirs['cache']);
	}
	if (!is_dir($dirs['cache'].'mem/')) {
		mkdir($dirs['cache'].'mem/');
	}
	if (!is_dir($dirs['cache'].'infra_cache_once/')) {
		mkdir($dirs['cache'].'infra_cache_once/');
	}
}

checkParentDir('data');
if (!is_dir($dirs['data'])) {
	@mkdir($dirs['data']); //Режим без записи на жёсткий диск
}

if (!is_file($dirs['data'].'.config.json')) {
	$pass = substr(md5(time()), 2, 8);
	//Режим без записи на жёсткий диск
	@file_put_contents($dirs['data'].'.config.json', '{"debug":true,"admin":{"login":"admin","password":"'.$pass.'"}}');
}


$t=infra_admin_time();
if (!$t) {
	infra_admin_time_set(time() - 1);//Нужно чтобы был, а то как-будто админ постоянно
}


infra_pluginRun(function ($dir) {
	if (realpath($dir) == realpath(__DIR__)) {
		return;
	}//Себя исключили
	if (!is_file($dir.'install.php')) {
		return;
	}
	require_once $dir.'install.php';
});
