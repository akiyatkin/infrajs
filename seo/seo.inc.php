<?php
function seo_delitem($name,$link){
	$seocache=infrajs_seo_getSeo($name);
	

	$src='infra/data/seo/'.infra_tofs($name).'.json';
	$seo=infra_loadJSON($src);
	if(!$seo)return false;
	$r=false;
	foreach($seo['items'] as $k=>$item){
		$l=infra_template_parse(array($seocache['link']),$item['data']);
		if($l==$link){
			$r=true;
			unset($seo['items'][$k]);
			break;
		}
	}
	if(!$r)return $r;
	return file_put_contents($src,infra_json_encode($seo));
}

function infrajs_seo_saveitem($name,$item){
	$dirs=infra_dirs();
	@mkdir($dirs['data'].'seo/');
	$src=$dirs['data'].'seo/'.infra_tofs($name).'.json';
	$data=infra_loadJSON($src);
	if(!$data){
		$data=array('items'=>array());
	}
	$r=false;
	if(is_string($item['keywords'])){
		$keys=explode(',',$item['keywords']);
		foreach($keys as $k=>$v){
			$keys[$k]=trim($v);
		}
		$keys=array_values(array_unique($keys));
		$item['keywords']=$keys;
		
	}
	foreach($data['items'] as $k=>$it){
		if($it['data']!=$item['data'])continue;
		$data['items'][$k]=$item;
		$r=true;
		break;
	}
	if(!$r)$data['items'][]=$item;
	file_put_contents($src,infra_json_encode($data));
	return $src;
}
function infrajs_seo_list(){
	$list=infra_loadJSON('*pages/list.php?src=infra/cache/seo/&f=1&d=0&onlyname=2');
	return $list;
}
function infrajs_seo_getSeo($name,$link=false){
	return infra_admin_cache('infrajs_seo_get',function($name,$link){
		return _infrajs_seo_getSeo($name,$link);
	},array($name),isset($_GET['re']));
}
function infrajs_seo_getSeoItem($name,$link){
	$seo=infrajs_seo_getSeo($name);
	foreach($seo['items'] as &$item){
		if($item['link']==$link){
			$seo['item']=$item;
			break;
		}
	}	
	foreach($seo['defitems'] as &$item){
		if($item['link']==$link){
			$seo['defitem']=$item;
			break;
		}
	}
	return $seo;
}
function _infrajs_seo_getSeo($name){
	//Возвращает все [seo,seo] с item указанными по умолчанию и item заполненными пользователем
	//Те что заполенны пользователем отмечены user:true
	//Для каждого item расчитан link
		
		
	$seo=infra_loadJSON('infra/cache/seo/'.$name.'.json');//Описания сделанные программистом в layers.json {link:'',items:[{}]}

	
	$linktpl=$seo['link'];
	if(!$linktpl)$linktpl='{root:}';

	if(!isset($seo['items']))$seo['items']=array();
	foreach($seo['items'] as &$item){
		$link=infra_template_parse(array($linktpl),$item['data']);
		$item['layer']=true;//item описан в слое
		$item['link']=$link;
	}		
	$data=infra_loadJSON('infra/data/seo/'.$name.'.json');//Описания сделанные из админки
	
	if(!isset($data['items']))$data['items']=array();
	foreach($data['items'] as &$item){
		$link=infra_template_parse(array($linktpl),$item['data']);
		$item['user']=true;//item описан пользователем
		$item['link']=$link;
	}

	$items=array();
	foreach($seo['items'] as &$item){
		$items[$item['link']]=$item;
	}
	foreach($data['items'] as &$item){
		
		if(!isset($items[$item['link']])){
			$items[$item['link']]=$item;
		}else{//было в layers а сейчас нашли и пользовательскую
			foreach($item as $p=>$v){
				$items[$item['link']][$p]=$item[$p];
			}
		}
	}

	$seo['items']=array_values($items);
		
			

	///************////
	
	if(isset($seo['defitems'])){
		$def=infra_loadJSON($seo['defitems']);
	}else{
		$def=array();
	}
	if(!isset($def['items']))$def['items']=array();
	$def['name']=$seo['name'];
	foreach($def['items'] as &$item){
		$item['link']=infra_template_parse(array($linktpl),$item['data']);
		$item['def']=true;
	}

	$seo['defitems']=$def['items'];
	

	
	$all=array();
	foreach($seo['defitems'] as &$item){
		$all[$item['link']]=&$item;
	}
	foreach($seo['items'] as &$item){
		if(!isset($all[$item['link']])){
			$all[$item['link']]=$item;
		}else{
			foreach($item as $p=>$v){
				$all[$item['link']][$p]=$item[$p];
			}
		}
	}
	$seo['all']=$all;

	$seo['defitem']=false;	
	$seo['item']=false;

	
	return $seo;
}
function _infrajs_seo_getSeo2($name){
	//Возвращает все [seo,seo] с item указанными по умолчанию и item заполненными пользователем
	//Те что заполенны пользователем отмечены user:true
	//Для каждого item расчитан link
		
		
	$seo=infra_loadJSON('infra/cache/seo/'.$name.'.json');//{link:'',items:[{}]}

	
	$linktpl=$seo['link'];
	if(!$linktpl)$linktpl='{root:}';

	if(!isset($seo['items']))$seo['items']=array();
	foreach($seo['items'] as &$item){
		$link=infra_template_parse(array($linktpl),$item['data']);
		$item['layer']=true;//item описан в слое
		$item['link']=$link;
	}		
	$data=infra_loadJSON('infra/data/seo/'.$name.'.json');
	
	if(!isset($data['items']))$data['items']=array();
	foreach($data['items'] as &$item){
		$link=infra_template_parse(array($linktpl),$item['data']);
		$item['user']=true;//item описан пользователем
		$item['link']=$link;
	}

	$items=array();
	foreach($seo['items'] as &$item){
		$items[$item['link']]=$item;
	}
	foreach($data['items'] as &$item){
		
		if(!isset($items[$item['link']])){
			$items[$item['link']]=$item;
		}else{//было в layers а сейчас нашли и пользовательскую
			foreach($item as $p=>$v){
				$items[$item['link']][$p]=$item[$p];
			}
		}
	}

	$seo['items']=array_values($items);
		
			

	///************////
	
	if(isset($seo['defitems'])){
		$def=infra_loadJSON($seo['defitems']);

	}else{
		$def=array();
	}
	if(!isset($def['items']))$def['items']=array();
	$def['name']=$seo['name'];
	foreach($def['items'] as &$item){
		$item['link']=infra_template_parse(array($linktpl),$item['data']);
		$item['def']=true;
	}

	$seo['defitems']=$def['items'];
	

	
	$all=array();
	foreach($seo['defitems'] as &$item){
		$all[$item['link']]=&$item;
	}
	foreach($seo['items'] as &$item){
		if(!isset($all[$item['link']])){
			$all[$item['link']]=$item;
		}else{
			foreach($item as $p=>$v){
				$all[$item['link']][$p]=$item[$p];
			}
		}
	}
	$seo['all']=$all;

	$seo['defitem']=false;	
	$seo['item']=false;

	
	return $seo;
}

