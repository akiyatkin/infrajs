/* Возвращает html данные переданного файла (doc,tpl) */
var fs = require('fs');
var path = require('path');
var select = require('soupselect').select;
var jsdom = require('jsdom');
var htmlparser = require("htmlparser");
var mkdirp = require('mkdirp');
var office = require('office');

var preparePath = function(src) {
	if (src && (src.indexOf('..') == -1)) {
		src = src.replace(/\/+/g,'/');
		return src;
	}
};
this.preparePath = preparePath;

this.init = function(req, res, next, root) {
	/* GET
	 * src - правильный путь до файла
	 * preview - возвращать предпросмотр или нет
	 * text - возвращать текст если указан предпросмотр или нет
	 * cache - путь до общей папки с кэшем
	 */
	var GET = {};
	var end = function() {
		res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' }); 
		res.end(JSON.stringify(ans, null, "\t"), 'utf-8');
	};
	var ans = { "result": 0 };
	if (req.query && Object.keys(req.query).length) {
		var name; for (name in req.query) { if (req.query.hasOwnProperty(name)) {
			var new_name = name.trim();
			if (new_name) { GET[new_name] = req.query[name].trim(); }
		}}
	}
	GET.cache = preparePath(GET.cache);
	GET.src = preparePath(GET.src);
	if (GET.src && GET.cache) {
		make(GET, root, function(err, data) {
			if (err) { console.error(err);
			} else { ans = data; }
			end();
		});
	} else { end() };
};

