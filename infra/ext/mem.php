<?php
@define('ROOT','../../../../');
@define('INFRA_MEM_DIR','infra/cache/mem/');
@mkdir(ROOT.INFRA_MEM_DIR,0755);
function infra_mem_set($key,&$val){
	$mem=&infra_memcache();
	if($mem){
		$mem->set($key,$val);
	}else{
		$v=serialize($val);
		file_put_contents(ROOT.INFRA_MEM_DIR.$key.'.ser',$v);
	}
}
function infra_mem_get($key){
	$mem=&infra_memcache();
	if($mem){
		$r=$mem->get($key);
	}else{
		if(is_file(ROOT.INFRA_MEM_DIR.$key.'.ser')){
			$r=file_get_contents(ROOT.INFRA_MEM_DIR.$key.'.ser');
			$r=unserialize($r);
		}else{
			$r=null;
		}
	}
	return $r; 
}
function infra_mem_delete($key){
	$mem=&infra_memcache();
	if($mem){
		$r=$mem->delete($key);
	}else{
		$r=@unlink(ROOT.INFRA_MEM_DIR.$key.'.ser');
	}
	return $r;
}
function &infra_mem_flush(){
	$mem=&infra_memcache();
	if($mem){
		$mem->flush();
	}else{
		foreach (glob(ROOT.INFRA_MEM_DIR.'*.*') as $filename) {
			@unlink($filename);
		}
	}
}
global $infra_mem;
function &infra_memcache(){
	global $infra_mem;
	if($infra_mem)return $infra_mem;
	$r=false;
	if(!class_exists('Memcache'))return $r;
	$conf=infra_config();
	if(!@$conf['memcache'])return $r;
	$infra_mem=new Memcache;
	$infra_mem->connect($conf['memcache']['host'],$conf['memcache']['port']) or die ("Could not connect");
	return $infra_mem;
};


?>
