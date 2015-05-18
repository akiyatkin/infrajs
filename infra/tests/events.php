<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');

	function ara($t){
		echo '<pre>';
			print_r($t);
		echo '</pre>';
	};


	$i=1;
	$obj=array();

	infra_wait($obj,'ontest',function(){
		global $i;
		$i++;
	});

	infra_listen($obj,'ontest',function(){
		global $i;
		$i++;
	});

	infra_fire($obj,'ontest');
	infra_fire($obj,'ontest');

	global $infra;
	global $j;

	$j=1;
	infra_listen($infra,'obj.onsome',function($obj){
		global $j;
		$j++;
	});
	infra_wait($infra,'obj.onsome',function($obj){
		global $j;
		$j++;
	});
	infra_fire($obj,'obj.onsome');
	infra_fire($obj2,'obj.onsome');

	if($i==4 && $j==4){ // не корректно
		$ans['result'] = 1;
	}
	else{
		$ans['result'] = 0;
	}
	return $ans;