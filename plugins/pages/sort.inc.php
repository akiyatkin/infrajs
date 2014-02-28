<?php
	/*
	Copyright 2008 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
	
	not ready for include
	history
	18.04.2010 создан
	25.04.2010
	Если в каком-то виде передан список расширений $exts пусть даже пустой массив.... ищим только файлы... иначе подходят и папки
	29.05.2010 "\d .файл.ext" теперь скрыт от листинга но доступен по ссылке.
	*/
	
	@define('ROOT','../../../');//Метка где корень сайта... используется в коде файла weblife.php
	require_once(ROOT.'infra/plugins/infra/infra.php');
	if(!function_exists('getSortedSrc')){
		function getSortedSrc($src,$exts=false){//Находит все шаги
			if($src){
				if(preg_match('/\?/',$src)){
					$pu=parse_url($src);
					$src=$pu['host'].$pu['path'];
					$query='?'.$pu['query'];
				}else{
					$query='';
				}
				
				$src=infra_tofs($src);
				if(!is_file(ROOT.$src)&&(!is_dir(ROOT.$src)||$exts)){
					$path=preg_split('/[\/]/',$src);
					$s='';//fsName
					for($i=0,$l=sizeof($path);$i<$l;$i++){
						$name=$path[$i];
						if(!$name)continue;
						if($i==$l-1){//Для последнего name передаём расширения
							$name=getSordedPath($s,$name,$exts);//Возвращает вместе со слэшем если дир				
						}else{;
							$name=getSordedPath($s,$name);//Возвращает вместе со слэшем если дир
						}
						if($name===false){
							$s=false;
							break;
						}

						$s.=$name;
					
					}
				
					if($s){
						$src=infra_tofs($s);
					}else{
						$src=false;
					}
				}else{
					$src=$src;
				}
				if($src){
					$src=$src.$query;
				}
				
			}
			return $src;
		}
		function getSordedPath($dir,$name,$exts=false){//Проверяет только один шаг в адресе
			
			if (is_dir(ROOT.$dir)) {
			    if ($dh = opendir(ROOT.$dir)) {
			        while (($file = readdir($dh)) !== false) {
			        	if($file[0]=='.')continue;
			        	if($file!=$name){
			        		$nfile=preg_replace("/^\d+[\s\.]+/",'',$file);
			        	}else{
			        		$nfile=$file;
			        	}
			        	if($nfile==$name){
			        		$type=filetype(ROOT.$dir . $file);
			        		if($exts&&$type=='dir')continue;
			        		if($type=='dir')$file.='/';
			        		return $file;
			        	}
			        	if($exts){
			        		for($i=0,$l=sizeof($exts);$i<$l;$i++){
			        			if($nfile==$name.'.'.$exts[$i]){
					        		$type=filetype(ROOT.$dir . $file);
					        		if($type=='dir')$file.='/';
					        		return $file;
					        	}
			        		}
			        	}
			        }
			        closedir($dh);
			    }
			}
			
			return false;
		}
	}
	

?>
