<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	$folder=infra_theme($_GET['src'],'d');
	$ans=array();
	if(!$folder)return infra_echo($ans);	
	$data=infra_plugin('*pages/list.php?src='.$folder.'&d=1&sub=1&e=mht,tpl&f=1','fp');
	//$data=array($data['1'],$data['10'],$data['11']);
	/*echo '<pre>';
	print_r($data);
	exit;*/
	$first=$_GET['first'];
	if(!$first)$first='Главная';
	$list=array('child'=>array(array('title'=>$first,'href'=>'','f'=>1)));
	foreach($data as $v){
		$path=explode('/',$v['dir']);
		$path[]=$v['name'];
		foreach($path as $i=>$n){
			if(!$path[$i]){
				unset($path[$i]);
			}else{
				//$path[$i]=preg_replace("/^\d+\s+/",'',$path[$i]);
			}
		}
		$path=array_values($path);
		
		//print_r($path);
		unset($link);
		$parent=&$list;
		$link=&$list;
		foreach($path as $n){
			if(!$link['child'])$link['child']=array();
			$r=false;
			foreach($link['child'] as $i=>$e){
				if($e['title']==$n){
					$r=true;
					break;
				}
			}
			
			if(!$r)$i=sizeof($link['child']);
			if(!$link['child'][$i])$link['child'][$i]=array('title'=>$n,'href'=>($link['href']?$link['href'].'/':'').$n);
			
			unset($parent);
			$parent=&$link;
			unset($link);
			$link=&$parent['child'][$i];
		}
		//if(!$link['child']&$link['d'])$link['child']=array();
		if($v['f'])$link['f']=1;
		else $link['d']=1;
		if(!$link['href'])$link['href']=($parent['href']?$parent['href'].'/':'').$v['name'];
	}
	if($_GET['debug']){
		echo '<pre>';
		print_r($list['child']);
	}else{
		return infra_echo($list['child']);
	}
?>