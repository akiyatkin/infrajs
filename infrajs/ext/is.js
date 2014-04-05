infrajs.isCheck=function(layer){
	var is=(layer.is===undefined)?true:layer.is;
	if(is=='0')is=false;//В шаблоне false не удаётся вернуть
	return is;
}
infrajs.istplparse=function(layer){
	var prop='is';
	var proptpl=prop+'tpl';
	if(!layer[proptpl])return;
	var p=layer[proptpl];
	p=infra.template.parse([p],layer);
	layer[prop]=p;
}
/*(function(){
//Свойство is 

	infrajs.isAdd('check',function(layer){//либо нет isCheck либо мы сюда зашли
		var prop='is';
		var proptpl=prop+'tpl';
		if(!layer[proptpl])return;
		var p=layer[proptpl];
		p=infra.template.parse([p],layer);
		layer[prop]=p;
	});
	
	var getIs=function(layer){//Для любова слоя, подслои не обрабатываются
		var is=(layer.is===undefined)?true:layer.is;
		if(is=='0')is=false;//В шаблоне false не удаётся вернуть
		return is;
	}
	
	infrajs.isAdd('check',function(layer){
		if(!layer.parent)return;
		delete layer.ses.exec_onchange_msg;
		if(getIs(layer.parent)===false){
			layer.ses.exec_onchange_msg='is неудовлетворительное - строгое false у родителя';
			return false;
		}
	});
	infrajs.isAdd('show',function(layer){
		if(!getIs(layer)){
			infrajs.isSaveBranch(layer,false);
			return false;
		}
	});
})();*/
