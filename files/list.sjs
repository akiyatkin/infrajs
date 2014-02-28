/*
Доступен метод init, который всегда выполнятся
{
	paths: {
		'headname': {
			realpath: '1 dir/4 file.html',
			name: 'href',
		}
	}
}
*/

if(typeof(ROOT)=='undefined')var ROOT='../../../';//при eval должен остаться ROOT родителя при require ROOT родителя не должен быть виден.
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
var fs = require('fs');
var pathlib=require('path');
var without_num = function(path) {
	if (path.match(/^\d+ .+$/))
		return  path.split(' ').slice(1).join(' ');
	else return path
}

/* Возвращает массив имен файлов в переданном каталоге */
var get_files = function(path, pattern, callback) {
	var pre_files = [];
	fs.readdir(pathlib.resolve(__dirname+'/'+ROOT+path), function(err, files) {
		if (!err) {
			var counter = files.length;
			if (counter) {
				files.forEach(function(file, index, array) {
					if(file.charAt(0)!='.'&&file.match(pattern)){
						
						fs.stat(__dirname+'/'+ROOT+path + '/' + file, function(err, stats) {
							if ((!err) && (stats.isFile())) {
								pre_files.push(path+file);
							}
							if (-- counter == 0) callback(pre_files.sort());
						});
					} else if (-- counter == 0) callback(pre_files.sort());
				})
			} else callback([]);
		} else callback([]);
	})
}
/* Возвращает массив имен директорий в переданном каталоге */
var get_dirs = function(path, callback) {
	var pre_dirs = [];
	fs.readdir(__dirname+'/'+ROOT+path, function(err, dirs) {
		if (!err) {
			var counter = dirs.length;
			if (counter) {
				dirs.forEach(function(dir, index, array) {
					fs.stat(__dirname+'/'+ROOT+path + '/' + dir, function(err, stats) {
						if ((!err) && (stats.isDirectory())) {
							pre_dirs.push(dir);
						}
						if (-- counter == 0) callback(pre_dirs);
					});
				})
			} else callback([]);
		} else callback([]);
	})
}
/* Возвращает актуальные пути, в зависимости от цифр в начале имени */
var choose_num = function(names) {
	names = names.sort();
	var new_names = [];
	for (var i = names.length; --i >= 0;) {
		var ignore = false;
		var name = names[i];
		var _name = without_num(name);
		for (var ii = new_names.length; --ii >= 0;) {
			var new_name = new_names[ii];
			var _new_name = without_num(new_name);
			if (_new_name == _name) ignore = true;
		}
		if (!ignore) new_names.push(names[i]);
	}
	return new_names;
}

var init = function(req, cb) {
	var root_dir = ROOT + '/infra/data/pages';
	var ans = {
		sections: [],
		result: 0,
		realpath: {},
	};
	get_dirs(root_dir, function(dirs) {
		dirs = choose_num(dirs);
		var counter = dirs.length;
		if (counter) {
			dirs = dirs.reverse();
			dirs.forEach(function(dir, index, array) {
				get_files(root_dir + '/' + dir, /\.html$/, function(files) {
					files = choose_num(files);
					var _dir = without_num(dir);
					var section = {head: _dir, pages: []};
					for (var i = files.length; --i >= 0;) {
						var file = files[i];
						var _file = without_num(file);
						var name = _file.slice(0, _file.length-5);
						section.pages.push({
							href: _dir + '/' + name,
							name: name
						})
						ans.realpath[_dir + '/' + name] = dir + '/' + file;
					}
					if (section.pages.length) ans.sections.push(section);
					if ( -- counter == 0) {
						ans.result = 1;
						cb(ans)
					}
				})
			})
		} else cb(ans);
	})
}
var list={
	get_dirs:get_dirs,
	get_files:get_files,
	init:init
}

module.exports=list;
