<?php
if(!isset($_SESSION)){
	session_start();
}
if(function_exists('mb_internal_encoding')){
	mb_internal_encoding('UTF-8');
}
function send_mime_mail($name_from, // имя отправителя
						$email_from, // email отправителя
						$name_to, // имя получателя
						$email_to, // email получателя
						$data_charset, // кодировка переданных данных
						$send_charset, // кодировка письма
						$subject, // тема письма
						$body, // текст письма
						$replayto=false //куда отвечать
						) {
	$to = mime_header_encode($name_to, $data_charset, $send_charset). ' <' . $email_to . '>';
	$subject = mime_header_encode($subject, $data_charset, $send_charset);
	$from =  mime_header_encode($name_from, $data_charset, $send_charset).' <' . $email_from . '>';
	if($data_charset != $send_charset) {
		$body = iconv($data_charset, $send_charset, $body);
	}
	$headers = "From: $from\r\n";
	$headers .= "Content-type: text/plain; charset=$send_charset\r\n";
	if($replayto){
		$headers .= "Reply-To: ".$replayto."\r\n";
	}
	
	/*echo $to.'<hr>';
	echo $subject.'<hr>';
	echo $body.'<hr>';
	echo $header.'<hr>';*/
	return @mail($to, $subject, $body, $headers);
}

function mime_header_encode($str, $data_charset, $send_charset) {
  if($data_charset != $send_charset) {
	$str = iconv($data_charset, $send_charset, $str);
  }
  return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
}
function infra_mail($body,$bodydata=array(),$email_to,$name_to,$subject,$email_from,$name_from,$dir=false,$replayto=false){
	$body=infra_plugin($body,'fsn');
	if(!$body){
		$body='Не найден шаблон письма';
		//return false;// не найден файл шаблона
	}

	$s=array();
	$r=array();
	foreach($bodydata as $k=>$v){
		$s[]='{'.$k.'}';
		$r[]=$v;
	}
	$body = str_replace($s, $r, $body);
	
	$arg=func_get_args();
	if($dir){
		$folder=infra_theme($dir,'sdn');
		if(!$folder){
			$folder=infra_tofs(preg_replace('/^\*/','infra/data/',$dir));
			mkdir(ROOT.$folder,'0755');
		}
		file_put_contents(ROOT.$folder.date('Y F j H-i').' '.time().'.txt',print_r($body,true)."\n\n\n\n\n".print_r($arg,true));
	}
	return send_mime_mail($name_from,
				   $email_from,
				   $name_to,
				   $email_to,
				   'UTF-8',  // кодировка, в которой находятся передаваемые строки
				   'UTF-8', // кодировка, в которой будет отправлено письмо
				   $subject,
				   $body,$replayto);
}
//шаблон
//данные для тела письма
//кому отправлять
//имя получателя
//тема письма
//адрес отправителя
//имя отправителя
function weblifemail($body,$bodydata=array(),$email_to,$name_to,$subject,$email_from,$name_from,$dir=false){
	$body=infra_plugin($body,'fs');
	if(!$body){
		return -1;// не найден файл шаблона
	}
	//$body=infra_tophp($body);
	//file_put_contents($dir.'/'.date('Y F j H-i').' '.time().'.body.txt',print_r($body,true).print_r($arg,true));

	$s=array();
	$r=array();
	foreach($bodydata as $k=>$v){
		$s[]='{'.$k.'}';
		$r[]=$v;
	}
	$body = str_replace($s, $r, $body);
	
	$arg=func_get_args();
	if($dir){
		if(!is_dir($dir)){
			mkdir($dir,'0755');
		}
		file_put_contents($dir.'/'.date('Y F j H-i').' '.time().'.txt',print_r($body,true)."\n\n\n\n\n".print_r($arg,true));
	}
	return send_mime_mail($name_from,
				   $email_from,
				   $name_to,
				   $email_to,
				   'UTF-8',  // кодировка, в которой находятся передаваемые строки
				   'UTF-8', // кодировка, в которой будет отправлено письмо
				   $subject,
				   $body);
}
//var_dump(weblifemail('../layers/contacts/mail'));