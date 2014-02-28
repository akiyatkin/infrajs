
//для того чтобы определить isCheck

infra.listen(infra,'layer.oncheck',function(layer){
	//if(layer.test) alert('proptpl '+layer.configtpl);

	var name='config';//stencil//
	var nametpl=name+'tpl';
	if(layer[nametpl]){
		if(!layer[name])layer[name]={};
		for(var i in layer[nametpl]){
			layer[name][i]=infra.template.parse([layer[nametpl][i]],layer);
		}
	}
	infra.forr(['tpl','env','div','json','dataroot','tplroot'],function(prop){
		var proptpl=prop+'tpl';
		if(layer[proptpl]){
			var p=layer[proptpl];
			if(layer[proptpl].constructor===Array){
				p=infra.template.parse(p,layer);
				layer[prop]=[p];
			}else{
				p=infra.template.parse([p],layer);
				layer[prop]=p;
			}
		}
	});

	var name='myenv';
	var nametpl=name+'tpl';
	if(layer[nametpl]){
		if(!layer[name])layer[name]={};
		for(var i in layer[nametpl]){
			layer[name][i]=infra.template.parse([layer[nametpl][i]],layer);
		}
	}

	/*var name='autoedit';
	var nametpl=name+'tpl';
	if(layer[nametpl]){
		if(!layer[name])layer[name]={};

		if(layer[nametpl]['title'])layer[name]['title']=infra.template.parse([layer[nametpl]['title']],layer);
		if(layer[nametpl]['descr'])layer[name]['descr']=infra.template.parse([layer[nametpl]['descr']],layer);

		if(layer[nametpl]['files']){
			var files=[];
			infra.fora(layer[nametpl]['files'],function(file){
				var f={};
				if(file['title'])f['title']=infra.template.parse([file['title']],layer);
				if(file['root'])f['root']=infra.template.parse([file['root']],layer);

				if(file['paths']){
					var paths=[];
					infra.fora(file['paths'],function(path){
						path=infra.template.parse([path],layer);
						if(!path)return;
						paths.push(path);
					});
					f['paths']=paths;
				}

				files.push(f);
			});
			layer[name]['files']=files;
		}
	}
	if(infrajs.external)infrajs.external.add('autoedittpl',function(now,ext,layer,external,i){
		if(layer[i.replace(/tpl$/,'')])return;
		if(layer[i])return;
		if(!now)now=ext;
		return now;
	});*/
	if(infrajs.external)infrajs.external.add('configtpl',function(now,ext,layer,external,i){
		//if(layer[i.replace(/tpl$/,'')])return; если у слоя уже есть config это не значит что не нужно наследовать configtpl
		if(layer[i])return;
		if(!now)now=ext;
		return now;
	});
	
});
/*
 * взять configtpl из external
 * распарсить положить в config значения
 * вставить в шаблоне
 * */
