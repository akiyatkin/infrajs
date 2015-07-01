<?php
	

	global $infra;
	$ans=array();
	$code=$_GET['code'];
	if(!$infra&&$code){
		$p=$_SERVER['REQUEST_URI'];
		$dirs=infra_dirs();
		$root='/'.$dirs['ROOT'];
		$p=explode($root,$p,2);
		$p=$p[1];
		$path='http://'.$_SERVER['HTTP_HOST'].$root.'?Error'.$code.'/'.$p;
		header('location: '.$path);
		exit;
	}else{
		$src=$_GET['src'];
		$p=explode('Error'.$code.'/',$src,2);
		$ans['path']=$p[1];
		if($code=='404'){
			header("HTTP/1.0 404 Not Found");
		}else if($code=='403'){
			header("HTTP/1.0 403 Forbidden");
		}
		return infra_echo($ans);
	}
