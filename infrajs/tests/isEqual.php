<?php
	@define('ROOT','../../../../');
	
	require_once(ROOT.'infra/plugins/infra/infra.php');
	

	

	$l=array('tpl'=>'asdf','test'=>'bad');

	
	$layers=array(&$l);
	$msg='Maybe good';
	

	//=========
	
	/*$run=function&(&$layers,$callback){
		return $callback($layers[0]);
	};
	$layer=&$run($layers,function&(&$layer){
		$layer['test']='maybe good';
		return $layer;
	});*/
	//=========
	/*
	$run=function&(&$layers,$callback){
		return call_user_func_array($callback,array(&$layers[0]));
	};
	$layer=&$run($layers,function&(&$layer){
		$layer['test']='maybe good';
		return $layer;
	});*/
	//=========

	/*$msg='Maybe good';	
	$layer=&infra_forr($layers,function&($msg,&$layer){
		$layer['test']='maybe good';
		return $layer;
	},array($msg));
	//=========*/
	/*$layers=array('asdf'=>&$l);
	$layer=&infra_foro($layers,function&($msg,&$layer){
		$layer['test']='maybe good';
		return $layer;
	},array($msg));
	//=========*/	
	/*$layer=&infra_fora($layers,function&($msg,&$layer){
		$layer['test']='maybe good';
		return $layer;
	},array($msg));

	//=========*/
	//$layer=&$layers[0];
	//=========
	
	infra_require('*infrajs/init.php');
	infra_require('*infrajs/initphp.php');
	$layer=&infrajs_run($layers,function&($msg,&$layer){
		$layer['test']=$msg;
		return $layer;
	},array($msg));
	//=========*/


	$l['test']='Good';
	echo $layer['test'];
	


?>