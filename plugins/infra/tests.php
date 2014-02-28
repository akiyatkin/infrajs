<?php
define('ROOT','../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_admin(true);
$plugs='infra/plugins/';
$list=infra_loadJSON('*pages/list.php?src='.$plugs.'&f=0&d=1&onlyname=1');
$ans=array();
foreach($list as $plugin){
	$src=$plugs.$plugin.'/tests/';
	if(is_dir(ROOT.$src)){
		$list=infra_loadJSON('*pages/list.php?src='.$src.'&f=1&d=0&onlyname=1');
		
		foreach($list as $v=>$name){
			$p=infra_nameinfo($name);
			$list[$v]=array('folder'=>$plugin,'name'=>$name);
			if(in_array($p['ext'],array('php','html')))continue;
			unset($list[$v]);
		}
		if(!sizeof($list))continue;
		
		$ans[]=array("folder"=>$plugin,"list"=>$list);
	}
}
infra_require('*infra/ext/template.php');
//echo "<pre>";
//print_r($ans);
$html=infra_template_parse('*infra/tests.tpl',$ans);
echo $html;
?>