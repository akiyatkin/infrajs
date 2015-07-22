<?php

/*
Copyright 2008-2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru

getorig
ignoremark
*/

infra_require('*imager/imager.inc.php');

$src = (string) infra_toutf(@$_GET['src']);
$or = (string) infra_toutf(@$_GET['or']);//Путь на случай если src не найден
$isrc = $src;
$mark = (bool) @$_GET['mark'];
if (!$mark) {
	$mark = (bool) @$_GET['m'];
}//Для совместимости со старой версией depricated


/*---------$src---------------*/
if (!preg_match('/\.php/', $isrc)) {
	//Нельзя считывать напрямую такое
	$tsrc = infra_theme($isrc);
	$date = filemtime($tsrc);//даже если это папка
	$last_modified = gmdate('D, d M Y H:i:s', $date).' GMT';
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		// разобрать заголовок
		$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);

		if ($if_modified_since == $last_modified) {
			// кэш браузера до сих пор актуален
			header('HTTP/1.0 304 Not Modified');
			//header('Cache-Control: max-age=8640000, must-revalidate');
			exit;
		}
	}
	//header('Cache-Control: max-age=86400, must-revalidate');
	header('Last-Modified: '.$last_modified);
}
/*------------------------*/

$w = (int) @$_GET['w'];
$h = (int) @$_GET['h'];

$top = (bool) @$_GET['top'];
$crop = (bool) @$_GET['crop'];


$ignoremark = null;
if (isset($_GET['ignoremark'])) {
	$ignoremark = (bool) $_GET['ignoremark'];
}

$getorig = (bool) @$_GET['getorig'];//Показывает оригинальную картинку без изменения размеров, как есть... без водяного знака
//$_GET['ignoremark'];//1 - Навсегда убирает водяной знак с картинки и больше водяной знак добавляться на неё не будет. 0 отменяет этот запрет.

$conf = infra_config();

if (!$conf['imager']) {
	$conf['imager'] = array('waterlim' => 22500);
}

$default = false;
$orig = false;
$src = imager_prepareSrc($src);

if (!$src && $or) {
	//Путь не найден смотрим or
	$src = imager_prepareSrc($or);
}

if (isset($_GET['info'])) {
	infra_admin(true);
	$info = imager_readInfo($src);
	if (!$info) {
		echo 'В файле нет сохранённых данных, файл оригинальный';
	}
	echo '<pre>';
	print_r($info);

	return;
}

if ($src && (preg_match("/\/\./", $src) || ($src{0} == '.' && $src{1} != '/'))) {
	header('HTTP/1.1 403 Forbidden');
	return;
}


if (!$src) {
	$default = true;
	$src = infra_theme('*imager/noimage.png');
	if (!$src) {
		header('HTTP/1.0 404 Not Found');

		return;
	}
}


if (!infra_admin()) {
	//Админ может видить запретные картинки, для него не кэшируем
	header('Cache-control: public');//Заголовок разрешающий сохранение на прокси-серверах
}



if ($getorig) {
	infra_admin(true);
}
if (!is_null($ignoremark)) {
	infra_admin(true);
}
if ($getorig) {
	infra_admin(true);
}

