//Связь с физическим хранилищем
if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/default.js');
infra.load('*session/session.js','r');

var folder='infra/data/.session/';
var fs=require('fs');
infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+folder,'0755');

var sesadm={
	folder:folder,
	/*get:function(type,id){
		var data=this.loadNew(type,id);
		var value={};
		for(var key in data){
			session_updateData(value,key,data[key]);
		}
		return value;
	},*/
	key:function(text){		
		var folder=this.folder;
		if(!this.secret){
			var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+folder+'.secret.js');
			if(!stat||!stat.isFile()){//Ну придётся генерировать, раз нет
				var secret=this.md5(new Date().getTime()+Math.random()).substr(0,6);
				infra.sync(fs,fs.writeFile)(__dirname+'/'+ROOT+folder+'.secret.js',secret);
			}else{
				var secret=infra.sync(fs,fs.readFile)(__dirname+'/'+ROOT+folder+'.secret.js','UTF-8');
			}
			this.secret=secret;
		}
		var key=this.md5(this.secret+text);
		return key;//Ключ который клиент не сможет сгененировать для text. Это может сделать только сервер.
	},
	md5:function(str){
		str=String(str);
		var crypto=require('crypto');
		var m=crypto.createHash('md5');
		m.update(str);
		return m.digest('hex');
	},
	rmfulldir:function(type,id){
		var folder='*.session/'+type+'/'+id+'/';
		folder=infra.theme(folder,'d');
		if(!folder)return true;
		if(!/data/.test(folder)&&!/cache/.test(folder))return infra.error('Полное удаление возможно только в папках cache и data');
		
		var files=infra.sync(fs,fs.readdir)(__dirname+'/'+ROOT+folder);

		for(var i=0,l=files.length;i<l;i++){
			var file=files[i];
			var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+folder+file+'/');
			if(stat&&stat.isDirectory()){
				var r=this.rmfulldir(folder+file+'/');
				if(!r)return false;
			}else{
				var r=infra.sync(fs,fs.unlink)(__dirname+'/'+ROOT+folder+file);
				if(!r)return false;
			}
		}
		return infra.sync(fs,fs.rmdir)(__dirname+'/'+ROOT+folder);
	},
	session_sort_name:function($a,$b){
		$a=$a['name'];
		$b=$b['name'];

		if($a==$b)return 0;
		return ($a < $b) ? -1 : 1;
	},
	
	writeNew:function(type,id,li){
		
		var folder=infra.theme(this.folder,'d');
		if(!folder)return infra.error('Нет папки для хранения сессий');
		folder=folder+type+'/';
		infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+folder,'0755');
		
		folder+=id+'/';
		infra.sync(fs,fs.mkdir)(__dirname+'/'+ROOT+folder,'0755');
		
		var name=li.name.join('.');
		//console.log(name);
		name=name.replace('/','.'+infra.Session.prototype.sign+'.');
		var file=folder+name+'.js';
		
		infra.sync(fs,fs.writeFile)(__dirname+'/'+ROOT+file,infra.Session.prototype.source(li.value));
		if(li.time){
			var time=new Date(li.time);
			infra.sync(fs,fs.utimes)(__dirname+'/'+ROOT+file,time,time);
		}
	},
	loadNew:function(type,id,timestart,timenow){//данные в объекте, Возвращает все новые записи в папке сесии в формате timestart, name, value. Если время не указано вернёт всё
		var opt=infra.Session.options[type];
		timestart=timestart||0;

		var r=(infra.Session.storage[type]&&infra.Session.storage[type][id]);
		if(r){
			var run=infra.Session.storage[type][id];
		}else if(opt.save){//Сессия может хранится на диске
			var run=[];
			var folder=this.folder+type+'/'+id+'/';
			var files=infra.sync(fs,fs.readdir)(__dirname+'/'+ROOT+folder);
			if(files){
				for(var i=0,l=files.length;i<l;i++){
					var file=files[i];
					if(!/\.js$/.test(file))continue;
					var name=file.replace(/\.js$/,'');
					name=name.replace('.'+infra.Session.prototype.sign+'.','/');
					name=name.split('.');
					
					
					var stat=infra.sync(fs,fs.stat)(__dirname+'/'+ROOT+folder+file);
					var t=new Date(stat['mtime']);
					//var t=php.filemtime(folder+file);//Дата изменения
					//if(timestart>=t||(timenow&&t>=timenow))continue;//Если равно timestart то забираем/ Если равно timenow то не берём
					var value=infra.load(folder+file,'Sksxj');

					run.push(infra.Session.prototype.pname({
						name:name,
						value:value,
						time:t.getTime()
					}));
				}
			}
			infra.Session.storage[type][id]=run;
		}else{
			var run=[];
		}
		var news=[];
		for(var i=0,l=run.length;i<l;i++){
			var t=run[i].time;
			if(timestart>=t||(timenow&&t>=timenow))continue;
			news.push(run[i]);
		}
		
		return news;
		
		/*
		if(php.is_dir(ROOT.$folder)&&$dh = opendir(ROOT.$folder)){
			while(($file = readdir($dh)) !== false){
				if($file[0]=='.')continue;
				$t=filemtime(ROOT.$folder.$file);//Дата изменения
				
				if($t<$timestart)continue;//Если равно в том числе заходим и забираем и то что было самими же в этот timestart прошлый раз установлено. Перекрёстный захват последней секунды..
				//А в начале секунды кем-то что-то записано
				//B мы записали какое-то значение. B записывается после вызвоа loadNew и эти данные не будут замечены так как записаны будут далее позже. 
				//С в конце секунды ещё кто-то записался

				//1 при записи последнюю секунду не забираем и A и B не берём. Для этого и нужен $timenow
				//2 при чтении или следующей записи забрали A B C к тому времени уже старые. На клиенте прежде чем сообщать об изменении сессии будет проверка что пришедшие значения не равны тем что уже есть на клиенте.
					//И если только B то проверка проскочит без оповещений. Если на клиенте данные изменились B то обязательно будет запрос до того как прошлый данные придут как новые. И в таком случае прошлый данные уже не придут как новые.

				if($timenow&&$t>$timenow)continue;//Вот он A уже успел записаться.. исключительная ситуация.

				$name=preg_replace("/data\./",'',infra_toutf($file));
				$name=preg_replace('/\.\w{0,4}$/','',$name);//Удалили расширение файла
				$ans[]=array('time'=>$t,'name'=>$name,'value'=>infra_plugin($folder.$file,'sfpe'));//e - не использовать кэш так как файлы был создан полсле проверки есть этот файл или нет...
			}
			closedir($dh);
		}
		usort($ans,'session_sort_name');//Сортировка по имени из расчёта что уровень объектов отделяется точкой и не важно что раньше data.a или data.b Все data.b.* будут по тойже логике рядом
		$res=array();
		foreach($ans as $v){
			$res[$v['name']]=$v['value'];
		}
		return $res;*/
	}
}
module.exports=sesadm;
