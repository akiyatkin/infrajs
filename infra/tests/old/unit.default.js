if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undeinfed')require(ROOT+'infra/plugins/infra/infra.js');		
infra.load('*infra/default.js');
var unit={
	"Наличие основных объектов":infra.fiber(function(test){
		if(infra.NODE)test.ok(infra.mail);
		if(infra.NODE)test.ok(infra.admin);
		if(infra.NODE)test.ok(infra.forr);
		test.ok(infra.template);
		test.ok(infra.View);
		test.done();
	})
}
if(typeof(module)=='object')module.exports={unit:unit}
