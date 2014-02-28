<?php
/*
Copyright 2008-2010 ITLife, Ltd. http://itlife-studio.ru

Функции
	infra_theme($src,$set);//f,u,s,h,n,d (f) 
	infra_plugin($src,$set);//f,s,h,n,p (f)
	infra_require($src,$set);//f,s,n (f,n)
	infra_load

f - file
d - dir
h - host
u - utf8
s - secure
p - tophp
r - загрузить код require
e - exec- не использовать кэш 
m - make - создаются все папки по этом адресу вплоть до последней. Файл не создаётся. с этим ключём infra_theme не возвращает false. fatal error если что-то впринципе не получилось 
n - find number - в адресе могут быть сортировочные цифры в имени файла, будут искать совпадения файлов без расширения 

*/
@define('ROOT','../../../../');
global $infra_themes;
$infra_themes=@file_get_contents(ROOT.'infra/data/themes.js');
if($infra_themes)$infra_themes=infra_tophp($infra_themes);
if(!$infra_themes)$infra_themes=array();
else $infra_themes=$infra_themes['themes'];

global $infra_src_cache;
$infra_src_cache=array();
function _infra_src($orig){//Возвращает список адресов которые нужно проверить
	global $infra_src_cache;
	//Возвращает последний слэш так как было передано... если небыло то и не будет
	if(isset($infra_src_cache[$orig]))return $infra_src_cache[$orig];

	$ans=array();
	$ans['orig']=$orig;
	$ans['path']=false;//Есть всегда

	$src=$orig;

	if(preg_match('/^https?:\/\//',$src)){
		$ans['path']=$src;
		$ans['ishost']=true;
		//$ans['paths'][]=$src;
	}else{
		$src=str_replace('\\','/',$src);
		//$src=preg_replace('/\/+/','/',$src);// TODO когда путь в параметрах передаётся.. да и в любых параметрах может быть двойной слэш который заменять не нужно.

		$ans['paths']=array();
		$ans['ishost']=false;
		$ans['isfindfirst']=0;
		$ans['isfolder']=0;
		$ans['find']=0;

		$src=trim($src);
		$src=infra_tofs($src);

		$real=str_replace('\\','/',realpath(ROOT));
		$src=str_replace($real,'',$src);//попытались привести абсолютный путь к относительному

		$src=preg_replace('/^\/+/','',$src);//Удалили слэш в начале с путями от корня работать не умеем



		if(preg_match('/^(.*)(\?.*)$/',$src,$match)){
			$src=$match[1];
			$ans['query']=$match[2];
		}else{
			$ans['query']='';
		}
		$ans['secure']=preg_match('/\/\./',$src);
		if(!$ans['secure'])$ans['secure']=preg_match('/^\./',$src);
		if(!$ans['secure'])$ans['secure']=preg_match('/\*\./',$src);


		if(preg_match('/\/+$/',$src)){
			$ans['isfolder']=true;
			$src=preg_replace('/\/+$/','',$src);//Удалили последний слэш, временно
		}
		if(preg_match('/\*/',$src)){
			$src=preg_replace('/^\*\/?/','',$src);
			$p=explode('/',$src);

			$name=array_pop($p);//Может быть пустой строкой

			$plugin=array_shift($p);
			if($plugin)$plugin.='/';

			$folder=implode('/',$p);
			if($folder)$folder.='/';
			$file=$folder.$name;//Есть обязательно

			if($file){
				if($ans['isfolder'])$file=$file.'/';
			}

			global $infra_themes;
			$themes=$infra_themes;
			/*1*/$ans['path']='infra/data/'.$plugin.$file;

			//if($themes){
			//	foreach($themes as $theme){//Тема локального плагина
			//		$ans['paths'][]='core/layers/plugins/'.$plugin.'themes/'.$theme.'/'.$file;
				//}
		/*		foreach($themes as $theme){//Локальная тема
					$ans['paths'][]='core/layers/themes/'.$theme.'/'.$plugin.$file;
				}
			}*/

			/*!!!!!*/$ans['paths'][]='infra/data/'.$plugin.$file;
			/*!!!!!2*/$ans['paths'][]='infra/layers/'.$plugin.$file;

			//$ans['paths'][]='core/layers/plugins/'.$plugin.$file;//Локальный плагин
			//if($themes){
				/*foreach($themes as $theme){//Тема глобального плагина
					//3/$ans['paths'][]='core/plugins/'.$plugin.'themes/'.$theme.'/'.$file;
				}*/
			//}

			//$ans['paths'][]='core/'.$plugin.$file;//Прям в core тоже можно .. для папки infra и для layers.js

			/*5*/$ans['paths'][]='infra/plugins/'.$plugin.$file;

			//$ans['paths'][]='core/lib/'.$plugin.$file;
			$ans['find']=$plugin.$file;//Этот путь можно вывести со звёздочкой и по нему опять будет файл найден при необходимости.
			if($ans['isfolder'])$ans['find'].='/';

		}else{//Если нет звёздочки
			if($ans['isfolder'])$src=$src.'/';
			$ans['path']=$src;
			if(preg_match('/^infra\/data\//',$src)){
				$ans['find']=preg_replace('/^infra\/data\//','',$src);
			}
		}
		if(!$ans['query']){
			$ans['isfindfirst']=(bool)!preg_match('/\.\w{1,4}/',$src);//Если нет расширения значит нужно искать в первую очередь
		}

	}
	return $ans;
}
	global $_infra_plugins_list;
