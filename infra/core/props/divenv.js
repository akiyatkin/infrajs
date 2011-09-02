//Обработка div и env после того как определено exec_onshow
	infra.isParent=function(layer,parent){
		while(layer){
			if(parent===layer)return true;
			layer=layer.parent;
		}
		return false;
	},
	infra.runparents=function(layer,call){
		while(layer.parent){
			layer=layer.parent;
			if(!layer.div)continue;
			var r=call.apply(this,[layer]);
			if(r!==undefined)return r;
		}
		return true;
	}
	infra.runfrontdiv=function(layer,call){
		var start=false;
		var r=infra.run(this.wlayers,function(l){
			if(!l.exec_onchange)return false;
			if(start){
				if(!l.exec_onshow)return;
				var r=call.apply(this,[l]);
				if(r!==undefined) return r;
			}
			if(layer===l)start=true;

		});
		return r;
	}
	infra.runbackdiv=function(layer,call){
		var start=true;
		return this.run(this.wlayers,function(l){
			if(!l.exec_onchange)return false;
			if(start){
				if(!l.exec_onshow)return;
				var r=call.apply(this,[l]);
				if(r!==undefined)return r;
			}
			if(layer===l)start=false;
		});
	}
	infra.isShow=function(layer){//Бежим вперёд 
		var is=function(el,ar,prop){
			var res;
			infra.fora(ar,function(e){
				if(e[prop]==el){
					res=e;
					return true;
				}
			});
			return res;
		}
		var res=is(layer,this.checkdivs,'layer');
		if(res){
			if(res.value!==undefined)return res.value;
			return 1;//'Уже проверяем не мешает';
		}
		res={layer:layer};


		this.checkdivs.push(res);
		delete layer.fight_msg;


		if(!layer.exec_onshow){
			layer.fight_msg='Предварительно скрыто';
			res.value=false;
			return res.value;
		}

		if(this.runfrontdiv(layer,function(l){
			if(l.div!=layer.div)return;
			if(infra.isShow(l)){
				layer.fight_msg='Кто-то дальше показывается в этом диве '+l;
				if(infra.isParent(l,layer)){
					layer.exec_onshow_savemybranch=true;
				}else{
					layer.exec_onshow_savemybranch=false;
				}
				return true;
			}
		})){
			res.value=false;
			return res.value;
		}


		if(layer.env){
			if(!this.ismainrun){
				layer.fight_msg='Пробежка не от корня. Использовали прошлое значение';
				res.value=!!layer.envval;
				if(!res.value){
					//Найдено false
					return res.value;
				}
			}else{
				var myenv;
				var r=this.run(this.wlayers,function(l){//Есть окружение и мы не нашли ни одного true для него
					if(!l.exec_onshow)return;//В back режиме выйти нельзя.. смотрятся все слои
					if(!l.myenv)return;
					if(l===layer)return;//Значение по умолчанию смотрится отдельно 
					if(l.myenv[layer.env]==undefined)return;
					if(infra.isShow(l)){
						myenv=l.myenv[layer.env];
						if(myenv){
							layer.fight_msg='Окружению сказано показаться '+l;
						}else{
							layer.fight_msg='Окружению сказано скрыться '+l;
							if(infra.isParent(l,layer)){//Если слой скрываем слой окружения который у него в родителях числиться он после этого сам всё равно должен показаться
								layer.exec_onshow_savemybranch=true;
							}else{
								layer.exec_onshow_savemybranch=false;
							}
						}
						return true;
					}
				},true);


				if(!r&&layer.myenv){
					var myenv=layer.myenv[layer.env];
					if(myenv!==undefined){//Оо есть значение по умолчанию для самого себя
						if(myenv){
							layer.fight_msg='Окружение значение по умолчанию ДА';
						}else{//Если слой по умолчанию скрыт его детей не показываем
							layer.fight_msg='Окружение значение по умолчанию НЕТ';
						}
						r=false;
					}
				}
				layer.envval=myenv;
				if(r){
					if(myenv){
						res.value=true;
						return res.value;
					}else{
						res.value=false;
						return res.value;
					}
				}else{
					layer.fight_msg='Окружению ничего не сказано не показывается';
					res.value=false;
					return res.value;
				}
			}
		}

		if(!this.runparents(layer,function(l){
			var r=infra.isShow(l);
			if(!r){//Какой-то родитель таки не показывается.. теперь нужно узнать скрыт он своей веткой или чужой
				if(l.exec_onshow_savemybranch===true)return;//Если родитель был скрыт своим child это не влияет
				layer.fight_msg='Родитель не показывается какой-то и этот родитель не скрыт слоем со своей ветки.. '+l;
				return false;
			}
		})){
			layer.exec_onshow_savemybranch===false;
			res.value=false;
			return res.value;
		}

		if(layer.divparent){
			if(!this.runbackdiv(layer,function(l){
				if(layer.divparent!==l.div)return;
				var v=infra.isShow(l);
				if(v&&v!==1){//Ситуация когда слой l сейчас проверяется и условие его показа упёрлось в текущий слой layer... Так как показ текущего слоя скроет слой который должен быть для него и слой уже как бы и недолжен будет показаться.. в общем layer в этом случае не показывается
					return true;//Означает всё ок слой найден и он показывается
				}
			})){
				layer.exec_onshow_savemybranch=true;//Вообще-то меня скрыл кто-то до меня но пофиг.. ветку свою мы не скрываем из-за этого
				layer.fight_msg='Не показывается родительский див divparent';
				res.value=false;
				return res.value;
			}
		}
		res.value='Всё проверили показываем';
		return res.value;
	}
	infra.onchange=function(){
		this.divs={};
		this.checkdivs=[];
		this.fightdivs=[];
		this.run(this.wlayers,function fightDiv(layer){//Ребёнок заменяет родителя, Родитель скрыт
			if(!layer.exec_onchange)return false;//onchange главный ограничитель- за него мы не забегаем
			if(!layer.exec_onshow)return;
			layer.exec_onshow=infra.isShow(layer);
			if(layer.exec_onshow) this.divs[layer.div]=layer;
		});
	}
	//myenv:(object),//Перечислены env которые нужно показать и значения которые им нужно передать в envval
	//env:(string),//Имя окружения которое нужно укзать чтобы слой с этим свойством показался
	//envval:(mix),//Значение, которое было установленое в myenv. envval устанавливается автоматически, в ручную устанавливать его нельзя
