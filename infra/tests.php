<?php

infra_test(true);
//ini_set('error_reporting',E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', 1);
$data=array();
$dirs=infra_dirs();
infra_pluginRun(function($dir,$name) use (&$data){
	$src=$dir.'tests/';	
	if(!is_dir($src))return;
	$data[$dir]=array();

	$list = scandir($src);
	infra_forr($list,function($file) use($dir,$src,$name,&$data){
		if($file{0}=='.')return;
		
		if(!is_file($src.$file))return;
		$finfo=infra_nameinfo($file);
		if($finfo['ext']!='php')return;
		$text=infra_loadTEXT($src.$finfo['file'].'?type=auto');
		if(strlen($text)>1000){
			$res=array('title'=>$name.' '.$finfo['name'],'result'=>0,'msg'=>'Слишком длинный текст','class'=>'bg-warning');
		}else{
			$res=json_decode($text,true);
			if(!is_array($res))$res=array('title'=>$name.' '.$finfo['name'],'result'=>0,'msg'=>'Некорректный json','class'=>'bg-warning');
		}
		$res['src']='?*'.$name.'/tests/'.$finfo['file'];
		$res['name']=$finfo['file']; //имя тестируемого файла
		$data[$dir][]=$res;
	});
});
$html=infra_template_parse('*infra/tests.tpl',$data);
echo $html;
