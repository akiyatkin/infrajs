	infra.wait(infrajs,'oninit',function(){
		infrajs.externalAdd('myenv','config');//Обрабатывается также как config
		infrajs.externalAdd('env','');//Никак не обрабатывается.. будет установлено только если нечего небыло
		infrajs.externalAdd('envs','childs');//Объединяется так же как childs
		infrajs.runAddKeys('envs');//Теперь бегаем и по envs свойству
	});
	/*infrajs.envSet=function(env,val){
		//Функция вызывается после того как все слои показаны и нужно среди рабочих слоёв проверить было ли показан слои указанного env
		if(infrajs.ignoreDOM())return;
		var layers=[];
		infrajs.run(infrajs.getWorkLayers(),function(layer){
			if(!layer.env)return;
			if(layer.env==env){
				if(layer.envval==val)return;
				layer.envval=val;
				layers.push(layer);
			}
		});
		if(layers.length)infrajs.check(layers);
	}*/
	infrajs.envCheck=function(layer){
		if(!layer.env)return;
		var store=infrajs.store();
		if(!store.ismainrun){
			return !!layer['envval'];
		}
		//Слои myenv надо показывать тогдаже когда и показывается сам слой
		var myenv,ll;
		infrajs.run(infrajs.getWorkLayers(),function(l){//Есть окружение и мы не нашли ни одного true для него
			if(!l.myenv)return;
			if(!infrajs.is('check',l))return;//В back режиме выйти нельзя.. смотрятся все слои
			if(l===layer)return;//Значение по умолчанию смотрится отдельно 
			if(l.myenv[layer.env]===undefined)return;
			if(infrajs.is('show',l)){//Ищим последнюю установку на счёт env
				myenv=l.myenv[layer.env];
				ll=l;
			}
		});
		var r;
		if(typeof(myenv)!=='undefined'){//Если слой скрываем слоем окружения который у него в родителях числиться он после этого сам всё равно должен показаться
			if(myenv){//Значение по умолчанию смотрим только если myenv undefined
				r=true;
			}else{
				r=false;
				infrajs.isSaveBranch(layer,!!infrajs.isParent(ll,layer));
				//infrajs.isSaveBranch(layer,false);
			}
		}
		if(typeof(r)=='undefined'&&layer.myenv){//Значение по умолчанию
			var myenv=layer.myenv[layer.env];
			if(myenv!==undefined){//Оо есть значение по умолчанию для самого себя
				if(myenv){
					r=true;
				}else{//Если слой по умолчанию скрыт его детей не показываем
					r=false;
					infrajs.isSaveBranch(layer,false);
				}
			}
		}
		layer.envval=myenv;
		if(r) return !!myenv;	
		return false;
	};


	//myenv:(object),//Перечислены env которые нужно показать и значения которые им нужно передать в envval
	//env:(string),//Имя окружения которое нужно укзать чтобы слой с этим свойством показался
	//envval:(mix),//Значение, которое было установленое в myenv. envval устанавливается автоматически, в ручную устанавливать его нельзя



/*
 	//когда есть главная страница и структура вложенных слоёв, но вложенные показываются не при всех состояниях и иногда нужно показать главную страницу. Это не правильно. Адреса должны автоматически нормализовываться.
	//Если такого состояния нет нужно сделать редирект на главную и по этому задачи показывать главную во внутренних состояниях отпадает
	//при переходе на клиенте должно быть сообщение страницы нет, а при обновлении постоянный редирект на главную или на страницу поиска
	infra.listen(infra,'layer.oncheck',function(){
		//myenv Наследуется от родителя только когда совсем ничего не указано. Если хоть что-то указано от родителя наследования не будет.
		var layer=this;
		if(layer.myenv)return;
		if(!layer.parent||!layer.parent.myenv)return;
		layer.myenv={};
		infra.foro(layer.parent.myenv,function(v,k){
			layer.myenv[k]=v;
		});
	});
	*/
	//Обработка envs, envtochild, myenvtochild
	
	

	
	infrajs.envEnvs=function(layer){
		if(!layer.envs)return;
		infra.forx(layer.envs,function(l,env){
			infrajs.run(l,function(la){
				if(!la.env)la.env=env;
				la.envtochild=true;
			});
		});
	}
	
	infrajs.envtochild=function(layer){
		var par=layer;
		while(par.parent&&par.parent.env){
			par=par.parent;
			if(par['envtochild']){
				layer['env']=par['env'];
				return;
			}
		}
	}


	infrajs.envframe=function(layer){
		if(!layer['envframe'])return;
		if(layer['env'])return;

		var stor=infra.stor();
		if(!stor['envcouter'])stor['envcouter']=0;
		stor['envcouter']++;
		layer['env']='envframe'+stor['envcouter'];
	}
	infrajs.envframe2=function(layer){
		var par=layer['parent'];
		if(!par)return;
		if(!par['envframe'])return;
		if(!layer['myenv'])layer['myenv']={};
		layer['myenv'][par['env']]=true;
		layer['myenvtochild']=true;
	}

	infrajs.envmytochild=function(layer){
		var par=layer;
		while(par.parent&&par.parent.myenv){
			par=par.parent;
			if(par['myenvtochild']){
				if(!layer['myenv'])layer['myenv']={};
				for(var i in par['myenv']){
					layer['myenv'][i]=par['myenv'][i];
				}
				return;
			}
		}
	}
	
	
