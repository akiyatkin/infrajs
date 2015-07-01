<?php
	

	$ans=array();
	$ans['msg']='Письмо не отправлено';
	$ans['result']=0;
	$persona=@$_POST['name'];
	$phone=@$_POST['phone'];
	$is_persona=strlen($persona)>2;
	if(!$is_persona){
		$ans['msg']='Уточние, пожалуйста, вашем имя!';
		$ans['result']=-1;
	}else{
		$email=@$_POST['email'];
		$is_email=preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/',$email);
		if($is_email!=true){
			$ans['msg']='Уточните, пожалуйста, адрес электронной почты!';
			$ans['result']=-2;
		}else{
			$text=@$_POST['text'];
			$is_text=strlen($text)>5;
			
			if($is_text!=true){
				$ans['msg']='Уточните, пожалуйста, текст письма!';
				$ans['result']=-3;
				//echo infra_tojs($answer);
			}else if(!$phone||strlen($phone)<6){
				$ans['msg']='Уточните, пожалуйста, номер телефона!';
				$ans['result']=-8;
			}else{
				
				$data=array();
				$data['email']=$email;
				$data['text']=$text;
				$data['name']=$persona;
				$data['org']=@$_POST['org'];
				$data['phone']=@$_POST['phone'];
				$data['ip']=$_SERVER['REMOTE_ADDR'];
				$data['ref']=$_SERVER['HTTP_REFERER'];
				$data['browser']=$_SERVER['HTTP_USER_AGENT'];
				$data['time']=date("F j, Y, g:i a");
				
				//шаблон
				//данные для тела письма
				//кому отправлять
				//имя получателя
				//тема письма
				//адрес отправителя
				//имя отправителя
				//Папка с копиями писем
				//$maildir=infra_tofs('infra/data/.Сообщения с сайта/');
				//@mkdir($maildir,0755);
				
				infra_require('*autoedit/admin.inc.php');
				$maildir='*.Сообщения с сайта/';
				$maildir=autoedit_createPath($maildir);//путь до файла или дирректории со * или без
				//$mdata=@file_get_contents('infra/data/.contacts.js');
				//$mdata=infra_tophp($mdata);
				
				$mdata=array();
				$p=explode(',',$data['email']);
				$mdata['email_from']=$p[0];
				//$mdata['subject']='Сообщение через форму контактов '.$_SERVER['HTTP_HOST'];
				$mdata['subject']='Сообщение через форму контактов '.$_SERVER['HTTP_HOST'];
				if(trim(mb_strtolower($data['name']))=='itlife'){
					$data['text']=print_r($mdata,true)."\n\n".$data['text'];
					$mdata['subject']='ПРОВЕРОЧНОЕ '.$mdata['subject'];
					$mdata['testmail']=true;
				}else{
					$mdata['testmail']=false;
				}
				$ans['testmail']=$mdata['testmail'];

				infra_require('*infra/ext/template.php');
				$body=infra_template_parse('*contacts/mail.tpl',$data);
				if(!$body) $body='Ошибка. Не найден шаблон письма!';

				if($maildir){
					$arg=$mdata;
					$folder=infra_theme($maildir);
					file_put_contents($folder.date('Y F j H-i').' '.time().'.txt',print_r($body,true)."\n\n\n\n\n".print_r($arg,true));
				}


				if(isset($mdata['email_from'])){
					$r=infra_mail_toAdmin($mdata['subject'],$mdata['email_from'],$body,$mdata['testmail']);
					if($r){
						$ans['msg']="Письмо отправлено!";
						$ans['result']=1;
					}else{
						$ans['msg']="Неудалось отправить письмо из-за ошибки на сервере!";
						$ans['result']=-4;
					}
				}else{
					$ans['msg']='Ошибка с адресом получателя!';
					$ans['result']=-777;
				}
			}
		}
	}
	@header('Content-type: application/javascript; charset=UTF-8');
	return infra_echo($ans);