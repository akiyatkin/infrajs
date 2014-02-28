
if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')infra=require(ROOT+'infra/plugins/infra/infra.js');
if(infra.NODE)infra.load('*infra/ext/admin.sjs');
unit = {
	'Загрузка текста': function (test) {
		var data=infra.load("infra/plugins/infra/tests/text.txt");
		//var data=infra.plugin.file_get_contents("infra/plugins/infra/tests/text.txt");
		test.ok(/text/.test(data),'Загрузка строки '+data);
		test.done();
	},
	'Ошибка 404': function (test) {
		var data=infra.load("infra/plugins/404");
		test.ok(!data,'404');
		test.done();
	},
	'Загрузка данных': function (test) {
		var data=infra.load("*infra/tests/data.json","j");
		test.ok(data&&data.result,'ok');
		test.done();
	},
	'require скрипта': function (test) {
		infra.load("*infra/tests/script.js","xr");
		test.ok(scriptvar,'ok');
		scriptvar=false;
		test.done();
	},
	'Выполнение скрипта': function (test) {
		infra.load("*infra/tests/script.js","xe");
		test.ok(scriptvar,'ok');
		test.done();
	},
	'Синхронная передача post данных и получение ответа согласно переданным данным post.njs': function (test) {
		var res=infra.load("*infra/tests/post.njs","mjxp",{asdf:1});

		test.ok(res.asdf==1,'ok');
		test.done();	
	},
	'Асинхронная передача post данных и получение ответа согласно переданным данным post.njs': function (test) {
		var as=0;
		//var mod=infra.plugin.mod("jxpa","*infra/tests/post.njs");
		//console.log(mod);
		var res=infra.load("*infra/tests/post.njs","wjxpa",{asdf:1},function(err,res){
			test.ok(as==1,'Проверка что действительно асинхронно');
			test.ok(res.asdf==1,'Проверка полученного ответа');
			test.done();
		});	
		as=1;
	},
	'Проверка что возвращается кэш при повторном вызове':function (test) {
		var data1=infra.load("*infra/tests/data.json","j");
		var data2=infra.load("*infra/tests/data.json","j");
		
		test.ok(data1===data2,'Проверка что данные абосолютно теже.');
		test.done();
	},
	'Загрузка файла с параметрами':function (test) {
		
		var p="*infra/tests/get.njs?asdf=1";
		var data=infra.load(p,"j");
		
		test.ok(data.asdf,'Проверка что get данные пришли njs.');
		return test.done();
		

		var p="*infra/tests/get.php?asdf=1";
		var data=infra.load(p,"j");
		test.ok(data.asdf,'Проверка что get данные пришли php.');

		

		
		var p='*imager/imager.php?src=Слайды/Прачечная.jpg';
		test.ok(infra.theme(p),'Путь найден.');
		var data=infra.load(p,"t");
		test.ok(data,'Проверка что загружен текст - ответ php.');

		return test.done();
	},
	'Админ не админ':function(test){
		if(infra.NODE){
			
			infra.load('*infra/ext/admin.sjs');
			var r=infra.admin();
			
			console.log('Статус '+(r?'Админ':'Пользователь'));
			infra.admin(false);
			var r=infra.admin();
			console.log('Статус '+(r?'Админ':'Пользователь'));
			if(r){
				infra.admin.set(false);
			}
		}
		test.done();
	},
	'GET в запросе m и w':function(test){
		//var r=infra.load('infra/plugins/pages/mht/mht.php?src=*Новости/120402 Новый дизайн.tpl&preview=1','wj');
		var p="*infra/tests/get.php";
		var data=infra.load(p,"jg",{asdf:1});
		test.ok(data.asdf,'Проверка что get данные пришли php.');

		/*var r=infra.load('infra/plugins/pages/mht/mht.php','gwj',{
				src:'*Новости/120402 Новый дизайн.tpl',
				preview:1
		});
		console.log(r);
		test.ok(r&&r.date,'Запрос через web к php');
		*/

		/*
		var r=infra.load('infra/plugins/pages/list.php?onlyname=1&src=*infra/','wj');
		test.ok(r&&r.length,'Запрос через web к php');

		var r=infra.load('infra/plugins/infra/tests/get.njs?some=1','wjx');
		test.ok(r&&r.some,'Запрос через web к njs');

		var r=infra.load('infra/plugins/infra/tests/get.njs?some=1','mjx');
		test.ok(r&&r.some,'Обращение к файлу с гет параметрами и с эмитацией');


		var r=infra.load('infra/plugins/infra/tests/get.njs','mjxg',{some:1});
		test.ok(r&&r.some,'Обращение к файлу с гет параметрами в 3тьем объекте');

		var r=infra.load('infra/plugins/infra/tests/get.njs?some1=2','mjxg',{some2:1});
		test.ok(r&&!r.some1&&r.some2,'переданные переданные объектом заменяют те, что уже есть');
		*/

		test.done();
	},
	'Темы':function(test){
		var t='.admin.js';
		var res=infra.plugin.parse('*'+t);
		test.ok(res.paths[0]=='infra/data/'+t,'Тест '+t);
		test.ok(!res.isfolder,'Папка '+t)
		
		var t='some/admin.js';
		var res=infra.plugin.parse('*'+t);
		test.ok(res.paths[0]=='infra/data/'+t,'Тест '+t);
		test.ok(res.paths[1]=='infra/layers/'+t,'Тест '+t);
		test.ok(!res.isfolder,'Папка '+t)		
		
		var t='';
		var res=infra.plugin.parse('*'+t);
		test.ok(res.paths[0]=='infra/data/'+t,'Тест '+t);
		test.ok(res.paths[1]=='infra/layers/'+t,'Тест '+t);
		test.ok(res.isfolder,'Папка '+t);
		
		var t='/';
		var res=infra.plugin.parse('*'+t);
		test.ok(res.paths[0]=='infra/data/','Тест '+t);
		test.ok(res.paths[1]=='infra/layers/','Тест '+t);
		test.ok(res.isfolder,'Папка '+t);
		
		test.done();
	}
};
if(typeof(module)=='object')module.exports={unit:unit}


