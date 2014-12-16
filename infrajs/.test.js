infra.wait(infrajs,'onshow',function(){
	var test=infra.test;

	test.tasks.push([
		'Проверка что есть телефон',
		function(){
			test.check();
		},function(){
			var phone=$('#topphones > b').text();
			if(!phone)return test.err('Телефона нет в шапке');
			test.ok();
		}
	]);


	test.tasks.push([
		'Переход на страницу о фирме',
		function(){
			infra.when(infrajs,'onshow',function(){
				test.check();
			});
			$('#menuitems > table > tbody > tr > td:nth-child(1) > a').click();
		},function(){
			var text=$('#page > h1').text();
			if(text!='О фирме')return test.err('Кажись не перешли');
			test.ok();
		}
	]);


	test.exec();

	//test.tasks
	//test.check
	//test.ok
	//test.err
	//test.exec
});