//Обработка envs
infra.run.add('object','envs');//Теперь бегаем и по envs свойству
infra.listen(infra,'layer.onchange.before',function(){
	var layer=this;
	if(!layer.envs)return;
	infra.forx(layer.envs,function(l,env){
		l.env=env;
	});
});
//
////Свойство div, reparse, reparseon
(function(){
	infra.listen(infra,'layer.onchange.after',function(){//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя
		var layer=this;
		if(!layer.div&&layer.parent)layer.div=layer.parent.div;
	});
	infra.listen(infra,'layer.onshow.cond',function(){
		if(!this.div){
			if(infra.DEBUG)this.exec_onshow_msg='Нет дива';
			return null;//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
		}
	});
	infra.listen(infra,'layer.onparse.cond',function(){
		var layer=this;
		if(!layer.exec_onshow)return;
		if(!layer.divparent)return;
		if(infra.div[layer.divparent].exec_onparse)return true;
	});
	infra.listen(infra,'layer.onparse.cond',function(){
		var layer=this;
		if(layer.reparse)return 'reparse';//Значит слой должен распарсится и показаться 
		if(layer.reparseone)return 'reparseone';

		var parent=layer.parent;
		while(parent&&!parent.exec_onshow)parent=parent.parent;//Находим первого показываемого родителя
		if(parent&&parent.inwork&&parent.exec_onparse)return 'Парсится родитель';
	});
	infra.listen(infra,'layer.onparse.after',function(){
		delete this.reparseone;
	});
	infra.listen(infra,'layer.onchange.after',function(){
		this.inwork=true;//Нужна потому что мы бегаем по родителям и если првоерка не от корня дерева слоёв забежим в необрабатываемые слои.. которые будут хранить старые значения
	});
	infra.listen(infra,'layer.onshow.before',function(){
		this.inwork=false;
	});
})();