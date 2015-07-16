<?php
	/*
		install безопасная обработка доступная всем. Работа с файлами и постоянные изменения

		исключение только папка cache её наличие проверяется при каждом запуске
		в install создаются папки.. И в install редактируются data/.config.json
		весь конфиг сохраняется в data/.config.json.. 
		интерфейс показывает сравнение собранного конфига всей системы с файлом data/.config.json
	*/
	$dirs=infra_dirs();
	if(!is_dir($dirs['ROOT'].'infra'))mkdir($dirs['ROOT'].'infra');
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
	@mkdir($dirs['cache'].'infra_cache_once/');
	infra_admin_time_set(time()-1);//Нужно чтобы был, а то как-будто админ постоянно
	infra_pluginRun(function($dir){
		if(realpath($dir)==realpath(__DIR__))return;//Себя исключили
		if(!is_file($dir.'install.php'))return;
		require_once($dir.'install.php');
	});