function infra_loadTEXT($src){
	return infra_load($src,'f');
}
function infra_loadJSON($src){
	return infra_load($src,'fj');
}
function infra_require($src,$set='f'){
	global $_infra_plugins_list;
	if(isset($_infra_plugins_list[$plugin.$set]))return $_infra_plugins_list[$plugin.$set];
	$plugin=infra_theme($src,$set);//Проверка на есть ли такой файл и определение пути до файла
	if(!$plugin){
		echo infra_tojs(array('result'=>1,'msg'=>'Не найден infra_require '.$src));
		exit;
	}
	$_infra_plugins_list[$plugin.$set]=require_once(ROOT.$plugin);
	return $_infra_plugins_list[$plugin.$set];
}

function infra_srcinfo($src){
	$src=preg_replace("/^\*/",'*/',$src);
	$src=preg_replace("/^\*\/+/",'*/',$src);
	$r=explode('/',$src);
	$name=array_pop($r);
	$isfolder=false;
	if(!$name){
		$isfolder=true;
		$name=array_pop($r);
	}
	$ans=infra_nameinfo($name);
	$ans['isfolder']=$isfolder;
	$ans['src']=$src;
	$ans['folder']=implode('/',$r).'/';
	return $ans;
}
function infra_nameinfo($file){
	if($file=='*'){
		$id=false;
		$name='*';
		$file='*';
		$date=false;
		$ext=false;
	}else{
		$file=infra_toutf($file);
		$p=explode('.',$file);
		if($p>1){
			$ext=array_pop($p);
			$name=implode('.',$p);
			if(!$name){
				$name=$file;
				$ext='';
			}
		}else{
			$ext='';
			$name=$file;
		}
		preg_match("/^(\d{6})[\s\.]/",$name,$match);
		$date=$match[1];
		$name=preg_replace("/^\d+[\s\.]/",'',$name);
		$ar=explode("@",$name);
		if(sizeof($ar)>1){
			$id=array_pop($ar);
			if(!$id)$id=0;
			$idi=(int)$id;
			$idi=(string)$idi;//12 = '12 asdf' а если и то и то строка '12'!='12 asdf'
			if($id==$idi){
				$name=implode('@',$ar);
			}else{
				$id=false;
			}
		}else{
			$ar=preg_split("/[\s\.]/",$name);
			if(sizeof($ar)>1){
				$id=array_pop($ar);
				$idi=(int)$id;
				$idi=(string)$idi;//12 = '12 asdf' а если и то и то строка '12'!='12 asdf'
				if($id==$idi){
					//$name=implode('.',$ar);
					$name=preg_replace("/[\s\.]\d+$/",'',$name);
				}else{
					$id=false;
				}
			}else{
				$id=false;
			}
		}
	}
	$ans=array(
		'id'=>$id,
		'name'=>trim($name),
		'file'=>$file,
		'date'=>$date,
		'ext'=>$ext
	);
	return $ans;
}
function _infra_sortfile($src1,$setf,$setd){//starpath infra/data
	$p=explode('/',$src1);
	//$srcext=preg_match('/\.\w{0,4}$/',$src1);//Расширение при поиске не учитываем
	//$src='infra/data';
	$src='';
	//$src=preg_replace('/\/$/','',$starpath);
	

	for($i=0,$l=sizeof($p);$i<$l;$i++){
		$name=$p[$i];//Критерий поиска id,name,полное имя файла
		if(!$name)continue;
		//$namer=infra_nameinfo($name);
		
		$namer=mb_strtolower(infra_toutf($name));
		$last=($i==$l-1);
		$res=false;



		if (is_dir(ROOT.$src)&&$dh = opendir(ROOT.$src)) {
			while (($file = readdir($dh)) !== false) {
				if($file=='..'||$file=='.')continue;
				if(($setf&&$last&&is_file(ROOT.$src.'/'.$file))||(($setd||!$last)&&is_dir(ROOT.$src.'/'.$file))){
					$r=infra_nameinfo($file);
					if($namer==mb_strtolower($r['name'])
						||$namer==$r['id']
						||$namer==mb_strtolower($r['name']).'.'.mb_strtolower($r['ext'])//Это надо чтобы определённое расширение взялось
						||$namer==mb_strtolower($r['file'])){
							$src.='/'.$file;
							$res=true;
							break;
					}

					/*$nfile=preg_replace('/^\d+\s+/','',$file);
					//$nfile=preg_replace('/^\d+[\s\.]+/','',$file);
					if($last)$oefile=preg_replace('/\.\w{0,4}$/','',$nfile);//Расширение при поиске не учитываем
					// проблема двух расширений name.ext1.ext2 мы не знаем может у реального файла есть и ext3 и при этом что взять если есть и то и другое

					$nfile=mb_strtolower(infra_toutf($nfile));
					$name=mb_strtolower(infra_toutf($name));
					$lfile=mb_strtolower(infra_toutf($file));
					$oefile=mb_strtolower(infra_toutf($oefile));

					if($nfile==$name||$lfile==$name||$oefile==$name){
						$src.='/'.$file;
						$res=true;
						break;
					}*/

				}
			}
			closedir($dh);
		}
		if(!$res)return false;
	}
	if(is_dir(ROOT.$src)){
		$src.='/';
	}
	return $src;
}
function infra_theme($src,$set='f'){
	$res=_infra_theme($src,$set);
	if(!$res){
		for($i=0,$l=strlen($set);$i<$l;$i++){
			if($set[$i]=='m'){//setm

				$nset='d';
				for($i=0,$l=strlen($set);$i<$l;$i++){
					if(in_array($set[$i],array('d','m','f')))continue;
					$nset.=$set[$i];
				}

				$psrc=_infra_src($src);
				$path=infra_tofs($psrc['path']);
				$sub=explode("/",$path);
				$res='';
				for($i=0,$l=sizeof($sub);$i<$l;$i++){
					$last=($l==$i+1);

					$name=$sub[$i];
					if(!$name)continue;
					$res.=$name;

					if(!$last){
						$is=_infra_theme($res,$nset);
						if(!$is){
							mkdir(ROOT.$res,0777);//Группа php и группа ftp зачастую разные
							chmod(ROOT.$res,0777);//Для FTP должен быть файл .ftpaccess Umask 000 000
							$res.='/';
						}else{
							$res=$is;
						}
					}
				}
			}
		}
	}
	for($i=0,$l=strlen($set);$i<$l;$i++){
		if($set[$i]=='u')return infra_toutf($res);
	}
	return $res;
}
function _infra_theme($src,$set='f'){
//Функция возвращает корректный путь до файла в нужной теме, без *. путь начинается после адреса ROOT
	//Если путь содержит * но файл не найден возвращается false иначе если файла нет возвращается false или переданный $src
	//Путь возвращается в кодировкe файловой системы.

	//d,f,s,h,u,n
	//$setp=false;
	//$infra_src_cache[$orig]=$ans;
	$setd=false;
	$setf=false;
	$sets=false;
	$seth=false;//depricated
	$setu=false;
	$setn=false;
	for($i=0,$l=strlen($set);$i<$l;$i++){
		$v='set'.$set[$i]; $$v=true;
	}
	$psrc=_infra_src($src);

	$path=$psrc['path'];
	$query=$psrc['query'];


	if($psrc['ishost']){
		if($seth) return $path;
		else return false;
	}
	//if($psrc['secure']&&(!$sets&&!infra_admin()))return false;
	if($psrc['secure']&&!$sets)return false;
	
	//Самая быстрая проверка
	if($setf&&is_file(ROOT.$path))return $path.$query;
	if($setd&&is_dir(ROOT.$path))return $path.$query;




	//Если поиск по цифрам в приоритете ищем
	if($setn&&$psrc['find']&&$psrc['isfindfirst']){//find это значит infra/data подойдёт потому что ищим только там
		foreach($psrc['paths'] as $path){
			$path=_infra_sortfile($path,$setf,$setd);
			if($path){
				if($setf&&is_file(ROOT.$path))return $path.$query;
				if($setd&&is_dir(ROOT.$path))return $path.'/'.$query;//Если path это папка слэш у неё обязан уже быть
				//if($path)return false;
			}
		}
	}
	
	//Бежим по всем возможным местам расположения файла... по темам, плагинам и тп...
	foreach($psrc['paths'] as $path){

		if($setf&&is_file(ROOT.$path))return $path.$query;
		if($setd&&is_dir(ROOT.$path))return $path.'/'.$query;
	}

	//Если в поиск по цифрам не в приоритете и ещё не искали ищем
	if($setn&&$psrc['find']&&!$psrc['isfindfirst']){
		foreach($psrc['paths'] as $path){
			$path=_infra_sortfile($path,$setf,$setd);
			if($path){
				if($setf&&is_file(ROOT.$path))return $path.$query;
				if($setd&&is_dir(ROOT.$path))return $path.'/'.$query;
				//if($path)return false;
			}
		}
	}
	return false;
}

