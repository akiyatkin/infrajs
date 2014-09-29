<?php
	$f=function&(&$arg,&$j){
		return $arg;
	};
	$a='bad or ';
	$c=null;
	$b=&$f($a,$c);
	//$b=&call_user_func_array($f,array(&$a));
	$a=1;

	if($b!==1)echo 'ERROR';
	else echo 'GOOD';

	phpinfo();
	
?>