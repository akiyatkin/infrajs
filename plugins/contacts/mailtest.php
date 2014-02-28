<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	ob_start();
	$from='noreplay@'.$_SERVER['HTTP_HOST'];
	$headers='From: '.$from."\r\n";
	$headers.="Content-type: text/plain; charset=UTF-8\r\n";
	$headers.='Reply-To: aky@list.ru'."\r\n";
	echo 'Нативная проверка<br>';
	$r=mail('info@itlife-studio.ru','Проверка с сервера '.$_SERVER['HTTP_HOST'],'Текст проверочного сообщения',$headers);
	var_dump($r);




	return;//нельзя зачастую лимит стоит сколько писем за раз можно отправлять
	echo '<br>Сложная проверка<br>';
	infra_load('*contacts/mail.php','r');
	$conf=infra_config();
	$admin=$conf['admin'];
	$ans=array();
	if(!$admin)return infra_echo($ans,'Не найден конфиг',0);
	if(!$admin['support'])return infra_echo($ans,'У администратора не указан email support',0);

	$email_to=$admin['support'];
	$bodydata=array(
		'host'=>$_SERVER['HTTP_HOST'],
		'date'=>date('j.m.Y')
	);
	infra_load('*infra/ext/template.php','r');
	$body=infra_template_parse('*contacts/mailtest.tpl',$bodydata);
	$subject='Тестовое письмо';
	$email_from='noreplay@'.$_SERVER['HTTP_HOST'];
	$r=infra_mail_toSupport($subject,$email_from,$body,true);
	if(!$r)return infra_echo($ans,'Ошибка. Не удалось отправить тестовое письмо');
	return infra_echo($ans,'Тестовое письмо отправлено');
?>
