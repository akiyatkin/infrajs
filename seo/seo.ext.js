//Свойства seo, seotpl
/*infra.wait(infra,'AUTOEDIT',function(){
	AUTOEDIT.menu.push({
		name:'SEO оптимизация',
		click:function(){
			infra.require('*seo/seo.js');
			infrajs.SEO('seo');
		}
	})
});*/

/*infrajs.seo_checkseolinktpl=function(layer){
	if(!layer.seotpl)return;
	if(!layer.seo)layer.seo={};
	var props=['link','json','name','title'];
	for(var i=0,l=props.length; i<l;i++){
		if(layer.seotpl[props[i]])layer.seo[props[i]]=infra.template.parse([layer.seotpl[props[i]]],layer);
	}
}

/*infrajs.seo_SaveOpenedWin=function(){
	infra.code_remove('popup');
	if(!infrajs.SEO)return;
	for(var i in infrajs.SEO.popups){
		var layer=infrajs.SEO.popups[i];
		if(!layer.showed)continue;//Нашли показанное окно SEO
		infra.code_save('popup','infra.require("*seo/seo.js");infrajs.SEO("'+layer.config.type+'","'+layer.config.id+'");');
	}
}*/
/*infrajs.seo_init=function(){
	var store=infrajs.store();	
	store['seolayer']=false;
}

infrajs.seo_now=function(layer){
	if(!layer['seo'])return;
	var store=infrajs.store();
	store['seolayer']=layer;

}
infrajs.seo_apply=function(){
	var store=infrajs.store();
	var layer=store['seolayer'];
	if(!layer)return;
	//Но зачем нам запрос ещё один к серверу!
	var title=layer.seo.name
	if(layer.seo.title)title=layer.seo.title;
	if(title)document.title=title;
}*/