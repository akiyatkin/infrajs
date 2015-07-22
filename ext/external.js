//Свойство external


infrajs.external={};
infrajs.external.props={ //Расширяется в env.js
	'div':function(now,ext){
		return ext;
	},
	'layers':function(now,ext){
		if(!now)now=[];
		else if(now.constructor!==Array)now=[now];
		infra.fora(ext,function(e){//Каждый элемент в layers должен попасть в отдельный слой, а не объединитсья в один
			now.push({external:e});
		});
		return now;
	},
	'external':function(now,ext){//Используется в global.js, css
		if(!now)now=[];
		else if(now.constructor!==Array)now=[now];
		now.push(ext);
		return now;
	},
	'config':function(now,ext,layer){//object|string any
		if(ext&&typeof(ext)=='object'&&ext.constructor!=Array){
			if(!now)now={};
			for(var j in ext){
				if(!ext.hasOwnProperty(j))continue;
				if(now.hasOwnProperty(j)&&now[j]!==undefined)continue;
				now[j]=ext[j];
			}
		}else{
			if(now===undefined)now=ext;
		}
		return now;
	}
}
infrajs.externalAdd=function(name,func){
	infrajs.external.props[name]=func;
}

infrajs.external.check=function(layer){
	 while(layer.external){
		 var ext=layer.external;
		 this.checkExt(layer,ext);
	 }
}
infrajs.external.merge=function(layer,external,i){//Используется в configinherit
	if(external[i]===layer[i]){
	}else if(this.props[i]){
		var func=this.props[i];
		while(typeof(func)=='string'){//Указана не сама обработка а свойство с такойже обработкой
			func=this.props[func];
		}
		layer[i]=func.apply(infrajs,[layer[i],external[i],layer,external,i]);
	}else if(typeof(external[i])=='function'){//Функции вызываются сначало у описания потом у external потому что external добавляется потом
		if(layer[i]===undefined)layer[i]=external[i];
		//infra.listen(layer,i,external[i]);
	}else{
		if(layer[i]===undefined)layer[i]=external[i];
	}
}
infrajs.external.checkExt=function(layer,external){
	if(!external)return;
	delete layer.external;
	/* ie изменить порядок неудаётся
	//-------- Управляем порядком свойств в слое
		var tlayer={};
		for(var i in layer){ if(!layer.hasOwnProperty(i))continue;
			if(i=='external'){
				delete layer[i];//Всё что до external остаётся в томже порядке, всё что после будет после свойств external
			}else if(!layer['external']){
				tlayer[i]=layer[i];

				delete layer[i];
			}
		}
		infra.fora(external,function(external){
			if(typeof(external)=='string')var external=infra.loadJSON(external);

			if(external)for(var i in external){
				if(typeof(layer[i])!=='undefined')continue;//Свойство было указано до external и не удалялось
				layer[i]=undefined;//создали пустые свойства в новом порядке. 
			}
		});
		for(var i in tlayer){ if(!tlayer.hasOwnProperty(i))continue;//Вернули родные свойства обратно но уже в нужном порядке
			layer[i]=tlayer[i];
		}
	//-----------
	*/

	infra.fora(external,function(external){
		if(typeof(external)=='string')var external=infra.loadJSON(external);
		//Есть или нет external проверяется на случай ошибок или отсутствия файла external	
		if(external)for(var i in external){
			infrajs.external.merge(layer,external,i);
		}
	});
}

	