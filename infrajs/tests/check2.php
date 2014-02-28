<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
$_SERVER['QUERY_STRING']="?test";
infra_require('*infrajs/initphp.php');

infra_html('<div id="main"></div>');


	$layers=infra_loadJSON('*infrajs/tests/resources/check2.json');
	infrajs_checkAdd($layers);
	infrajs_check();


	$layer=&$layers['layers'];


	

//infrajs_check();
/*
infrajs_run(infrajs_getWorkLayers(),function(&$layer){
	if(@$layer['div']=='a'){
		unset($layer['parent']);
		unset($layer['state']);
		unset($layer['istate']);
		
		var_dump(infrajs_is('check',$layer));

		echo '<pre>';
		print_r($layer['store']);
		exit;
	}
});*/

$html=infra_html();
preg_match_all('/x/', $html, $matches);
$count=sizeof($matches[0]);

echo '<textarea style="width:700px; height:100px">';
echo $count;
echo $html;
echo '</textarea>';



if($count==4){
	echo '<h1 style="color:green">PASS</h1>';
}else{
	echo '<h1 style="color:red">ERROR</h1>';
}
?>