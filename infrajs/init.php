<?php
	@define('ROOT','./');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	
	function infrajs($index,$div,$src){
		$conf=infra_config();
		infra_admin_modified();//Здесь уже выход если у браузера сохранена версия
		$html=infra_admin_cache('index.php',function($index,$div,$src){
			@header("infrajs-cache: Fail");//Афигенный кэш, когда используется infrajs не подгружается даже
			infra_require('*infrajs/initphp.php');
			global 	$infrajs;

			$h=infra_loadTEXT($index);

			infra_html($h);//Добавить снизу
			
			$layers=&infra_loadJSON($src);
			
			if($div)infra_fora($layers,function(&$layer) use($div){
				$layer['div']=$div;
			});
			
			infrajs_checkAdd($layers);
			infrajs_check();//В infra_html были добавленыs все указаные в layers слои
			
			$html=infra_html();
			
			$script=<<<END
				<link rel="stylesheet" href="infra/plugins/infrajs/style.css"/>
END;

			$conf=infra_config();
			if(!$conf['infrajs']['onlyserver']){
				$script.=<<<END
					<script src="infra/plugins/infrajs/initjs.php?loadJSON={$src}"></script>
END;
			}
			$html=str_replace('<head>','<head>'.$script,$html);
			
			if(!$conf['infrajs']['onlyserver']){
				$script=<<<END
					<script type="text/javascript">
							var layers=infra.loadJSON("{$src}");
							var div='{$div}'
							if(div)infra.fora(layers,function(layer){
								layer.div=div;
							});
							infrajs.checkAdd(layers);
							infra.listen(infra.State,'onchange',function(){
								infrajs.check();
							});
					</script>
END;
				$html.=$script;
			}

			return $html;
		},array($index,$div,$src,$_SERVER['QUERY_STRING']));//Если не кэшировать то будет reparse

		@header("HTTP/1.1 200 Ok");

		
		
		
		echo $html;
	}
?>
