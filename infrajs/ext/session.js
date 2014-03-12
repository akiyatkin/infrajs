infra.wait(infrajs,'oninit',function(){
	//session и template
	infra.seq.set(infra.template.scope,infra.seq.right('infra.session.get'),function(name,def){
		return infra.session.get(name,def);
	});
	infra.seq.set(infra.template.scope,infra.seq.right('infra.session.getLink'),function(){
		return infra.session.getLink();
	});
	infra.seq.set(infra.template.scope,infra.seq.right('infra.session.getTime'),function(){
		return infra.session.getTime();
	});
	infra.seq.set(infra.template.scope,infra.seq.right('infra.session.getId'),function(){
		return infra.session.getId();
	});
	infra.seq.set(infra.template.scope,infra.seq.right('infra.State.get'),function(){
		return infra.State.get();
	});
	infra.seq.set(infra.template.scope,infra.seq.right('infra.State.getState'),function(){
		return infra.State.getState();
	});
});