<?php
	require_once(__DIR__.'/../infra/infra.php');


	$ans=array('result'=>1);

	$ifolder=infra_theme($_GET['folder'],'du');
	$nextfolder=infra_toutf($_GET['nextfolder']);
	$reverse=(int)$_GET['reverse'];


	$path=explode('/',$nextfolder);
	array_pop($path);

	$fold='';
	$name='Главная';
	foreach($path as $k=>$n){
		if($_GET['dir']&&infra_theme($ifolder.$fold.$n,'d')){
			$fold.=$n.'/';
			$path[$k]=array(
				'path'=>$fold,
				'name'=>$n
			);
			$name=$n;
		}else{
			array_pop($path);
		}
	}
	$folder=$ifolder.$fold;
	$ans['folder']=$fold;
	$ans['path']=$path;
	$ans['name']=$name;
	if($_GET['isexist']){
		$set='nu';
		if($_GET['list'])$set.='d';
		if($_GET['page'])$set.='f';

		$ans['result']=!!infra_theme(preg_replace("/\/$/",'',$ifolder.$nextfolder),$set);
	}else{
		$ans['pages']=infra_plugin('*pages/list.php?e=tpl,mht&reverse='.$reverse.'&onlyname=2&src='.$folder,'fp');
		if($_GET['dir']){
			$ans['folders']=infra_plugin('*pages/list.php?d=1&f=0&reverse='.$reverse.'&onlyname=1&src='.$folder,'fp');
		}else{
			$ans['folders']=array();
		}
		foreach($ans['pages'] as $k=>$page){
			foreach($ans['folders'] as $fold){
				if($fold==$page){
					array_splice($ans['pages'],$k,1);
					break;
				}
			}
		}
	}

	if($FROM_PHP) return $ans;
	else echo infra_tojs($ans);