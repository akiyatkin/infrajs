<?php
require_once(__DIR__.'/../session.php');
$ans=array();
$ans['title']='Проверка сессии на сервере';

$db=&infra_db();
if(!$db)return infra_err($ans,'ERROR нет базы данных');
$val=infra_session_get('test');



$val=(int)$val+1;
infra_session_set('test',$val);

$d=infra_session_get();
$ans['test']=$d['test'];
if($d['test']>1){
	return infra_ret($ans,'PASS');
}else{
	return infra_err($ans,'ERROR нажмите 1 раз F5');
}
