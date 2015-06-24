<?php
	require_once(__DIR__.'/infra.php');
	infra_admin_modified();
	$re=isset($_GET['re']);
	$html=infra_admin_cache('infra_js_php',function($str){

		$loadTEXT=function($path){
			$html=infra_loadTEXT($path);
			$html='infra.store("loadTEXT")["'.$path.'"]={value:"'.$html.'",status:"pre"};'; //код отметки о выполненных файлах
			return $html;
		};
		$loadJSON=function($path){
			$obj=infra_loadJSON($path);
			$html='infra.store("loadJSON")["'.$path.'"]={value:'.infra_json_encode($obj).',status:"pre"};'; //код отметки о выполненных файлах
			return $html;
		};
		$require=function($path){
			$html="\n\n".'//requrie '.$path."\n";
			$html.=infra_loadTEXT($path).';';
			$html.='infra.store("require")["'.$path.'"]={vlaue:true};'; //код отметки о выполненных файлах
			return $html;
		};
		$html='window.infra={};';

		$conf=infra_config('secure');
		$html.='infra.conf=('.infra_json_encode($conf).');infra.config=function(){return infra.conf;};';
		//$html.='infra.admin=function(){ return '.(infra_admin()?'true':'false').';};';//Эта функция запрещает кэш. После админа кэш будет сделан первым неадмином. и в кэше будет false и только.
		//=======================
		//
		$html.=$require('*infra/ext/load.js');
		$html.=$require('*infra/ext/forr.js');
		$html.=$require('*infra/ext/view.js');
		
		$html.=$require('*infra/ext/seq.js');
		
		$html.=$require('*infra/ext/admin.js');

		$html.=$require('*infra/ext/events.js');
		
		//Внутри расширений зависимости подключаются, если используется API
		//Здесь подключение дублируется, тем более только здесь это попадёт в кэш
		$html.=$require('*infra/ext/html.js');
		$html.=$require('*infra/ext/template.js');
		$html.=$require('*infra/ext/crumb.js');
		$html.=$require('*infra/ext/loader.js');
		
		if($conf['debug']||$conf['admin']['popup'])$html.=$require('*infra/ext/test.js');

		
		return $html;
	},array($_SERVER['QUERY_STRING']),$re);
	@header('content-type: text/javascript; charset=utf-8');
	echo $html;