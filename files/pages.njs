/* Собирает форматированное содержимое директории */

this.init = function(req, res, next, root) {
	var GET = req.query;
	var dir = require(__dirname + '/dir.njs').dir;
	var src;
	if (GET && Object.keys(GET).length) {
		for (var name in GET) { if (GET.hasOwnProperty(name)) {
			GET[name] = GET[name].trim();
		}}
		src = GET.src;
	}
	if (src) {
		dir({ src: src, sort: 'name', f: 1, d: 1, sub: 1, realname: 2 }, root, function(ans) {
			if (ans) {
				var counter = ans.length;
				ans.forEach(function (val, index, array) {
					val.path = ('/'+val.dir+'/'+val.name+'/').replace('//','/');
					if (val.ext) {
						val.realpath = ('/'+val.realdir+'/'+val.realname+'.'+val.ext).replace('//','/');
					} else {
						val.realpath = ('/'+val.realdir+'/'+val.realname+'/').replace('//','/');
					}
					if (--counter==0) {
						res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' }); 
						res.end(JSON.stringify(ans, null, "\t"), 'utf-8');
					}
				});
			} else { res.writeHead(502); res.end('Bad Gateway'); }
		});
	} else { res.writeHead(502); res.end('Bad Gateway'); }
};