function infra_load($path,$set='f',$data=array()){
	if($set=='r')return infra_require($path,$set.'f');
	return infra_plugin($path,$set,$data);
};

global $_infra_plugins_list;
$_infra_plugins_list=array();
function infra_plugin($initial_plugin,$set='f',$data=array()){//Функция шлёт заголовки
	global $_infra_plugins_list;
	//foreach(get_included_files() as $f)$_infra_plugins_list[infra_theme($f,'f').$set]=true;
	/*
		1. Путь http:// или core/.. или *some/any 
		2. Попробовать сообщить плагину что он запускается напрямую, чтобы плагин мог вернуть данные адаптированные для работы с ними из php, скорее всего это будет массив php а не строка json данных
		В плагине можно проверить наличие переменны $FROM_PHP и определить переменную $TO_PHP, впринципе $TO_PHP можно определять всегда,  Если будет передан параметр $tophp функция вернёт значение переменной $TO_PHP, Но если проверять $FROM_PHP можно оптимизировать и не делать echo tojs, напримре.
	*/
	
	$setp=false;//post
	$setx=false;//Не использовать кэш
	$setd=false;
	$setf=false;
	$sets=false;
	for($i=0,$l=strlen($set);$i<$l;$i++){
		$v='set'.$set[$i];$$v=true;
		if($set[$i]=='u')unset($set[$i]);//модификатор u убираем
	}
	//$setj=true;

	if(!$setx&&isset($_infra_plugins_list[$initial_plugin.$set]))return $_infra_plugins_list[$initial_plugin.$set];


	$plugin=infra_theme($initial_plugin,$set);//Проверка на есть ли такой файл и определение пути до файла
	$result=false;
	if($plugin&&($setf||$seth)){//Если plugin был получен значит поверку usf он прошёл..
		if($seth&&preg_match('/^https?:\/\//',$plugin)){//u
			$result=_infra_geturl($plugin);
			if($setj){
				$result=infra_tophp($result);
			}
		}else if(preg_match('/\.njs$/',$plugin)||preg_match('/\.njs\?/',$plugin)){
			$root=infra_load('*httproot.json','fj');
			$root=$root['root'];
			$result=_infra_geturl('http://'.$root['host'].$root['root'].$plugin);
			if($setj){
				$result=infra_tophp($result);
			}
		}else if(preg_match('/\.php$/',$plugin)||preg_match('/\.php\?/',$plugin)){//f Если кто-то сможет в data создать php файл это самособой будет смертельно, надеюсь не получится

			$r=preg_split('/\?/',$plugin,2);
			$plug=$r[0];
			$getstr=infra_toutf($r[1]);//get параметры в utf8
			parse_str($getstr,$get);
			if(!$get)$get=array();

			if($setp)$post=$data;
			else $post=array();


			if (get_magic_quotes_gpc()){
				$get = array_map('stripslashes_deep', $get);
			}
			
			/*foreach($GLOBALS as $k=>&$v){
				if($k[0]=='_'||$k=='GLOBALS')continue;
				global $$k;
			}*/
			//unset($v);
		


			$GET=$_GET;
			$_GET=$get;

			$POST=$_POST;
			$_POST=$post;
			
			$REQUEST=$_REQUEST;
			$_REQUEST=array_merge($_GET, $_POST, $_COOKIE);

			$SERVER_QUERY_STRING=$_SERVER['QUERY_STRING'];
			$_SERVER['QUERY_STRING']=$getstr;
			global $FROM_PHP;
			$FROM_PHP_OLD=$FROM_PHP;
			$FROM_PHP=true;

			ob_start();
			$rrr=include(ROOT.$plug);
			$result=ob_get_contents();
			$resecho=$result;
			ob_end_clean();
			$FROM_PHP=$FROM_PHP_OLD;

			//if($rrr&&$rrr!==1&&!is_string($rrr)){//в include небыло return.. он просто выполнился и всё
			if($rrr!==1&&!is_null($rrr)){//в include небыло return.. он просто выполнился и всё
				$result=$rrr;
				if(!$setj) $result=infra_tojs($result);
				if($resecho)$result=$resecho.$result;
			}else{
				if($setj) $result=infra_tophp($result);
			}
			
			$_SERVER['QUERY_STRING']=$SERVER_QUERY_STRING;
			$_REQUEST=&$REQUEST;
			$_GET=&$GET;
		}else{//А тут можно посмотреть какой-нить секретный файл, но в функции theme отфилтрованы пути выше core и файлы содержащие точку. Остальное палится
			$result=file_get_contents(ROOT.$plugin);
			if($setj){
				$result=infra_tophp($result);
			}
		}
	}
	
	$_infra_plugins_list[$initial_plugin.$set]=$result;
	return $result;
}

function _infra_geturl($url,$set=''){//thanks http://petewarden.typepad.com/searchbrowser/2008/06/how-to-post-an.html
	$url=infra_toutf($url);
	$parts=parse_url($url);
	$host=isset($parts['host'])?$parts['host']:$_SERVER['HTTP_HOST'];
	$port=isset($parts['port'])?$parts['port']:80;
	$path=$parts['path'].'?'.$parts['query'];
	$fp = fsockopen($host,$port,$errno, $errstr);
	if(!$fp)return false;
	$path=preg_replace("/\s/","%20",$path);
	$out = "GET ".$path." HTTP/1.0\r\n";
	$out.= "Host: ".$host."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	$r=fwrite($fp, $out);
	if(!$fp)return '';
	$ans=stream_get_contents($fp);

	$a=preg_split("/\n\r\n/",$ans,2);
	$ans=$a[1];

/*	$a=preg_split("/\n/",$a[0]);
	foreach($a as $v){
		$s=preg_split("/Location:\s/",$v);
		if(sizeof($s)>1){
			return _infra_geturl(infra_toutf($s[1]),$set);
		}
	}*/
	return $ans;
}

?>
