<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	ini_set("display_errors", 1);
	ob_start();
	$from='noreplay@'.$_SERVER['HTTP_HOST'];
	$headers='From: '.$from."\r\n";
	$headers.="Content-type: text/plain; charset=UTF-8\r\n";
	$headers.='Reply-To: aky@list.ru'."\r\n";
	//echo 'Нативная проверка<br>';
	//$r=mail('info@itlife-studio.ru','Проверка с сервера '.$_SERVER['HTTP_HOST'],'Текст проверочного сообщения',$headers);
	//var_dump($r);




	//return;//нельзя зачастую лимит стоит сколько писем за раз можно отправлять
	//echo '<br>Сложная проверка<br>';
	infra_require('*contacts/mail.php');
	$conf=infra_config();
	$admin=$conf['admin'];
	$ans=array();
	if(!$admin)return infra_err($ans,'Не найден конфиг');
	if(!$admin['support'])return infra_err($ans,'У администратора не указан email support');

	$bodydata=array(
		'host'=>$_SERVER['HTTP_HOST'],
		'date'=>date('j.m.Y')
	);
	infra_require('*infra/ext/template.php');
	$body=infra_template_parse('*contacts/mailtest.tpl',$bodydata);
	$subject='Тестовое письмо';
	$email_from='noreplay@'.$_SERVER['HTTP_HOST'];
	$r=infra_mail_toSupport($subject,$email_from,$body,true);
	
	if(!$r)return infra_err($ans,'Ошибка. Не удалось отправить тестовое письмо');
	return infra_ret($ans,'Тестовое письмо отправлено');
?>