infrajs.SEO=function(type,id){
	infra.require('*autoedit/autoedit.js');
	if(!id)id='';
	var self=arguments.callee;
	if(!self.popups)self.popups={};
	var save=type+'|'+id;//Сохраняются разные окна чтобы у каждого был свой autosave (редактирование файлов)
	if(!self.popups[save]){
		self.popups[save]={
			external:'*seo/seo.layer.js',
			config:{
				type:type,
				id:id
			}
		}
	}
	return popup.toggle(self.popups[save]);
}
/*infra.wait(infrajs,'oncheck',function(){
	infra.seq.set(infra.template.scope,['infrajs','seo','get'],function(){
		return infrajs.seo.get();
	});
});
infrajs.seo={
	data:{}, //Собираются значения в onchange и потом в oninsert вставляются
	get:function(){
		var seo={};
		infrajs.run(infrajs.getAllLayers(),function(layer){
			if(!layer.showed)return;	
			if(layer.seo&&!infra.conf.infrajs.seoforall){
				seo=layer.seo;
				return false;
			}
			infra.foro(layer.seo,function(val,key){
				if(!val)return;
				seo[key]=val;
			});
		});
		return seo;
	},
	//checktpl:function(layer){
	//	var name='seo';//stencil//
	//	var nametpl=name+'tpl';
	//	if(layer[nametpl]){
	//		if(!layer[name])layer[name]={};
	//		for(var i in layer[nametpl]){
	//			layer[name][i]=infra.template.parse([layer[nametpl][i]],layer);
	//		}
	//	}
	//},
	collect:function(layer){
		infra.foro(layer.seo,function(val,key){
			infrajs.seo.data[key]=val;
		});
	},
	use:function(layer){
		var store=infrajs.store();
		if(store.counter==1)return;
		if(!infra.foro(infrajs.seo.data,function(){ return true; }))return;
		var view=infra.view;
		var pre='';
		if(infra.conf&&infra.conf.infrajs){
			var pre=infra.conf.infrajs.seoname;
			if(pre)pre+=' ';
		}
		if(infrajs.seo.data['title']){
			view.setTitle(pre+infrajs.seo.data['title']);
		}
		//TODO: keywords, description
	}

};
*/