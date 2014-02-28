<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_require('*infrajs/initphp.php');



infra_html('<div id="oh"></div>');

$layer=array('tpl'=>array('хой'),"div"=>"oh");
infrajs_check($layer);

$html=infra_html();


if($html=='<div id="oh">хой</div>'){
	echo '<h1 style="color:green">PASS</h1>';
}else{
	echo '<h1 style="color:red">ERROR</h1>';
}
?>