<?php
//Свойства seo и seotpl. title и title, keywords keywords
/*
Карта сайта обновляется при клике и если файла вообще нет
*/
use itlife\infrajs\infrajs;

function infrajs_seo_init(){//Делается при каждой пробежки
	$store=&infrajs::store();
	$store['seo']=array();
	$store['seolayer']=array();
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	if(!$conf['seo']['robots'])return;
	infra_admin_cache('infrajs_seo_init',function(){
		if(!infra_theme('robots.txt')){
			$data=array();
			$data['host']=$_SERVER['HTTP_HOST'];
			$data['root']=infra_view_getRoot();
			
			$html=infra_template_parse('*seo/sitemap.tpl',$data,'robots')."\n";
			$dirs=infra_dirs();
			file_put_contents('robots.txt',$html);
		}
	});
}
function infrajs_seo_checkseolinktpl(&$layer){
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	if(!isset($layer['seotpl']))return;
	if(!isset($layer['seo']))$layer['seo']=array();
	$props=array('tpl','link','json','name','title');
	for($i=0,$l=sizeof($props);$i<$l;$i++){
		if(isset($layer['seotpl'][$props[$i]]))$layer['seo'][$props[$i]]=infra_template_parse(array($layer['seotpl'][$props[$i]]),$layer);
	}
}
function infrajs_seo_checkopt(&$layer){
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	if(!isset($layer['seo']))return;
	$seo=&$layer['seo'];
	if(!$seo['name']){
		die("У seo необходио указать name. Слой:".$layer['tpl'].':'.$layer['tplroot']);
	}
	$seo['name']=infra_State_forFS($seo['name']);
	if(!isset($seo['link'])){
		$seo['link']=$layer['crumb']->toString();
		if(preg_match("/###/",$seo['link'])){
			die("Невозможно автоматически определить Link Необходимо указать в layers.json ".$seo['link'].". Слой:".$seo['name']);
		}
	}
	if(isset($seo['schema'])){
		if(!isset($seo['items'])&&!isset($seo['defitems'])){
			die("Если указан schema должно быть указано items или defitems. Слой:".$seo['name']);
		}
	}
	if(!isset($seo['schema'])&&!isset($seo['items'])){
		$item=array(
			"data"=>true,
			"keywords"=>$seo['keywords'],
			"title"=>$seo['title'],
			"description"=>$seo['description']
		);
		unset($seo['keywords']);
		unset($seo['title']);
		unset($seo['description']);
		$seo['items']=array($item);
	}
}

function infrajs_seo_collectLayer(&$layer){
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	if(!isset($layer['seo']))return;
	$store=&infrajs_store();
	$store['seo'][$layer['seo']['name']]=$layer['seo'];
}





function infrajs_seo_now(&$layer){
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	if(!isset($layer['seo']))return;
	$store=&infrajs_store();
	$store['seolayer']=&$layer;

}
function infrajs_seo_save(){
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	infra_admin_cache('infrajs_seo_save',function(){
		$store=&infrajs_store();
		$dir='infra/cache/seo/';
		if(is_dir($dir)){
			$list=infra_loadJSON('*pages/list.php?src='.$dir.'&onlyname=1');
			foreach($list as $file){
				unlink($dir.infra_tofs($file));
			}
			$r=rmdir($dir);
			if(!$r){
				$conf=infra_config();
				if($conf['debug'])die('Не удалось удалить папку '.$dir);
			}

		}
		@mkdir($dir);
		foreach($store['seo'] as $name=>$seo){
			file_put_contents($dir.infra_tofs($name).'.json',infra_json_encode($seo));
		}
	});
}
function infrajs_seo_value($value){//load для <input value="...
	if(!$value)$value='';
	$value=preg_replace('/"/','&quot;',$value);
	return $value;
}
function infrajs_seo_apply(){
	$conf=infra_config();
	if(!$conf['seo']['seo'])return;
	$store=&infrajs_store();
	$layer=&$store['seolayer'];
	if(!$layer)return;
	$seo=$layer['seo'];

	$reallink=$layer['crumb']->toString();

	$item=$seo;
	if(isset($seo['name'])){
		$id=$seo['name'].'|'.$reallink;
		$r=infra_loadJSON('*seo/seo.php?type=item&id='.$id);
		$item=$r['item'];
	}
	

	//Применяем
	$html=infra_html();
	
	$name='keywords';//stencil//
	if(isset($item[$name])){
		if(!is_string($item[$name]))$item[$name]=implode(', ',$item[$name]);

		$r=preg_match('/<meta.*name=.{0,1}'.$name.'.{0,1}.*>/i',$html);
		if(!$r){
			$html=str_ireplace('<head>',"<head>\n<meta name=\"".$name.'" content="'.infrajs_seo_value($item[$name]).'">',$html);
		}else{
			$html=preg_replace('/(<meta.*name=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta name="'.$name.'" content="'.infrajs_seo_value($item[$name]).'" >',$html);
		}
	}
	$name='description';//stencil//
	if(isset($item[$name])){
		$r=preg_match('/<meta.*name=.{0,1}'.$name.'.{0,1}.*>/i',$html);
		if(!$r){
			$html=str_ireplace('<head>',"<head>\n<meta name=\"".$name.'" content="'.infrajs_seo_value($item[$name]).'">',$html);
		}else{
			$html=preg_replace('/(<meta.*name=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta name="'.$name.'" content="'.infrajs_seo_value($item[$name]).'" >',$html);
		}
	}
	$name='title';//stencil//
	if(isset($item[$name])){
		$r=preg_match('/<title>/i',$html);
		if(!$r){
			$html=str_ireplace('<head>',"<head>\n<title>".infrajs_seo_value($item[$name]).'</title>',$html);
		}else{
			$html=preg_replace('/<title>.*<\/title>/i','<title>'.infrajs_seo_value($item[$name]).'</title>',$html);
		}
	}
	infra_html($html,true);
	
}