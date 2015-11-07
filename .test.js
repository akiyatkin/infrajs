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

	test.tasks.push([
		'Пробежка по всем страницам',
		function() {
			infra.wait(infrajs,'onshow',function(){
				var alllinks={};
				var countLinks={};
				var count = [];
				var getNextHref = function(){
					var nexthref=false;
					for(var i in alllinks){
						if(alllinks[i]===true){
							continue;
						}
						nexthref=i;
						alllinks[nexthref]=true; //страница посещена. На самом деле она будет посещена дальше, но мы уверены что это произойдет
						break;
					}
					return nexthref;
				}

				var gohref=function(){
					$('a').each(function(){
						var href=$(this).attr('weblife_href');
						if(!href)return;
						if(typeof(alllinks[href])==='undefined'){
							alllinks[href]=false;//Страница найдена и она не посещена
							countLinks[href]=0;//для подсчета количества повторяющихся ссылок
							count.push(href);
						}
						countLinks[href]+=1;
						
					});

					//Посчитать количество упоминаний каждой ссылки. отсортировать по количеству упоминаний и вывести по окончанию теста эту переменную.
					//Выводить в консоли все посещаемые страницы.
					
					var nexthref = getNextHref();
					console.log(nexthref);
					
					
					var countSort = [];
					if(!nexthref){
						

						console.log(alllinks);
						console.log(countLinks);
						
						var index = [];
						
						for(var key in countLinks){
							index.push({
								href:key,
								count:countLinks[key]
							});
						}

						index.sort( function(a,b){ 
							return b.count-a.count; 
						});
						


						//console.log(index);
						for(var i=0; i<index.length; i++){
							console.log(index[i].count+' переходов на '+index[i].href);
						}
						/*var newArr = [];
						for(var i = 0; i<index.length;i++){
							
							
						}*/
						
						/*console.log(count);
						for(var i=0; i<count.length; i++){
							countSort.push(countLinks[count[i]]);
							console.log('Количество переходов по ссылке "'+count[i]+'": '+countLinks[count[i]]);
						}
						console.log(countSort);
						countSort.sort(function(a,b){return b-a;});
						console.log(countSort);*/
						return test.check();
						
					}
					infra.when(infrajs,'onshow',gohref);

					infra.Crumb.go(nexthref);
				}


				gohref();
				
			});
			
		}, function() {
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