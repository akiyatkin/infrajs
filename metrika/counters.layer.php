<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$layer=array();
	$layer['divs']=array();

	$data=infra_load('*counters.json','fj');
	if($data){
		$i=0;
		infra_foro($data,function(&$layer,&$i, $val,$key){
			if(!$val||!$val['id'])return;
			$i++;
			$layer['divs']['counter'.$i]=array(
				'external'=>infra_load('*metrika/'.$key.'.layer.js','fj'),
				'data'=>true,
				'config'=>$val
			);
		},array(&$layer,&$i));
	}
	return infra_echo($layer);
?>
