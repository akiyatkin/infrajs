// возвращает объект
// принимает
// src = полный правильный путь до файла от корня сайта
var fs = require('fs');
var path = require('path');
var request = require('request');
var preparePath = function(src) {
	if (src && (src.indexOf('..') == -1)) {
		src = src.replace(/\/+/g,'/');
		return src;
	}
};
this.init = function(req, res, next, root) {
	var host = req.headers.host;
	var page = require(path.join(root, 'plugins/files/page.njs'));
	/* Что загружать mht или doc */
	/* Преобразовать текст с тэгами getTpl page*/
	/* Вернуть подготовленный результат */
	var GET = {};
	var ans = { "result": 0 };
	if (req.query && Object.keys(req.query).length) {
		var name; for (name in req.query) { if (req.query.hasOwnProperty(name)) {
			var new_name = name.trim();
			if (new_name) { GET[new_name] = req.query[name].trim(); }
		}}
	}
	var run;
	GET.src = preparePath(GET.src);
	if (GET.src) {
		GET.mht = preparePath(GET.mht);
		if (GET.mht) {
			GET.cache = preparePath(GET.cache);
			run = true;
		}
	}
	var end = function() {
		res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' }); 
		res.end(JSON.stringify(ans, null, "\t"), 'utf-8');
	};
	if (run) {
		var cachedir = path.join(
			GET.cache, GET.src.replace(/^\//,'').replace(/\/$/,'').replace(/\//g,'|')
		);
		var options = { cache: cachedir, preview: GET.preview };
		var filename = path.join(root, GET.src);
		var ext = path.extname(filename).toLowerCase();
		if (GET.mht && (ext == '.mht')) {
			var url = 'http://' + host + encodeURI(GET.mht) + encodeURIComponent(GET.src);
			request({ url: url, timeout: 60000 }, function(error, response, body) {
				if (!error && response.statusCode == 200) {
					page.parse(body, options, function(err, data) {
						if (!err) { ans = data;
						} else { console.error(err); }
						end();
					});
				} else {
					console.error('load error', error, response?response.statusCode:'', file);
					end();
				}
			});
		} else {
			page.load(GET.src, options, function(err, data) {
				if (!err) { ans = data;
				} else { console.error(err); }
				end();
			}, root);
		}
	} else { end(); }
};
