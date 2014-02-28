if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
require('fibers');
/*
	Считываем хранилище из текущего потока и если переда аргументом дочерний поток ссылка на хранилище сохраняется и в нём.
	Если хранилища нет оно создаётся.
	Нельзя ещё без первого потока создавать разные дочерние потоки. Родительский поток должен быть всегда один и все дети должны быть от него, 
	обычно родительский поток создаётся в скрипте сервера.

	Когда нет ещё потока вызов infra.stor обязательно должен быть с потоком в аргументе, что и происходит в infra.fiber.
	Пользователь всегда вызывает infra.stor() без аргумента, даже если infra.fiber ещё не вызывался достаточно потока созданного стандартным образом.
	Стандартный поток всегда создаётся в скрипте сервера, можно и там поток создать с помощью infra.fiber. после обоих вариантов infra.stor будет корректно далее работать.
*/
infra.stor=function(fiber_child,fiber_parent){//fiber_child текущий новый поток в котором должно появится stor. fiber_parent родительский поток из которого этот stor можно взять либо возьмётся из Fiber.current либо stor будет создан.
	var fibers=[fiber_parent,Fiber.current,fiber_child];
	var stor=false;
	for(var i=0,l=fibers.length;i<l;i++){
		if(!fibers[i])continue;
		stor=fibers[i].stor;
		break;
	}
	var crstor=stor||{};//Ну мы не знаем толи потока ещё нет толи stor ещё не разу не был создан

	for(var i=0,l=fibers.length;i<l;i++){
		if(!fibers[i])continue;
		fibers[i].stor=crstor;
		stor=crstor;//Хотябы один раз зашли, значит какой-то поток есть и stor реально создан
	}
	return stor;//Создавать stor когда нет потока нельзя и возвращаться дожен undefined
}
infra.fiber=function(fn){ //Обернуть функцию в поток
	var fiber_parent=Fiber.current;
	return function(){
		var a=arguments;
		var fiber=Fiber(function(){
			infra.exec(fn,'функция переданная в infra.fiber',a);
		});
		infra.stor(fiber,fiber_parent);//Либо хранилище создано, либо унаследованно. В этом месте доступен текущий поток и аргументом переда новый дочерний.
		fiber.run();
	}
};
infra.block=function(mark,fn){//Любой кто вызовет функцию block с меткой mark будет ждать пока другие процессы закончат выполнение block с меткой mark
	var i=0;
	while(infra.block[mark]){//Исключаем паралельное выполнение разными потоками
		i++;
		console.log(i+') Совпали потоки infra.block с меткой '+mark+'\n'+fn);
		var fiber=Fiber.current;
		setTimeout(function(){
			fiber.run();
		},200);
		yield();
	};
	infra.block[mark]=true;
	try{
		var r=infra.exec(fn,'Функция переданная в infra.block с меткой '+mark);
	}catch(e){
		infra.block[mark]=false;
		infra.error(e,'Ошибка в infra.block');
	}
	infra.block[mark]=false;
	return r;
};
/*
	Возвращает функцию, которую можно уже запускать синхронно. er означает генерировать исключение в случае ошибки.
*/
infra.sync=function(obj,fn,er){
	return function(){
		var r, fiber=Fiber.current, e, args=[],i,l;
		for(i=0,l=arguments.length;i<l;i++){
			args.push(arguments[i]);
		}
		args.push(function(err,res){
			e=err;
			r=res;
			fiber.run();
		});
		fn.apply(obj,args);
		yield();
		if(er&&e)throw e;
		return r;
	}
}

