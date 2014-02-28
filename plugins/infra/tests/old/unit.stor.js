if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')infra=require(ROOT+'infra/plugins/infra/infra.js');
var unit = {
	'stor':function(test){
		var stor1=infra.stor();
		test.ok(infra.test_globstor!==stor1,'Равны');
		setTimeout(function(){
			var stor2=infra.stor();
			if(infra.NODE)test.ok(!stor2,'stor без потока');
			if(!infra.NODE)test.ok(stor2,'stor без потока в браузере есть');
		},1);
		setTimeout(infra.fiber(function(){
			var stor2=infra.stor();
			test.ok(stor2,'stor с потоком');
			test.ok(stor1===stor2,'stor во всех связанных потоках одинаковые');
			infra.test_globstor=stor1;
			test.done();
		}),1);
	}
}
if(typeof(module)=='object')module.exports={unit:unit}
else window.unit=unit;
