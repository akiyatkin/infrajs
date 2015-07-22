//deep:(number),//Для crumb определяет на каком уровне от текущего будет тру... пропускает родителей. Только когда что-то будет на нужном уровне от указанного crumb
infra.listen(infra,'layer.onchange.cond',function(){

	var layer=this;
	var deep=layer.deep||0;
	var crumb=layer.crumb;
	while(deep&&crumb.child){
		deep--;
		crumb=crumb.child;
	}
	if(!crumb.is||deep)return false;
});
