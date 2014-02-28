infra.mail=function(subject,to,tpl,data){//depricated
	if(!tpl)return false;
	var tpl=infra.load(tpl,'ft');
	var body=infra.template.parse([tpl],data);
	if(!body)return false;
	return this.mail.admin(subject,from,body);
}
infra.mail.toAdmin=function(subject,from,body){//письмо админу
	var adm=infra.load('*.admin.js','fsjS');
	return this.sent(subject,from,adm.email,body);
}
infra.mail.fromAdmin=function(subject,to,body){//письмо от админу
	var adm=infra.load('*.admin.js','fsjS');
	var from=adm.email.split(',');
	from=from[0];//Первый email в списке от его лица отправляется письмо и ответ только на него придёт
	return this.sent(subject,adm.email,to,body);
}
infra.mail.sent=function(subject,from,to,body){
	var res=[];
	if(!from)return false;
	if(!to)return false;
	if(!subject)return false;
	var mailer=require('nodemailer');
	//mailer.sendmail=true;
	mailer.sendmail='/usr/sbin/sendmail';
	var opt={
		sender: from,
		to:to,
		subject:subject,
		body:body
	}
	var fiber=Fiber.current;
	mailer.send_mail(opt,function(er,suc){
		res=arguments;
		fiber.run();
	});
	yield();
	return !res[0];
}
