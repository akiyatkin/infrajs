//deep:(number),//Для istate определяет на каком уровне от текущего будет тру... пропускает родителей. Только когда что-то будет на нужном уровне от указанного istate
infra.listen(infra,'layer.onchange.cond',function(){

	var layer=this;
	var deep=layer.deep||0;
	var state=layer.istate;
	while(deep&&state.child){
		deep--;
		state=state.child;
	}
	if(!state.obj||deep)return false;
});
