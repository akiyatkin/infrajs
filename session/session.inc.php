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
	function infra_session_getEmail(){
		return infra_once('infra_session_getEmail',function(){
			//В рамках одного запуска php скрипта можно cat_getSessionEmail можно вызывать сколько угодно раз. Обращение к базе будет одно.
			$db=&infra_session_db();
			if(!$db)return;
			infra_require('*session/session.php');
			$session_id=infra_session_getId();
			if(!$session_id)return false;
			$sql='select email from ses_sessions where session_id=?';
			$stmt=$db->prepare($sql);
			$stmt->execute(array($session_id));
			$email=$stmt->fetchColumn();
			return $email;
		});
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
	function infra_session_change($id,$pass){
		infra_view_setCookie(infra_session_getName('pass'),$pass);
		infra_view_setCookie(infra_session_getName('id'),$id);
		infra_view_setCookie(infra_session_getName('time'),1);
		$olddata=infra_session_get();
		$email=infra_session_getEmail();
		infra_session_syncNow();
		if(!$email){//Нельзя объединять два авторизированных аккаунта
			$newdata=infra_session_get();
			$email=infra_session_getEmail();
			if($email){//Объединяем только с авторизированным аккаунтом
				$data=array_merge($userold,$usernew);
				infra_session_set('',$data);
			}
		}

	}
/**/
?>
