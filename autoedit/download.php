<?php 
/*
Copyright 2008-2010 http://itlife-studio.ru

*/
	
	require_once(__DIR__.'/../infra/infra.php');
	if(!function_exists('file_download')){
		function infra_download_browser($agent=false){
			if(!$agent)$agent=$_SERVER['HTTP_USER_AGENT'];
			$agent=strtolower($agent);
			$name=infra_once('infra_imager_browser',function($agent){
				if (preg_match('/msie (\d)/', $agent,$matches)) {
					$name = 'ie ie'.$matches[1];
				}elseif (preg_match('/opera/', $agent)) {
					$name = 'opera';
					if(preg_match('/opera\/9/', $agent)) {
						$name.=' opera9';
					}else if(preg_match('/opera (\d)/', $agent,$matches)){
						$name.=' opera'.$mathces[1];
					}
					if(preg_match('/opera\smini/', $agent)) {
						$name.=' opera_mini';
					}
				}elseif (preg_match('/gecko\//', $agent)){
					$name='gecko';
					if (preg_match('/firefox/', $agent)){
						$name .= ' ff';
						if (preg_match('/firefox\/2/', $agent)){
							$name .= ' ff2';
						}elseif (preg_match('/firefox\/3/', $agent)){
							$name .= ' ff3';
						}
					}
				}elseif (preg_match('/webkit/', $agent)) {
					$name = 'webkit';
					if (preg_match('/chrome/', $agent)) {
						$name .= ' chrome';
					}else{
						$name .= ' safari';
					}
				}elseif (preg_match('/konqueror/', $agent)) {
					$name='konqueror';
				}elseif (preg_match('/flock/', $agent)) {
					$name='flock';
				}else{
					$name='stranger';
				}
				if (!preg_match('/ie/', $name)){
					$name.=' noie';
				}
				if (preg_match('/linux|x11/', $agent)) {
				   $name.=' linux';
				}elseif (preg_match('/macintosh|mac os x/', $agent)) {
				    $name.=' mac';
				}elseif (preg_match('/windows|win32/', $agent)) {
				    $name.=' win';
				}
				if(preg_match('/stranger/',$name)){
					$name='';
				}
				return $name;
			},array($agent));
			return $name;
		}
		function file_download($filename, $mimetype='application/octet-stream') {
			//thanks http://shaman.asiadata.ru/node/217
			if(file_exists($filename)){

				
				$br=infra_download_browser();
				$name=preg_replace("/(.*\/)*/",'',$filename);
				//$name=infra_tofs($name);
				$name=infra_toutf($name);
				if(!preg_match('/ff/',$br)){
					$name=rawurlencode($name);
				}
				//$name=preg_replace("/ё/",'e',$name);
				if(preg_match('/chrome/',$br)){
					$name=preg_replace('/%40/','@',$name);
				}
				if(preg_match('/ie6/',$br)){
					$name=preg_replace("/\s/",'%20',$name);
				}


				header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK');
				header('Content-Type: '.$mimetype);
				header('Last-Modified: ' . gmdate('r', filemtime($filename)));
				header('ETag: ' . sprintf('%x-%x-%x', fileinode($filename), filesize($filename), filemtime($filename)));
				header('Content-Length: ' . (filesize($filename)));
				header('Connection: close');
				header('Content-Disposition: attachment; filename="'.$name.'";');
			// Открываем искомый файл
				$f=fopen($filename, 'r');
				while(!feof($f)) {
			// Читаем килобайтный блок, отдаем его в вывод и сбрасываем в буфер
				  echo fread($f, 1024);
				  flush();
				}
			// Закрываем файл
				fclose($f);
		  } else {
			header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
			header('Status: 404 Not Found');
		  }
		}
	}
	$file=urldecode($_SERVER['QUERY_STRING']);

	$set='fn';
	$path=infra_theme($file,$set);
	if(!$path){//Нет не скрытого файла
		$r=infra_admin();
		if(!$r){
			header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
			header('Status: 404 Not Found');
			exit;
		}else{
			$set='fns';
			$path=infra_theme($file,$set);
		}
	}
	if(!$path){
		header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
		header('Status: 404 Not Found');
		exit;
	}else{
		$ext=false;
		$dyn=preg_match('/\?/',$path);
		if(!$dyn){
			preg_match('/.*\.(.*)$/','.'.$path,$match);
			$ext=mb_strtolower($match[1]);

			$file_types_user= array( 
				'gif'=>'image/gif',
				'jpg' =>'image/jpeg',
				'jpeg'=>'image/jpeg',
				"rtf" => "text/rtf",
				'png' =>'image/png',
				'mht' =>'application/msword',
				'doc' =>'application/msword',
				'docx' =>'application/msword',
				"avi" => "video/x-msvideo",
				"xls" => "application/msexcel",
				"tpl" => "text/html",
				"html" => "text/html",

				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
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
				'docx' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',

				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
			);
			$file_types_admin= array( 
				'php' => 'text/html',
			);
		}
		if(!$dyn&&$ext&&$file_types_user[$ext]){
			//header( "Content-type: ".$file_types[$ext] ) ;
			//header( "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime($path))." GMT" );
			file_download($path,$file_types_user[$ext]);
		}else{
			infra_admin(true);
			if(!$dyn&&$ext&&$file_types_admin[$ext]){
				file_download($path,$file_types_admin[$ext]);
			}else{
				die('Исключение');
			}
		}
	}