(function() {
	var onloadNews = function(cb) {
		var layer = this;
		var list = layer.data;
		layer.config.list =[];
		var counter = list.length;
		/* рекурсивная функция для синхронности */
		var setPreview = function(i, counter) {
			var val = list[i];
			var path = layer.config.dir + list[i].realpath;
			var src;
			if (list[i].ext.toLowerCase() === 'mht') {
				src = '/core/plugins/pages/mht/mht.php?preview&src='+layer.config.dir+list[i].dir+'/'+list[i].name;
			} else {
				src = '/plugins/files/page.njs?src='+path+'&preview=1&cache=/cache/templates/';
			}
			src = src.replace('//','/').replace('//','/');
			infra.load.json(src, function(err, data) {
				if (!err && data) {
					data.name = list[i].name;
					data.dir = list[i].dir;
					layer.config.list[i] = data;
				}
				if (--counter === 0) {
					var l;
					for(i=0,l=layer.config.list.length;i<l;i++){
						var item = layer.config.list[i];
						if(!item.title) { item.title=item.name; }
						if(item.date) { item.sdate=phpdate('d F Y',item.date); }
						if (item.img) {
							item.image=encodeURIComponent(item.img);
							item.img=item.img.replace(/\?.+/gim,'');
						}
					}
					layer.config.list = layer.config.list.reverse();
					if (layer.config.count) {
						layer.config.list = layer.config.list.splice(0, layer.config.count);
					}
					cb();
				} else { setPreview(i-1, counter); }
			});
		};
		if (counter) {
			infra.load('/core/lib/phpdate/phpdate.js', function(err, data) {
				if (typeof(phpdate) === 'undefined') { eval(data); }
				// загружаем по одной новости попорядку
				setPreview(counter-1, counter);
			});
		} else { layer.config.list = []; cb(); }
	};
	return {
		config: {
			dir: "", // /dir/
			list: "", // list.tpl
			loader: "/plugins/files/loadpage.njs?cache=/cache/templates/&mht=/core/plugins/pages/mht/mht.php?src=&src="
		},
		oncheck: function(cb) {
			// читает config.dir, ищет совпадения, загружает страницу или листинг страниц или 404
			var layer = this;
			layer.show = false;
			var level = 1;
			var page = '/' + infra.state.slice(1,-1).split('/').splice(level).join('/') + '/';
			var realpath;
			var item;
			infra.load.json(layer.json, function(err, data) { // загружаем листинг директории
				if (!err && data && data.length) {
					layer.data = data;
					var i; for (i = layer.data.length; --i >= 0;) { // ищем совпадение имен
						if (layer.data[i].path === page) {
							item = layer.data[i];
							realpath = layer.data[i].realpath;
							break;
						}
					}
				}
				if (realpath) {
					if (item.f) {
						var path = (layer.config.dir + realpath).replace('//','/');
						infra.load.json(layer.config.loader + path, function(err, data) {
							if (!data || err) {
								infra.status_code = 404;
								layer.htmlString = 'Страница не найдена';
							} else {
								layer.htmlString = data.text;
								infra.title = data.title;
								if (data.meta) {
									if (data.meta.keywords) {
										infra.meta.keywords = data.meta.keywords;
									}
									if (data.meta.description) {
										infra.meta.description = data.meta.description;
									}
								}
							}
							cb();
						});
					} if (layer.config.list) { // если определен шаблон для листинга, выводим листинг
						// собираем данные для layer.data
						var list = [];
						var ii;
						var listing = layer.data;
						for (ii = listing.length; --ii >= 0;) {
							if (item.realname === listing[ii].realdir) {
								list.push(listing[ii]);
							}
						}
						layer.htmlString = false;
						layer.tpl = layer.config.list;
						layer.data = list.reverse();
						infra.functions.onloadNews.call(layer, cb);
					}
				} else { //404
					infra.status_code = 404;
					layer.htmlString = 'Страница не найдена';
					cb();
				}
			});
		}
	};
}());
