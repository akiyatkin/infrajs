if (!infra) { // если проверяется из консоли
	var infra = require('../core/infra.js').infra;
}

this.infra_files = {
	testTheme : function(test) {
		var path = '*testfile.js';
		var end_path = infra.theme(path);
		test.expect(1);
		test.equals('infra/plugins/testfile.js', end_path, "* theme error");
		test.done();
	}
};
