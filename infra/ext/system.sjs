if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/ext/forr.js','r');
var fs=require('fs');
infra.forr(['infra/cache/infra/','infra/cache/infra/system/'],function(path){
	var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+path);
	if(!stat||!stat.isDirectory())infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+path,'0755');
});
	
		
infra.system=function(cmd){//Не может быть асинхронной иначе будут паралельные запуски на самом деле дважды не запускаемых кэшируемых операций (парсинг excel)
	var FFI = require("node-ffi");
	var libc = new FFI.Library(null, {
	  "system": ["int32", ["string"]]
	});
	
	var crypto=require('crypto');
	var m=crypto.createHash('md5');
	m.update(cmd);
	var md5cmd=m.digest('hex');
	console.log(cmd);
	var tf='infra/cache/infra/system/'+md5cmd;
	cmd+=' > '+__dirname+'/'+ROOT+tf;
	
	libc.system(cmd);
	
	var res=infra.sync(fs,fs.readFile)(__dirname+'/'+ROOT+tf,'UTF-8');
	infra.sync(fs,fs.unlink)(__dirname+'/'+ROOT+tf);
	return res;
};
