<?php
	
	require_once(__DIR__.'../infra/infra.php');
	$layer=array();
	$layer['divs']=array();

	$data=infra_load('*counters.json','fj');
	if($data){
		$i=0;
		infra_foro($data,function($val,$key) use(&$layer,&$i){
			if(!$val||!$val['id'])return;
			$i++;
			$layer['divs']['counter'.$i]=array(
				'external'=>infra_load('*metrika/'.$key.'.layer.js','fj'),
				'data'=>true,
				'config'=>$val
			);
		});
	}
	return infra_echo($layer);