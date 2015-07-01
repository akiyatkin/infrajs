<?php

infra_require('*infra/ext/template.php');

function rub_search($dir,$str,$exts){//Найти указанный в $str файл

	$files=rub_list($dir,0,0,$exts);
	
	if(@$files[$str]){
	       	$files[$str]['idfinded']=true;//Найдено по id
	       	return $files[$str];
	}
	foreach($files as $d)if(mb_strtolower($d['name'])==mb_strtolower($str))return $d;
	return array();
}
function rub_ptube(){
	$ptube='http.*youtube\.com.*watch.*=([\w\-]+).*';
	return $ptube;
}
function rub_ptube2(){
	$ptube='http.{0,1}:\/\/youtu\.be\/([\w\-]+)';
	return $ptube;
}
function rub_article($src){

	$html=infra_loadTEXT('*pages/get.php?'.$src);
	
	$info=infra_srcinfo($src);
	if(!in_array($info['ext'],array('html','tpl'))){
		$html=preg_replace("/<table>/",'<table class="table table-striped">',$html);
	}

	$html=preg_replace("/<\/a>/","</a>\n",$html);

	//youtube
	$ptube=rub_ptube();
	$pattern='/(<a.*href="'.$ptube.'".*>)'.$ptube.'(<\/a>)/i';

	$youtpl = <<<END
<iframe width="640" height="480" src="http://www.youtube.com/embed/{3}?rel=0" frameborder="0" allowfullscreen></iframe>
END;

	do{
		$match=array();
		preg_match($pattern,$html,$match);
		if(sizeof($match)>1){
			$a=$match[1];
			$aa=$match[4];
			$files[]=$match[2];
			$youhtml=infra_template_parse(array($youtpl),$match);
			$html=preg_replace($pattern,$youhtml,$html,1);
		}
	}while(sizeof($match)>1);

	//youtube2
	$ptube=rub_ptube2();
	$pattern='/(<a.*href="'.$ptube.'".*>)'.$ptube.'(<\/a>)/i';
	$youtpl = <<<END
	<iframe width="640" height="480" src="http://www.youtube.com/embed/{3}?rel=0" frameborder="0" allowfullscreen></iframe>
END;
	do{
		$match=array();
		preg_match($pattern,$html,$match);
		if(sizeof($match)>1){
			$a=$match[1];
			$aa=$match[4];
			$files[]=$match[2];
			$youhtml=infra_template_parse(array($youtpl),$match);
			$html=preg_replace($pattern,$youhtml,$html,1);
		}
	}while(sizeof($match)>1);

	//files
	setlocale(LC_ALL, "ru_RU.UTF-8");
	$files=array();
	$pattern='/(<a.*href="[^"]*infra\/[^"]*\/files\.php[^"]*id=(\d+)&[^"]*load".*>)([^~<]*?)(<\/a>)/u';
	do{
		$match=array();
		preg_match($pattern,$html,$match);
		/*echo '<pre>';
		print_r($match);
		exit;*/
		if(sizeof($match)>1){
			$a=$match[1];
			$id=$match[2];
			$title=$match[3];
			$aa=$match[4];

			
			$files[]=$id;
			
			$html=preg_replace($pattern,$a.$title.'~'.$aa,$html,1);

		}
	}while(sizeof($match)>1);
	

	$conf=infra_config();
	$dir=$conf['files']['folder_files'];
	$filesd=array();
	foreach($files as $id){
		$filed=rub_get($dir,$id,array());
		if($filed)$filesd[$id]=$filed;
	}

	$pattern='/(<a.*href="[^"]*infra\/[^"]*\/files\.php[^"]*id=(\d+)&[^"]*load".*>)([^~<]*?)~(<\/a>)/u';
	$tpl=<<<END
		<nobr>
			<a href="?*files/files.php?id={id}&type=files&load" title="{name}">{title}</a>
			<img style="margin-right:3px; margin-bottom:-4px;" src="?*imager/imager.php?src=*autoedit/icons/{ext}.png&w=16" title="{name}"> {size} Mb</nobr>
END;
	do{
		preg_match($pattern,$html,$match);

		if(sizeof($match)>1){
			$a=$match[1];
			$title=$match[3];
			$aa=$match[4];

			$id=$match[2];

			if($filesd[$id]){
				$d=$filesd[$id];
				$d['title']=$title;
				$t=infra_template_parse(array($tpl),$d);
				$html=preg_replace($pattern,$t,$html,1);
			}else{
				$html=preg_replace($pattern,$a.$title.$aa,$html,1);
			}
		}
	}while(sizeof($match)>1);
	$html=preg_replace("/<\/a>\n/","</a>",$html);
	
	return $html;
}

