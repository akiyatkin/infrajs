<?php

function infra_mem_set($key, $val)
{
	$mem = &infra_memcache();
	if ($mem) {
		$mem->delete($key);
		$mem->set($key, $val);
	} else {
		$key=infra_forFS($key);
		$dirs = infra_dirs();
		$dir = $dirs['cache'].'mem/';
		$v = serialize($val);
		file_put_contents($dir.$key.'.ser', $v);
	}
}
function infra_mem_get($key)
{
	$mem = &infra_memcache();
	if ($mem) {
		$r = $mem->get($key);
	} else {
		$key=infra_forFS($key);
		$dirs = infra_dirs();
		$dir = $dirs['cache'].'mem/';
		if (is_file($dir.$key.'.ser')) {
			$r = file_get_contents($dir.$key.'.ser');
			$r = unserialize($r);
		} else {
			$r = null;
		}
	}

	return $r;
}
function infra_mem_delete($key)
{
	$mem = &infra_memcache();
	if ($mem) {
		$r = $mem->delete($key);
	} else {
		$key=infra_forFS($key);
		$dirs = infra_dirs();
		$dir = $dirs['cache'].'mem/';
		$r = @unlink($dir.$key.'.ser');
	}

	return $r;
}
function &infra_mem_flush()
{
	$mem = &infra_memcache();
	if ($mem) {
		$mem->flush();
	} else {
		$dirs = infra_dirs();
		$dir = $dirs['cache'].'mem/';
		foreach (glob($dir.'*.*') as $filename) {
			@unlink($filename);
		}
	}
}
function &infra_memcache()
{
	global $infra_mem;
	if (isset($infra_mem)) {
		return $infra_mem;
	}
	$conf=infra_config();
	if ($conf['infra']['cache'] != 'mem') {
		$infra_mem=false;
		return false;
	}
	$r = false;
	if (!class_exists('Memcache')) {
		return $r;
	}
	$conf = infra_config();
	if (!@$conf['memcache']) {
		return $r;
	}
	$infra_mem = new Memcache();
	$infra_mem->connect($conf['memcache']['host'], $conf['memcache']['port']) or die('Could not connect');

	return $infra_mem;
};
