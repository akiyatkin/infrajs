
if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')infra=require(ROOT+'infra/plugins/infra/infra.js');

infra.load('*infra/ext/event.js');//когда не r это попадает в кэш

unit = {
	'Основные опреации с событиями и подписками': infra.fiber(function (test) {
		test.ok(infra.fire,'Подключение');
		var r=false;
		infra.listen(infra,'asdf',function(){
			r=true;
		});
		test.ok(!r,'Не cгенерировано событие');
		infra.fire(infra,'asdf2');
		test.ok(!r,'Не cгенерировано событие');
		infra.fire(infra,'asdf');
		test.ok(r,'Сгенерировано событие');
		test.done();
	})
};
if(typeof(module)=='object')module.exports={unit:unit}


