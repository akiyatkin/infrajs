if (!infra) { // если проверяется из консоли
	var infra = require('../core/infra.js').infra;
	//var sys = require('sys');
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
};

/* Тесты обработки контроллера */
this.infra_event = {
	onevent : function(test) {
		var r = false;
		infra.listen(infra,'onevent',function(){
			r = true;
		});
		infra.fire(infra,'onevent');
		test.ok(r, 'Не сработал onevent');
		test.done();
	}
}

this.infra_controller = {
	infra_check: function(test) {
		infra.check([]);
		test.done();
	}
}
