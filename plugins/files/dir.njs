//echo "/*папки, файлы, раcширения через запятую, путь ?s=0&h=0&d=0&f=1&e=0&src=path&onlyname=0&random=0*/\n";

var fs = require('fs');
var path = require('path');
var naturalSort = require(__dirname + '/naturalSort.js');

/* Запретить выше рута и скрыте файлы */
var getPath = function(root, GET) {
	var src = path.join(root, GET.src)
	if ((GET.h!=1) && (/\/\./.test(src))) {
		return;
	} else {
		if (new RegExp('^'+root).test(src)) return src;
		return root;
	}
}

var _dir = function(GET, item_dir, cb) {
	var files2 = [];
	if (GET.src) {
		var src = path.join(GET.src, item_dir);
		fs.readdir(src, function(err, files) {
			if (!err) {
				var counter = files.length;
				if (counter) {
					var n_files = [];
					var n_dirs = [];
					var itemFiles = [];
					var itemDirs = [];
					files.forEach(function (val, index, array) {
						if ((GET.h!=1) && (/^\./.test(val))) {;
							if (--counter==0) cb(files2);
						} else {
							fs.stat(path.join(src, val), function(err, stats) {
								if (!err) {
									var item = {};
									item.f = stats.isFile();
									item.dir = item_dir;
									item.ext = '';
									if (item.f) item.ext = path.extname(val).slice(1);
									if (item.ext) item.name = val.slice(0, -(item.ext.length+1));
									else item.name = val;
									item.size = stats.size;
									item.time = stats.mtime.getTime();
									if (item.f) {
										if (GET.f == 1) {
											if (GET.e && GET.e.length) {
												if (GET.e.indexOf(item.ext.toLowerCase()) != -1) {
													itemFiles.push(item);
													n_files.push(val);
												}
											} else {
												itemFiles.push(item);
												n_files.push(val);
											}
										}
									} else {
										itemDirs.push(item);
										n_dirs.push(val);
									}
									// не идти дальше пока не закончили с текущей директорией
									if (--counter==0) {
										// выбрать верные имена с помощью цифр
										n_files = checkNum(n_files);
										n_dirs = checkNum(n_dirs);
										// оставить верные итемы
										itemFiles = setItems(itemFiles, n_files);
										itemDirs = setItems(itemDirs, n_dirs);
										// добавить итемы в ответ
										files2 = files2.concat(itemFiles);
										if (GET.d==1) files2 = files2.concat(itemDirs);
										// пойти в глубь
										counter = itemDirs.length;
										if ((GET.sub==1) && counter) {
											itemDirs.forEach(function (val, index, array) {
												_dir(GET, path.join(item_dir, val.name), function(files3) {
													files2 = files2.concat(files3);
													if (--counter==0) cb(files2)
												});
											})
										} else cb(files2);
									}
								} else if (--counter==0) cb(files2);
							})
						}
					});
				} else cb(files2);
			} else cb(files2);
		})
	} else cb(files2);
}

var setItems = function(items, names) {
	var new_items = [];
	var len = items.length;
	for (var i = 0; i < len; i++) {
		if (items[i].ext) {
			if (names.indexOf(items[i].name + '.' + items[i].ext)!=-1) new_items.push(items[i]);
		} else {
			if (names.indexOf(items[i].name)!=-1) new_items.push(items[i]);
		}
	}
	return new_items;
}

/* Убираются повторения без цифр, имена все разные */
var checkNum = function(names) {
	var obj = {};
	var new_names = [];
	var len = names.length;
	for (var i = 0; i < len; i++) {
		obj[names[i]] = {};
		var num = /^(\d+)\s/.exec(names[i]);
		if (num && num.length) {
			obj[names[i]].num = num[1];
			obj[names[i]].name = names[i].slice(num[0].length);
		} else {
			obj[names[i]].num = '';
			obj[names[i]].name = names[i];
		}
	}
	for (var key in obj) if (obj.hasOwnProperty(key)) {
		var max_num = obj[key].num;
		// если число самое большое
		for (var key2 in obj) if (obj.hasOwnProperty(key2)) {
			if (obj[key2].name == obj[key].name) {
				if (obj[key2].num > max_num) max_num = obj[key2].num;
			}
		}
		var name = obj[key].name;
		if (max_num) name = max_num + ' ' + obj[key].name;
		if (new_names.indexOf(name)==-1) new_names.push(name);
	}
	return new_names;
};

var shuffle = function(array) {
    var tmp, current, top = array.length;
    if(top) while(--top) {
        current = Math.floor(Math.random() * (top + 1));
        tmp = array[current];
        array[current] = array[top];
        array[top] = tmp;
    }
    return array;
}

var sortName = function(a, b) {
	var anum = parseInt(a.num);
	var bnum = parseInt(b.num)
	if(a < b) return -1
	if(a.num > b.num) return 1
	return 0
}

