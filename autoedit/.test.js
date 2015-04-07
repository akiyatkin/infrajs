infra.wait(infrajs,'onshow',function(){
	var test=infra.test;
	test.onshowcheck=function(){
		infra.when(infrajs,'onshow',function(){
			test.check();
		});
	}
	infra.require('*autoedit/autoedit.js');



	test.tasks.push(['Проверяем объект autoedit',function(){
		test.check();
	},function(){
		if(!window.AUTOEDIT)return test.err('Нет объекта AUTOEDIT');
		if(!infra.admin())return test.err('Нет авторизации.');
		if(popup.isShow())return test.err('Закройте все окна.');
		test.ok();
	}]);

	test.tasks.push(['Показать окно',function(){
		test.onshowcheck();
		AUTOEDIT('admin');
	},function(){
		if(!popup.isShow())return test.err('Не открылось окно.');
		test.ok();
	}]);

	test.tasks.push(['Тест чекбокса 1',function(){
		$('[name=autoblockeditor]').prop('checked', true);//.change();
		test.check();
	},function(){
		if(!$('[name=autoblockeditor]').get(0).checked)return test.err('Чек бокс редактор блоков не отметился');
		test.ok();
	}]);

	test.tasks.push(['Тест чекбокса 2',function(){
		$('[name=autoblockeditor]').prop('checked', false);//.change();
		test.check();
	},function(){
		if($('[name=autoblockeditor]').get(0).checked)return test.err('Чек бокс редактор блоков остался отмеченный');
		test.ok();
	}]);


	test.tasks.push(['Тест чекбокса 3',function(){
		$('[name=autoblockeditor]').prop('checked', true);//.change();
		test.check();
	},function(){
		if(!$('[name=autoblockeditor]').get(0).checked)return test.err('Чек бокс редактор блоков не отметился');
		test.ok();
	}]);

	test.tasks.push(['Закрыли окно',function(){
		test.onshowcheck();
		popup.hide();
	},function(){
		if(popup.isShow())return test.err('Не закрылось окно.');
		if(!$('[data-infrajs-admin-layer]').length)return test.err('Нет ни одного редактируемого блока.');
		test.ok();
	}]);
	
	test.tasks.push(['Редактируем все блоки',function(){
		test.onshowcheck();
		$('[data-infrajs-admin-layer]').each(function(){
			$(this).click();
		});
	},function(){
		if(!popup.isShow())return test.err('Не открылось окно.');
		test.ok();
	}]);
	test.tasks.push(['Всё закрываем',function(){
		test.onshowcheck();
		popup.hideAll();
	},function(){
		if(popup.isShow())return test.err('Осталось открытое окно');
		test.ok();
	}]);


	test.exec();
});