function _seo_all($name=false){
	//Возвращает все [seo,seo] с item указанными по умолчанию и item заполненными пользователем
	//Те что заполенны пользователем отмечены user:true
	//Для каждого item расчитан link
	$list=infra_admin_cache('seo.inc.php',function($name){
		$list=infra_loadJSON('*pages/list.php?src=infra/cache/seo/&f=1&d=0&onlyname=2');
		$seo=array();
		$items_cache=array();
		foreach($list as $i){
			
			$s=infra_loadJSON('infra/cache/seo/'.$i.'.json');//{link:'',items:[{}]}
			
			
			if(!isset($s['link']))continue;//link обязателен
			$linktpl=$s['link'];
			$seo[$i]=$s;
			if(!isset($seo[$i]['items']))$seo[$i]['items']=array();
			$items_cache[$i]=array();
			foreach($seo[$i]['items'] as $item){

				if($linktpl){
					$link=infra_template_parse(array($linktpl),$item['data']);
				}else{
					$link='';
				}


				$item['layer']=true;
				$item['link']=$link;
				$items_cache[$i][$link]=$item;
			}
		}

		$list=infra_loadJSON('*pages/list.php?src=infra/data/seo/&f=1&d=0&onlyname=2');
		
		foreach($list as $i){
			if(!isset($seo[$i]))continue;//Хранятся данные seo для слоя которого сейчас нет.. и нет значит и link
			$linktpl=$seo[$i]['link'];
			$data=infra_loadJSON('infra/data/seo/'.$i.'.json');
			if(!isset($data['items']))continue;//items обязателен
			

			foreach($data['items'] as $n=>$item){
				$link=infra_template_parse(array($linktpl),$item['data']);
				$item['user']=true;
				$item['link']=$link;
				
				if(isset($items_cache[$i][$link])){
					foreach($item as $k=>$v){
						$items_cache[$i][$link][$k]=$v;
					}
				}else{
					$items_cache[$i][$link]=$item;
				}
			}
		}
		$list=array();
		foreach($items_cache as $k=>$v){
			$r=array(
				'json'=>@$seo[$k]['json'],
				'tpl'=>@$seo[$k]['tpl'],
				'name'=>$k,
				'defitems'=>@$seo[$k]['defitems'],
				'link'=>$seo[$k]['link'],
				'schema'=>$seo[$k]['schema'],
				'items'=>array_values($v)
			);
			foreach($r['items'] as $item){
				if(isset($item['user'])){
					if($item['user']){
						$r['user']=true;
						break;
					}
				}
			}


			$list[]=$r;
			if($k==$name)return $r;
		}
		
	
		if($name)return array('name'=>$name);
		return $list;
	},array($name));

	if($name){
		$seos=array($list);
	}else{
		$seos=$list;
	}

	return $seos;
}




