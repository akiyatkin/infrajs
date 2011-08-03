/* Запуск сервера, инициализация серверного infrajs */
var express = require('express');
var fs = require('fs');
var path = require('path');
var infra = require('./infra/core/infra.js');

var app = express.createServer();

/* Конфигурация сервера. DEVELOPMENT */
app.configure(function(){
	app.use(express.logger());
	app.use(express.bodyParser());
	app.use(express.methodOverride());
	//app.use(express.cookieParser(...));
	//app.use(express.session(...));
	app.use(app.router);
	app.use(express.errorHandler({ dumpExceptions: true, showStack: true }));
	//app.use(express.static(__dirname + '/../../data'));
});

app.get(infra.config.root + '*.(js|css|json|ico|png|jpeg|jpg)', function(req, res) {
	var file = __dirname + req.params[0] + '.' + req.params[1];
	fs.readFile(file, function(err, data) {
		if (err) {
			res.send(404);
		} else {
			res.contentType(file);
			res.send(data);
		}
	});
});

app.all(infra.config.root + '*', function(req, res) {
	infra.init(req, function(ans) {
		res.send(ans);
	});
});

app.listen(3000, '127.0.0.1');
