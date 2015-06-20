<?php
/*
(c) All right reserved. http://itlife-studio.ru

infra_cache(true,'somefn',array($arg1,$arg2)); - выполняется всегда
infra_cache(true,'somefn',array($arg1,$arg2),$data); - Установка нового значения в кэше 
*/
@define('ROOT','../../../../');


function infra_cache_fullrmdir($delfile){
	if (file_exists(ROOT.$delfile)){
		//chmod($delfile,0777);
		if (is_dir(ROOT.$delfile)){
            $handle = opendir(ROOT.$delfile);
            while($filename = readdir($handle)){
				if ($filename != '.' && $filename != '..'){
					$src=$delfile.$filename;
					if(is_dir(ROOT.$src))$src.='/';
					
						infra_cache_fullrmdir($src);
					
				}
			}
            closedir($handle);
            return rmdir(ROOT.$delfile);
		}else{
			return unlink(ROOT.$delfile);
		}
	}
}

if(is_file(ROOT.'infra/update')){//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
	@unlink(ROOT.'infra/update');
	$r=@infra_cache_fullrmdir('infra/cache/');
	if(!$r)header('infra-update:Fail');
	else header('infra-update:OK');
	infra_admin_time_set(time()-1);
}


if(!is_dir(ROOT.'infra/cache/')){
	mkdir(ROOT.'infra/cache/');//Создаём если нет папку infra/cache
	if(!is_dir(ROOT.'infra/cache/')){
		die('Не удалось создать папку infra/cache/, пользователю от которого запущен процесс php нужно дать права на редактирование папки с сайтом');
	}
	@unlink(ROOT.'infra/update');
}



define('INFRA_CACHE_DIR','infra/cache/infra_cache_once/');//Используется в xml/xml.php
@mkdir(ROOT.INFRA_CACHE_DIR,0755);
function infra_cache_path($name,$args=null){
	$name=infra_tofs($name);
	$dirfn=INFRA_CACHE_DIR.$name.'/';
	@mkdir(ROOT.$dirfn,0755);
	if(is_null($args))return $dirfn;
	$strargs=infra_hash($args);
	//$strargs=md5($strargs);
	$path=$dirfn.$strargs.'.json';
	return $path;
}



function infra_cache_is(){ //Возможны только значения no-store и no-cache
	$list=headers_list();
	foreach($list as $name){
		$r=explode(':',$name,2);
		if($r[0]=='Cache-Control')return (strpos($r[1],'no-store')===false);
	}
	return true;
}
function infra_cache_no(){
	header("Cache-Control: no-store"); //Браузер всегда спрашивает об изменениях. Кэш слоя не делается.
	//header("Expires: ".date("r"));
}
function infra_cache_yes(){
	header("Cache-Control: no-cache"); //По умолчанию. Браузер должен всегда спрашивать об изменениях. Кэш слоёв делается.
	//header_remove("Cache-Control");
	//header_remove("Expires");
}
function infra_cache_check($call){
	$cache=infra_cache_is();
	if(!$cache)infra_cache_yes();
	$call();
	$cache2=infra_cache_is();
	if(!$cache&&$cache2)infra_cache_no();
	return $cache2;
}

function &infra_cache($conds,$name,$fn,$args=array(),$re=false){

	return infra_admin_cache('cache_admin_'.$name,function($conds,$name,$fn,$args, $re){
		
		//цифры нельзя, будут плодиться кэши
		//если условие цифра значит это время, и если время кэша меньше.. нужно выполнить
		


		
			
		$max_time=1;
		for($i=0,$l=sizeof($conds);$i<$l;$i++){
			$mark=$conds[$i];
			$mark=infra_theme($mark);
			if($mark){
				$m=filemtime(ROOT.$mark);
				if($m>$max_time)$max_time=$m;
				if(is_dir(ROOT.$mark)){
					foreach (glob(ROOT.$mark.'*.*') as $filename) {
						$m=filemtime($filename);
						if($m>$max_time)$max_time=$m;
					}
				}
			}else{
				array_splice($conds,$i,1);
				//Если переданной метки не существует меняется путь до кэша
			}
		}
		$cache_time=0;
		$path=infra_cache_path($name,array($conds,$args));
		if($cond){
			$path=infra_tofs($path);
			if(is_file(ROOT.$path))$cache_time=filemtime(ROOT.$path);//стартовая временная метка равна дате изменения самого кэша
		}
		
		$execute=($max_time>$cache_time)||$re;//re удаляет кэш только для текущих параметров
		

		if(!$execute){
			$data=infra_loadTEXT($path);
			$data=unserialize($data);
		}else{

			$cache_control=infra_cache_is();
			if($cache_control)infra_cache_no();

			$data=call_user_func_array($fn,array_merge($args,array($re)));

			$list=headers_list();//Проверяем появился ли заголовок после запуска функции кэшируемой
			$cache_control2=infra_cache_is();
			if(!$cache_control2&&$cache_control)infra_cache_yes();

			if(!$cache_control2){
				$cache=serialize($data);
				file_put_contents(ROOT.$path,$cache);
			}
		}
		return $data;
	},array(&$conds,$name,$fn,$args),$re);
}
?>