function &seo_createItem(&$list,$data,$title=false){
		if(!$title){
			if(is_string($data))$title=$data;
			else $title='';
		}
		$key=seo_normalizeValue($data);
		if(is_array(@$list[$key]))return $list[$key];
		$v=array(
			'data'=>$data,
			'title'=>$title,
			'keywords'=>array($title),
			'description'=>$title
		);
		$list[$key]=&$v;
		return $v;
	}

function seo_normalizeValue($key){
	if(is_integer($key))$key=(string)$key;
	if(is_array($key))$key=infra_json_encode($key);
	if(!is_string($key))$key='';

	$key=strip_tags($key);
	$key=infra_State_forFS($key);
	return $key;
}
function seo_addKeys(&$list,$keys){
	foreach($keys as $k=>$key){		
		$key=seo_normalizeValue($key);
		if(mb_strlen($key)>60)continue;
		if(mb_strlen($key)<2)continue;
		$list[]=$key;
	}
}
function seo_pageResearch($page,&$v){
	if(!isset($v['keywords']))$v['keywords']=array();

	if(preg_match_all('/<h1>(.*?)<\/h1>/si', $page, $arr)){
		$t=seo_normalizeValue($arr[1][0]);
		if($t)$v['title']=$t;
		seo_addKeys($v['keywords'],$arr[1]);
	}
	if(preg_match_all('/<h2>(.*?)<\/h2>/si', $page, $arr)){
		seo_addKeys($v['keywords'],$arr[1]);
	}
	if(preg_match_all('/<h3>(.*?)<\/h3>/si', $page, $arr)){
		seo_addKeys($v['keywords'],$arr[1]);
	}
	if(preg_match_all('/<li>(.*?)<\/li>/si', $page, $arr)){
		seo_addKeys($v['keywords'],$arr[1]);
	}
	if(preg_match_all('/<strong>(.*?)<\/strong>/si', $page, $arr)){
		seo_addKeys($v['keywords'],$arr[1]);
	}
	if(preg_match_all('/<b>(.*?)<\/b>/si', $page, $arr)){
		seo_addKeys($v['keywords'],$arr[1]);
	}
	if(preg_match_all('/<p>(.*?)<\/p>/si', $page, $arr)){
		$page=implode(' ',$arr[1]);
	}
	$v['keywords']=array_values(array_unique($v['keywords']));
	$v['description']=preg_replace("/\..*/si",".",strip_tags($page));
	$v['description']=seo_normalizeValue($v['description']);
}