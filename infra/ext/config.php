<?php

//Copyright 2008-2013 http://itlife-studio.ru
/*
	infra_config
*/

if (DIRECTORY_SEPARATOR == '/') {
	function infra_realpath($dir)
	{
		return realpath($dir);
	}
	function infra_getcwd()
	{
		return getcwd();
	}
} else {
	function infra_realpath($dir)
	{
		$dir = realpath($dir);

		return str_replace(DIRECTORY_SEPARATOR, '/', $dir);
	}
	function infra_getcwd()
	{
		$dir = getcwd();

		return str_replace(DIRECTORY_SEPARATOR, '/', $dir);
	}
}
function infra_pluginRun($callback)
{
	$dirs = infra_dirs();
	global $infra_plugins;
	if (empty($infra_plugins)) {
		$infra_plugins = array();
		for ($i = 0, $il = sizeof($dirs['search']); $i < $il; ++$i) {
			$dir = $dirs['search'][$i];
			$list = scandir($dir);
			for ($j = 0, $jl = sizeof($list); $j < $jl; ++$j) {
				$plugin = $list[$j];
				if ($plugin{0} == '.') {
					continue;
				}
				if (!is_dir($dir.$plugin)) {
					continue;
				}
				$infra_plugins[] = array('dir' => $dir, 'name' => $plugin);
			}
		}
	}
	for ($i = 0, $il = sizeof($infra_plugins); $i < $il; ++$i) {
		$pl = $infra_plugins[$i];
		$r = $callback($pl['dir'].$pl['name'].'/', $pl['name']);
		if (!is_null($r)) {
			return $r;
		}
	}
}
function infra_dirs()
{
	global $infra_dirs;
	if (!empty($infra_dirs)) {
		return $infra_dirs;
	}
	//Корень сайта относительно этого файла
	$vendorroot = infra_realpath(__DIR__.'/../../../../../');//AВ до vendor
	//Корень сайта определёный по рабочей дирректории
	$siteroot = infra_getcwd();
	//Определёный корень сайта двумя способами сравниваем
	//Если результат разный значит система запущена не из той папки где находится vendor с текущим кодом
	if ($siteroot != $vendorroot) {
		die('Start infrajs only from site root - directory which have subfolder vendor with itlife/infrajs');
	}

	$infra_dirs = array(
		'cache' => 'infra/cache/',
		'data' => 'infra/data/',
		'backup' => 'infra/backup/',
		'search' => array(
			'infra/data/', //Обязательно на первом месте, папка с данными пользователя!
			'infra/layers/',
			'./',
			'vendor/itlife/',
			'vendor/itlife/infrajs/',
		),
	);

	return $infra_dirs;
}
function infra_test($r = false)
{
	$conf=infra_config();
	$ips=$conf['infra']['testips'];
	if (!$ips) {
		$ips=array();
	}
	$is=in_array($_SERVER["REMOTE_ADDR"], $ips);
	
	if ($r) {
		if (!$is) {
			header('HTTP/1.0 403 Forbidden');
			die('{"msg":"Required config.infra.testips:['.$_SERVER["REMOTE_ADDR"].']"}');
		}
	} else {
		return $is;
	}
}
function &infra_config($sec = false)
{
	$sec = $sec ? 'secure' : 'unsec';

	global $infra_config;
	if (isset($infra_config[$sec])) {
		return $infra_config[$sec];
	}

	$dirs = infra_dirs();
	$dirs['search'] = array_reverse($dirs['search']);
	$data = array();

	foreach ($dirs['search'] as $src) {
		if (is_dir($src)) {
			$list = scandir($src);
			foreach ($list as $name) {
				if ($name[0] == '.') {
					continue;
				}
				if (!is_dir($src.$name)) {
					continue;
				}
				if (!is_file($src.$name.'/.config.json')) {
					continue;
				}

				$d = file_get_contents($src.$name.'/.config.json');
				$d = infra_json_decode($d);
				if (is_array($d)) {
					foreach ($d as $k => &$v) {
						if (@!is_array($data[$k])) {
							$data[$k] = array();
						}
						if (isset($d[$k]['pub']) && isset($data[$k]['pub'])) {
							$d[$k]['pub'] = array_unique(array_merge($d[$k]['pub'], $data[$k]['pub']));
						}
						if (is_array($v)) {
							foreach ($v as $kk => $vv) {
								$data[$k][$kk] = $vv;
							}
						} else {
							$data[$k] = $v;
						}
					}
				}
			}
		}
		if (is_file($src.'.config.json')) {
			$d = file_get_contents($src.'.config.json');
			$d = infra_json_decode($d);
			if (is_array($d)) {
				foreach ($d as $k => &$v) {
					if (@!is_array($data[$k])) {
						$data[$k] = array();
					}
					if (isset($d[$k]['pub']) && isset($data[$k]['pub'])) {
						$d[$k]['pub'] = array_unique(array_merge($d[$k]['pub'], $data[$k]['pub']));
					}
					if (is_array($v)) {
						foreach ($v as $kk => $vv) {
							$data[$k][$kk] = $vv;
						}
					} else {
						$data[$k] = $v;
					}
				}
			}
		}
	}
	$infra_config['unsec'] = $data;
	foreach ($data as $i => $part) {
		$pub = @$part['pub'];
		if (is_array($pub)) {
			foreach ($part as $name => $val) {
				if (!in_array($name, $pub)) {
					unset($data[$i][$name]);
				}
			}
		} else {
			unset($data[$i]);
		}
	}
	$data['debug'] = $infra_config['unsec']['debug'];
	$infra_config['secure'] = $data;

	return $infra_config[$sec];
}
