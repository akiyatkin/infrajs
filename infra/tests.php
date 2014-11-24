<?php
define('ROOT','../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_admin(true);
ini_set('error_reporting',E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', 1);
$data=array();
$ar=array('infra/layers/','infra/plugins/');
infra_forr($ar,function($dir) use(&$data){
	$list=infra_loadJSON('*pages/list.php?src='.$dir.'/&f=0&d=1&onlyname=1');
	infra_forr($list,function($plugin) use($dir,&$data){		
		$src=$dir.$plugin.'/tests/';
		if(!is_dir(ROOT.$src))return;
		
		$data[$dir.$plugin]=array();

		$list=infra_loadJSON('*pages/list.php?src='.$src.'/&f=1&d=0');
		infra_forr($list,function($finfo) use($dir,$src,$plugin,&$data){
			if($finfo['ext']!='php')return;

			$text=infra_loadTEXT($src.$finfo['file']);
			if(strlen($text)>1000){
				$res=array('title'=>$plugin.' '.$finfo['name'],'result'=>0,'msg'=>'Слишком длинный текст');
			}else{
				$res=json_decode($text,true);
				if(!$res)$res=array('title'=>$plugin.' '.$finfo['name'],'result'=>0,'msg'=>'Некорректный json');
			}
			$res['src']=$src.$finfo['file'];
			$res['name']=$finfo['file']; //имя тестируемого файла
			$data[$dir.$plugin][]=$res;
		});
	});
});
$html=infra_template_parse('*infra/tests.tpl',$data);
echo $html;