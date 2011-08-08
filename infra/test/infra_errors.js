if (!infra) { // если проверяется из консоли
	var fs = require('fs');
	var jsdom  = require('jsdom');
	var infra = require('../core/infra.js').infra;
	var index = fs.readFileSync('infra/test/test.html','utf-8');
	//var sys = require('sys');
	jsdom.env({
		html: index,
		done: function(errors, _window) {
			window = _window;
			$ = window.$;
			document = window.document;
		}
	})
	infra.NODE = true;
}
infra.DEBUG = true;

/* Тесты обработки ошибок */
this.infra_errors = {
	error : function(test) {
		try {
			infra.error(new Error('test error'), false, false, false, false, false, true);
		} catch(e) {
			test.equal('test error', e.message, "test error != "+e.message);
		}
		test.done();
	},
	exec : function(test) {
		var cb = function(val, i) {
			if (i!=23) throw new Error('test error2');
			test.equal('value', val, "value != "+val);
			test.equal('23', i, "23 != "+i);
			return true;
		}
		var noerr = infra.exec(cb,'cb',this,['value',23],[true]);//callback,name,context,args,more
		test.ok(noerr, 'Ошибки быть не должно');
		try {
			var yeserr = infra.exec(cb,'cb',this,['value'],[true], true);//callback,name,context,args,more
		} catch(e) {
			test.equal('test error2', e.message, "test error2 != "+e.message);
			test.ok(!yeserr, 'Тут должна быть ошибка');
		}
		test.done();
	}
};

/* Тесты обработки файлов */
this.infra_files = {
	theme : function(test) {
		var path = '*testfile.js';
		end_path = infra.theme(path);
		test.expect(1);
		//console.log(end_path);
		test.equal(infra.ROOT + 'infra/plugins/testfile.js', end_path, "* theme error");
		test.done();
	},
	loadJS : function(test) {
		infra.loadJS('infra/test/mock.js')
		test.equal(123, mock.a(), "loadJS error");
		test.done();
	}
	/*
	*/
};

/* Тесты обработки событий */
this.infra_event = {
	onevent : function(test) {
		var r = false;
		infra.listen(infra,'onevent',function(){
			r = true;
		});
		infra.fire(infra,'onevent');
		test.ok(r, 'Не сработал onevent');
		test.done();
	},
	typeevent: function(test) {
		var r=2;
		infra.listen(infra,'type.onsome.before',function(){
			r--;
		});

		var obj={};
		infra.fire(obj,'onsome','type',true,infra);

		var obj1={};
		infra.fire(obj1,'onsome','type',true,infra);

		if(r==0)r=true;
		else r=false;
		test.ok(r, 'Не сработал typeevent');
		test.done();
	},
	typeevent2: function(test) {
		var obj={};
		var r=infra.fire(infra,'testobj.ontestevt.cond',false,1,obj);
		if(r===1)r=true;
		else r=false;
		test.ok(r, 'Не сработал typeevent2');
		test.done();
	}
}

/* Тесты обработки контроллера */
this.infra_controller = {
	infra_run: function(test) {
		var r1=1;
		var r2=0;
		var r3=0;
		//test.expect(1);
		infra.run([], function(){
			r1--;
		});
		infra.run([{}], function(){
			r2++;
		});
		infra.run([{asdf:true}], function(){
			r3++;
		});
		test.ok(r1, "Не надо было заходить");
		test.ok(r2, "Не зашли почему-то");
		test.ok(r3, "Зашли внутрь и споткнулись");
		test.done();
	},
	infra_check_simple: function(test) {
		infra.check([]);
		infra.check([{}]);
		test.done();
	},
	infra_check_hi: function(test) {
		test.expect(1);

		infra.loadJS('infra/core/props/parsed.js');
		infra.loadJS('infra/core/template.js');
		infra.loadJS('infra/core/props/tpl.js');

		infra.check({div: 'infra_test', tpl: ['<div id="hi"></div>']});
		infra.listen(infra,'onshow',function() {
			var div = document.getElementById('hi');
			test.ok(!!div, "Не  вставили");
			test.done();
			infra.unlisten(infra,'onshow',arguments.callee);
		});
	},
	infra_check_state: function(test) {
		var div=document.getElementById('infra_test');
		if(!div){
			test.ok(false,"Не найден див infra_test");
			test.done();
			return;
		}else{
			div.innerHTML='';
		}
		infra.loadJS('infra/core/props/parsed.js');
		infra.loadJS('infra/core/template.js');
		infra.loadJS('infra/core/props/tpl.js');
		
		infra.loadJS('infra/core/state.js');
		infra.loadJS('infra/core/props/state.js');
		if(!infra.state){
			test.ok(false,"Не найдено расширение state");
			test.done();
			return;
		}
		mock_index=[{
			div:'infra_test',
			istate:'main',
			tpl:['<div id="main"></div>']
		},{
			div:'infra_test',
			istate:'about',
			tpl:['<div id="about"></div>']
		}]
		infra.check(mock_index);
			
		counter=1;
		infra.listen(infra,'onshow',function(){
			if(counter==1) {
				var div=document.getElementById('infra_test'); 
				var html=div.innerHTML;
				if(!html)r=true;
				else r=false;
				if(!r){
					test.ok(false,"Ошибка. На главной странице показался слой привязанный к состоянию. <textarea>"+html+'</textarea>');
					test.done();
					return;
				}
				counter=2;
				
				infra.state.setHash('#$$/main');
				infra.check();
			}else if(counter==2){
				var div=document.getElementById('main'); 
				if(div)r=true;
				else r=false;
				if(!r){
					test.ok(false,"Не показался div main");
					test.done();
					return;
				}
				counter=3;
				infra.state.setHash('#$$/about');
				infra.check();
			}else if(counter==3){
				var div=document.getElementById('about');
				if(div)r=true;
				else r=false;
				if(!r){
					test.ok(false,"Не показался div main");
					test.done();
					return;
				}else{
					test.ok(true,'Ништяк');
					test.done();
				}
			}
		});
	}
}