var make = function(GET, root, cb) {
	var cachedir = path.join(
		GET.cache, GET.src.replace(/^\//,'').replace(/\/$/,'').replace(/\//g,'|')
	);
	load(GET.src, {cache: cachedir, preview: GET.preview, text: GET.text}, function(err, data) {
		cb(err, data);
	}, root);
};

this.make = make;

var ignore_protocols = [
	RegExp('^mailto:', "gim"),
	RegExp('^http://', "gim"),
	RegExp('^https://', "gim"),
	RegExp('^ftp://', "gim"),
	RegExp('^file://', "gim")
	//RegExp('^/', "gim")
];

/* Загружаем файл, обрабатываем, сохраняем кэш */
var _save = function(filename, cachename, root, tplparse, ext, options, cb) {
	//cache_without_root, origname, cachename, filename, tplparse, cb)
	// загрузить оригинальный файл
	fs.readFile(path.join(root, filename), 'utf-8', function(err, data) {
		if (!err) {
			jsdom.env(data, function(errors, window) {
				try {
					var all = window.document.getElementsByTagName('*');
					// убираем стили и классы
					for (var x = 0; x < all.length; x++) {
						all[x].removeAttribute('style');
						all[x].removeAttribute('class');
						all[x].removeAttribute('STYLE');
						all[x].removeAttribute('CLASS');
					}
					// обрабатываем дурацкие таблицы
					var tables = window.document.getElementsByTagName('table');
					for (var x = 0; x < tables.length; x++) {
						var common1 = tables[x].previousSibling.previousSibling;
						var common2 = tables[x].nextSibling.nextSibling;
						if ((common1.innerHTML.indexOf('####') != -1) && (common2.innerHTML.indexOf('####') != -1)) {
							tables[x].className += " common";
							tables[x].width = "auto";
							common1.innerHTML = '';
							common2.innerHTML = '';
						};
					}
					// картинки
					var imgs = window.document.getElementsByTagName('img');
					for (var x = 0; x < imgs.length; x++) {
						//заменить относительные src
						var src = imgs[x].getAttribute("src").replace(/\?.+$/,''); // вконце бывают вопросы
						var ignore = false;
						for (var i = ignore_protocols.length; --i >= 0;) {
							if (src.search(ignore_protocols[i]) != -1) { ignore = true; break; }
						}
						if (!ignore) {
							if (tplparse) { // возможно картинки лежат рядом с оригиналом
								src = path.join(path.dirname(filename), src);
							} else { // картинки могут лежать рядом с кэшем
								//fs.existsSync(path.join(root, cachename, '..', src))
								src = path.join(cachename, '..', src);
							}
							var width = imgs[x].width;
							var height = imgs[x].height;
							if (width) { src = src + '?w=' + width; }
							if (!width && height) { src = src + '?h=' + height; }
							if (width && height)  { src = src + '&h=' + height; } 
							imgs[x].setAttribute("width", 'auto');
							imgs[x].setAttribute("height", 'auto');
							imgs[x].setAttribute("src", src);
						}
						//добавить class left right для align
						var align = imgs[x].getAttribute("align").toLowerCase();
						if (align == 'left') {
							imgs[x].className += " left";
						} else if (align == 'right') {
							imgs[x].className += " right";
						}
					};
					var htmlString = window.document.getElementsByTagName('body')[0].innerHTML;
					// делаем в одну строку
					//htmlString = htmlString.replace(/\s/gim,' ');
					fs.writeFile(path.join(root, cachename), htmlString, 'utf-8', function(err) {
						if (!err) { parse(htmlString, options, cb);
						} else { cb(err); }
					});
				} catch(e) { cb(e); }
			});
		} else { cb(err); }
	});
};

var _checkCache = function(filename, cachedir, root, cb) {
	/* Кэшированный файл храниться в формате html */
	var ext = path.extname(filename).toLowerCase();
	if (ext != '.tpl') {
		var cachename = path.join(cachedir, path.basename(filename).replace(/\..{0,4}$/,'.html'));
		mkdirp(path.join(root, cachedir), function(err) {
			fs.stat(path.join(root, filename), function(err, stats) {
				if (!err) {
					var origtime = stats.mtime.getTime();
					fs.stat(path.join(root, cachename), function(err, stats) {
						if (!err || (err && (err.code == 'ENOENT'))) {
							var cachetime = 0;
							if (!err) { cachetime = stats.mtime.getTime(); }
							cb(null, (origtime<cachetime), cachename, ext);
						} else { cb(err); }
					});
				} else { cb(err); }
			});
		});
	} else { cb(null, true, filename, ext); }
};

/* Загружает файл и парсит данные файла, кэш */
/* options.cache - длинная директория где будет уже лежать html кэш-файл */
var load = function(filename, options, cb, root) {
	/* нужно ли делать кэш */
	_checkCache(filename, options.cache, root, function(err, cacheExists, cachename, ext) {
		options.cachename = cachename;
		if (!err) {
			if (!cacheExists) {
				console.log('сделать заново кэш');
				if (ext == '.html') {
					_save(filename, cachename, root, true, ext, options, cb);
				} else {
					office.parse(path.join(root, filename), {
						path: path.join(root, options.cache)
					}, function(err, root_cachename) {
						if (!err) {
							var cachename2 = root_cachename.replace(root, '');
							if (cachename2 != cachename) { cb(Error('office parse'));
							} else {
								// пересохранить один и тот же файл
								_save(cachename, cachename, root, false, ext, options, cb);
							}
						} else { cb(err); }
					});
				}
			} else {
				fs.readFile(path.join(root, cachename), 'utf-8', function(err, text) {
					if (!err) { parse(text, options, cb);
					} else { cb(err); }
				});
			}
		} else { cb(err); }
	}); 
};

var getTags = function(text, tag, delimiter) {
	var regExp = new RegExp('####'+tag+'####[\\s\\S]+?####', 'gim');
	var tags = text.match(regExp);
	if (tags) {
		regExp = new RegExp('####'+tag+'####', 'gim');
		return tags[0].replace(regExp,'').replace('####','').trim();
	}
};

/* Парсит данные */
var parse = function(text, options, cb) {
	// делаем в одну строку
	//text = text.replace(/\s/gim,' ');
	var date = 0;
	try { date = path.basename(options.cachename).match(/^\d+/)[0];
	} catch (e) {}
	var data = {
		date: date,
		title: path.basename(options.cachename, path.extname(options.cachename)).replace(/^\d+\s/,''),
		meta: {
			title: getTags(text, 'title', '####'),
			description: getTags(text, 'description', '####'),
			keywords: getTags(text, 'keywords', '####')
		}
	};
	text = text.replace(/####keywords####[\s\S]+?####/gim,'').replace(/####description####[\s\S]+?####/gim,'').replace(/####title####[\s\S]+?####/gim,'');
	var handler = new htmlparser.DefaultHandler(function(err, dom) {
		if (!err) {
			var img, h1;
			try { h1 = select(dom, 'h1')[0].children[0].data;
			} catch (e) {};
			// удалить заголовок
			text = text.replace(/<h1.*?<\/h1>/, '');
			if ((!options.preview && !options.text) || options.text) {
				data.text = text.replace(/####cut####/gim,'').replace(/###cut###/gim,'').trim();
			}
			if (options.preview) {
				// найти первую картинку
				try { img = select(dom, 'img')[0].attribs.src;
				} catch (e) {};
				// удалить картинки
				text = text.replace(/<img.*?>/gim, '');
				text = text.replace(/<img[^>]*?$/gim, ''); // обрезанное изображение
				// сократить по cut, если cut нету то сократить по лимиту до первого абзаца
				if (text.indexOf('###cut###') == -1) {
					text = text.slice(0, 1750);
					// удалить последний не полный абзац
					var _data = text.split(/<\/p>/gim);
					if (_data.length > 1) {
						text = _data.splice(0, _data.length-1).join('</p>');
					}
				} else { text = text.replace(/####cut####[\s\S]+/gim,'').replace(/###cut###[\s\S]+/gim,'') }
				data.preview = text.trim();
				data.img = img;
			}
			data.h1 = h1;
			cb(null, data);
		} else { cb(err); }
	});
	var parser = new htmlparser.Parser(handler);
	parser.parseComplete(text);
};

this.load = load;
this.parse = parse;
