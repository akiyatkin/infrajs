<?php 
/*
Copyright 2008 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru

History
- Проверяется что указанный путь ведёт к файлу из папки infra
- Запрет на файлы начинающийся с точки
- Путь не может быть от корня
- Если файл php то результат файла возвращается через веб сервер иначе файл берётся напрямую

10.04.2010
Добавлена проверка HTTP_X_REQUESTED_WITH и передача заголовка json
25.04.2010
Добавлено кэширование modified
24.10.2010 
адаптирован для infra

*/
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$file=urldecode($_SERVER['QUERY_STRING']);
	//$file='*'.urldecode($_SERVER['QUERY_STRING']);//depricated... надо передавать со звёздочкой	
	//$file='*'.$_SERVER['QUERY_STRING'];//depricated... надо передавать со звёздочкой
	//$file=preg_replace("/^\*+/","*",$file);//Если вдруги получилось две из-за того что одна уже была.. будет одна
	$set='f';
	$src=infra_theme($file,$set);
	
	if($src){
		if(preg_match("/\.js$/",$src)){
			//$src=infra_minsrc($file,$set);
		}else if(preg_match("/\.njs$/",$src)){
			return '';
		}else if(preg_match("/\.sjs$/",$src)){
			return '';
		}else{
			//$src=$file;
		}
		$third=null;
		if(!preg_match("/\?/",$src)&&!preg_match("/\.php$/",$src)){
			/*---------$src---------------*/
				$date=filemtime(ROOT.$src);
				$last_modified=gmdate('D, d M Y H:i:s', $date).' GMT';
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
				//header('Cache-Control: max-age=86400, must-revalidate');//Сколько секунд хранить кэш в браузере
				header('Last-Modified: '.$last_modified);
			/*------------------------*/

			
			
			$data=infra_loadTEXT($src);
			$p=infra_srcinfo($src);

			$data=file_get_contents(ROOT.$p['src']);
			
			if($p['ext']=='gif')@header('Content-Type: image/gif');
			else if($p['ext']=='png')@header('content-type: image/png');
			else if($p['ext']=='jpeg')@header('content-type: image/jpeg');
			else if($p['ext']=='css')@header('content-type: text/css');
			
		}else{
			$third=$_POST;
			$data=infra_loadTEXT($src);//infra_loadTEXT и infr_loadJSON могут возвращать объект
		}
		//Из-безопасности нельзя использовать параметр p так как некоторые скрипты могут не проверять права если обращение из php ($FROM_PHP) которое становится таковым при наличии модификатора p
	}else{
		header('HTTP/1.0 404 Not Found');
		$data='';
	}
	echo $data;
?>