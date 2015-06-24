<?php

require_once(__DIR__.'/../../infrajs.php');

$db=&infra_db();
if($db){
	$val=infra_session_get('test');


	echo "Было: ";
	var_dump($val);
	echo "\nУстановили: ";
	$val++;
	infra_session_set('test',$val);

	var_dump($val);
	echo '<hr>';
	echo 'infra_session_getId()='.infra_session_getId();
	echo '<hr>';
	echo '<pre>';
	$d=infra_session_get();
	print_r($d);
	if($d['test']>1){
		echo '<h1 id="res" style="color:green;">PASS</h1>';
	}else{
		echo '<h1 id="res" style="color:red;">ERROR нажмите 1 раз F5</h1>';
	}
}else{
	echo '<h1 id="res" style="color:red;">ERROR нет базы данных</h1>';
}

?>