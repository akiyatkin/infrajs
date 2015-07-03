<?php

infra_admin_modified();

infra_require('*rubrics/rubrics.inc.php');

$layer=infra_loadJSON('*rubrics/rubrics.layer.json');

$conf=infra_config();
if(empty($conf['rubrics']))return infra_ans($layer);

$types=$layer['childs'];
$layer['childs']=array();

$list=$conf['rubrics']['list'];
foreach($list as $rub=>$param){
	if(!$param)continue;
	if(!$types[$param['type']])continue;
	$layer['childs'][$rub]=$types[$param['type']];
	if($conf['rubrics']['main']==$rub){
		$layer['childs'][$rub]['crumb']='/';
	}
}
return infra_ans($layer);