if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/default.js','r');
this.init=function(){
	var view=infra.View.init(arguments);
	if(!infra.admin(true))return;
	var adm=infra.load('*.admin.js','sjkS');
	var r=infra.mail.sent('Проверочное письмо',
			adm.email,
			adm.email,//from, to
			'Автоматическое сообщение contacts/mailtest.njs'
	);
	return view.end({r:r,adm:adm});
}
