//Свойство dyn, state, istate, link
//infra.load('*infrajs/props/external.js');//Уже должен быть
infra.wait(infrajs,'oninit',function(){
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
	infrajs.externalAdd('state',function(now,ext,layer,external,i){//проверка external в onchange
		infrajs.setState(layer,'state',ext);
		return layer[i];
	});
	infrajs.externalAdd('istate',function(now,ext,layer){
		alert('istate в external быть не может потому что istate определяет когда запускается onchange и сейчас onchange уже сработал и проверяется externals где совсем будет не втему обнаружить изменение istate \n'+layer['tplroot']);
	});		

	infrajs.runAddKeys('childs');
	infrajs.runAddList('child');
});
infrajs.setState=function(layer,name,value){
	if(!layer.dyn)layer.dyn={};
	layer.dyn[name]=value;
	var root=layer.parent?layer.parent[name]:infra.State.getState();//От родителя всегда сможем наследовать

	
	if(layer.dyn[name])layer[name]=root.getState([layer.dyn[name]]);
	else layer[name]=root;
}
/*infrajs.stateChilds=function(layer){//oncheck
	infra.forx(layer['childs'],function(l,key){//У этого childs ещё не взять external
		if(!l['state'])l['state']=key;
		if(!l['istate'])l['istate']=key;
	});
}
/*infrajs.stateChild=function(layer){//oncheck
	if(!layer['child'])return;//Это услвие после setState 

	var st=layer['state']['child'];
	if(st) var state=st['name'];
	else var state='###child###';
	infra.fora(layer['child'],function(l){
		l['state']=state;
		l['istate']=state;
	});
	//infrajs_setState($layer,'state',$state);//Функция setState определена в state.js
	//$layer['istate']=&$layer['state'];//В случае с child istate и state становятся одинаковыми и равны state - состоянию от которого считываются параметры
}*/

	
	

	/**
	* layer.istate=.. - так делать нельзя
	* нужно делать так infrajs.setState(layer,'istate','Компания'); - Компания это относительный путь от состояния родителя
	*/
	


