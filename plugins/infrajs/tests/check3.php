<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');


infra_require('*infrajs/initphp.php');

infra_html('<div id="main"></div>');

$layers=infra_loadJSON('*infrajs/tests/resources/check3.json');
infra_State_set("?test");
infrajs_check($layers);

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
$countneed=4;
echo '<br><textarea style="width:700px; height:100px">';
echo $countneed;
echo ':';
echo $count;
echo $html;
echo '</textarea>';



if($count==$countneed){
	echo '<h1 style="color:green">PASS</h1>';
}else{
	echo '<h1 style="color:red">ERROR</h1>';
}
?>