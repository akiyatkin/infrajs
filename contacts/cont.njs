if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/default.js','r');

this.init=function(){
	var view=infra.View.init(arguments);
	var path='*Каталог/';
	var POST=view.getPOST();
	var ans={};
	var tpl=infra.theme('*contacts/cont.mail.tpl');

	var data={
		email:POST.email,
		org:POST.org,
		persona:POST.persona,
		phone:POST.phone,
		text:POST.text,
		ref:view.getREF(),
		ip:view.getIP(),
		host:view.getHost(),
		browser:view.getAGENT(),
	};


	var fs=require('fs');

	var dirpath='infra/data/.Cообщения с сайта/';
	if(!infra.theme(dirpath,'dsS')) infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+dirpath,'0766');

	var body=infra.template.parse(tpl,data);
	var subject='Сообщение с сайта '+view.getHost();
	
	var phpdate=infra.load('infra/lib/phpdate/phpdate.js');

	var name=phpdate('Y m j H-i ')+data.email;
	infra.sync(fs,fs.writeFile)(__dirname+'/'+ROOT+dirpath+name+'.txt',body);

	console.log(subject);
	console.log(data.email);
	console.log(body);

	var r=infra.mail.toAdmin(subject,data.email,body);

	if(r){
		ans['msg']='Оповещение отправлено';
		ans['result']=1;
	}else{
		ans['msg']='Не удалось отправить оповещение. <br>Попробуйте позже или свяжитесь по телефону.';
		ans['result']=0;
	}

	return view.end(ans);
}
