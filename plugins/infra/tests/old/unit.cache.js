if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');

var testfn=function(){//Тестовая функция возвращает случайное значение
	return new Date().getTime()+Math.random();
};
if(infra.NODE)infra.load('*infra/ext/cache.sjs','r');
unit={
	"Без кэша":function(test){

		var r1=Math.random();
		var r2=infra.cache(true,function(){
			return r1;
		});
		
		test.ok(r1==r2);
		test.done();
	},
	"Метка":function(test){
		var r1=infra.cache('unittest',testfn);
		var r2=infra.cache('unittest',testfn);
		test.ok(r1==r2,'Без аргументов');

		var r1=infra.cache('unittest',testfn,[1,2]);
		var r2=infra.cache('unittest',testfn,[1,2]);
		test.ok(r1==r2,'С аргументами');
		
		test.done();
	},
	"Обновление метки":function(test){
		var r1=infra.cache('unittest',testfn);
		
		infra.cache('unittest');// - сбросили метку
		
		var r2=infra.cache('unittest',testfn); //из-за сброшенной метки повторное выполнение
		
		
		test.ok(r1!=r2,r1+' значение должно было изменится');
		test.done();
	},
	"Разные кэши для разных аргументов":function(test){
		var r1=infra.cache('unittest',testfn,[1,2]);
		var r2=infra.cache('unittest',testfn,[1,3]);
		test.ok(r2!=r1);
		test.done();
	},
	"Кэш по дате изменения файла":function(test){
		var real_file='*files/tests/README';
		var r1=infra.cache(real_file,testfn);
		var r2=infra.cache(real_file,testfn);	
		test.ok(r2==r1,'Обращение к кэшу');
		
		var r1=infra.cache('*files/nofile.js',testfn);
		var r2=infra.cache('*files/nofile.js',testfn);	
		test.ok(r2!=r1,'Обращение к кэшу с меткой на не существующий файл. должен пересоздаваться');
		
		infra.cache(real_file);		
		var r3=infra.cache(real_file,testfn);		
		test.ok(r2!=r3,'Изменение кэша');
		
		test.done();
	}
}
if(!infra.NODE)unit={};
if(typeof(module)=='object')module.exports={unit:unit}
