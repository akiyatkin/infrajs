
	//loaded loadedtpl
	infrajs.loaded=function(layer){
		/*
		 Проблема в том что данные уже загружены и хранятся в кэше
		 и изменение datatpl само по себе не вызовет обновление
		 */
		if(layer.loadedtpl)layer.loaded=infra.template.parse([layer.loadedtpl],layer);
		return layer.loaded;
	}
	infra.listen(infra,'layer.onparse.cond',function(){//Не всегда срабатывает, так как достаточно одного true
		if(this._loaded!==infrajs.loaded(this)){
			return 'Необходимо перезагрузить данные';
		}
	});
	infra.listen(infra,'layer.onparse',function(){
		if(this._loaded&&this._loaded!==infrajs.loaded(this)){
			infra.unload(this.data);
		}
		this._loaded=infrajs.loaded(this);//Выставляется после обработки шаблонов в которых в событиях onparse могла измениться data
	});
