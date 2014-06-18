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
/**/
?>
