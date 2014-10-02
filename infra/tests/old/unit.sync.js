if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undeinfed')require(ROOT+'infra/plugins/infra/infra.js');		

var unit={
	"Синхронное выполнение асинхронных функций":infra.fiber(function(test){
		if(infra.NODE){
			var fs=require('fs');
			var files=infra.sync(fs,fs.readdir)(__dirname+'/'+ROOT); 
			console.log(files);
			test.ok(files.length);
			test.done();
		}else{
			test.ok(false,'На сервере');
			test.done();
		}
	})
}
if(!infra.NODE)unit={};
if(typeof(module)=='object')module.exports={unit:unit}
