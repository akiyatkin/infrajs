<?php
	/*
	Copyright 2008 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
	
	ready for include
	history
	02.04.2010
	Добавлен obj=2 для возвращения массивом, а не объектом.. но в свойстве obj
	18.04.2010
	Добавлено sort. Добавлено свойство length когда obj
	25.04.2010
	Добавлено кэширование modified
	02.05.2010
	Добавлен параметр lim
	14.05.2010 Добавлена сортировка в обратном порядке reverse
	29.05.2010 Добавлена обработка точки после цифры
	06.12.2012 Не Добавлен order 
	
	*/
	
	
	
	/*
	s - возвращать размер файла
	f - файлы
	d - дирректории
	h - показывать скрытые файлы, только если вызво идёт из php файла есть переменная $FROM_PHP
	e - список необходимых расширений
	sub - заходить ли во вложенные папки
	time - время последнего изменения файла
	src - путь от корня.. включает corе...
	lim - от какого, сколько
	onlyname - определяет нужно ли выделять расширение файла
	notsort - возвращать без отсеения цифр
	obj - возвращать объектом или массивом
	random - перемешивать каждый раз
	reverse - вернуть в обратном порядке
	sort - size,name,time 
	realname- не убирать цифры из имени 
	preview - возвращать данные для списка новостей, блога 
	debug - print_r
	*/
	//echo "/*папки, файлы, раcширения через запятую, путь ?s=0&h=0&d=0&f=1&e=0&src=path&onlyname=0&random=0*/\n";
	//obj=0
	//exit делать нельзя
	if(isset($_GET['preview'])){
		if(!isset($_GET['onlyname']))$_GET['onlyname']=1;
		$_GET['onlyname']=1;
	}
	if(!function_exists('runfolder')){
		function runfolder($dir,$hidden,$sub,$f,$d,&$filelist=array(),$pre=''){
			if (is_dir(ROOT.$dir)&&$dh = opendir(ROOT.$dir)) {
				while (($file = readdir($dh)) !== false) {
					if(!$hidden&&$file[0]=='.')continue;
					if($file[0]=='~')continue;
					if($file=='Thumbs.db')continue;
					
					//$count++;
					//if($count<$lims)continue;
					//if($count>=($lims+$limc))break;
					$path=$dir.$file;


					if(!$f && is_file(ROOT.$path)&&(!$d||!is_dir(ROOT.$path)))continue;//Файлы не надо


					
					//if(!$f && is_file(ROOT.$path))continue;//Файлы не надо
					if(is_dir(ROOT.$path)){
						if($sub)runfolder($path.'/',$hidden,$sub,$f,$d,$filelist,$pre.infra_toutf($file).'/');
						if(!$d)continue;//Папки не надо
					}
					if($d&&preg_match("/\.files$/",$file))continue;
					//$weblife->modified(false,$path);
					$file=infra_toutf($file);
					array_push($filelist,$pre.$file);
				}
				closedir($dh);
			}
			return $filelist;
		}
	}

	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');

	$images=array();
	$src=$_GET['src'];
	$src=infra_theme($src,'dn');
	$d=_infra_src($src);

	if($src){
		$dir=$src;
		if(!preg_match("/[\/\*]$/",$dir)){
			die('{msg:"list.php - адреса до папок должны заканчиваться на слэш / или *","result":0}');
		}
		//Если требуемая папка содержит в своём реальном адресе(без ../) путь до папки infra то можно иначен $ican будет false
		$ican=(strstr(realpath(ROOT.$dir),realpath(ROOT.'infra'))!==false);
		/*$ican=$ican1||$ican2;
		//if(!infra_admin()){
			$ican2=(strstr(realpath(ROOT.$dir),realpath(ROOT.'infra/')));
			$ican=$ican1||$ican2;
		//}*/
		if($ican){
			if(isset($_GET['e'])&&$_GET['e']){//Какие нужны расширения//Делятся запятой
				$e=preg_split('/,/',$_GET['e']);
			}else{
				$e=false;
			}
			if(isset($_GET['time'])&&$_GET['time']){//Какие нужны расширения//Делятся запятой
				$time=(bool)$_GET['time'];
			}else{
				$time=false;
			}
			if(isset($_GET['obj'])){//Вернуть результат объектом, если возможно
				$isobj=(int)$_GET['obj'];
			}else{
				$isobj=false;
			}
			if(isset($_GET['f'])){//Файлы нужны?
				$f=(boolean)$_GET['f'];
			}else{
				$f=true;
			}
			
			if(isset($_GET['sub'])){//Заходить во вложенные папки?
				$sub=(bool)$_GET['sub'];
			}else{
				$sub=false;
			}
			if($FROM_PHP&&@$_GET['h']){
				$hidden=1;
			}else{
				$hidden=0;
			}
			if(isset($_GET['d'])){//папки нужны?
				$d=(boolean)$_GET['d'];
			}else{
				$d=false;
			}
			
			if(isset($_GET['s'])){
				$s=(bool)$_GET['s'];
			}else{
				$s=false;
			}
			$onlyname=@$_GET['onlyname'];
			//if(!$f&&$onlyname)$onlyname=1;//Расширение отбрасываем только у файлов
			
			
			if(@!$_GET['lim'])$_GET['lim']='0,1000';
			$lim=explode(',',$_GET['lim']);
			$lims=(int)$lim[0];
			$limc=(int)$lim[1];
			$unick=array();//Только уникальные

			$filelist=runfolder($dir,$hidden,$sub,$f,$d);
			
			$sort=@$_GET['sort'];
			if($sort){
				if(!function_exists('pages_list_sort')){
					function pages_list_sort($a,$b){
						$a=$a['val'];
						$b=$b['val'];

						if($a==$b)return 0;
						return $a < $b ? +1 : -1;

					}
				}
				$sorted=array();
				for($i=0,$l=sizeof($filelist);$i<$l;$i++){
					$name=$filelist[$i];
					$d=array('name'=>$name);
					if($sort=='name')      $val=$filelist[$i];
					else if($sort=='size') $val=filesize(ROOT.$dir.infra_tofs($name));
					else if($sort=='time') $val=filemtime(ROOT.$dir.infra_tofs($name));
					$d['val']=$val;

					$sorted[]=$d;
				}
				usort($sorted,'pages_list_sort');

				$filelist=array();
				for($i=0,$l=sizeof($sorted);$i<$l;$i++){
					$filelist[]=$sorted[$i]['name'];
				}

			}else{
				sort($filelist);
			}
			
			if(@$_GET['reverse']){
				$filelist=array_reverse($filelist);
			}
			
			//$filelist=array_slice($filelist,$lims,$limc);
			//$reali - реальный номер позиции после фильтра
			$reali=0;

			
			
			for($i=0,$l=sizeof($filelist);$i<$l;$i++){
				$real_file=$filelist[$i];
				$path=$dir.infra_tofs($real_file);
				$prefile=dirname($real_file);
				if($prefile!='.')$prefile.='/';
				else $prefile='';
				$fileXXX = preg_replace( '/^.+[\\\\\\/]/', '', $real_file );
				$p=infra_nameinfo($fileXXX);
				$p['f']=is_file(ROOT.$path);
				$p['dir']=$prefile;

				$ar = explode('.',$fileXXX);
				if(sizeof($ar)==2&&!$ar[0]&&$ar[1]){
					$filename = implode('.',$ar);
				}else if(sizeof($ar)>1){//Это может быть папка или файл без расширения, тогда считаем что имя файла есть а расширения нет
					$ext = array_pop($ar);
					$filename = implode('.',$ar);
				}else{
					$filename = array_pop($ar);
					$ext = '';
				}
				/*$p=array(
					'f'=>is_file(ROOT.$path),
					'dir'=>$prefile,
					'name'=>$filename,
					'ext'=>$ext
				);
				 */
					
				if($e&&is_file(ROOT.$path)){
					if(!in_array(strtolower($p['ext']),$e))continue;
				}
				
				if($s&&is_file(ROOT.$path)){
					$p['size']=round(filesize(ROOT.$path)/1000);
				}
				if($time&&is_file(ROOT.$path)){
					$p['time']=filemtime(ROOT.$path);
				}

				if($onlyname==2){//Имя без расширения
					$p=$filename;
				}else if($onlyname==1){
					$p=$real_file;
				}else{
					$p['name']=$filename;
				}

				$fileunick=infra_toutf($dir).$prefile.infra_toutf($fileXXX);
				if(isset($unick[$fileunick]))continue;//Только уникальные
				$unick[$fileunick]=true;
				
				$reali++;
				if($reali<$lims)continue;
				array_push($images,$p);
				if($reali>=($lims+$limc))break;
			}
		
			if(@$_GET['sub']&&@!$_GET['onlyname']){
				/*if(!function_exists('sortmegasub')){ хз как это работает и зачем это
					function sortmegasub($a,$b){
						return ($a['name'] > $b['name']) ? +1 : -1;
					}
				}
				usort($images,'sortmegasub');*/
			}
			//if($_GET['sort']){

			if(@$_GET['random']){
				shuffle($images); 
			}else if(@!$_GET['notsort']){
				
				if($onlyname==2){//[0=>имя,1=>имя]
					
					foreach($images as $kk=>$vv){
						
						if(!@$_GET['realname'])$images[$kk]=preg_replace("/^\d+\s+/",'',$vv);
						
						if(!$hidden&&$images[$kk][0]=='.'){
							unset($images[$kk]);
						}
					}
					if(is_array($images)){
						$images=array_values($images);
					}
				}else if(!$onlyname){//[0=>{name:,ext},1=>имя]
					for($k=0;$k<sizeof($images);$k++){
						if(!$_GET['realname']){
							$images[$k]['name']=preg_replace("/^\d+\s+/",'',$images[$k]['name']);
							$images[$k]['dir']=preg_replace("/^\d+\s+/",'',$images[$k]['dir']);
							//$images[$k]['dir']=preg_replace("/\/\d+\s+/",'/',$images[$k]['dir']);
						}
						if(!$hidden&&$images[$k]['name'][0]=='.'){
							array_splice($images,$k,1);
							$k--;
						}
					}
				}
			}
			
			if($isobj){
				$length=sizeof($images);
				$obj=array();
				if($isobj==2){
					for($i=0;$i<$length;$i++){
						array_push($obj,$images[$i]);
					}
				}else{
					if($onlyname){
						for($i=0;$i<$length;$i++){
							$obj[$images[$i]]=1;
						}
					}else{
						for($i=0;$i<$length;$i++){
							$obj[$images[$i]['name']]=$images[$i];
						}
					}
				}
				$images=array('obj'=>$obj,'length'=>$length);
			}
			
		}
	}
	if(isset($_GET['preview'])){
		$list=array();
		for($i=0,$l=sizeof($images);$i<$l;$i++){
			$path=infra_theme(infra_toutf($src).$images[$i],'fnu');
			$ar = explode('.',$path);
			$ext='';
			if(sizeof($ar)==2&&!$ar[0]&&$ar[1]){
				$filename = implode('.',$ar);
			}else if(sizeof($ar)>1){//Это может быть папка или файл без расширения, тогда считаем что имя файла есть а расширения нет
				$ext = array_pop($ar);
				$filename = implode('.',$ar);
			}else{
				$filename = array_pop($ar);
				$ext = '';
			}

			if($ext=='mht'||$ext=='tpl'||$ext=='html'||$ext=='htm'){
				$s='*pages/mht/mht.php?preview=1&src='.infra_toutf($src).$images[$i];
			}else if($ext=='docx'){
				$s='*pages/docx.php?preview=1&src='.infra_toutf($src).$images[$i];
			}
			$data=infra_loadJSON($s);
			if($data)$list[]=$data;
		}
		$images=$list;
	}
	
	if(@$_GET['debug']){
		echo '<pre>';
		print_r($images);
		exit;
	}
	return infra_echo($images);
?>
