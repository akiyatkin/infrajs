<?php
	/*
	Copyright 2008-2010 ITLife, Ltd. http://itlife-studio.ru
	*/
	require_once(__DIR__.'/../infra/infra.php');
	function cache(){
		$ar=func_get_args();
		return call_user_func_array('pages_cache',$ar);
	}
	global $_pages_cache_results;
	$_pages_cache_results=array();
	function pages_cache($marks,$fn,$arg=array(),$reload=false){//marks - массив файлов на дату изменения которых нужно реагировать
		//arg должен быть массивом. Аргументы должны быть короткими простыми.. тому как сохраняются в сериализованном виде
		global $_pages_cache_results;
		if(is_array($fn)){
			$fnstr='obj_'.$fn[1].'-'.$fn[2].'-'.$fn[3];
		}else{
			$fnstr=$fn;
		}
		$dir=ROOT.'infra/cache/pages_cache/';
		@mkdir($dir,0755);
		$dir='infra/cache/pages_cache/'.$fnstr.'/';
		@mkdir(ROOT.$dir,0755);
		$argcache=array();
		foreach($arg as $a){
			$argcache[]=infra_toutf($a);
		}
		$path=$dir.md5(infra_tojs($argcache));
		if($_pages_cache_results[$path])return $_pages_cache_results[$path];
		if(!is_array($marks))$marks=array($marks);
		//array_push($marks,__FILE__);
		$files=get_included_files();
		$marks+=$files;
		$marks[]=$_SERVER['SCRIPT_FILENAME'];
		$timer=0;
		foreach($marks as $k=>$a){
			$marks[$k]=infra_tofs($a);
			if(is_numeric($a)){
				unset($marks[$k]);
				$timer=$a*60*60;//Когда уже можно запускать
			}
		}

		$execute=!is_file(ROOT.$path)||$reload;//Закэшированный результат запроса
		//$execute=true;
		$cache_time=0;
		if(!$execute){
			$cache_time=filemtime(ROOT.$path);
		}
		$timer+=$cache_time;
		$timerwait=($timer>time());
		//$timerwait=false;
		if(!$timerwait){
			$mark_time=$cache_time;//Если ни одной метки не будет кэш на всегда
			$global_size=0;
			if(!$execute){
				for($i=0,$l=sizeof($marks);$i<$l;$i++){
					$mark=$marks[$i];
					$mark=infra_theme($mark,'fd');
					if($mark){
						if(is_dir(ROOT.$mark)){
							foreach (glob(ROOT.$mark.'*.*') as $filename) {
								$m=filemtime($filename);
								$global_size+=filesize($filename);
								if($m>$mark_time){
									$mark_time=$m;
								}
							}
						}else if(is_file(ROOT.$mark)){
							$m=filemtime(ROOT.$mark);
							$global_size+=filesize(ROOT.$mark);
							if($m>$mark_time){
								$mark_time=$m;
							}
						}
						
						if(!$execute){//Метка версии файла для которого обновление сделано есть
							if($cache_time<$mark_time){
								$execute=true;
							}
						}
					}else{//Если переданной метки-файла не существует
						if(is_int($marks[$i])){//Кэш на сколько-то часов
							$h=time();
							$ch=$cache_time+60*60*$marks[$i];//time(date('H')+$marks[$i], date('j'), date('s'), date('m'), 1, date('Y'));
							if($ch<=$h)$execute=true;
						}
						//die('Метка не найдена '.$mark);
						//$execute=true;
						//$isload=true; ничего не загружаем
						//return false;
					}
				}
			}
		}
		if(!$execute){
			$alldata=infra_plugin($path,'fp');
			$data=$alldata['data'];
			if(!$timerwait){
				if($alldata['global_size']!=$global_size){//  !$data['global_size'] что бы это могло значить
					$execute=true;
				}
			}
		}
		if($execute){
			$data=call_user_func_array($fn,$arg);
			$cache=array('data'=>$data,'global_size'=>$global_size);
			$cache=infra_tojs($cache);
			file_put_contents(ROOT.$path,$cache);
		}
		$_pages_cache_results[$path]=$data;
		return $data;
	}
