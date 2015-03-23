infra.wait(infrajs,'onshow',function(){
	var test=infra.test;
	
	test.tasks.push(['Проверяем объект popup',function(){
		test.check();
	},function(){
		if(!window.popup)return test.err('Нет объекта popup');
		var funcs=['alert','isShow','confirm','open','error','hide','hideAll','show','text','progress','toggle','render'];
		var r=infra.forr(funcs,function(name){
			if(!popup[name])return !test.err('Не найдена функция popup.'+name);
		});
		if(!r)test.ok();
		if(popup.isShow())return test.err('Не должно быть показанных окон для запуска теста');
	}]);



	test.tasks.push(['alert',function(){
		infra.when(infrajs,'onshow',test.check.bind(test));
		popup.alert('wkjc');
	},function(){
		if(popup.div.find('.modal-body').text()!='wkjc')return test.err('Сообщение не показалось');
		if(!popup.isShow())return test.err('Нет отметки о показе окна');
		test.ok();
	}]);

	test.tasks.push(['alert',function(){
		infra.when(infrajs,'onshow',test.check.bind(test));
		popup.hide();
	},function(){
		if(popup.isShow())return test.err('Окно не скрылось');
		if($('.modal-backdrop').length)return test.err('Осталось затемнение');
		test.ok();
	}]);

	test.tasks.push(['alert',function(){
		infra.when(infrajs,'onshow',test.check.bind(test));
		popup.text('skjoind');
	},function(){
		if($.trim(popup.div.find('#popup_content').text())!='skjoind')return test.err('Сообщение не показалось');
		if(!popup.isShow())return test.err('Нет отметки о показе окна');
		test.ok();
	}]);
	test.tasks.push(['alert',function(){
		infra.when(infrajs,'onshow',test.check.bind(test));
		popup.hide();
	},function(){
		if(popup.isShow())return test.err('Окно не скрылось');
		if($('.modal-backdrop').length)return test.err('Осталось затемнение');
		test.ok();
	}]);

	test.exec();
});