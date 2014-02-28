<?php
global $infra;
infra_listen($infra,'layer.oninit',function(&$layer){
	if(@$layer['istpl'])unset($layer['is']);//Иначе onchange не запустится
});
infra_listen($infra,'layer.oncheck',function(&$layer){

	$name='config';//stencil//
	$nametpl=$name.'tpl';
	if(@$layer[$nametpl]){
		if(@!$layer[$name])$layer[$name]=array();
		foreach($layer[$nametpl] as $i=>$v){
			$layer[$name][$i]=infra_template_parse(array($layer[$nametpl][$i]),$layer);
		}
	}
	$list=array('autosavename','title','keywords','env','div','dataroot','tplroot');
	infra_forr($list,function(&$layer,$prop){
		$proptpl=$prop.'tpl';
		if(@$layer[$proptpl]){
			$p=$layer[$proptpl];

			if(infra_isAssoc($layer[$proptpl])===false){
				$p=infra_template_parse($p,$layer);
				$layer[$prop]=array($p);
			}else{
				$p=infra_template_parse(array($p),$layer);
				$layer[$prop]=$p;
			}
		}
	},array(&$layer));

	$name='myenv';
	$nametpl=$name.'tpl';
	if(@$layer[$nametpl]){
		if(@!$layer[$name])$layer[$name]=array();
		foreach($layer[$nametpl] as $i=>$v){
			$layer[$name][$i]=infra_template_parse(array($layer[$nametpl][$i]),$layer);
		}
	}

	/*$name='autoedit';
	$nametpl=$name.'tpl';
	if(@$layer[$nametpl]){
		if(@!$layer[$name])$layer[$name]=array();

		if(@$layer[$nametpl]['title'])$layer[$name]['title']=infra_template_parse(array($layer[$nametpl]['title']),$layer);
		if(@$layer[$nametpl]['descr'])$layer[$name]['descr']=infra_template_parse(array($layer[$nametpl]['descr']),$layer);

		if(@$layer[$nametpl]['files']){
			$files=array();
			infra_fora($layer[$nametpl]['files'],function(&$layer,&$files,$file){
				$f=array();
				if($file['title'])$f['title']=infra_template_parse(array($file['title']),$layer);
				if($file['root'])$f['root']=infra_template_parse(array($file['root']),$layer);

				if($file['paths']){
					$paths=array();
					infra_fora($file['paths'],function(&$paths,$path){
						$path=infra_template_parse(array($path),$layer);
						if(!$path)return;
						$paths[]=$path;
					},array(&$paths));
					$f['paths']=$paths;
				}

				$files[]=$f;
			},array(&$layer,&$files));
			$layer[$name]['files']=$files;
		}
	}
	if(function_exists('infrajs_external_add'))infrajs_external_add('autoedittpl',function&(&$now,&$ext,&$layer,&$external,$i){
		if(@$layer[$i])return $now;
		if(@$layer[preg_replace("/tpl$/","",$i)])return $now;
		if(!$now)$now=$ext;
		return $now;
	});*/
	
});
?>
