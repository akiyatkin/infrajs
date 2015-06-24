<?php
/*
Copyright 2008-2010 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru

History
23.04.2010
Скрипт получает src без расширения и без цифры сортировки.... а возвращает html
25.04.2010
Добавлено кэширование modified

09.05.2010
Добавлена поддерж php файлов и возможность передачи get параметров запрашиваемому файлу
*/

require_once(__DIR__.'/../infra/infra.php');

$exts=array(
	'tpl'=>'',
	'html'=>'',
	'docx'=>'*pages/docx.php?src=',
	'mht'=>'*pages/mht/mht.php?src=',
	'php'=>''
);
//..'xls'=>'?*pages/xls/xls.php?src='
$isrc=infra_toutf(urldecode($_SERVER['QUERY_STRING']));


$ext=preg_match('/\.(\w{0,4})$/',$isrc,$match);//Расширение при поиске не учитываем
if($ext){
	$ext=$match[1];
}
$src=infra_theme($isrc);

$src=infra_toutf($src);

$fdata=infra_srcinfo($isrc);
$name=preg_replace('/\.\w{0,4}$/','',$fdata['file']);

	
$isrc=$fdata['folder'].$name;
if($src){
	@header('Content-Type: text/html; charset=utf-8');
	if(isset($exts[$ext])){//Расширение уже было 
		$info=infra_loadTEXT($exts[$ext].$src);

	}else{//Расширения небыло берём по приоритету

		if(!$ext){//приоритет

			$ext='tpl';
			$info=infra_loadTEXT($exts[$ext].$isrc.'.'.$ext);
			if(!$info){
				$ext='html';
				$info=infra_loadTEXT($exts[$ext].$isrc.'.'.$ext);
				if(!$info){
					$ext='docx';
					$info=infra_loadTEXT($exts[$ext].$isrc.'.'.$ext);
					if(!$info){
						$ext='mht';

						$info=infra_loadTEXT($exts[$ext].$isrc.'.'.$ext);
					}
				}
			}
			if(!$info){
				$p=infra_srcinfo($src);
				$ext=$p['ext'];
				if(isset($exts[$ext])){
					$info=infra_loadTEXT($exts[$ext].$isrc,$set);
				}

			}
		}else{
			$info='';
			//$info=infra_plugin($isrc);
		}
	}
	echo $info;
}else{
	//@header("HTTP/1.0 404 Not Found");
}
