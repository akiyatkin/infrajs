<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	function &infra_session_db(){

		infra_admin_cache('session_db',function(){

			$db=&infra_db();

			
			if(!$db)return;

			$sql=<<<END
			CREATE TABLE IF NOT EXISTS `ses_sessions` (
			  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id сессии',
			  `password` varchar(255) NOT NULL COMMENT 'Пароль сессии',
			  `email` varchar(255) COMMENT 'Email чтоб была возможность авторизироваться и чтоб сессия для одного email-а была уникальная, сама сессия email никак не обрабатывает, обработка делается отдельно кому это надо.',
			  PRIMARY KEY (`session_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
END;
			try{
				$r=$db->exec($sql);
			}catch(Exception $e){
				echo '<pre>';
				print_r($e);
				die('adsf');
			}

			if($r===false)infra_error(print_r($db->errorInfo(),true));
			
			$sql=<<<END
			CREATE TABLE IF NOT EXISTS `ses_records` (
			  `rec_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id записи в сессию',
			  `session_id` int(10) NOT NULL COMMENT 'Уникальный идентификатор сессии пользователя',
			  `name` varchar(510) NOT NULL COMMENT 'Имя сохранённой переменной infra_seq_short',
			  `value` text NULL COMMENT 'Значение json переменной, NULL означает что переменная удалена',
			  `time` datetime NOT NULL COMMENT 'PHP-дата записи',
			  PRIMARY KEY (`rec_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
END;
			$r=$db->exec($sql);
			if($r===false)infra_error(print_r($db->errorInfo(),true));

		},array());
		return infra_db();
	}
	function infra_session_setPass($password){
		$db=&infra_session_db();
		if(!$db)return;
		
		$session_id=infra_session_getId();
		if(!$session_id){
		   infra_session_set('init', 1);
		   $session_id=infra_session_getId();
		}
		$sql='UPDATE ses_sessions
					SET password = ?
					WHERE session_id=?';
		$stmt=$db->prepare($sql);
		$stmt->execute(array($password, $session_id));
		return true;
	}
	function infra_session_getEmail($session_id=false){
		if(!$session_id)$session_id=infra_session_getId();
		return infra_once('infra_session_getEmail',function($session_id){
			$db=&infra_session_db();
			if(!$db)return;
			infra_require('*session/session.php');
			if(!$session_id)return false;
			$sql='select email from ses_sessions where session_id=?';
			$stmt=$db->prepare($sql);
			$stmt->execute(array($session_id));
			$email=$stmt->fetchColumn();
			return $email;
		},array($session_id));
	}
	function infra_session_setEmail($email){
		$db=&infra_session_db();
		if(!$db)return;
		
		$session_id=infra_session_getId();
		if(!$session_id){
		   infra_session_set('init', 1);
		   $session_id=infra_session_getId();
		}
		$sql='UPDATE ses_sessions
					SET email = ?
					WHERE session_id=?';
		$stmt=$db->prepare($sql);
		$stmt->execute(array($email, $session_id));
		return true;
	}
	function infra_session_getUser($email){
		return infra_once('infra_session_getUser',function($email){
			$db=&infra_session_db();
			if(!$db)return;
			$sql='select password, session_id, email from ses_sessions where email=?';
			$stmt=$db->prepare($sql);
			$stmt->execute(array($email));
			$userData=$stmt->fetch(PDO::FETCH_ASSOC);
			return $userData;
		},array($email));
	}
	function infra_session_writeNews($list,$session_id){
		if(!$list)return;
		$db=infra_session_db();
		global $infra_session_lasttime;
		$isphp=!!$infra_session_lasttime;
		//if(!$isphp)sleep(1);
		$sql='insert into `ses_records`(`session_id`, `name`, `value`, `time`) VALUES(?,?,?,FROM_UNIXTIME(?))';
		$stmt=$db->prepare($sql);
		$sql='delete from `ses_records` where `session_id`=? and `name`=? and `time`<=FROM_UNIXTIME(?)';
		$delstmt=$db->prepare($sql);
		infra_fora($list,function($rec) use($isphp,&$delstmt,&$stmt,$session_id){
			if(!$isphp&&$rec['name'][0]=='safe')return;
			$name=infra_seq_short($rec['name']);
			$delstmt->execute(array($session_id,$name,$rec['time']));
			$stmt->execute(array($session_id,$name,infra_json_encode($rec['value']),$rec['time']));
		});
	}
	function infra_session_change($session_id,$pass){

		
		$email=infra_session_getEmail();
		if(!$email){
			$email=infra_session_getEmail($session_id);
			if($email){//У новой сессии есть регистрация
				$newans=infra_session_recivenews();
				//Нужно это всё записать в базу данных для сессии 1
				infra_session_writeNews($newans['news'],$session_id);
				
			}
		}
		

		global $infra_session_data;
		$infra_session_data=array();
		infra_view_setCookie(infra_session_getName('pass'),$pass);
		infra_view_setCookie(infra_session_getName('id'),$session_id);
		infra_view_setCookie(infra_session_getName('time'),1);
		infra_session_syncNow();
	}
	function &infra_session_user_init($email){
		$user=infra_session_getUser($email);
		$session_id=$user['session_id'];
		$nowsession_id=infra_session_getId();
		if($session_id==$nowsession_id)return infra_session_get();
		return infra_once('infra_session_user_init',function($session_id){
			$sql='select name, value, unix_timestamp(time) as time from ses_records where session_id=? order by time,rec_id';
			$db=infra_session_db();
			$stmt=$db->prepare($sql);
			$stmt->execute(array($session_id));
			$news=$stmt->fetchAll();
			if(!$news)$news=array();
			$obj=array();
			infra_forr($news,function(&$v) use(&$obj){
				if($v['value']=='null'){
					$value=null;
				}else{
					$value=infra_json_decode($v['value']);
				}
				$right=infra_seq_right($v['name']);
				$obj=infra_seq_set($obj,$right,$value);
			});
			return $obj;
		},array($session_id));
	}
	function infra_session_user_get($email,$short=array(),$def=null){
		$obj=&infra_session_user_init($email);
		$right=infra_seq_right($short);
		$value=infra_seq_get($obj,$right);
		if(is_null($value))$value=$def;
		return $value;
	}
/**/
?>
