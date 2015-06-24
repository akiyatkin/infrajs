<?php
	
	require_once(__DIR__.'/../../infra/infra.php');

	$obj=array();
	$obj['tpl']=array('1{:add}');
	$obj['tplsm']=array('{add:}2');
	$obj['data']=array('asdf'=>1);

	$tpls=infra_template_make($obj['tpl']);//С кэшем перепарсивания
			
	$repls=array();
	$t=infra_template_make($obj['tplsm']);
	$repls[]=$t;
	$alltpls=array(&$repls,&$tpls);

	$html=infra_template_exec($alltpls,$obj['data'],@$layer['tplroot'],@$layer['dataroot']);

	if($html!='12') return infra_err($ans, 'err');
	return infra_ret($ans, 'ret');