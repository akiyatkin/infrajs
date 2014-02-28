<?php
global $infra_once;
$infra_once=array();
function infra_hash($args){
	$a=array();
	foreach($args as $k=>$v){
		if(is_callable($v))$a[$k]='func!';
		else if(is_array($v))$a[$k]=infra_hash($v);
		else $a[$k]=$v;
	}
	return md5(serialize($a));
}
function &infra_once($name,$call,$args=array(),$re=false){
	global $infra_once;

	$strargs=infra_hash($args);
	$name=$name.$strargs;

	if(!is_callable($call)){
		$re=false;
		$infra_once[$name]=array('result'=>$call);
	}
	if(isset($infra_once[$name])&&!$re)return $infra_once[$name]['result'];
	$infra_once[$name]=array('exec'=>true);
	
	$v=array_merge($args,array($re));
	
	$v=call_user_func_array($call,$v);
	
	$infra_once[$name]['result']=$v;
	return $infra_once[$name]['result'];
}
/*

infra_once('somefunc',function(){
	
},array($name));

infra_once('somefunc',$value,array($name));

*/
?>