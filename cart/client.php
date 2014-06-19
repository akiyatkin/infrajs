<?php
@define('ROOT','../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_require('*cart/catalog.inc.php');

infra_require('*session/session.php');

$basket=infra_session_get('order.basket',array());

$ans=array(
	'allcount'=>0,
	'allsum'=>0,
	'list'=>array()
);

$issubmit=isset($_GET['submit']);
foreach($basket as $key=>$obj){
	$ans['allcount']+=1;
	$pos=cat_getpos($key);
	if(!$pos)continue;
	
	$pos['count']=$obj['count'];
	if($pos['Цена']){
		$sum=$obj['count']*$pos['Цена'];
		$pos['sum']=$sum;
		$ans['allsum']+=$sum;
	}
	$ans['list'][]=$pos;
	
}

if($issubmit){
	$ans['carttime']=infra_session_get('user.carttime');
	$user=infra_session_get('user',array());
	if(!$user['name'])return infra_echo($ans,'Укажите ваше имя');

	if(!$user['email'])return infra_echo($ans,'Укажите ваш email');
	$is_email=preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/',$user['email']);
	if(!$is_email)return infra_echo($ans,'Некорректный Email');
	if(!$user['phone'])return infra_echo($ans,'Необходимо указать телефон');


	$ans['user']=$user;
	$ans['host']=$_SERVER['HTTP_HOST'];
	$ans['time']=time();
	$ans['browser']=$_SERVER['HTTP_USER_AGENT'];
	$ans['ip']=$_SERVER['REMOTE_ADDR'];
	$content=infra_template_parse('*cart/client.mail.tpl',$ans);
	$subject=$_SERVER['HTTP_HOST'].' заявка с сайта '.$ans['user']['name'];
	$dir=infra_tofs('infra/data/.Заявки с сайта/');
	if(!is_dir(ROOT.$dir)){
		mkdir(ROOT.$dir);
	}
	$file=$dir.date('Y-m-d H i').' '.infra_tofs($ans['user']['name']);

	file_put_contents(ROOT.$file,$content);

	$r=infra_mail_toAdmin($subject,$user['email'],$content);
	if(!$r)return infra_echo($ans,'Произошла ошибка во время отправки заявки. Воспользуйтесь пожайлуйста телефоном, чтобы убедится что заявка была получена');
	$ans['result']=$r;
	$ans['mail']=$content;
	$ans['msg']='Заявка отправлена';
	infra_session_set('user.carttime',$ans['time']);
}
$ans['carttime']=infra_session_get('user.carttime');
return infra_echo($ans);
?>