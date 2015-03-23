infra.test=function(plugin,step){
	setTimeout(function(){//надо чтобы в консоли сначало вывелась строка return а потом уже тест запустился. наоборот тупо.
		infra.test.plugin=plugin;
		infra.test.index=0;
		infra.test.step=step;
		infra.test.iserr=false;
		infra.test.tasks=[];
		infra.unload('*infra/ext/test.php?'+plugin);
		infra.require('*infra/ext/test.php?'+plugin);
	},1);
	return 'Тест '+plugin;
}
infra.test.ok=function(msg){
	if(infra.test.iserr)return;
	if(!msg)msg='ok';
	console.info(this.index+': '+msg);
	this.index++;
	this.exec();
}
infra.test.err=function(msg){
	infra.test.iserr=true;
	console.warn(this.index+':ОШИБКА: '+msg);
	return false;
}
infra.test.exec=function(){
	if(typeof(infra.test.step)!=='undefined'){

		var tasks=[];
		infra.fora(infra.test.step,function(val){
			tasks.push(infra.test.tasks[val]);
		});
		infra.test.tasks=tasks;
		delete infra.test.step;
		
	}
	setTimeout(function(){//Все процессы javascript должны закончится test.ok может запускаться в центри серии подписок
		var task=infra.test.tasks[infra.test.index];
		if(!task){
			console.info('Тест '+infra.test.plugin+' выполнен!');
			alert('Тест '+infra.test.plugin+' выполнен');
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