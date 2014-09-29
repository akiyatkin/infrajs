if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')infra=require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/ext/template.js');

unit={
	'Запуск':function(test){
		test.ok(infra.template,'Подключение шаблонизатора');
		var res=infra.template.parse(['asdf']);
		test.ok(res=='','Шаблон без данных не парсится');
		var res=infra.template.parse(['asdf'],{});
		test.ok(res=='asdf','Должно было получиться asdf а получилось'+res);
		test.done();
	},
	'Переносы':function(test){
		var t='{:asdf}\na\nb{asdf:}\nc';
		var res=infra.template.parse([t],{});
		test.ok(res.length==5,'Переносы в начале и вконце подшаблона удаляются в других местах остаются');
		test.done();
	}
};
if(typeof(module)=='object')module.exports={unit:unit}





