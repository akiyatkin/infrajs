infra.wait(infrajs,'onshow',function(){
	var test=infra.test;


	test.tasks.push([
		'Переход на страницу ?test',
		function(){
			infra.when(infrajs,'onshow',function(){
				test.check();
			});
			infra.Crumb.go('?test');
		},function(){
			if(location.search!='?test'){
				return test.err('Страница test не открылась');
			}
			test.ok();
		}
	]);
	test.tasks.push([
		'Переход на главную ?test',
		function(){
			infra.when(infrajs,'onshow',function(){
				test.check();
			});
			infra.Crumb.go('?');
		},function(){
			if(location.search!=''){
				return test.err('Главная страница не открылась');
			}
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