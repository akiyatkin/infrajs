<?php
namespace itlife\infrajs\infrajs\ext;
use itlife\infrajs\infrajs;
use itlife\infrajs\infrajs\ext\external;
class seojson {
	function value($value){//load для <input value="...
		if(!$value)$value='';
		$value=preg_replace('/\$/','&#36;',$value);
		$value=preg_replace('/"/','&quot;',$value);
		return $value;
	}
	function check(&$layer){
		if(!empty($layer['seojsontpl'])){
			$layer['seojson']=infra_template_parse(array($layer['seojsontpl']),$layer);
		}
		if(empty($layer['seojson']))return;
		$item=infra_loadJSON($layer['seojson']);
		if(!$item)return;

		/*
		$item=array(
			"keywords"=>$seo['keywords'],
			"html"
			"title"=>$seo['title'],
			"description"=>$seo['description'],
			"canonical"=>
		);
		*/
	//Применяем
		$html=infra_html();

		$name='canonical';//stencil//
		if(isset($item[$name])){
			$r=preg_match('/<link.*rel=.{0,1}'.$name.'.{0,1}.*>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n<link rel=\"".$name.'" href="'.seojson::value($item[$name]).'"/>',$html);
			}else{
				$html=preg_replace('/(<link.*rel=.{0,1}'.$name.'.{0,1})(.*>)/i','<link rel="'.$name.'" href="'.seojson::value($item[$name]).'" >',$html);
			}
		}

		$name='keywords';//stencil//
		if(isset($item[$name])){
			if(!is_string($item[$name]))$item[$name]=implode(', ',$item[$name]);
			$r=preg_match('/<meta.*name=.{0,1}'.$name.'.{0,1}.*>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n<meta name=\"".$name.'" content="'.seojson::value($item[$name]).'"/>',$html);
			}else{
				$html=preg_replace('/(<meta.*name=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta name="'.$name.'" content="'.seojson::value($item[$name]).'" >',$html);
			}
		}
		$name='description';//stencil//
		if(isset($item[$name])){
			$r=preg_match('/<meta.*name=.{0,1}'.$name.'.{0,1}.*>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n<meta name=\"".$name.'" content="'.seojson::value($item[$name]).'"/>',$html);
			}else{
				$html=preg_replace('/(<meta.*name=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta name="'.$name.'" content="'.seojson::value($item[$name]).'" >',$html);
			}
		}
		$name='title';//stencil//
		if(isset($item[$name])){
			$r=preg_match('/<title>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n<title>".seojson::value($item[$name]).'</title>',$html);
			}else{
				$html=preg_replace('/<title>.*<\/title>/i','<title>'.seojson::value($item[$name]).'</title>',$html);
			}
		}
		infra_html($html,true);
	}
}