infra.wait(infrajs,'onshow',function(){
	var test=infra.test;

	test.tasks.push([
		'В одной секунде. Клиент потом сервер',
		function(){
			infra.session.set('test','Клиент',true);
			var path='infra/plugins/session/set.php?name=test&val=Сервер';
			infra.unload(path);
			infra.loadJSON(path);
			infra.session.syncNow();
			test.check();
		},
		function(){
			if(infra.session.get('test')!='Сервер')return test.err(infra.session.get('test'));
			infra.session.set('test',null,true);
			test.ok();
		}
	]);


	test.tasks.push([
		'В одной секунде. Cервер потом клиент',
		function(){
			var path='infra/plugins/session/set.php?name=test&val=Сервер';
			infra.unload(path);
			infra.loadJSON(path);
			infra.session.set('test','Клиент',true);
			infra.session.syncNow();
			test.check();
		},
		function(){
			if(infra.session.get('test')!='Клиент')return test.err(infra.session.get('test'));
			infra.session.set('test',null,true);
			test.ok();
		}
	]);

	test.tasks.push([
		'Асинхронно. В одной секунде. Клиент потом сервер',
		function(){
			infra.session.set('test','Клиент');
			var path='infra/plugins/session/set.php?name=test&val=Сервер';
			infra.unload(path);
			infra.loadJSON(path);
			infra.session.syncNow();
			test.check();
		},
		function(){//Синхронная запись Клиент придёт позже... и это норм.
			if(infra.session.get('test')!='Клиент')return test.err(infra.session.get('test'));
			infra.session.set('test',null,true);
			test.ok();
		}
	]);


	test.tasks.push([
		'Асинхронно. В одной секунде. Cервер потом клиент',
		function(){
			var path='infra/plugins/session/set.php?name=test&val=Сервер';
			infra.unload(path);
			infra.loadJSON(path);
			infra.session.set('test','Клиент');
			infra.session.syncNow();
			test.check();
		},
		function(){
			if(infra.session.get('test')!='Клиент')return test.err(infra.session.get('test'));
			infra.session.set('test',null,true);
			test.ok();
		}
	]);

	test.tasks.push([
		'Удаление последнего свойства в объекте',
		function(){
			infra.session.set('test.test.test',{count:1},true,function(){});
			infra.session.set('test.test.test.count',null,true,function(){});
			test.check();
		},
		function(){
			if(infra.session.get('test'))return test.err('Не удалился test');
			test.ok();
		}
	]);

	test.tasks.push([
		'Работа с одим name',
		function(){
			infra.session.set('test',true,true);
			infra.session.set('test',null,true);
			test.check();
		},
		function(){
			if(infra.session.get('test'))return test.err('Не удалился test');
			test.ok();
		}
	]);
	
	test.tasks.push([
		'Срабатывание callback при удалении',
		function(){
			infra.session.set('test.test',null,true,function(){
				test.check();
			});
		},
		function(){
			test.ok();
		}
	]);
	test.tasks.push([
		'Установка и удаление срабатываение callback',
		function(){
			infra.session.set('test.test',true,true); infra.session.set('test.test',null,true,function(){
				test.check();
			});
		},
		function(){
			test.ok();
		}
	]);
	test.tasks.push([
		'Туда Сюда',
		function(){
			infra.session.set('test.test',true,true); 
			infra.session.set('test.test',null,true);
			infra.session.set('test.test',true,true);
			infra.session.set('test.test',null,true);
			test.check();
		},
		function(){
			if(infra.session.get('test.test'))test.err('Значение осталось');
			test.ok();
		}
	]);
	test.tasks.push([
		'Проверка safe',
		function(){
			infra.session.set('safe.test1',true,true); 
			infra.session.set('safe.test2',1,false,function(){
				test.check();		
			});
		},
		function(){
			if(infra.session.get('safe.test1')||infra.session.get('safe.test2'))test.err('Мы как-то установили значение в safe');
			test.ok();
		}
	]);
	test.exec();
});