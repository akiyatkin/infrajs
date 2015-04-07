window.AUTOEDIT=function(type,id){
	if(!id)id='';
	var self=arguments.callee;
	if(!self.popups)self.popups={};
	var save=type+'|'+id;//Сохраняются разные окна чтобы у каждого был свой autosave (редактирование файлов)
	if(!self.popups[save]){
		self.popups[save]={
			AUTOEDIT:AUTOEDIT,//из-за menu depricated
			external:'*autoedit/autoedit.layer.json',
			showanimate:true,
			config:{
				type:type,
				id:id
			}
		}
	}
	popup.toggle(self.popups[save]);
}



AUTOEDIT.jsonedit=function(ta,schema){
	infra.require('*autoedit/json.js');
	infra.require('*autoedit/jsonedit.js');
	infra.loadCSS('*autoedit/jsonwidget.css');
	jsonedit(ta,schema);
}
AUTOEDIT.menu=[
	{
		name:'Доступные блоки',
		click:function(){
			AUTOEDIT("allblocks");
		}
	},{
		name:'Информация о версии',
		click:function(){
			AUTOEDIT("version");
		}
	},{
		name:'Папка с данными',
		click:function(){
			AUTOEDIT("editfolder","*");
		}
	},{
		name:'Редактируемые файлы',
		click:function(){
			AUTOEDIT("takeshow")
		}
	}
]
AUTOEDIT.takefile=function(path,take){
	take=take|'';
	var src='*autoedit/autoedit.php?submit=1&type=takefile&id='+encodeURIComponent(path)+'&take='+take;
	infra.unload(src);
	var data=infra.loadJSON(src);
	/*if(data.result&&!data.noaction){
		if(!take){
			//popup.alert('<div style=\'width:300px\'><b>Метка о редактировании файла убрана!</b> Рекомендуется удалить скаченный ранее файл. В следующий раз, когда появится необходимость что-то изменить необходимо будет скачать последнюю версию файла с сайта.</div>');
		}else{
			//popup.alert('<div style=\'width:300px\'><b>Поставлена метка о редактировании файла!</b> Не забудьте после окончания работы эту метку убрать.</div>');
		}
	}*/
	if(!data.result){
		popup.alert('Ошибка на сервере<br>'+data.msg);
	}
	infrajs.global.set('autoedit');
	infrajs.check();
}
/*AUTOEDIT.clearAns=function(){
	for(var i in ADMIN.popups){
		if(!layer.onsubmit)continue;
		var layer=ADMIN.popups[i];
		delete layer.config.ans;
	}
}*/
infrajs.autoeditTpl=function(layer,autoedittpl){
	if(!layer.autoedit)layer.autoedit={};
	for(var i in autoedittpl){
		if(!autoedittpl.hasOwnProperty(i))continue;
		if(typeof(autoedittpl[i])=='string'){//fast
			layer.autoedit[i]=infra.template.parse([autoedittpl[i]],layer);
		}
	}
	if(autoedittpl['files']){
		var files=[];
		infra.fora(autoedittpl['files'],function(file){
			var f={};
			if(file['title'])f['title']=infra.template.parse([file['title']],layer);
			if(file['root'])f['root']=infra.template.parse([file['root']],layer);

			if(file['paths']){
				var paths=[];
				infra.fora(file['paths'],function(path){
					path=infra.template.parse([path],layer);
					if(!path)return;
					paths.push(path);
				});
				f['paths']=paths;
			}

			files.push(f);
		});
		layer.autoedit['files']=files;
	}
}
AUTOEDIT.setHandlers=function(){
	infrajs.run(infrajs.getAllLayers(),function(layer){
		
		if(layer.autoedittpl){
			infrajs.autoeditTpl(layer,layer.autoedittpl);
		}

		if(!layer.autoedit)return;
		if(!layer.div)return;
		if(!layer.showed)return;
		var attr='data-infrajs-admin-layer';
		var div=layer.autoedit.div||layer.div;
		var div=$('#'+div).not('['+attr+']');//Именно для этого слоя мы ещё обработчик не сделали
		if(!div.length)return;

		div.hover(function(){
			if(!AUTOEDIT.active)return;
			if(!layer.showed)return;//в диве может показаться новый слой со своей админкой и там будет ещё один hover

			$('.adminblock').removeClass('adminblock');
			$(this).addClass('adminblock');
			return false;
		},function(){
			if(!AUTOEDIT.active)return;
			if(!layer.showed)return;
			$('.adminblock').removeClass('adminblock');
			return false;
		}).attr(attr,layer.unick).click(function(e){
			if(!AUTOEDIT.active)return;//false блокирует переход по внешней ссылке
			if(!layer.showed)return false;
			if(!layer.autoedit)return false;
			AUTOEDIT.checkLayer(layer);
			return false;
		});
	});	
}

AUTOEDIT.checkLayer=function(layer){
	if(!layer||!layer.autoedit)return AUTOEDIT('404');

	if(layer.autoedit.json){
		var file=layer.autoedit.json;
		var schema=layer.autoedit.schema||'any';
		return AUTOEDIT('jsoneditor',file+'|'+schema);
	}
	if(layer.autoedit.text){
		var file=layer.autoedit.text;
		return AUTOEDIT('corfile',file);
	}
	if(layer.autoedit.html){
		var file=layer.autoedit.html;
		return AUTOEDIT('rte',file);
	}
	if(layer.autoedit.fast){
		var fast=layer.autoedit.fast;
		var r=fast.split('/');
		var name=r[r.length-1];
		if(!name){//Папка
			return AUTOEDIT('editfolder',fast);
		}else{
			var r=name.split('.');
			if(r.length>1){
				var ext=r[r.length-1];
				if(infra.forr(['tpl','html'],function(e){
					if(e==ext)return true;
				}))return AUTOEDIT('rte',fast);
				if(infra.forr(['txt','json','js'],function(e){
					if(e==ext)return true;
				}))return AUTOEDIT('corfile',fast);
				return AUTOEDIT('editfile',fast);
			}else{
				return AUTOEDIT('editfile',fast);
			}
		}
	}
}
AUTOEDIT.refreshAll=function(){
	infra.store();
	var store=infra.store();
	store['loadJSON']={};
	store['loadTEXT']={};
	infrajs.run(infrajs.getAllLayers(),function(layer){
		layer.showed=false;
	});
	var store=infra.template.store();
	store.cache={};
}
infra.fire(infra,'AUTOEDIT');