function rub_get($dir,$id,$exts){
	$files=rub_list($dir,0,0,$exts);
	$res=$files[$id];
	if(!$res)$res=array();
	return $res;
}
function rub_list($dir,$start=0,$count=0,$exts=array()){
	$conf=infra_config();
	
	
	$files=infra_cache(array($dir),'rub_list',function($dir,$start,$count,$exts){
		$dir=infra_theme($dir);
		return _rub_list($dir,$start,$count,$exts);
	},array($dir,$start,$count,$exts),isset($_GET['re']));

	return $files;
}
function _rub_list($dir,$start,$count,$exts){

	if(!$dir)return array();
	$dir=infra_toutf($dir);
	$dir=infra_theme($dir);

	$res=array();

	if(!$dir||!is_dir($dir))return $res;
	if (is_dir($dir)&&$dh = opendir($dir)) {
		$files=array();
		while (($file = readdir($dh)) !== false) {
			if($file[0]=='.')continue;
			if($file[0]=='~')continue;
			if($file=='Thumbs.db')continue;
			if(!is_file($dir.$file))continue;
			$rr=infra_nameinfo(infra_toutf($file));
			if($exts&&!in_array($rr['ext'],$exts))continue;
			$size=filesize($dir.$file);
			$file=infra_toutf($file);
			$ext=$rr['ext'];
			if(isset($_GET['re']))$re='&re=1';
			else $re='';

			if(in_array($ext,array('mht','tpl','html','txt'))){
				$rr=infra_loadJSON('*pages/mht/mht.php?preview=1&src='.infra_toutf($dir).$file.$re);
				if(!$rr)echo $file.'<br>';
			}else if(in_array($ext,array('docx'))){
				$rr=infra_loadJSON('*pages/docx.php?preview=1&nocom&src='.infra_toutf($dir).$file.$re);
			}

			$rr['size']=round($size/1000000,2);
			$links=@$rr['links'];
			if($links){
				unset($rr['links']);
				$ptube=rub_ptube();
				$ptube2=rub_ptube();

				foreach($links as $v){
					$r=preg_match('/'.$ptube.'/',$v['href'],$match);
					$r2=preg_match('/'.$ptube2.'/',$v['href'],$match);
					if($r){
						if(!@$rr['video'])$rr['video']=array();
						$v['id']=$match[1];
						$rr['video'][]=$v;
					}else if($r2){
						if(!@$rr['video'])$rr['video']=array();
						$v['id']=$match[1];
						$rr['video'][]=$v;
					}else{
						if(!@$rr['links'])$rr['links']=array();
						$rr['links'][]=$v;
					}
				}
			}
			$files[]=$rr;
		}
		usort($files,function($b,$a){
			$a=@$a['date'];
			$b=@$b['date'];
			return $a < $b ? +1 : -1;
		});
		$maxid=0;
		foreach($files as $fdata){
			if(!$fdata['id'])continue;
			if($fdata['id']>$maxid)$maxid=$fdata['id'];
		}
		foreach($files as &$fdata){
			if($fdata['id']&&$fdata['date'])continue;
			if(!$fdata['id'])$fdata['id']=++$maxid;
		}
		$files=array_reverse($files);
		if($count||$start) $files=array_splice($files,$start,$count);
		foreach($files as $fdata){
			$res[$fdata['id']]=$fdata;
		}
	}

	return $res;
}