$args=array($src, $ignoremark, $mark, $default, $getorig, $w, $h, $crop, $top);
$data=infra_cache(array($isrc), 'imager.php', function ($src, $ignoremark, $mark, $default, $getorig, $w, $h, $crop, $top, $re) use ($isrc) {

	

	$p1 = infra_srcinfo($isrc);//Нужна папка со звёздочкой
	$p = infra_srcinfo($src);

	if (in_array($p['ext'], array('docx','mht'))) {
		/*
			Смотрим подключён ли плагин files для того чтобы достать картинку и файла
		*/
		if (!infra_theme('*files/files.inc.php')) {
			$default=true;
			$src = infra_theme('*imager/noimage.png');
		} else {
			infra_require('*files/files.inc.php');

			if ($re) {
				$re = '&re';
			} else {
				$re = '';
			}
			if ($p['ext'] == 'docx') {
				$p = files_get(infra_toutf($p1['folder']), infra_toutf($p['id']));
				if (!$p['images'][0]) {
					$default=true;
					$src = infra_theme('*imager/noimage.png');
					//header('HTTP/1.1 404 Not Found');
					//return;
				} else {
					$src = $p['images'][0]['src'];
				}
			} elseif ($p['ext'] == 'mht') {
				$p = infra_loadJSON('*pages/mht/mht.php?preview'.$re.'&src='.infra_toutf($p['src']));
				if (!$p['images'][0]) {
					$default=true;
					$src = infra_theme('*imager/noimage.png');
					//header('HTTP/1.1 404 Not Found');
					//return;
				} else {
					$src = $p['images'][0]['src'];
				}
			}
		}
	}
	$src = infra_tofs($src);
	$type = imager_type($src);

	if (!is_null($ignoremark)) {
		//Метку ignore может выставить только администратор
		//На файлы с такой меткой водяной знак никогда не ставится
		$info = imager_makeInfo($src);

		if ($ignoremark && $info['water']) {
			//Если файл был с водяным знаком
			$orig = $info['orig'];
			if ($orig) {
				$orig = infra_theme($orig);
				if ($orig) {
					//Если оригинальный файл найден
					$r = copy($orig, $src);//Востановили оригинал без удаления оригинала
					$info['water'] = false;
					if (!$r) {
						imager_writeInfo($src, $info);
						die('Не удалось востановить оригинал чтобы поставить метку ignore');
					}
					$info['ignore'] = $ignoremark;
				} else {
					imager_writeInfo($src, $info);
					die('На файле установлен водяной знак. Оригинальный файл не найден. Метку установить неудалось');
				}
			} else {
				imager_writeInfo($src, $info);
				die('Водяной знак есть а оригинал не указан. исключение.');
			}
		} else {
			//Водяного знака небыло
			$info['ignore'] = $ignoremark;
		}
		imager_writeInfo($src, $info);
	}

	if ($type && $mark && !$default) {
	//Это не значит что нужно делать бэкап
		imager_mark($src, $type);//Накладываем водяной знак
	}

	/*$info=imager_readInfo($src);
	if($info['ignore']){
		$orig=$info['orig'];
	}*/

	$limark = false;//Не делать водяной знак если площать меньше 150x150
	if ($w && $h) {
		$limark = ($conf['imager']['waterlim'] > ($w * $h));
	} elseif ($w || $h) {
		$wl = $w;
		$hl = $h;
		if (!$w) {
			$wl = $h;
		}
		if (!$h) {
			$hl = $w;
		}
		$limark = ($conf['imager']['waterlim'] > $wl * $hl);
	}
	if ($getorig) {
		$w = 0;
		$h = 0;
		$crop = false;
		$info = imager_readInfo($src);
		$orig = $info['orig'];

		if ($orig) {
			$orig = infra_theme($orig);
			if (!$orig) {
				die('Оригинал не найден');
			} else {
				$src = $orig;//Что далее будет означать что возьмётся для вывода оригинальная картинка
			}
		} else {
			die('Already original');
		}
	} elseif ($limark) {
		$info = imager_readInfo($src);
		if (@$info['water']) {
			$orig = infra_theme($info['orig']);
			if ($orig) {
				$src = $orig;
			} else {
				//die('Не найден оригинал');
			}
		}
	}
	//$src с водяной меткой если нужно
	if (isset($_GET['gray'])) {
		$src = imager_makeGray($src);//новый src уже на серую картинку
	}


	$data = imager_scale($src, $w, $h, $crop, $top);
	if (!$data) {
		die('Resize Error');
	}

	$br = infra_imager_browser();
	$name = preg_replace("/(.*\/)*/", '', $isrc);
	$name = infra_toutf($name);
	if (!preg_match('/ff/', $br)) {
		$name = rawurlencode($name);
	}
	if (preg_match('/ie6/', $br)) {
		$name = preg_replace("/\s/", '%20', $name);
	}

	if (!$type) {
		$type='image/jpeg';
	}

	$data=array('data'=>$data,'name'=>$name,'type'=>$type);
	return $data;
}, $args, isset($_GET['re']));

header('Content-Disposition: filename="'.$data['name'].'";');
header('content-type: image/'.$data['type']);
echo $data['data'];
