<?php
	global $infra;
	function infrajs_isCheck(&$layer){
		if(!isset($layer['is'])||is_null($layer['is'])){
			$is=true;
		}else{
			$is=$layer['is'];
		}
		if($is=='0')$is=false;//В шаблоне false не удаётся вернуть
		return $is;
	}
/*
(function(){
//Свойство is 
	var getIs=function(layer){//Для любова слоя, подслои не обрабатываются
		var is=(layer.is===undefined)?true:layer.is;
		if(is=='0')is=false;//В шаблоне false не удаётся вернуть
		return is;
	}
	infra.listen(infra,'layer.onchange.cond',function(){
		var layer=this;
		if(!layer.parent)return;
		delete this.exec_onchange_msg;
		if(getIs(layer.parent)===false){
			this.exec_onchange_msg='is неудовлетворительное - строгое false у родителя';
			return false;
		}
	});
	infra.listen(infra,'layer.onshow.cond',function(){
		delete this.exec_onshow_msg;
		if(!getIs(this)){
			this.exec_onshow_msg='is неудовлетворительное';
			return false;
		}
	});
})();
 */
?>
