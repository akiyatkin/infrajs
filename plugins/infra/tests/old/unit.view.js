if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');		
var unit={
	"get post cookie":function(test){
		var view=infra.View.get();
		if(infra.NODE){
			if(infra.NODE)var POST=view.getPOST();
			var COOKIE=view.getCOOKIE();
			var REQUEST=view.getREQUEST();
			test.ok(REQUEST);

			//view.setCOOKIE('name1','value1');
			//view.setCOOKIE('name2','value2');
			var http=require('http');
			var httppath='infra/data/httproot.json';
			var httproot=infra.sync(fs,fs.readFile)(__dirname+'/'+ROOT+httppath,'UTF-8');
			
			if(httproot)httproot=infra.exec(httproot,'httproot загружаем из '+httppath);

			var headers={};
			var path=httproot.root+'infra/plugins/infra/tests/cookie.njs';
			var options={
				host: httproot.host,
				port: httproot.port,
				headers: headers,
				path: path,
				method: 'GET'
			}
			var fiber=Fiber.current;
			var req=http.request(options,function(res){
				test.ok(res.headers['set-cookie'].length==2,'Кукисы были установлены и приняты');
				fiber.run();
			});
			req.end();
			yield()
			test.done();
		}else{
			var view=infra.View.init();
			var REQUEST=view.getREQUEST();
			test.ok(REQUEST);
			test.done();
		}
	}
}
if(typeof(module)=='object')module.exports={unit:unit}
