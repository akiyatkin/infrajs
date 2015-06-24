<?php

require_once(__DIR__.'../infra/infra.php');
infra_admin_modified();

infra_require('*files/files.inc.php');

$layer=infra_loadJSON('*files/files.layer.json');

$conf=infra_config();
if(empty($conf['rubrics']))return infra_ans($layer);

$types=array();
$types['info']=$layer['childs']['Информация'];
$types['files']=$layer['childs']['Файлы'];

unset($layer['childs']['Информация']);
unset($layer['childs']['Файлы']);
unset($layer['childs']['Блог']);
unset($layer['childs']['События']);
unset($layer['childs']['Тексты']);
unset($layer['childs']['Error404']);
unset($layer['childs']['Error403']);
$rubrics=$conf['rubrics'];
unset($rubrics['pub']);

foreach($rubrics as $rub=>$param){
	if(!$param)continue;
	$layer['childs'][$rub]=$types[$param['type']];
	$layer['childs'][$rub]["seo"]["link"]=$param['title'];
	$layer['childs'][$rub]["seo"]["name"]='Раздел '.$param['title'];
	$layer['childs'][$rub]["tpl"]=array('<h1>'.$param['title'].'</h1><div id="allevents"></div>');
	$layer['childs'][$rub]['divs']['allevents']['tplroot']='rubric-'.$param['type'];
}
return infra_ans($layer);