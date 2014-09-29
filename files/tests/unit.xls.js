if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');

if(infra.NODE)var xls=infra.load('*files/xls.sjs');		
unit={
	"parse":infra.fiber(function(test){
		var data=xls.parse('*files/tests/test.xls');
		test.ok(data.length==24,'Получены строки');		
		if(!data.length==24){
			console.log(data);
		}
		test.done();
	}),
	"parseAll":infra.fiber(function(test){
		var data=xls.parseAll('infra/plugins/files/tests/test.xls');
		test.ok(data['Позиции'],'Данные получены');
		var data=xls.parseAll('*files/tests/test.xls');
		test.ok(data['Позиции'],'Данные получены');
		test.done();
	}),
	"make":infra.fiber(function(test){
		var data=xls.make('*files/tests/test.xls');
		var count=2;
		test.ok(data.childs.length==count,count+' Количество групп');
		test.done();
	}),
	"init":infra.fiber(function(test){
		var data=xls.init('*files/tests/test.xls');
		var count=2;
		test.ok(data.childs[0].childs[0].title=='Персонал','Данные получены');
		test.done();
	}),
	"kvant":infra.fiber(function(test){
		var data=xls.make('*Главное меню.xls');
		xls.processDescr(data);
		xls.run(data,function(group) {
			delete group.parent;
			for(var i=0,l=group.data.length;i<l;i++){
				var pos=group.data[i];
				delete pos.group;
			}
		});
		//console.log(data.childs[0]);
		test.done();
	})
}
if(!infra.NODE)unit={};
module.exports={unit:unit}
