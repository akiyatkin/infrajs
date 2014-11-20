<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');

infra_require('*infra/ext/state.php');

infra_State_set('');
$state=infra_State_getState('');
$f=$state->obj;




infra_State_set('?test');

$s=&infra_State_getState('some');
$s2=&infra_State_getState('some');
$r=infra_isEqual($s,$s2);


$s=infra_State_store();
$r2=infra_isEqual($s['first'],infra_State_getState());

$r=$r&&$r2;



$state=infra_State_getState('test');
$state2=infra_State_getState('test2');
if($f==Null&&$r&&!is_null($state->obj)&&is_null($state2->obj)){
	echo '<h1 style="color:green">PASS</h1>';
}else{
	echo '<h1 style="color:red">ERROR</h1>';
}