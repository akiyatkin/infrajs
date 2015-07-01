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
require_once(__DIR__.'/infra.php');



$file=urldecode($_SERVER['QUERY_STRING']);

//$file='*'.urldecode($_SERVER['QUERY_STRING']);//depricated... надо передавать со звёздочкой	
//$file='*'.$_SERVER['QUERY_STRING'];//depricated... надо передавать со звёздочкой
//$file=preg_replace("/^\*+/","*",$file);//Если вдруги получилось две из-за того что одна уже была.. будет одна
$filesrc=$file;
$a=strpos($file,'&');
if($a!==false){
	//Амперсанд идущий до вопроса или без вопроса, заменяется на вопрос ?*file.php&stat=2 >>> ?*file.php?stat=2
	$q=strpos($file,'?');
	if(!$q||$a<$q){
		$filesrc=substr_replace($file,'?',$a,1);
	}	
}
$src=infra_theme($filesrc);
infra_isphp(false);//Метка для подключаемого файла если такой будет, что он рабоает вне php и должен проверять права и делать соответствующие выводы

if($src){
	$p=infra_srcinfo($src);
	if(preg_match("/\/\./",$p['path'])){
		header('HTTP/1.0 403 Forbidden');
		return;
	}
	if($p['ext']!=='php'){
		$mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

            //added
            'tpl' => 'text/html'
        );
		if(isset($mime_types[$p['ext']])){
			$type=$mime_types[$p['ext']];
		}else{
			$type='application/octet-stream';
		}
		if($p['query']&&(!$p['ext']||in_array($p['ext'],array('jpeg','jpg','png')))){
			$fex=explode('?',$filesrc);
			$src=infra_theme('*imager/imager.php').'?src='.$fex[0].'&'.mb_substr($p['query'],1);
			
		}else{

			@header('Content-Type: '.$type);
		}
	}
	
	
	$third=null;
	if(!preg_match("/\?/",$src)&&!preg_match("/\.php$/",$src)){
		
		/*---------$src---------------*/
			$date=filemtime($src);
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
		
		$data=file_get_contents($p['src']);

	}else{
		$third=$_POST;
		if(preg_match("/\/\./",$src)){
			header('HTTP/1.0 403 Forbidden');
			$data='';
		}else{
			$data=infra_loadTEXT($src);//infra_loadTEXT и infr_loadJSON могут возвращать объект
		}
	}
}else{
	header('HTTP/1.0 404 Not Found');
	$data='';
}
echo $data;