if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')infra=require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/ext/state.js','r');
var unit = {
	'Считываем адрес': function (test) {
		var view=infra.View.get();
		test.ok(infra.State,'Скрипт подключен');

		var state=infra.State.getState();
		//infra.State.set('http://127.0.0.1/svn/iable/infra/plugins/nodeunit/test.njs?$$/asdf/asdf');//Устанавливается адрес срабатывают события
		infra.State.set('$$/asdf/asdf');//Устанавливается адрес срабатывают события
		test.ok(state.childs['asdf'],'Дочерние состояния определились asdf');
		test.ok(state.childs['asdf'].childs['asdf'],'Дочерние состояния определились asdf.asdf');
		


		infra.State.set('$&asdf/qwer');
		test.ok(state.childs['asdf'].childs['qwer'],'Дочерние состояния определились asdf.qwer');

		var st=infra.State.getState('asdf');
		test.ok(st.childs['qwer'],'Дочерние состояния определились qwer');




		var state=infra.State.getState('asdf.asdf');
		var s1change=false;
		infra.listen(state,'onchange',function(){
			s1change=true;
		});
		var s1show=false;
		infra.listen(state,'onshow',function(){
			s1show=true;
		});
		var s1hide=false;
		infra.listen(state,'onhide',function(){
			s1hide=true;
		});

		var state=infra.State.getState(['asdf','asdf','asdf']);
		var s2change=false;
		infra.listen(state,'onchange',function(){
			s2change=true;
		});
		var s2show=false;
		infra.listen(state,'onshow',function(){
			s2show=true;
		});
		var s2hide=false;
		infra.listen(state,'onhide',function(){
			s2hide=true;
		});

		var state=infra.State.getState('asdf.qwer');
		var s3change=false;
		infra.listen(state,'onchange',function(){
			s3change=true;
		});
		var s3show=false;
		infra.listen(state,'onshow',function(){
			s3show=true;
		});
		var s3hide=false;
		infra.listen(state,'onhide',function(){
			s3hide=true;
		});

		var state=infra.State.getState('asdf.some');
		var s4change=false;
		infra.listen(state,'onchange',function(){
			s4change=true;
		});
		var s4show=false;
		infra.listen(state,'onshow',function(){
			s4show=true;
		});
		var s4hide=false;
		infra.listen(state,'onhide',function(){
			s4hide=true;
		});
		//?asdf/asdf&qwer
		//?asdf/asdf/asdf
		//1 asdf.asdf
		//2 asdf.asdf.asdf
		//3 asdf.qwer
		//4 asdf.some
		//
		infra.State.set('asdf/asdf/asdf');

		test.ok(
			s1change
			&&!s1show
			&&!s1hide
			&&s2change
			&&s2show
			&&!s2hide
			&&!s3change
			&&!s3show
			&&s3hide
			&&!s4change
			&&!s4show
			&&!s4hide
			,'Сработали события');


		return test.done();
	}
}
if(typeof(module)=='object')module.exports={unit:unit}
else window.unit=unit;
