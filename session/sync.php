<?php
	require_once(__DIR__.'/../infra/infra.php');
	infra_require('*infra/ext/seq.php');
	infra_require('*session/session.inc.php');


	$ans=array('result'=>1);

	try{
		$db=&infra_session_db();
	}catch(Exception $e){
		$db=false;
	}

	if(!$db)return infra_echo($ans,'Нет соединения с базой данных. Сессия только в браузере.',0);
	$conf=infra_config();
	$conf=$conf['http'];
	
	$session_id=infra_view_getCookie('infra_session_id');
	$session_pass=infra_view_getCookie('infra_session_pass');

	$timelast=isset($_REQUEST['time'])?(int)$_REQUEST['time']:infra_view_getCookie('infra_session_time');
	if(!$timelast)$timelast=0;

	$time=time();//время синхронизации и время записываемых данных, устанавливается в cookie
	$ans['time']=$time;
	$list=infra_json_decode($_POST['list']);
	if($list===false)$list=null;

	infra_fora($list,function(&$li) use($time){
		$li['time']=$time;
	});
	
	$ans['is']=array();
	$ans['is']['news']=false;

	if($session_id){
		$session_id=infra_once('sync_php_checksession',function($session_id,$session_pass){
			$db=&infra_session_db();
			$sql='select password from ses_sessions where session_id=?';
			$stmt=$db->prepare($sql);
			$stmt->execute(array($session_id));
			$real_pass=$stmt->fetchColumn();
			if(md5($real_pass)!=$session_pass){
				$session_id=false;
			}
			return $session_id;
		},array($session_id,$session_pass),isset($_GET['re']));
		if(!$session_id){
			$ans['Авторизация']=false;
		}else{
			$ans['Авторизация']=true;
		}
	}
	//Здесь session_id проверенный

	if($session_id&&$timelast<=$time){
		$sql='select name, value, unix_timestamp(time) as time from ses_records where session_id=? and time>=from_unixtime(?) order by time, rec_id';
		$stmt=$db->prepare($sql);
		$stmt->execute(array($session_id,$timelast));
		$news=$stmt->fetchAll();
		$ans['list']=$list;
		$ans['orignews']=$news;
		if($news){
			
			$ans['news']=$news;
			infra_forr($ans['news'],function(&$v) use($list,&$ans){
				
				
				$v['value']=infra_json_decode($v['value'],true);
				
				
				$v['name']=infra_seq_right($v['name']);
				$r=infra_forr($list,function($item) use(&$v,&$ans){
					//Устанавливаемое значение ищим в новости
					if(infra_seq_contain($item['name'],$v['name'])!==false)return true;//найдено совпадение новости с устанавливаемым значением.. новость удаляем

					//Новость ищим в устанавливаемом значение
					$right=infra_seq_contain($v['name'],$item['name']);
					if($right)$v['value']=infra_seq_set($v['value'],$right,$item['value']);//Новость осталась но она включает устанавливаемые данные
				});

				if($r){
					$ans['counter']++;
					$ans['del']=$v;
					return new infra_Fix('del');
				}
			});


			$ans['is']['news']=!!$ans['news'];
			if(!$ans['news'])unset($ans['news']);
		}
		
	}
	
	$ans['is']['list']=!!$list;

	if($list){
		if(!$session_id){
			$pass=md5(print_r($list,true).time().rand());
			$pass=substr($pass,0,8);
			$sql='insert into `ses_sessions`(`password`) VALUES(?)';
			$stmt=$db->prepare($sql);
			$stmt->execute(array($pass));
			$session_id=$db->lastInsertId();
			infra_view_setCookie('infra_session_id',$session_id);
			infra_view_setCookie('infra_session_pass',md5($pass));
		}
		infra_session_writeNews($list,$session_id);
		//$ans['news']=array_merge($news,$list);
	}
	$ans['is']['session_id']=!!$session_id;
	
	return infra_echo($ans);
/**/