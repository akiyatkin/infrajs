
infrajs.autoeditInit=function(){
	if(!infra.config().admin.popup)return;
	infrajs.externalAdd('autoedittpl',function(now,ext,layer,external,i){
		if(layer[i.replace(/tpl$/,'')])return;
		if(layer[i])return;
		if(!now)now=ext;
		return now;
	});
	
	$(document).bind('keydown',function(event){
		if (event.keyCode == 113){
			//infra.loader.show();
			infra.require('*autoedit/autoedit.js');
			AUTOEDIT('admin');
		}
	});
}
infrajs.autoeditLink=function(){//infrajs onshow
	if(!infra.config().admin.popup)return;
	$('.showAdmin[showAdmin!=true]').attr('nohref','1').attr('showAdmin','true').click(function(){
		infra.loader.show();
		infra.require('*autoedit/autoedit.js');
		AUTOEDIT('admin');
		return false;
	});
}
infrajs.autoedit_SaveOpenedWin=function(){
	if(!window.sessionStorage)return;
	if(!window.AUTOEDIT)return;	
	for(var i in window.AUTOEDIT.popups){
		var layer=window.AUTOEDIT.popups[i];
		if(!layer.showed)continue;
		infrajs.popup_memorize('infra.require("*autoedit/autoedit.js");AUTOEDIT("'+layer.config.type+'","'+layer.config.id+'");');
	}
}



