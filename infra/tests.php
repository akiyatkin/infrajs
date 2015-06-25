<?php

require_once(__DIR__.'/infra.php');


infra_admin(true);
ini_set('error_reporting',E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', 1);
$data=array();
$dirs=infra_dirs();
infra_forr($dirs['search'],function($dir) use(&$data){
	
	$list = scandir($dir);
	infra_forr($list,function($plugin) use($dir,&$data){
		if($plugin{0}=='.')return;
		if(!is_dir($dir.$plugin))return;
		$src=$dir.$plugin.'/tests/';
		
		if(!is_dir($src))return;
		if(!is_file($dir.$plugin.'/.config.json')){
			$data['<small title=".config.json required" style="color:gray; font-weight:normal;">'.$dir.$plugin.'/tests/</small>']=array();
			return;
		}
		$data[$dir.$plugin]=array();

		$list = scandir($src);
		infra_forr($list,function($file) use($dir,$src,$plugin,&$data){
			if($file{0}=='.')return;
			
			if(!is_file($src.$file))return;
			$finfo=infra_nameinfo($file);
			if($finfo['ext']!='php')return;
			$text=infra_loadTEXT($src.$finfo['file'].'?type=auto');
			if(strlen($text)>1000){
				$res=array('title'=>$plugin.' '.$finfo['name'],'result'=>0,'msg'=>'Слишком длинный текст','class'=>'bg-warning');
			}else{
				$res=json_decode($text,true);
				if(!is_array($res))$res=array('title'=>$plugin.' '.$finfo['name'],'result'=>0,'msg'=>'Некорректный json','class'=>'bg-warning');
			}
			$res['src']=$plugin.'/tests/'.$finfo['file'];
			$res['name']=$finfo['file']; //имя тестируемого файла
			$data[$dir.$plugin][]=$res;
		});
	});
});
$html=infra_template_parse('*infra/tests.tpl',$data);
echo $html;