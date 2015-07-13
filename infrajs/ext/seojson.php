<?php
namespace itlife\infrajs\infrajs\ext;
use itlife\infrajs\infrajs;
use itlife\infrajs\infrajs\ext\external;
class seojson {
	function check(&$layer){
		if(!empty($layer['seojsontpl']))$layer['seojson']=infra_template_parse(array($layer['seojsontpl']),$layer);
		if(empty($layer['seojson']))return;
		$item=self::load($layer['seojson']);
		if(!$item)return;

		$html=infra_html();

		if(!empty($item['image_src'])){
			self::meta($html,$item,'link','image_src');
			self::meta($html,$item,'property','og:image',$item['image_src']);
			self::meta($html,$item,'name','twitter:image',$item['image_src']);
			self::meta($html,$item,'itemprop','image',$item['image_src']);
		}
			
		if(!empty($item['canonical'])){
			self::meta($html,$item,'link','canonical');
			self::meta($html,$item,'property','og:url',$item['canonical']);
			self::meta($html,$item,'property','business:contact_data:website',$item['canonical']);
		}

		if(!empty($item['description'])){
			self::meta($html,$item,'name','description');
			self::meta($html,$item,'property','og:description',$item['description']);
			self::meta($html,$item,'name','twitter:description',$item['description']);
		}
			
		self::meta($html,$item,'name','keywords');

		if(!empty($item['title'])){
			self::meta($html,$item,'title','title');
			self::meta($html,$item,'property','og:title',$item['title']);
			self::meta($html,$item,'name','twitter:title',$item['title']);
		}

		if(!empty($item['site_name'])){
			self::meta($html,$item,'property','site_name');	
			self::meta($html,$item,'itemprop','name',$item['site_name']);
		}
		
		if(!empty($item['properties']))foreach($item['properties'] as $k=>$v){
			self::meta($html,$item['properties'],'property',$k);
		}
		if(!empty($item['names']))foreach($item['names'] as $k=>$v){
			self::meta($html,$item['names'],'name',$k);
		}
		if(!empty($item['itemprops']))foreach($item['itemprops'] as $k=>$v){
			self::meta($html,$item['itemprops'],'itemprop',$k);
		}

		infra_html($html,true);
	}
	function load($src){
		$item=infra_loadJSON($src);
		if (!$item) {
			$item=array();
		}
		
		if ($item['external']) {
			if (!is_array($item['external'])) {
				$item['external']=explode(', ', $item['external']);
			}

			foreach ($item['external'] as $esrc) {
				$ext=self::load($esrc);
				foreach ($ext as $k => $v) {
					if( in_array($k,array('itemprops','property','name')) )continue;
					if(isset($item[$k]))continue;
					$item[$k]=$v;
				}
				if(!empty($item['properties']))foreach($ext['properties'] as $k=>$v){
					if(isset($item['properties'][$k]))continue;
					$item['properties'][$k]=$v;
				}
				if(!empty($item['names']))foreach($ext['names'] as $k=>$v){
					if(isset($item['names'][$k]))continue;
					$item['names'][$k]=$v;
				}
				if(!empty($item['itemprops']))foreach($ext['itemprops'] as $k=>$v){
					if(isset($item['itemprops'][$k]))continue;
					$item['itemprops'][$k]=$v;
				}
			}
		}
		
		return $item;
	}
	function value($value){//load для <input value="...
		$value=preg_replace('/\$/','&#36;',$value);
		$value=preg_replace('/"/','&quot;',$value);
		return $value;
	}
	function meta(&$html,$item,$type,$name,$val=null){
		if(is_null($val))$val=$item[$name];
		if(empty($val))return;
		$val=seojson::value($val);

		if($type=='property'){
			$r=preg_match('/<meta.*property=.{0,1}'.$name.'.{0,1}.*>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n\t<meta property=\"".$name.'" content="'.$val.'"/>',$html);
			}else{
				$html=preg_replace('/(<meta.*property=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta property="'.$name.'" content="'.$val.'" >',$html);
			}
		}else if($type=='title'){
			$r=preg_match('/<'.$name.'>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n\t<".$name.">".$val.'</'.$name.'>',$html);
			}else{
				$html=preg_replace('/<'.$name.'>.*<\/'.$name.'>/i','<'.$name.'>'.$val.'</'.$name.'>',$html);
			}
		}else if($type=='name'){
			$r=preg_match('/<meta.*name=.{0,1}'.$name.'.{0,1}.*>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n\t<meta name=\"".$name.'" content="'.$val.'"/>',$html);
			}else{
				$html=preg_replace('/(<meta.*name=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta name="'.$name.'" content="'.$val.'" >',$html);
			}
		}else if($type=='link'){
			if(isset($item[$name])){
				$r=preg_match('/<link.*rel=.{0,1}'.$name.'.{0,1}.*>/i',$html);
				if(!$r){
					$html=str_ireplace('<head>',"<head>\n\t<link rel=\"".$name.'" href="'.$val.'"/>',$html);
				}else{
					$html=preg_replace('/(<link.*rel=.{0,1}'.$name.'.{0,1})(.*>)/i','<link rel="'.$name.'" href="'.$val.'" >',$html);
				}
			}
		}else if($type=='itemprop'){
			$r=preg_match('/<meta.*itemprop=.{0,1}'.$name.'.{0,1}.*>/i',$html);
			if(!$r){
				$html=str_ireplace('<head>',"<head>\n\t<meta itemprop=\"".$name.'" content="'.$val.'"/>',$html);
			}else{
				$html=preg_replace('/(<meta.*itemprop=.{0,1}'.$name.'.{0,1})(.*>)/i','<meta itemprop="'.$name.'" content="'.$val.'" >',$html);
			}
		}
		
	}
	
}