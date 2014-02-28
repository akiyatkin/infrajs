/* Возвращает иерархию файлов */
var path = require('path');

// убираем файлы, если есть такая директория
var del_file_dir = function(files) {
	var _files = [];
	var _files2 = [];
	var v; for (v = 0, len0 = files.length; v < len0; v++) {
		var pos = _files.indexOf(files[v].name);
		if (pos != -1) {
			if (files[v].f != 1) { // значит там файл, нужно заменить
				_files2[pos] = files[v];
			}
		} else {
			_files.push(files[v].name);
			_files2.push(files[v]);
		}
	}
	return _files2;
};

var update_list = function(list, files, cb) {
	var dirs = { paths:[], items:{} };
	var counter = files.length;
	if (counter) {
		files.forEach(function (file, index, array) {
			var path = (file.dir+'/'+file.name).replace(/\/$/,'').replace(/^\//,'');
			var realpath = (file.realdir+'/'+file.realname).replace(/\/$/,'').replace(/^\//,'');
			var title = path.split('/').slice(-1)[0];
			var item = {
				title: title,
				href: (list.href?list.href+'/':'') + title
			}
			if (file.f) {
				item.f = 1;
			} else {
				item.d = 1;
				item.child = [];
				dirs.paths.push(realpath);
				dirs.items[realpath] = item;
			}
			list.child.push(item);
			if (--counter==0) { cb(dirs); }
		});
	} else {
		cb(dirs);
	}
}

var update_list2 = function(src, dirs, dir, root, cb) {
	var counter = dirs.paths.length;
	if (counter) {
		dirs.paths.forEach(function(val, index, array) {
			dir({src: path.join(src, val),
				sort: 'name', f: 1, d: 1, sub: 0, realname: 2, e: 'mht,tpl'
			}, root, function(files2) {
				files2 = del_file_dir(files2);
				update_list(dirs.items[val], files2, function(dirs2) {
					var counter2 = dirs2.paths.length;
					if (counter2) {
						dirs2.paths.forEach(function(val1, index, array) {
							dir({src: path.join(src, val, val1),
								sort: 'name', f: 1, d: 1, sub: 0, realname: 2, e: 'mht,tpl'
							}, root, function(files3) {
								files3 = del_file_dir(files3);
								update_list(dirs2.items[val1], files3, function(dirs4) {
									if (--counter2==0) { if (--counter==0) { cb(); } }
								});
							});
						});
					} else {
						if (--counter==0) { cb(); }
					}
				});
			});
		});
	} else { cb() }
};

this.init = function(req, res, next, root) {
	var GET = {};
	if (req.query && Object.keys(req.query).length) {
		var name;
		for (name in req.query) { if (req.query.hasOwnProperty(name)) {
			var new_name = name.trim();
			if (new_name) { GET[new_name] = req.query[name].trim(); }
		}}
	}
	var dir = require(__dirname + '/dir.njs').dir;
	if (GET.src) {
		dir({ src: GET.src, sort: 'name', f: 1, d: 1, sub: 0, realname: 2, e: 'mht,tpl' }, root, function(files) {
			files = del_file_dir(files);
			if (!GET.first) { GET.first = 'Главная'; }
			var list = {
				'child': [ {
					'title': GET.first,
					'href': '',
					'f': 1
				} ]
			};
			update_list(list, files, function(dirs) {
				update_list2(GET.src, dirs, dir, root, function() {
					res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' }); 
					res.end(JSON.stringify(list.child, null, "\t"), 'utf-8');
				});
			});
		});
	} else {
		res.writeHead(502); res.end('Bad Gateway');
	}
};
