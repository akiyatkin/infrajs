//Свойство dyn, state, istate

//infra.loadJS('*infra/props/external.js');//Уже должен быть

(function(){
	infra.listen(infra,'layer.onchange.cond',function(){
		if(!this['istate'].obj)return false;
	});

	infra.listen(infra,'layer.oninit.before',function(){
			var layer=this;
			if(!layer.dyn){//Делается только один раз
				infra.setState(layer,'state',layer.state);
				infra.setState(layer,'istate',layer.istate);
			}
	});
	infra.listen(infra,'onchange',function(){
		//Так как расширение external может быть загружено и позже. 
		//Работаем с ним перед запуском
		if(!infra.external)return;
		infra.external.add('state',function(now,ext,layer,external,i){//проверка external в onchange
			infra.setState(layer,'state',ext);
			return layer[i];
		});

		infra.external.add('istate',function(now,ext,layer){
			if(infra.debug)alert('istate в external быть не может потому что istate определяет когда запускается onchange и сейчас onchange уже сработал и проверяется externals где совсем будет не втему обнаружить изменение istate \n'+layer);
		});
	});

	infra.listen(infra,'onshow',function(){
		this.state.setA(document);//Пробежаться по всем ссылкам и добавить спициальный обработчик на onclick... для перехода по состояниям сайта.
	});

	/**
	* layer.istate=.. - так делать нельзя
	* нужно делать так infra.setState(layer,'istate','Компания'); - Компания это относительный путь от состояния родителя
	*/
	infra.setState=function(layer,name,value){
		if(!layer.dyn)layer.dyn={};
		layer.dyn[name]=value;
		var root=layer.parent?layer.parent[name]:infra.state;//От родителя всегда сможем наследовать
		layer[name]=root.getState(layer.dyn[name]);
	}

})();