var sort = function(ans, sortname) {
	if (sortname == 'size') {
		return naturalSort(ans, function(item){return item.size}).reverse();
	} else if (sortname == 'time') {
		return naturalSort(ans, function(item){return item.time.toString()}).reverse();
	} else { // 'name'
		return naturalSort(ans, function(item){
			var dir = item.realdir?item.realdir:item.dir;
			var name = item.realname?item.realname:item.name;
			return (dir+'/'+name+item.ext)
		});
	}
}

var dir = function(_GET, root, cb, server) {
	var ans = [];
	// значения по умолчанию
	var GET = {
		s: 0, // возвращать размер файла
		f: 1, // файлы
		d: 0, // дирректории
		e: false, // список необходимых расширений
		time: 0, // время последнего изменения файла
		src: '', // путь от корня.. включает corе...
		sub: 0, // заходить ли во вложенные папки
		onlyname: 0, // определяет нужно ли выделять расширение файла
		lim: false, // от какого, сколько
		obj: 0, // возвращать объектом или массивом
		random: 0, // перемешивать каждый раз
		reverse: 0, // вернуть в обратном порядке
		sort: 'name', // size,name,time
		//notsort: 0, // возвращать без отсеения цифр
		realname: 0, // (1) не убирать цифры из имени, (2) добавить вывод два имени, (3) добавить пути
		h: 0, // показывать скрытые файлы, только для сервера
	}
	// переопределить из _GET
	for (var prop in _GET) if (_GET.hasOwnProperty(prop)) {
		if (GET.hasOwnProperty(prop)) {
			if (_GET[prop].splice) GET[prop] = _GET[prop][_GET[prop].length-1];
			else GET[prop] = _GET[prop];
		}
	}
	if (GET.e) GET.e = GET.e.split(',').map(function(a) { return a.toLowerCase()});
	if (!server) GET.h=0;
	GET.src = getPath(root, GET);
	_dir(GET, '', function(ans) {
		if (GET.random==1) {
			ans = shuffle(ans);
		} else {
			ans = sort(ans, GET.sort);
			if (GET.reverse==1) ans = ans.reverse();
		}
		if (GET.lim) {
			var lim = GET.lim.split(',');
			ans = ans.splice(lim[0], lim[1]);
		}
		var len = ans.length;
		if (GET.realname != 1) {
			if (GET.realname > 1) {
				for (var i = 0; i < len; i++) {
					ans[i].realname = ans[i].name;
					ans[i].realdir = ans[i].dir;
				}
			}
			for (var i = 0; i < len; i++) {
				ans[i].name = ans[i].name.replace(/^\d+\s/g,'');
				ans[i].dir = ans[i].dir.replace(/^\d+\s/g,'');
				ans[i].dir = ans[i].dir.replace(/\/\d+\s/g,'\/');
			}
			if (GET.realname == 3) {
				for (var i = 0; i < len; i++) {
					var val = ans[i];
					val.path = ('/'+val.dir+'/'+val.name+'/').replace('//','/');
					if (val.ext) {
						val.realpath = ('/'+val.realdir+'/'+val.realname+'.'+val.ext).replace('//','/');
					} else {
						val.realpath = ('/'+val.realdir+'/'+val.realname+'/').replace('//','/');
					}
				}
			}
		}
		if (GET.s==0) { for (var i = len; --i >= 0;) {
			delete ans[i].size
		}}
		if (GET.time==0) { for (var i = len; --i >= 0;) {
			delete ans[i].time
		}}
		var _ans = [];
		if (GET.onlyname == 1) {
			for (var i = 0; i < len; i++) {
				if (ans[i].ext) _ans.push(ans[i].name + '.'+ ans[i].ext);
				else _ans.push(ans[i].name);
			}
			ans = _ans;
		}
		if (GET.onlyname == 2) {
			for (var i = 0; i < len; i++) _ans.push(ans[i].name);
			ans = _ans;
		}
		if (GET.obj==1) {
			var obj = { obj: {}, length: len };
			if (ans[0] && ans[0].name) {
				for (var i = 0; i < len; i++) {
					if (ans[i].ext) {
						obj.obj[path.join(ans[i].dir, ans[i].name)+'.'+ans[i].ext] = ans[i];
					} else {
						obj.obj[path.join(ans[i].dir, ans[i].name)] = ans[i];
					}
				}
			} else {
				for (var i = 0; i < len; i++) obj.obj[ans[i]] = 1;
			}
			ans = obj;
		}
		cb(ans);
	})
}

if(typeof(ROOT)=='undefined')var ROOT='../../../';

this.init = function(req, res, next, root) {
	var view=false;
	if(!req.query){
		require(ROOT+'infra/plugins/infra/infra.js');
		var view=infra.View.init(arguments);
		req.query=view.getGET();
	}
	if(!root)root=path.join(__dirname,ROOT);
	//console.log(req.query);
	
	dir(req.query, root, function(ans) {
		if(view){
			view.end(ans);
		}else{
			res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' }); 
			res.end(JSON.stringify(ans, null, "\t"), 'utf-8');
		}
	});
	
}
this.dir = dir;







