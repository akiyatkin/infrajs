<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	function autoedit_setLastFolderUpdate($path){
		$path=infra_theme($path);
		if(!$path)return;
		$p=explode('/',$path);
		if($p[0]!=='infra')return;
		$dir='';
		foreach($p as $d){
			$dir=$dir.$d.'/';
			if(@is_file(ROOT.$dir.'last_folder_update.txt')){
				$fle = fopen(ROOT.$dir.'last_folder_update.txt',"a");
				$r=fwrite($fle,"\nАдминка ".date('H:i d.m.Y').' '.infra_toutf($path));
				break;
			}
		}
	}
	function autoedit_createPath($p,$path=''){//путь до файла или дирректории со * или без, возвращается тот же путь без звёздочки
		$f=infra_tofs('');
		if(is_string($p)){
			$f=preg_replace('/^\*\/*/','*/',$p);
			$p=explode('/',$f);
			if($p[0]=='*')$p[0]='infra/data';
			$f=array_pop($p);//достали файл или пустой элемент у дирректории
			$f=infra_tofs($f);
		}
		$dir=array_shift($p);
		$dir=infra_tofs($dir);
		if($dir){
			if(!is_dir(ROOT.$path.$dir)){
				$r=mkdir(ROOT.$path.$dir);
			}else{
				$r=true;
			}
			if($r){
				return autoedit_createPath($p,$path.$dir.'/').$f;
			}else{
				throw Exception('Ошибка при работе с файловой системой');
			}
		}
		return $path.$dir.'/'.$f;

	}
	function autoedit_ext($file){
		if(!$file)return '';
		$ext=preg_match('/\.(\w{0,4})$/',$file,$match);
		$ext=$match[1];
		return $ext;
	}
	function autoedit_parsefile($origpath){//Путь со звёздочкой до файла
		$ans=array();
		$path=infra_theme($origpath);
		if($path&&is_file(ROOT.$path)){//Если файл есть
			$p=explode('/',$path);//Имя с расширением
			$file=array_pop($p);
		}else if(!$path){//Если файла нет.. определяем имя path 
			$p=explode('/',$origpath);//Имя с расширением
			$file=array_pop($p);
			$file=preg_replace("/^\*/",'',infra_toutf($file));
		}
		$ans['file']=$file;
		$p=explode('/',$origpath);
		array_pop($p);
		$ans['folder']=infra_toutf(implode('/',$p));
		if($ans['folder']=='/'||!$ans['folder'])$ans['folder']='*';
		else $ans['folder'].='/';
		$ans['path']=infra_theme('infra/plugins/autoedit/download.php?'.$origpath,'fu');
		return $ans;
	}
	function autoedit_folder($file){
		$s=explode('/',$file);
		$name=array_pop($s);
		$folder=implode('/',$s);
		if($folder!='*')$folder.='/';
		return $folder;
	}
	function autoedit_takepath($file=false){
		$takepath='infra/cache/admin_takefiles/';
		if($file===false)return $takepath;
		@mkdir(ROOT.$takepath,0755);
		$path=$takepath.preg_replace('/[\\/\\\\\*]/','_',infra_tofs($file)).'.js';
		return $path;
	}
	function autoedit_ismytake($file){
		$takepath=autoedit_takepath($file);
		$take=infra_loadJSON($takepath);
		if(!$take)return true;
		if($take['ip']!=$_SERVER['REMOTE_ADDR']||$take['browser']!=$_SERVER['HTTP_USER_AGENT'])return false;
		return true;
	}
	if(!function_exists('err')){
		function err($ans,$msg){
			$ans['msg']=$msg;
			echo infra_json_encode($ans);
		}
	}
	function autoedit_backup($file){
		$backup='infra/backup/';
		@mkdir(ROOT.$backup,0755);
		$backup.='admin_deletedfiles/';
		@mkdir(ROOT.$backup,0755);
		$backup.=date('Y.m.d_H-i-s').'_'.preg_replace('/[\\/\\\\\*]/','_',$file);
		$r=@copy(ROOT.$file,ROOT.$backup);
		if(!$r){
			infra_echo($ans,'Не удалось сделать backup '.infra_toutf($file).'<br>Скопировать файл в '.infra_toutf($backup),0);
			return false;
		}
		return true;
	}
	function cpdir($src,$dst){
		$dir = opendir($src);
		mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					cpdir($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
		return true;
	}
?>
