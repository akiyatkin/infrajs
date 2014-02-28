<?php 
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*autoedit/admin.inc.php');
	$ans=array('result'=>0);
	$type=$_REQUEST['type'];
	$id=$_REQUEST['id'];
	$ans['id']=$id;
	$ans['type']=$type;
	$RTEABLE=array('tpl','html','htm','');
	$CORABLE=array('json','tpl','html','htm','txt','js','css','');
	if(infra_admin()||$type=='admin'){
		if($type=='admin'){
			$ans['admin']=infra_admin();
			$ans['result']=1;
		}else if($type=='addfile'){
			$name=$_REQUEST['name'];
			if($name){
				$file=infra_theme($id.$name,'sfn');
				$takepath=autoedit_takepath($file);
				$take=infra_plugin($takepath,'sfj');
				$ans['path']=$id.$name;
				$ans['take']=$take;
			}
		}else if($type=='version'){
			$ar=array();
			$ar['interface']=filemtime(ROOT.'infra/infra/interface.php');
			$ar['js']=filemtime(ROOT.'infra/plugins/infra/infra.js');
			$ar['layers']=filemtime(ROOT.'infra/layers.js');
			$ar['infrajs']=filemtime(ROOT.infra_theme('*infrajs/infrajs.js'));
			$ans['dates']=$ar;
			$ans['result']=1;
		}else if($type=='createcache'){
			@session_start();
			$ans['iscache']=(int)$_SESSION['cache_save'];
			$ans['result']=1;
		}else if($type=='autoedit'){
			$list=$_GET['list'];
			if($list){
				$ans['result']=1;
				$list=infra_tophp($list);
				$files=array();
			}else{
				$ans['result']=0;
			}
		}else if($type=='editfolder'){

			$folder=$id;
			$parent=preg_replace("/^\*/","infra/data/",$folder);
			$p=explode('/',$parent);
			array_pop($p);//'/'
			array_pop($p);//'name/'
			$parent=implode('/',$p).'/';// *Разделы/
			if(preg_match('/^\*/',$parent)||preg_match('/^infra\/data/',$parent)){
				$parent=preg_replace("/^infra\/data\//","*",$parent);
			       	$ans['parent']=$parent;
			}

			$folder=infra_theme($id,'nsd');
			$folder=infra_toutf($folder);
			$folder=preg_replace("/^infra\/data\//","*",$folder);


			$ans['list']=infra_plugin('*pages/list.php?s=1&notsort=1&reverse=0&h=1&time=1&src='.$folder,'fj');
			$folders=infra_plugin('*pages/list.php?s=1&d=1&f=0&onlyname=1&notsort=1&reverse=0&h=1&time=1&src='.$folder,'fj');
			$ans['folders']=array();
			if($folders){
				foreach($folders as &$v){
					if($v&&$v!='.'&&$v!='..'){
						$ans['folders'][]=array('name'=>$v);
					}
				}
			}
			if($ans['list']){
				foreach($ans['list'] as &$v){
					$e=$v['ext']?'.'.$v['ext']:'';
					$file=$folder.$v['name'].$e;
					$takepath=autoedit_takepath($file);
					$d=infra_plugin($takepath,'sfj');
					$v['corable']=in_array(strtolower($v['ext']),$CORABLE);
					$v['rteable']=in_array(strtolower($v['ext']),$RTEABLE);

					$v['pathload']=infra_theme('infra/plugins/autoedit/download.php?'.$file,'fu');

					if($v['rteable']) $ans['rteable']=(bool)infra_theme('infra/lib/wymeditor/','d');
					$v['mytake']=autoedit_ismytake($file);
					if($d){
						$v['take']=$d['date'];
					}
				}
			}
			$ans['result']=1;
		}else if($type=='takeinfo'){
			$file=infra_theme($id,'sfn');
			$takepath=autoedit_takepath($file);
			$take=infra_plugin($takepath,'sfj');
			$ans['path']=$id;
			$ans['take']=$take;
			preg_match("/\.([a-zA-Z]+)$/",$id,$match);
			$ans['ext']=strtolower($match[1]);
		}else if($type=='takeshow'){
			$takepath=autoedit_takepath();
			$list=infra_plugin('*pages/list.php?onlyname=1&src='.$takepath,'fj');
			$files=array();
			if($list){
				foreach($list as $file){
					$d=infra_plugin($takepath.$file,'sfj');
					if(is_dir(ROOT.'infra/')){
						$d['path']=str_replace("infra/data/","*",$d['path']);
					}else{
						$d['path']=str_replace("core/data/","*",$d['path']);//dedicated
					}
					
					$d['modified']=filemtime(ROOT.infra_theme($d['path']));
					preg_match("/\.([a-zA-Z]+)$/",$d['path'],$match);
					$d['ext']=strtolower($match[1]);
					$files[]=$d;
				}
			}
			$ans['files']=$files;
		}else if($type=='corfile'){
			$ans['name']=preg_replace("/(.*\/)*/",'',$id);
			if($ans['name'][0]=='*'){
				$ans['name']=preg_replace('/^\*/','',$ans['name']);
				$ans['folder']='*';
			}else{
				$ans['folder']=str_replace($ans['name'],'',$id);
			}
			$ans['content']=infra_plugin($id,'nfs');
		}else if($type=='editfile'){
			$file=infra_theme($id,'fsn');//Можно указывать путь без расришения
			$ans['path']=$id;
			if(!$file){
				$ans['take']=false;
				$ans['isfile']=false;
				$ans['msg']='Файл ещё не существует, <br>рекомендуется для загрузки нового файла<br>скачать и поправить файл из другова<br>анологичного места. Если это возможно.';
				$ans['result']=1;
				preg_match("/\.([a-zA-Z]+)$/",$id,$match);
				$ans['ext']=strtolower($match[1]);
			}else{
				$ans['isfile']=true;
				$takepath=autoedit_takepath($file);
				$take=infra_plugin($takepath,'sfj');
				if($take){
					$ans['take']=$take['date'];
				}else{
					$ans['take']=false;
				}

				$ans['size']=ceil(filesize(ROOT.$file)/1000);
				$ans['time']=filemtime(ROOT.$file);
				$ans['result']=1;
				preg_match("/\.([a-zA-Z]+)$/",$file,$match);
				$ans['ext']=strtolower($match[1]);
			}
			$ans['corable']=in_array(strtolower($ans['ext']),$CORABLE);
			$ans['rteable']=(bool)infra_theme('infra/lib/wymeditor/','d');
			if($ans['rteable']){
				$ans['rteable']=in_array(strtolower($ans['ext']),$RTEABLE);
			}
			$imgext=array('jpg','png','gif','jpeg');
			infra_forr($imgext,function(&$ans, $e){
				if($e==$ans['ext'])$ans['image']=true;//Значит это картинка
			},array(&$ans));


			if($file){//Если файл есть
				$p=explode('/',$file);//Имя с расширением
				$ans['file']=array_pop($p);
			}else{//Если файла нет.. определяем имя из id
				$p=explode('/',$id);//Имя с расширением
				$ans['file']=array_pop($p);
			}
			$ans['file']=preg_replace("/^\*/",'',infra_toutf($ans['file']));

			$p=explode('/',$ans['id']);
			array_pop($p);
			$ans['folder']=implode('/',$p);
			if($ans['folder']=='/'||!$ans['folder'])$ans['folder']='*';
			else $ans['folder'].='/';
			$ans['pathload']=infra_theme('infra/plugins/autoedit/download.php?'.$id,'fu');
			$ans['path']=infra_theme($id,'fusn');

		}else if($type=='deletefile'||$type=='renamefile'||$type=='copyfile'){
			$ans['name']=preg_replace("/(.*\/)*/",'',$id);
			if($ans['name'][0]=='*'){
				$ans['name']=preg_replace('/^\*/','',$ans['name']);
				$ans['folder']='*';
			}else{
				$ans['folder']=str_replace($ans['name'],'',$id);
			}
			$ans['full']=infra_theme($ans['folder'],'fsn');
			$ans['full']=infra_toutf($ans['full']);
			$file=infra_theme($id,'fsn');
			$ans['isfile']=(bool)$file;

			$takepath=autoedit_takepath($file);
			$ans['take']=infra_plugin($takepath,'sfj');
		}
	}else{
		return err($ans,'Вам нужно авторизоваться');
	}
	echo infra_tojs($ans);
?>
