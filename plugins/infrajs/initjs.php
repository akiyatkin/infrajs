<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_admin_modified();
	$re=@$_GET['re'];
	$html=infra_admin_cache('infra_initjs_php',function($str){

		$loadTEXT=function($path){
			$html=infra_loadTEXT($path);
			$html='infra.store("loadTEXT")["'.$path.'"]={value:"'.$html.'",status:"pre"};'; //код отметки о выполненных файлах
			return $html;
		};
		$loadJSON=function($path){
			$obj=infra_loadTEXT($path);
			$html='infra.store("loadJSON")["'.$path.'"]={value:'.$obj.',status:"pre"};'; //код отметки о выполненных файлах
			return $html;
		};
		$require=function($path){
			$html="\n\n".'//requrie '.$path."\n";
			$html.=infra_loadTEXT($path).';';
			$html.='infra.store("require")["'.$path.'"]={vlaue:true};'; //код отметки о выполненных файлах
			return $html;
		};
		$html='';
		
		
		$html.=$require('*infra/js.php');
		$html.=$require('*infrajs/infrajs.js');//

		$html.=$require('*infrajs/ext/state.js');//
		$html.=$require('*infrajs/ext/external.js');//
		$html.=$require('*infrajs/ext/env.js');//
		
		$html.=$require('*infrajs/ext/subs.js');
		$html.=$require('*infrajs/ext/divparent.js');
		
		//$html.=$require('*infrajs/ext/proptpl.js');//После external
		$html.=$require('*infrajs/ext/tpl.js');//
		$html.=$require('*infrajs/ext/parsed.js');//
		$html.=$require('*infrajs/ext/div.js');//После subs, до tpl
		$html.=$require('*infrajs/ext/autoview.js');
		$html.=$require('*infrajs/ext/code.js');
		$html.=$require('*infrajs/ext/layers.js');//		
		$html.=$require('*infrajs/ext/unick.js');//
		$html.=$require('*infrajs/ext/is.js');//
		$html.=$require('*infrajs/ext/show.js');//
		$html.=$require('*infrajs/ext/config.js');//
		
		
		
		

		
		
		
	
		
		$html.=$require('*infra/ext/tablecommon.js');

		
		

		//	$conf=infra_config('secure');
		//if(!$conf['infrajs'])$conf['infrajs']=array();
//		$html.=<<<END
//			infra.wait(infrajs,'oncheck',function(){
//				
//			});
//END;
/*

//Скролл слева и прокрутка при кликах
		$html.=$require('*infrajs/ext/scroll.js');
		$sconf=$conf['infrajs']['scroll'];
		$sconfs=infra_tojs($sconf);
		$html.=<<<END
			infra.wait(infra,'oninit',function(){
				window.roller.init($sconfs);      
			});
			infra.wait(infra,'oninit',function(){
				infra.listen(infra.State,'onchange',function(){
					setTimeout(function(){
						if(infrajs.scroll!==false)window.roller.goTop();      
						delete infrajs.scroll;
					},1);
				});
			});
END;

		$html.=$require('*infrajs/ext/is.js');
		
		
		$html.=$require('*infrajs/ext/js.js');
		
		
		$html.=$require('*infrajs/ext/toString.js');
		$html.=$require('*infrajs/ext/loaded.js');
		$html.=$require('*infrajs/ext/ext.js');
		
		$html.=$require('*infrajs/ext/css.js');
		$html.=$require('*infrajs/ext/autoview.js');
		
		
		
		*/
		$html.=$require('*infrajs/ext/scroll.js');
			

			
		$html.=$require('*seo/seo.ext.js');
		$html.=$require('*infrajs/ext/global.js');
		
		$html.=$require('*autoedit/autoedit.ext.js');
		$html.=$require('*infrajs/ext/onsubmit.js');
		$html.=$require('*infrajs/ext/autosave.js');
		$html.=$require('*popup/popup.js');
		$html.=$require('*contacts/showContacts.js');
		$html.=$require('*session/session.js');
		$html.=$require('*infrajs/ext/session.js');
		$html.=$require('*infrajs/ext/autofocus.js');

		$html.=$require('*infrajs/make.js');

		if(isset($_GET['loadJSON'])){
			$ts=explode(',',$_GET['loadJSON']);
			for($i=0,$l=sizeof($ts);$i<$l;$i++){
				if(!$ts[$i])continue;
				$html.=$loadJSON($ts[$i]);
			}
		}
		if(isset($_GET['require'])){
			$ts=explode(',',$_GET['require']);
			for($i=0,$l=sizeof($ts);$i<$l;$i++){
				if(!$ts[$i])continue;
				$html.=$require($ts[$i]);
			}
		}
		if(isset($_GET['loadTEXT'])){
			$ts=explode(',',$_GET['loadTEXT']);
			for($i=0,$l=sizeof($ts);$i<$l;$i++){
				if(!$ts[$i])continue;
				$html.=$loadTEXT($ts[$i]);
			}
		}		
		return $html;
	},array($_SERVER['QUERY_STRING']),$re);
	@header('content-type: text/javascript; charset=utf-8');
	echo $html;
?>