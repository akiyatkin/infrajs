/* Запуск сервера, инициализация серверного infrajs */
var express = require('express');
var fs = require('fs');
var path = require('path');
var infra = require('./infra/core/infra.js').infra;
var jsdom  = require('./infra/node_modules/jsdom');

var jquery = fs.readFileSync('infra/lib/jquery/jquery-1.6.2.min.js').toString();
var index = fs.readFileSync('infra/layers/index/index.html','utf-8');

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

app.get(infra.ROOT + '*.(js|css|json|ico|png|jpeg|jpg)', function(req, res) {
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

app.all(infra.ROOT + '*', function(req, res) {
	jsdom.env({ html: index, src: [ jquery ],
		done: function(errors, _window) {
			window = _window;
			$ = window.$;
			document = window.document;
			infra.NODE = true;
			infra.listen('onload',function(){
				res.send(window.document.documentElement.innerHTML);
			});
			infra.loadJS('infra/layers.js');
		}
	})
});

app.listen(3000, '127.0.0.1');
