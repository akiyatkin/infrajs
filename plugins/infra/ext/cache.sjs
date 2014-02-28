/*
(c) All right reserved. http://itlife-studio.ru

infra_cache(true,'somefn',array($arg1,$arg2)); - выполняется всегда
infra_cache(true,'somefn',array($arg1,$arg2),$data); - Установка нового значения в кэше, функция somefn не запускается
infra_cache('путь до файла или метка-файл','somefn',array($arg1,$arg2),$data); - Установка нового значения в кэше, функция somefn не запускается
*/
if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');

var fs=require("fs");
var util=require("util");
//var pathlib=require("path");



var dirmain='infra/cache/files/';
infra.forr([
		dirmain+'../',
		dirmain,
		dirmain+'cachedata/',
		dirmain+'cachemarks/',
		dirmain+'csv/'
	],function(path){
		var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+path);
		if(!stat||!stat.isDirectory())infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+path,'0755');
});


var cache_path=function(fn,args){//Создаётся папка для кэшей функции и возвращается путь до файла в котором есть кэш или куда можно сохранить кэш
	fn=fn.toString();

	var crypto=require('crypto');

	var m=crypto.createHash('md5');
	m.update(fn);
	var fns=m.digest('hex');

	var dirfn=dirmain+'cachedata/'+fns+'/';
	var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+dirfn);
	if(!stat||!stat.isDirectory())infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+dirfn,'0755');

	var m=crypto.createHash('md5');
	m.update(util.inspect(args));
	ars=m.digest('hex');

	return dirfn+ars+'.js';//Путь до файла с кэшем данных
}

/*
	Если в условиях есть цифра это будет означать через сколько минут кэш должен самостоятельно сброситься и то что кэш не сохраняется на жёстком диске.
	Для того что бы кэш не сохранялся на диске и самостоятельно не обновлялся а только по переданным условия нужно передать нуль
	*/
//infra.mem=function(1,fn){
//}
infra.cache=function(conds,fn,args){ //Условия и аргументы всегда массив

	conds=[conds];//если conds не массив мы не можем его изменить в циклах аналогичным с массивом образом conds[key] получается глупость... Нужно либо обязательно самому делать... conds подменить автоматом нельзя так как это простое значение и ссылка на него потеряется точнее останется простым значением.
	//fn=fn||false;
	if(typeof(args)=='undefined')args=[];//Аргументы должны быть строго массивом

	ar=arguments;
	var isnewval=(ar.length==4);
	if(isnewval) newval=ar[3];//Так как новое значение может быть любым значением проверяем его наличие извращённым способом, в этом случае undefined тоже будет значением если его передать.


//   cache
//  		infra_cache
//			marks - папка с метками
//			data - папка с папками функций. Каждая папка функций содержит файлы с данными для разных комбинаций параметров.
  
	var dirmarks=dirmain+'cachemarks/';

	infra.fora(conds,function(val,key,group){
		if(typeof(val)=='string'&&!/[\*\/]/.test(val)){//Если не найдено ни звёздочки ни слэша.. значит это метка и нужно её превратить в путь до файла
			group[key]=dirmarks+val+'.mark';
			var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+group[key]);
			if(!stat||!stat.isFile()){
				infra.sync(fs,fs.writeFile)(__dirname+'/'+ROOT+group[key],'Создана метка');//Фактически файл только что изменён и будет выполнение функции
			}
		}
		//Разница метки и файла
		//Если нет файла, то он не учитывается, как будто и не указывался. Если файла нет выполняться будет всё время
		//Если нет метки, то при перепарсивании метка создастся и будет устаревать и учитываться уже далее. То есть с меткой повторного не будет выполнения
	});

	if(!fn){//обновление метки  infra_cache('mark'); То есть переданы только метки.. выше файл метки уже был создан. А вот если нет файла то пропускаем
		infra.fora(conds,function(val){//Если метки и небыло она не будет создана.
			if(typeof(val)=='string'){
				val=infra.theme(val);
				if(!val)return;
				var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+val);
				if(stat&&stat.isFile()){
					var time = new Date().getTime()+1000;//Обновили метку Зачем +1000 - 1 секунда чтобы время не было равно ??
					time=new Date(time);
					var str='touch -m -d "'+time.toUTCString()+'" "'+__dirname+'/'+ROOT+val+'"';//Дата изменения хранится с точностью до секунды
					infra.load('*infra/ext/system.sjs');
					infra.system(str);
				}
			}
		});
		return;//Если нет функции значит просто обновляем метки и выходим.
	}

	var execute=infra.fora(conds,function(a,k,group){
		if(a===true){//Если true Значит запускаем всегда.. это своего рода refresh. и также это нужно для устновки ного значения без выполнения функции
			return true;
		}
	});

	var path=cache_path(fn,args);//Путь до требуемого кэша. block синхронная команда в зависимости от функции внутри неё
	var cachefn=function(){//Запрещаем паралельное выполнение разными потоками
		var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+path);
		if(!execute&&!(stat&&stat.isFile())){//Если нет кэша
			execute=true;
		}else{
		}


		if(!execute){
			var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+path);
			var cache_time=new Date(stat['mtime']);
			
			var mark_time=cache_time;//Если ни одной метки не будет кэш никогда обновлён при таком запросе не будет.
			infra.fora(conds,function(mark,i,group){
				mark=infra.theme(mark,'fds');
				if(mark){
					var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+mark);
					if(stat){//с true сюда уже не попадаем
						var m=new Date(stat['mtime']);
						if(m>mark_time){
							mark_time=m;
						}
						if(stat&&stat.isDirectory()){
							var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+mark+'last_folder_update.txt');
							if(stat&&stat.isFile()){
								var m=new Date(stat['mtime']);
								if(m>mark_time)mark_time=m;
							}
							var list=infra.sync(fs,fs.readdir)(__dirname+'/'+ROOT+mark);
							infra.forr(list,function(li){
								var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+mark+'/'+li);
								if(stat&&stat.isFile()){
									var m=new Date(stat['mtime']);
									if(m>mark_time)mark_time=m;
								}
							});
							
						}
					}//Если переданной метки не существует, кэш не обновляем когда он уже есть. 
					if(cache_time<mark_time){
	
						execute=true;
						return false;
					}
				}else{//Если файла нет то перезаписываем кэш
					execute=true;
					return false;
				}
				
			});
		}
	
		var data;
		if(!execute){
			var data=infra.load(path,'kjXS');
			//var data=infra.sync(fs,fs.readFile)(__dirname+'/'+ROOT+path,'UTF-8');
			//data=infra.exec(data,'Востановление кэша infra.cache');
		}else{
			if(isnewval){//Псевдозапуск
				data=newval;
			}else{
				data=fn.apply(infra,args);//Там могут быть остановки потока. И другой процесс запустит этот же кэш и будет делать его
			}
			var cache=JSON.stringify(data);
			infra.sync(fs,fs.writeFile)(__dirname+'/'+ROOT+path,cache);
			infra.unload(path);
		}
		return data;
	};
	cachefn.toString=function(){//Для отладки в infra.block чтобы понять что за функция кэшируется
		return fn.toString();
	}
	return infra.block('cache-'+path,cachefn);
}
infra.cache.proc={};
module.exports={cache:infra.cache};
/*console.log('');
if(infra.query){
	console.log('Сбросилась метка');
	cache('test');
}
var r=cache('test',function(v){
	console.log('Выполнилось');
	return v+'1';
},'asdf');
console.log('Итого: '+r);

infra.end('asdf');*/
