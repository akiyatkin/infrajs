<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	
	$src=urldecode($_GET['src']);



	$src=preg_replace("/\.mht$/",'',$src);
	$src=preg_replace("/\.tpl$/",'',$src);
	$src=preg_replace("/\.html$/",'',$src);
	$src=str_replace("infra/data/",'*',$src);

	$filename=infra_theme($src.'.mht');

	$ftype='mht';
	infra_require('*pages/cache.inc.php');
	if(!$filename){
		$ftype='tpl';
		$filename=infra_theme($src.'.tpl');
		if(!$filename){
			$ftype='html';
			$filename=infra_theme($src.'.html');
		}
		//if(!$filename)$filename=infra_theme($src);
		if($filename){
			$p=infra_srcinfo($filename);
			$ftype=$p['ext'];
		}
	}

	if($filename){
		//@header("Content-Type: text/plain");
		$reparse=isset($_GET['re']);
		//$reparse=true;//debug
		$conf=infra_config();
		if($conf['files']&&$conf['files']['imgmaxwidth']){
			$imgmaxwidth=$conf['files']['imgmaxwidth'];
		}
		if(!$imgmaxwidth)$imgmaxwidth=1000;
		$imgmaxwidth=(int)$imgmaxwidth;

		$previewlen=150;
		if(@$conf['mht'])$previewlen=@$conf['mht']['previewlen'];
		else if(@$conf['pages'])$previewlen=$conf['pages']['previewlen'];
		if(!$previewlen)$previewlen=150;

		$args=array($filename,isset($_GET['preview']),$ftype,$imgmaxwidth,$previewlen);


		$data=infra_cache(array($filename),'mhtparse',function ($filename,$what,$ftype,$imgmaxwidth,$previewlen){
			$data=file_get_contents(ROOT.$filename);
			$p=explode("/",$filename);
			$fname=array_pop($p);
			$fnameext=$fname;
			//$fname=basename($filename);
			

			preg_match("/^(\d*)/",$fname,$match);
			$date=$match[0];
			$fname=infra_toutf(preg_replace('/^\d*\s+/','',$fname));
			$fname=preg_replace('/\.\w{0,4}$/','',$fname);
			if($ftype=='mht'){
				$ar=preg_split("/------=_NextPart_.*/",$data);
				if(sizeof($ar)>1){//На первом месте идёт информация о ворде... 
					unset($ar[0]);
					unset($ar[sizeof($ar)-1]);
				}
				$ar=array_values($ar);
				@mkdir(ROOT.'infra/cache/pages_mht/',0755);
				$folder='infra/cache/pages_mht/'.preg_replace('/[\/\\\.]/','_',$filename).'/';
				@mkdir(ROOT.$folder,0755);
				$html='';
				for($i=0,$l=sizeof($ar);$i<$l;$i++){
					if(!$ar[$i])continue;
					$d=preg_split("/\n/",$ar[$i],6);

					$j=-1;
					do{
						$j++;
					}while(@$d[$j][0]!=='C'&&$j<=5);
					
					if($j>=5){
						/*
							не нашли
							Content-Location: file:///C:/0FCF1655/file9909.files/header.htm
							Content-Transfer-Encoding: quoted-printable
							Content-Type: text/html; charset="us-ascii"
						*/
						continue; 
					}

					$location=preg_replace("/Content-Location: /",'',$d[$j]);
					$location=trim($location);
					$encoding=preg_replace("/Content-Transfer-Encoding: /",'',$d[$j+1]);
					$type=preg_replace("/Content-Type: /",'',$d[$j+2]);
					$content=$d[5];
					$name=basename($location);
					if(preg_match("/text\/html/",$type)||preg_match('/Subject:/',$type)){
						$html.=$content;
					}else{
						
						@file_put_contents(ROOT.$folder.$name,base64_decode($content));//Сохраняем картинку или тп...
					}
					
				}
			}else{
				$html=$data;
			}
			if(!$html)$html='';
			$html=preg_replace("/=\r\n/",'',$html);
			$html=preg_replace("/\s+/",' ',$html);
			$html=preg_replace("/^.*<body .*>\s*/U",'',$html,1);
			$html=preg_replace("/\s*<\/body>.*/",'',$html,1);

			//$html=preg_replace("/<v:shapetype.*<\/v:shapetype>/U",'',$html,1);
			//$html=preg_replace('/<v:shape.*(src=.*"\s).*<\/v:shape>/U','<img ${1}>',$html,1);

			$images=array();
			if($ftype=='mht'){
				preg_match_all('/src=3D".*\.files\/(image.+)"/U',$html,$match);
				for($i=0,$l=sizeof($match[1]);$i<$l;$i=$i+2){
					/*$r1=filesize(ROOT.$folder.$match[1][$i]);
					$r2=filesize(ROOT.$folder.$match[1][$i+1]);
					if($r1>$r2){
						$images[$match[1][$i+1]]=$match[1][$i+1];
						echo infra_toutf($folder.$match[1][$i]);
						exit;
					}else{*/
						$images[$match[1][$i+1]]=$match[1][$i];//Каждая следующая картинка есть уменьшенная копия предыдущей оригинального размера
					//}
				}
			}else{
				preg_match_all('/<img.*src=["\'](.*)["\'].*>/U',$html,$match);

				for($i=0,$l=sizeof($match[1]);$i<$l;$i=$i+2){
					$images[]=$match[1][$i];
				}
			}

			$html=preg_replace("/<\!--.*-->/U",'',$html);
			
			$html=preg_replace("/<!\[if !vml\]>/",'',$html);
			$html=preg_replace("/<!\[endif\]>/",'',$html);
			
		
			$html=preg_replace("/=3D/",'=',$html);
			
			
			
			$html=preg_replace('/align="right"/','align="right" class="right"',$html);
			$html=preg_replace('/align="left"/','align="left" class="left"',$html);
			$html=preg_replace('/align=right/','align="right" class="right"',$html);
			$html=preg_replace('/align=left/','align="left" class="left"',$html);
			
			//$html=preg_replace("/<span.*class=.*Spell.*>(.*)<\/span>/U",'${1}',$html);
			if($ftype=='mht'){//Была ошибка когда файловая система utf8 далее в html который ещё в cp1251 подставлялся путь в utf8 и когда весь html конвертировался путь рушился
				$html=infra_toutf($html);
			}
			if($ftype=='mht'){
				$folder=infra_toutf($folder);
				$html=preg_replace('/ src=".*\/(.*)"/U',' src="'.$folder.'${1}"',$html);
			}
			//$html=preg_replace("/<span.*>/U",'',$html);
			//$html=preg_replace("/<\/span>/U",'',$html);
			//$html=preg_replace("/<div.*>/U",'',$html);
			//$html=preg_replace("/<\/div>/U",'',$html);
			
			//$html=preg_replace("/ class=MsoNormal/",'',$html);
			
			

			$html=preg_replace('/<span class=SpellE>(.*)<\/span>/U','${1}',$html);
			$html=preg_replace('/<span lang=.*>(.*)<\/span>/U','${1}',$html);
			$html=preg_replace('/<span class=GramE>(.*)<\/span>/U','${1}',$html);
			$html=preg_replace("/<span style='mso.*>(.*)<\/span>/U",'${1}',$html);
			$html=preg_replace("/<span style='mso.*>(.*)<\/span>/U",'${1}',$html);
			$html=preg_replace("/<span style='mso.*>(.*)<\/span>/U",'${1}',$html);
			$html=preg_replace("/<span style='mso.*>(.*)<\/span>/U",'${1}',$html);
			$html=preg_replace('/ class=MsoNormal/U','',$html);
			$html=preg_replace('/<a name="_.*>(.*)<\/a>/U','${1}',$html);
			
			//Приводим к единому виду маркерные списки
			$patern='/<p class=MsoListParagraphCxSp(\w+) .*>(.*)<\/p>/U';
			$count=3;
			do{
				preg_match($patern,$html,$match);
				if(sizeof($match)==$count){
					$pos=strtolower($match[1]);
					$text=$match[2];
					$text=preg_replace('/^.*(<\/span>)+/U','',$text,1);
					$text='<li>'.$text.'</li>';
					if($pos=='first')$text='<ul>'.$text;
					if($pos=='last')$text=$text.'</ul>';
					$html=preg_replace($patern,$text,$html,1);
				}else{
					break;
				}
			}while(sizeof($match)==$count);
			
			
			
			/*if($ftype=='mht'){
				$html=infra_toutf($html);
			}*/
			$patern="/###cut###/U";
			$d=preg_split($patern,$html);
			if(sizeof($d)>1){
				$html=preg_replace($patern,'',$html);
				$preview=$d[0]; 
			}else{
				$temphtml=strip_tags($html,'<p>');
				//preg_match('/^(<p.*>.{'.$previewlen.'}.*<\/p>)/U',$temphtml,$match);
				preg_match('/(<p.*>.{1}.*<\/p>)/U',$temphtml,$match);
				if(sizeof($match)>1){
					$preview=$match[1];
				}else{
					$preview=$html;
				}


				/*$temphtml=strip_tags($html,'<p>');
				preg_match('/(<p.*>.{'.$previewlen.'}.*<\/p>)/U',$temphtml,$match);
				if(sizeof($match)>1){
					$preview=$match[1];
				}else{
					$preview=$html;
				}*/
			}
			$preview=preg_replace('/<h1.*<\/h1>/U','',$preview);
			$preview=preg_replace("/<img.*>/U",'',$preview);
			$preview=preg_replace('/<p.*>\s*<\/p>/iU','',$preview);
			preg_match('/<img.*src=["\'](.*)["\'].*>/U',$html,$match);
			if($match&&$match[1]){
				$img=$match[1];
			}else{
				$img=false;
			}

			/*preg_match('/<h1.*>(.*)<\/h1>/U',$html,$match);
			if($match&&$match[1]){
				//$title=trim($match[1]);
				$title=trim(strip_tags($match[1]));
			}else{
				$title=false;
			}*/
			$title=$fname;
			
			
			
			if($ftype=='mht'){	
				$patern='/<img(.*)>/U';
				$count=2;
				do{
					preg_match($patern,$html,$match);
					if(sizeof($match)==$count){
						$sfind=$match[1];
						//$sfind='<img src="/image.asdf">';
						preg_match("/width=(\d*)/",$sfind,$match2);

						$w=trim($match2[1]);
						preg_match("/height=(\d*)/",$sfind,$match2);
						$h=trim($match2[1]);



						if(!$w||$w>$imgmaxwidth)$w=$imgmaxwidth;
						
						preg_match('/src="(.*\/)(image.*)"/U',$sfind,$match2);
						$path=trim($match2[1]);
						$small=$match2[2];
						
						
						
						preg_match('/alt="(.*)".*/U',$sfind,$match2);
						$alt=trim(@$match2[1]);
						$alt=html_entity_decode($alt, ENT_QUOTES, "utf-8");
						
						preg_match('/align="(.*)".*/U',$sfind,$match2);
						$align=trim($match2[1]);
						$align=html_entity_decode($align, ENT_QUOTES, "utf-8");
			
						$big=$images[$small];
						if(!$big)$big=$small;

						$isbig=preg_match('/#/',$alt);
						if($isbig){
							$alt=preg_replace('/#/','',$alt);
						}
						//$i="<IMG title='$alt' src='infra/plugins/imager/imager.php?w=$w&h=$h&src=".($path.$big)."' align='$align' class='$align' alt='$alt'>";
						$i="<IMG src='infra/plugins/imager/imager.php?w=$w&h=$h&src=".($path.$big)."' align='$align' class='$align'>";
						//urlencode решает проблему с ie7 когда иллюстрации с адресом содержащим пробел не показываются
						if($isbig){
							$i="<a target='about:blank' href='infra/plugins/imager/imager.php?src=".urlencode($path.$big)."'>$i</a>";
						}
						//$i.='<textarea style="width:500px; height:300px">'.$i.'</textarea>';
						$html=preg_replace($patern,$i,$html,1);
					}else{
						break;
					}
				}while(sizeof($match)==$count);
			}
			/*
			$patern='/<img width=(\d*\s).*height=(\d*\s).*src="(.*\/)(image.*)"(.*)alt="(.*)".*>/U';
			$count=7;
			do{
				preg_match($patern,$html,$match);
				if(sizeof($match)==$count){
					echo $html;
					exit;
					$w=trim($match[1]);
					$h=trim($match[1]);
					$path=$match[3];
					$small=$match[4];
					$align=trim($match[5]);
					preg_match('/align="(.*)"/',$align,$some);
					if(sizeof($some)>0){
						$align=$match[1];
					}else{
						$align='';
					}
					$alt=trim($match[6]);
					$alt=html_entity_decode($alt, ENT_QUOTES, "utf-8");
					$big=$images[$small];
					$isbig=preg_match('/#/',$alt);
					if($isbig){
						$alt=preg_replace('/#/','',$alt);
					}
					
					$i="<img title='$alt' src='infra/plugins/imager/imager.php?w=$w&h=$h&src=$path$big' align='$align' class='$align' alt='$alt'>";
					if($isbig){
						$i="<a target='about:blank' href='infra/plugins/imager/imager.php?src=$path$big'>$i</a>";
					}
					//$i.='<textarea style="width:500px; height:300px">'.$i.'</textarea>';
					$html=preg_replace($patern,$i,$html,1);
				}else{
					break;
				}
			}while(sizeof($match)==$count);*/
		
			
			$patern="/###\{(.*)\}###/U";//js код
			do{
				preg_match($patern,$html,$match);
				
				if(sizeof($match)>0){
					$param=$match[1];
					$param=strip_tags($param);
					$param=html_entity_decode($param, ENT_QUOTES, "utf-8");
					$param=preg_replace('/(‘|’)/',"'",$param);
					$param=preg_replace('/(“|«|»|”)/','"',$param);
					$html=preg_replace($patern,$param,$html,1);
					
				}else{
					break;
				}
			}while(sizeof($match)>1);
			
			
			
			$patern="/####.*<table.*>(.*)<\/table>.*####/U";
			do{
				preg_match($patern,$html,$match);
				if(sizeof($match)>0){
					$param=$match[1];
					$param=preg_replace('/style=".*"/U','',$param);
					$param=preg_replace("/style='.*'/U",'',$param);
					$html=preg_replace($patern,'<table cellspacing="0" cellpadding="0" class="common">'.$param.'</table>',$html,1);
				}else{
					break;
				}
			}while(sizeof($match)>1);
			
			
			
			do{
				preg_match("/###(.*)###/U",$html,$match);
				if(sizeof($match)>1){
					
					$param=$match[1];
					$param=strip_tags($param);
					$param=html_entity_decode($param, ENT_QUOTES, "utf-8");
					$param=preg_split('/#/',$param);
					for($i=0,$l=sizeof($param);$i<$l;$i++){
						$param[$i]=trim($param[$i]);
					}
					$name=$param[0];
					$qp=http_build_query($param,'p','&');
					$inset=infra_loatTEXT('*pages/insets/'.$name.'.php?type=html&'.$qp);
					if($inset){
						$html=preg_replace("/###(.*)###/U",$inset,$html,1);
					}else{
						$html=preg_replace("/###(.*)###/U",'',$html,1);
					}
					
					
				}else{
					break;
				}
			}while(sizeof($match)>1);
			
			
			
			$r=preg_match('/<h.*>(.*)<\/h.>/U',$html,$match);
			if($r)$heading=strip_tags($match[1]);
			else $heading=false;

			preg_match_all('/<a.*href="(.*)".*>(.*)<\/a>/U',$html,$match);
			$links=array();
			foreach($match[1] as $k=>$v){
				$title=strip_tags($match[2][$k]);
				if(!$title)continue;
				$links[]=array('title'=>$title,'href'=>$match[1][$k]);
			}

			do{
				preg_match("/(<a.*)title=.##(.*)##.(.*>)/U",$html,$match);
				if(sizeof($match)>1){
					$param=$match[2];
					$param=strip_tags($param);
					$param=html_entity_decode($param, ENT_QUOTES, "utf-8");
					$param=preg_split('/#/',$param);
					for($i=0,$l=sizeof($param);$i<$l;$i++){
						$param[$i]=trim($param[$i]);
					}
					$name=$param[0];
					$qp=http_build_query($param,'p','&');
					$inset=infra_loadTEXT('*pages/insets/'.$name.'.php?type=title&'.$qp);
					if($inset){
						$html=preg_replace("/<a.*title=.##.*##.*>/U",$match[1].$inset.$match[3],$html,1);
					}else{
						$html=preg_replace("/##.*##/U",'',$html,1);
					}
				}else{
					break;
				}
			}while(sizeof($match)>1);

			
			//$html=preg_replace("/\s+/",' ',$html); С этим появляется какой-то левый символ и всё падает... глюкс был

			$preview=preg_replace("/\s+/",' ',$preview);
			
			
			
			$preview=trim($preview);
			$img=trim($img);
			$title=trim($title);
			$html=trim($html);
			
			if($what){
				$filetime=filemtime(ROOT.$filename);

				$s=infra_toutf($fnameext);
				$data=infra_nameinfo($s);

				$data['modified']=$filetime;
				if($heading)$data['heading']=$heading;

				//if($param['images'])$data['images']=$param['images'];
				if($links)$data['links']=$links;
				if($data['name'])$data['title']=$data['name'];
				$data['img']='';
				if($ftype=='mht'){
					if($img){
						$data['img']=$img;
						$data['images']=array();
						foreach($images as $v)$data['images'][]=array('src'=>infra_toutf($folder).$v);
					}
				}else{
					if($images){
						$data['images']=array();
						foreach($images as $v)$data['images'][]=array('src'=>$v);
					}
				}
				if($preview)$data['preview']=html_entity_decode($preview,ENT_COMPAT,'UTF-8');

				if($ftype!='mht'&&$data['img']){
					$data['img']=preg_replace('/^.*theme\.php\?/','',$data['img']);
					$data['img']=preg_replace('/^.*src=/','',$data['img']);//сбросили imager/ Все атрибуты должны быть вначале а src указан в конце всех параметров
					$data['img']=preg_replace('/&.*$/','',$data['img']);//сбросили imager/ Все атрибуты должны быть вначале а src указан в конце всех параметров
				}
			}else{
				$data=html_entity_decode($html,ENT_COMPAT,'UTF-8');
			}

			return $data;
		}, $args,$reparse);//4 параметр означает установить новое значение
		//$data=pages_cache(array($filename,__FILE__),'mhtparse',array($filename,isset($_GET['preview']),$type),$reparse);//Если нет папки то выполнять в любом случае. Иначе согласно были ли изменения $filename
		
		if(isset($_GET['preview'])){
			
			return infra_echo($data);
		}else{
			echo $data;
		}
		
	}else{
		//@header("HTTP/1.0 404 Not Found");
	}
?>
