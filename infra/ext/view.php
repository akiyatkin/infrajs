<?php
@define('ROOT','../../../../');
function infra_view_getHost(){
	return $_SERVER['HTTP_HOST'];
}
function infra_view_getSchema(){
	return $_SERVER['REQUEST_SCHEME'].'://';
}
function infra_view_getAgent(){
	return $_SERVER['HTTP_USER_AGENT'];
}
function infra_view_getIP(){
	return $_SERVER['REMOTE_ADDR'];
}
function infra_view_getRef(){
	return $_SERVER['HTTP_REFERER'];
}
function infra_view_getCookie($name=null){
	if(is_null($name))return $_COOKIE;
	return @$_COOKIE[$name];
}
function infra_view_setCookie($name,$val=null){
	$_COOKIE[$name]=$val;
	$root=infra_view_getRoot(ROOT);
	if(is_null($val)){
		$time=time()-60*60*24*30*24;
	}else{
		$time=time()+60*60*24*30*24;
	}
	return setcookie($name,$val,$time,'/'.$root);
}

function infra_view_getRoot($root=false){
	//Путь начинается без слэша svn/project/ например
	$path=$_SERVER['PHP_SELF'];
	$p=explode('?',$path);
	$path=explode('/',$p[0]);

	$conf=infra_config();
	if($conf['infra']['rootisfolder']){
		array_pop($path);
	}

	if(!$path[0])array_shift($path);
	if($root){
		if($root!='./'){
			$rr=explode('/',$root);//'../', '../../'
			array_pop($rr);
			while(array_pop($rr)){
				array_pop($path);
			};
		}
	}
	$path=implode('/',$path);
	if($path&&$conf['infra']['rootisfolder']){
		$path.='/';
	}

	return $path;
}

/*function infra_view_setCOOKIE($name,$val){
	return infra_view_setCookie($name,$val);
}
function infra_view_getCOOKIE($name,$val){
	return infra_view_getCookie($name,$val);
}*/