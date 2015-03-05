<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	//$_GET['re']=1;
	if(!function_exists('docx_getTextFromZippedXML')){
		function docx_full_del_dir($directory) {
			if(!$directory)return;
			$dir = @opendir(ROOT.$directory);
			if(!$dir)return;
			while($file = readdir($dir)) {
				if (is_file(ROOT.$directory."/".$file)) {
					unlink(ROOT.$directory."/".$file);
				} elseif (is_dir(ROOT.$directory."/".$file) && $file !== "." && $file !=="..") {
					docx_full_del_dir($directory."/".$file);
				}
			}
			closedir($dir);
			rmdir(ROOT.$directory);
		} 
		function docx_getTextFromZippedXML($archiveFile, $contentFile,$cacheFolder,$debug) {
		    // Создаёт "реинкарнацию" zip-архива...
		    $zip = new ZipArchive;
		    // И пытаемся открыть переданный zip-файл
		    if ($zip->open($archiveFile)) {
				@mkdir(ROOT.$cacheFolder);
				$zip->extractTo(ROOT.$cacheFolder.'/');
			// В случае успеха ищем в архиве файл с данными
			$xml=false;
				$xml2=false;
			$file=$contentFile;
			if (($index = $zip->locateName($file)) !== false) {
			    // Если находим, то читаем его в строку
			    $content = $zip->getFromIndex($index);
			    // После этого подгружаем все entity и по возможности include'ы других файлов
			    // Проглатываем ошибки и предупреждения
			    $xml = @DOMDocument::loadXML($content, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

			}
			$file='word/_rels/document.xml.rels';
			if (($index = $zip->locateName($file)) !== false) {
			    // Если находим, то читаем его в строку
			    $content = $zip->getFromIndex($index);
			    // После этого подгружаем все entity и по возможности include'ы других файлов
			    // Проглатываем ошибки и предупреждения
			    $xml2 = @DOMDocument::loadXML($content, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				//@ - https://bugs.php.net/bug.php?id=41398 Strict Standards:  Non-static method DOMDocument::loadXML() should not be called statically

			}
			    $zip->close();
			    return array($xml,$xml2);
		    }
		    // Если что-то пошло не так, возвращаем пустую строку
		    return "";
		}

		function docx_dom_to_array($root){ 
		    $result = array(); 
		    if ($root->hasAttributes()){ 
			$attrs = $root->attributes; 

			foreach ($attrs as $i => $attr) 
			    $result[$attr->name] = $attr->value; 
		    } 

		    $children = $root->childNodes; 
		    if ($children->length == 1){ 
			$child = $children->item(0); 
			if ($child->nodeType == XML_TEXT_NODE){ 
			    $result['_value'] = $child->nodeValue; 
			    if(count($result)==1)return $result['_value']; 
			    else return $result; 
			} 
		    } 

		    $group = array(); 
		    for($i = 0; $i < $children->length; $i++){ 
			$child = $children->item($i); 
			$name=$child->nodeName;
			if($name=='w:hyperlink'){
				$name='w:r';
				$child->setAttribute('hyperlink','1');
			}
			if($name=='w:tbl'){
				$name='w:p';
				$child->setAttribute('tbl','1');
			}

			if (!isset($result[$name])){
			    $result[$name] = docx_dom_to_array($child); 
			}else{ 
			    if(!isset($group[$name])){ 
				$tmp = $result[$name]; 
				$result[$name] = array($tmp); 
				$group[$name] = 1; 
			    } 
			    $result[$name][] = docx_dom_to_array($child); 
			} 
		    } 

		    return $result; 
		} 
		function docx_each(&$el,$callback,&$param,$key=false){//Бежим в какие узлы для анализа заходим в какие нет
			$tagelnext=array('w:document','w:body');//Проходные без анализа
			$tagel=array();//Узлы в этом массиве должны быть обработаны
			//<br>
			$tagelnext=array_merge($tagelnext,array('w:p','w:r'));
			$tagel=array_merge($tagel,array('w:br'));
			//Картинка
			$tagelnext=array_merge($tagelnext,array('w:r','w:drawing'));
			$tagel=array_merge($tagel,array('wp:anchor','wp:inline','w:pict'));
			//p table h1 h2 h3 h4
			$tagelnext=array_merge($tagelnext,array());
			$tagel=array_merge($tagel,array('w:p'));
			//a
			$tagelnext=array_merge($tagelnext,array());
			$tagel=array_merge($tagel,array('w:r'));
			//b i u
			$tagelnext=array_merge($tagelnext,array());
			$tagel=array_merge($tagel,array('w:p','w:r'));
			//Список как абзац
			$tagelnext=array_merge($tagelnext,array());
			$tagel=array_merge($tagel,array('w:p'));//У списка есть [w:pPr][w:numPr]
			//Текст
			$tagelnext=array_merge($tagelnext,array('w:p','w:r'));
			$tagel=array_merge($tagel,array('w:t'));
			 
			//Таблицы table
			$tagelnext=array_merge($tagelnext,array());
			$tagel=array_merge($tagel,array('w:p','w:tr','w:tc'));
			 
			
			$h='';
			foreach($el as $k=>&$val){
				if(is_integer($k)){
					$h.=docx_each($val,$callback,$param,$key);
				}else if(in_array($k,$tagel)){
					if(is_array($val)&&isset($val[0])){
						foreach($val as $kk=>&$vv){
							$h.=call_user_func_array($callback,array(&$vv,$k,&$param,$key));
						}
					}else{
						$h.=call_user_func_array($callback,array(&$val,$k,&$param,$key));
					}
				}else if(in_array($k,$tagelnext)){
					$h.=docx_each($val,$callback,$param,$k);
				}
			}
			if($key===false){//Специально для </ul>
				$h.=call_user_func_array($callback,array(array(),'',&$param,false));
			}
			return $h;
		}
		function docx_analyse($el,$key,&$param,$keyparent){
			$tag=array('','');
			$isli=false;
			$isheading=false;
			$h='';

			//Таблицы
			if(is_array($el)&&@$el['tbl']=='1'){
				$param['istable']=true;
				$tag=array("<table class='common'>\n",'</table>');
			}else if($key==='w:tr'&&$param['istable']){
				$tag=array("<tr>\n",'</tr>');
			//}else if($key==='w:p'&&$param['istable']){
			}else if($key==='w:tc'&&$param['istable']){
				$tag=array('<td>','</td>');
			}else if($key=='w:pict'&&$el['v:shape']){

				$rid=$el['v:shape']['v:imagedata']['id'];
				$src=$param['folder'].'word/'.$param['rIds'][$rid];
				$style=$el['v:shape']['style'];
				if(preg_match("/:right/",$style)){
					$align='right';
				}else{
					$align='left';
				}
				if(!$param['images'])$param['images']=array();
				$param['images'][]=array('src'=>$src);
				//$tag=array('<img align="'.$align.'" src="'.$src.'">','');
				$tag=array('<div style="background-color:gray; color:white; font-weight:normal; padding:5px; font-size:14px; float:'.$align.'">Некорректно<br>добавленная<br>картинка</div>','');
			//Картинки
			}else if($keyparent==='w:drawing'){

				if(!@$param['imgnum'])$param['imgnum']=0;
				$imgnum=++$param['imgnum'];

				//$origsrc=$el['wp:docPr']['descr'];
				$inline=($key=='wp:inline');
				$align=@$el['wp:positionH']['wp:align'];
				if($align!=='left')$align='right';

				$width=ceil($el['wp:extent']['cx']/8000);
				$height=ceil($el['wp:extent']['cy']/8000);

				if($width>$param['imgmaxwidth']){
					$width=$param['imgmaxwidth'];
					$height='';
				}
				$src=$param['folder'].'word/media/image'.$imgnum;
				if(is_file(ROOT.$src.'.jpeg'))$src.='.jpeg';
				else if(is_file(ROOT.$src.'.jpg'))$src.='.jpg';
				else if(is_file(ROOT.$src.'.png'))$src.='.png';
				else if(is_file(ROOT.$src.'.gif'))$src.='.gif';
				else $src.='.wtf';


				$alt=@$el['wp:docPr']['title'];

				if(!@$param['images'])$param['images']=array();
				$param['images'][]=array('src'=>$src);

				$src='infra/plugins/imager/imager.php?src='.$src;
				if($height)$src.='&h='.$height;
				if($width)$src.='&w='.$width;

				
				$tag='<img src="'.$src.'"';
				
				//if($height)$tag.=' height="'.$height.'px"';
				//if($width)$tag.=' width="'.$width.'px"';
				if($alt)$tag.=' alt="'.$alt.'"';
				if(!$inline&&$align)$tag.=' class="'.$align.'" align="'.$align.'"';

				$tag.='>';
				$tag=array($tag,'');
				
				if(isset($el['wp:docPr'])&&isset($el['wp:docPr']['a:hlinkClick'])){//Ссылка на самой картинке
					$r=$el['wp:docPr']['a:hlinkClick']['id'];
					$link=$param['rIds'][$r];
					$tag[0]='<a href="'.$link.'">'.$tag[0];
					$tag[1]='</a>';
				}
			//Список
			}else if($key==='w:p'&&@$el['w:pPr']&&@$el['w:pPr']['w:numPr']){
				$isli=true;
				$param['isli']=true;
				$v=@$el['w:pPr']['w:numPr']['w:numId'];
				if(@$param['isul']!==$v){
					if(@$param['isul']){
						$h.="</ul>\n";
					}
					$param['isul']=$v;
					$h.="<ul>\n";
					$tag=array('<li>','</li>');
				}else{
					$tag=array('<li>','</li>');
				}
			//h1 h2 h3 h4
			}else if($key==='w:p'&&@$el['rsidR']&&@$el['w:pPr']&&@$el['w:pPr']['w:pStyle']&&in_array(@$el['w:pPr']['w:pStyle']['val'],array(1,2,3,4,5,6))){
				$isheading=true;
				$v=$el['w:pPr']['w:pStyle']['val'];
				$tag=array('<h'.$v.'>','</h'.$v.">\n");
			//Абзац
			}else if($key==='w:p'&&@$el['rsidR']){
				$tag=array('<p>',"</p>\n");
			//a
			}else if($key==='w:r'&&@$el['history']){
				$href=$param['rIds'][$el['id']];
				$tag=array('<a href="'.$href.'">',"</a>");
			//b i u
			}else if($key==='w:r'&&@$el['w:rPr']&&
			       (isset($el['w:rPr']['w:i'])||isset($el['w:rPr']['w:b'])||isset($el['w:rPr']['w:u'])) ){
					if(isset($el['w:rPr']['w:i']))$tag[0].='<i>';
					if(isset($el['w:rPr']['w:b']))$tag[0].='<b>';
					if(isset($el['w:rPr']['w:u']))$tag[0].='<u>';

					if(isset($el['w:rPr']['w:u']))$tag[1].='</u>';
					if(isset($el['w:rPr']['w:b']))$tag[1].='</b>';
					if(isset($el['w:rPr']['w:i']))$tag[1].='</i>';
			//<i>
			}else if($key==='w:r'&&@$el['w:rPr']&&isset($el['w:rPr']['w:i'])){
				$tag=array('<i>','</i>');
			//<b>
			}else if($key==='w:r'&&@$el['w:rPr']&&isset($el['w:rPr']['w:b'])){
				$tag=array('<b>','</b>');
			//<br>
			}else if($key==='w:br'){
				$tag=array('<br>','');
			}

			//Список
			if(@$param['isul']&&!@$param['isli']){//Есть метка что мы в ul и нет что в li
				$param['isul']=false;
				$h.='</ul>';
			}
			


			//=====================
			if($key==='w:t'){//Текст
				if(is_string($el))$t.=$el;
				else $t.=$el['_value'];

				$h.=$tag[0].$t;
			}else{//Вложенность
				$hr=docx_each($el,'docx_analyse',$param,$key);
				if($tag[0]=='<p>'&&preg_match("/\{.*\}/",$hr)){
					$t=strip_tags($hr);
					if($t{0}=='{'&&$t{strlen($t)-1}=='}'){
						$t=substr($t,1,strlen($t)-2);
						$t=explode(':',$t);
						if(sizeof($t)==2){
							$name=$t[0];
							$val=$t[1];
							if($name=='env'){//чтобы обработать env нужно уже загрузить этот слой к этому времени env обработаны
								//$hr='<script>if(window.infra)infra.when(infrajs,"onshow",function(){ infrajs.envSet("'.$t[1].'",true)});</script>';
								$tag[0]='';
								$tag[1]='';
								$hr='';
								if(!isset($param['com']['env']))$param['com']['env']=array();
								$param['com']['env'][]=$val;//в infrajs нельзя добавлять здесь тоже должно попасть в кэш docx
							}else if($name=='envdiv'){//чтобы обработать env нужно уже загрузить этот слой к этому времени env обработаны
								//$hr='<script>if(window.infra)infra.when(infrajs,"onshow",function(){ infrajs.envSet("'.$t[1].'",true)});</script>';
								$tag[0]='<div id="'.$val.'">';
								$tag[1]='</div>';
								$hr='';
								if(!isset($param['com']['env']))$param['com']['env']=array();
								$param['com']['env'][]=$val;//в infrajs нельзя добавлять здесь тоже должно попасть в кэш docx
							}
						}
					}
				}
				$h.=$tag[0];//Открывающий тэг
				//<a>
				if($isheading&&!@$param['heading'])$param['heading']=strip_tags($hr);
				if($key==='w:r'&&@$el['history']){
					if(!@$param['links'])$param['links']=array();
					$href=$param['rIds'][$el['id']];
					$param['links'][]=array('href'=>$href,'title'=>$hr);
				}
				$h.=$hr;
			}
			//=====================

			//Таблицы
			if(is_array($el)&&@$el['tbl']=='1'){
				$param['istable']=false;
			//Список
			}else if($isli){//Вышли из какого-то li
				$param['isli']=false;
			}else if($isheading){//Вышли из какого-то li
				$isheading=false;
			}

			$h.=$tag[1];//Закрывающий тэг

			
			return $h;
			
		}
		function docx_get($src,$type='norm',$re=false){
			$debug=$re;
			$conf=infra_config();

			if(@$conf['files']&&@$conf['files']['imgmaxwidth']){
				$imgmaxwidth=$conf['files']['imgmaxwidth'];
			}
			if(!$imgmaxwidth)$imgmaxwidth=1000;
			$imgmaxwidth=(int)$imgmaxwidth;

			$previewlen=150;
			$args=array($src,$type,$imgmaxwidth,$previewlen);

			$dhtml=infra_cache(array($src),'docx_parse',function($src,$type,$imgmaxwidth,$previewlen,$debug){
				$cachename=md5($src);
				$cachefolder='infra/cache/docx/'.$cachename.'/';
				
				//В винде ингда вылетает о шибка что нет прав удалить какой-то файл в папке и как следствие саму папку
				//Обновление страницы проходит уже нормально
				//Полагаю в линукс такой ошибки не будет хз почему возникает
				@docx_full_del_dir($cachefolder);



				$xmls=docx_getTextFromZippedXML(ROOT.$src, "word/document.xml",$cachefolder,$debug);
				$rIds=array();
				$param=array('com'=>array(),'folder'=>$cachefolder,'imgmaxwidth'=>$imgmaxwidth,'previewlen'=>$previewlen,'type'=>$type,'rIds'=>$rIds);
				if($xmls[0]){
					$xmlar=docx_dom_to_array($xmls[0]);
					$xmlar2=docx_dom_to_array($xmls[1]);
					
					foreach($xmlar2['Relationships']['Relationship'] as $v){
						$rIds[$v['Id']]=$v['Target'];
					}

					$param['rIds']=$rIds;
					$html=docx_each($xmlar,'docx_analyse',$param);
				}else{
					$param['rIds']=array();
					$html='';
				}

				/*if($debug){
					echo $html;
					echo '<textarea style="width:600px; height:400px">';
					echo $html;
					echo '</textarea>';
					echo '<pre>';
					print_r($xmlar);
				}*/
				if($type=='preview'||$type=='news'){
					$p=explode("/",$src);
					$fname=array_pop($p);
					$s=infra_toutf($fname);
					$data=infra_nameinfo($s);
					//$p=explode("/",$src);
					//$fname=array_pop($p);
					//preg_match("/^(\d*)/",$fname,$match);
					//$fname=infra_toutf(preg_replace('/^\d*/','',$fname));
					//$fname=preg_replace('/\.\w{0,4}$/','',$fname);
					//$fname=trim($fname);
					//$date=$match[0];

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
					}
					$preview=preg_replace('/<h1.*<\/h1>/U','',$preview);
					$preview=preg_replace("/<img.*>/U",'',$preview);
					$preview=preg_replace('/<p.*>\s*<\/p>/iU','',$preview);
					$preview=preg_replace("/\s+/",' ',$preview);
					$preview=trim($preview);
					preg_match('/<img.*src=["\'](.*)["\'].*>/U',$html,$match);
					if($match&&$match[1]){
						$img=$match[1];
					}else{
						$img=false;
					}
					$filetime=filemtime(ROOT.$src);
					$data['modified']=$filetime;
					if(@$param['links'])$data['links']=$param['links'];
					if(@$param['heading'])$data['heading']=$param['heading'];
					//title - depricated
					if(@$data['name'])$data['title']=$data['name'];
					if($img)$data['img']=$img;
					if(@$param['images'])$data['images']=$param['images'];
					if($preview)$data['preview']=$preview;

					return array($data,$param['com']);
				}else{
					return array($html,$param['com']);
				}
			},$args,$re);
			//$html=$dhtml[0];
			//кэш этого место в infrajs в getHTML
			return $dhtml;
		}
	}

	$src=infra_theme($_GET['src']);
	$type='norm';
	if(isset($_GET['type']))$type=$_GET['type'];
	if(isset($_GET['preview']))$type='preview';
	if(isset($_GET['news']))$type='news';
	if($src){
		infra_admin_cache('docx',function(){
			@mkdir(ROOT.'infra/cache/docx/');
		});

		$dhtml=docx_get($src,$type,isset($_GET['re']));
		$html=$dhtml[0];
		$com=$dhtml[1];//Команды из вордовского файла
		//Следующий кэш в getHtml там подгружается html с помощью infra_loadTEXT
		//и Ещё один главный кэш в check но там уже всё будет применено, а сейчас у нас весит команда для env и что с ней делать?
		//Эта же команда обработана и вставлена в javascript html
		if(!isset($_GET['nocom'])){
			@header('infra-com:'.json_encode($com));
		}

		if($type=='norm'){
			echo $html;
		}else{
			return infra_echo($html);
		}
	}

?>
