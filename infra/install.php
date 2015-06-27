<?php
	$dirs=infra_dirs();
	if(!is_dir($dirs['ROOT'].'/infra'))mkdir($dirs['ROOT'].'/infra');
	if(!is_dir($dirs['cache']))mkdir($dirs['cache']);
	if(!is_dir($dirs['cache'].'mem/'))mkdir($dirs['cache'].'mem/');
	if(!is_dir($dirs['data']))mkdir($dirs['data']);
	if(!is_dir($dirs['backup']))mkdir($dirs['backup']);
	if(!is_file($dirs['data'].'.config.json')){
		$pass=substr(md5(time()),2,8);
		echo 'Создан аккаунт администратора в .config.json<br>admin:<b>'.$pass.'</b><br>Запишите пароль и обновите страницу';
		file_put_contents($dirs['data'].'.config.json','{"debug":true,"admin":{"login":"admin","password":"'.$pass.'"}}');
		exit;
	}
	header('location: ?*infra/tests.php');