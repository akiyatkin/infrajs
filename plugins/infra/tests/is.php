<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');

infra_require('*infrajs/initphp.php');

$i=0;
infrajs_isAdd('test',function(&$layer){
	global $i;
	$i++;
	return true;
});
infrajs_isAdd('test',function(&$layer){
	global $i;
	$i++;
	return false;
});
infrajs_isAdd('test',function(&$layer){
	global $i;
	$i++;
	return true;
});


$layer=array();
$cw=&infrajs_storeLayer($layer);//work
$cc=&infrajs_store();//check
$cw['counter']=$cc['counter']=1;


$r=infrajs_is('test',$layer);
if($i==2&&!$r){
	echo '<h1 style="color:green">PASS</h1>';
}else{
	echo '<h1 style="color:red">ERROR</h1>';
}

?>