infra.test=function(plugin,step){

	setTimeout(function(){//надо чтобы в консоли сначало вывелась строка return а потом уже тест запустился. наоборот тупо.
		
		infra.test.index=0;
		if(typeof(step)!=='undefined')infra.test.index=step;
		infra.test.tasks=[];
		infra.test.step=step;

		infra.unload('*infra/ext/test.php?'+plugin);
		infra.require('*infra/ext/test.php?'+plugin);

	},1);
	return 'Тест '+plugin;
}





infra.test.ok=function(msg){
	if(!msg)msg='ok';
	console.info(this.index+': '+msg);
	if(this.index===this.step)return console.log('Выполнен шаг '+this.step);
	this.index++;
	this.exec();
}
infra.test.err=function(msg){
	console.warn(this.index+':ОШИБКА: '+msg);
}
infra.test.exec=function(){
	setTimeout(function(){//Все процессы javascript должны закончится test.ok может запускаться в центри серии подписок
		var task=infra.test.tasks[infra.test.index];
		if(!task){
			console.info('Тест выполнен!');
		}else{
			console.info(infra.test.index+': '+task[0]);
			task[1]();
		}
	},1);
}
infra.test.check=function(){
	var task=this.tasks[this.index];
	task[2]();
}