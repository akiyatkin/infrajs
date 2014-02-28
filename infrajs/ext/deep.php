<?php
//deep:(number),//Для istate определяет на каком уровне от текущего будет тру... пропускает родителей. Только когда что-то будет на нужном уровне от указанного istate
global $infra;
infrajs_isAdd('check',function($layer){
	$deep=(int)$layer['deep'];
	if(!$deep)return;
	$state=&$layer['istate'];
	while($deep&&$state->child){
		$deep--;
		$state=&$state->child;
	}
	if(is_null($state->obj)||$deep)return false;
});
?>
