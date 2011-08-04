/* Тесты обработки ошибок */
if (!infra) { // если проверяется из консоли
	var infra = require('../core/infra.js').infra;
	//var sys = require('sys');
	infra.NODE = true;
}
infra.DEBUG = true;

this.infra_errors = {
	testError : function(test) {
		try {
			infra.error(new Error('test error'), false, false, false, false, false, true);
		} catch(e) {
			test.equal('test error', e.message, "test error != "+e.message);
		}
		test.done();
	},
	testExec : function(test) {
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
