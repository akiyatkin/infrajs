//Свойство dyn, setCrumb
//infra.load('*infrajs/props/external.js');//Уже должен быть
infra.wait(infrajs,'oninit',function(){
	infra.seq.set(infra.template.scope,infra.seq.right('infra.Crumb'),infra.Crumb);
	infrajs.externalAdd('child','layers');
	infrajs.externalAdd('childs',function(now,ext){//Если уже есть значения этого свойства то дополняем
		if(!now)now={};
		infra.forx(ext,function(n,key){
			if(now[key])return;
			//if(!now[key])now[key]=[];
			//else if(now[key].constructor!==Array)now[key]=[now[key]];
			//now[key].push({external:n});
			now[key]={external:n};
		});
		return now;
	});
	infrajs.externalAdd('crumb',function(now,ext,layer,external,i){//проверка external в onchange
		infrajs.setCrumb(layer,'crumb',ext);
		return layer[i];
	});	

	infrajs.runAddKeys('childs');
	infrajs.runAddList('child');
});
infrajs.setCrumb=function(layer,name,value){
	if(!layer.dyn)layer.dyn={};
	layer.dyn[name]=value;
	var root=layer.parent?layer.parent[name]:infra.Crumb.getInstance();//От родителя всегда сможем наследовать

	
	if(layer.dyn[name])layer[name]=root.getInstance([layer.dyn[name]]);
	else layer[name]=root;
}