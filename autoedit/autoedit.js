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
		var attr='infrajs_admin'+layer.unick;
		var div=layer.autoedit.div||layer.div;
		var div=$('#'+div+'['+attr+'!=1]');//Именно для этого слоя мы ещё обработчик не сделали
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
		}).attr(attr,'1').click(function(e){
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


/*

AUTOEDIT.getFolder=function(file){
		var s=file.split('/');
		var name=s.pop();
		if(s.length==0){
			var folder='*';
		}else{
			var folder=s.join('/');
			if(folder!='*')folder+='/';
		}
		return folder;
}





window.ADMIN=function(type,id,param){
	if(type=='corfile')return AUTOEDIT(type,id);
	
	
	if(!id)id='';
	var self=arguments.callee;
	if(!self.popups)self.popups={};

	var save=type+'|'+id;

	if(type=='allblocks'){
		if(!param)param=infrajs.getAllLayers();
	}

	if(!self.popups[save]){
		var title='Тип окна не определен '+type;
		var config={
			now:{},
			id:id,
			esc:ADMIN.tplesc,
			type:type
		};
		config.now[type]=true;
		var data='*autoedit/admin.php?id='+encodeURIComponent(id)+'&type='+encodeURIComponent(type);
		var onchange=function(){};
		var onparse=function(){};
		//var reparse=false;
		var success=false;

		
		
		if (type=='admin'){
			infrajs.check();
			title='Администрация';
		}else if(type=='addfile'){
			title='Загрузка нового файла';
		}else if(type=='settings'){
			title='Настройки';
		}else if(type=='corfile'){
			title='Редактирование файла';
		}else if(type=='version'){
			title='Версии';
		}else if(type=='editfolder'){
			title='Управление папкой';
		}else if(type=='editfile'){
			title='Управление файлом';
		}else if(type=='takeshow'){
			title='Список редактируемых файлов';
		}else if(type=='takeinfo'){
			title='Кто редактирует файл?';
		}else if(type=='copyfile'){
			title='Создание копии файла';
		}else if(type=='createcache'){
			title='Самостоятельное создание кэша';
		}else if(type=='renamefile'){
			title='Переименование файла';
			success=function(layer){
				var ans=layer.config.ans;
			}
		}else if(type=='deletefile'){
			title='Удаление файла';

		}else if(type=='allblocks'){
			//reparse=true;//Перепарсивается при переходах по страницам с открытым окном
			title='Список доступных блоков';
			data=true;
			onparse=function(){
				var list=[];
				var layers=this.config.param;
				var unicktitle={};
				infrajs.run(layers,function(layer){
					if(!layer.autoedit)return;
					if(!layer.showed)return;
					var title=layer.autoedit.title||layer.autoedit.text||layer.autoedit.fast||layer.autoedit.html;
					if(unicktitle[title])return;
					unicktitle[title]=true;
					var num=list.length;
					list[num]={title:title,layer:layer,num:num};
				});
				this.config.list=list;
			}
		}



		config.title=title;
		self.popups[save]={
			tpl:'*autoedit/admin.tpl',
			global:['autoedit'],
			onchange:onchange,
			data:data,
			//reparse:reparse,
			onchange:function(){
				setTimeout(function(){
					popup.center();
				},1);
			},
			onhide:function(){
				if(type=='admin'){
					ADMIN.active=false;
					ADMIN.clear();
				}
			},
			autosavename:save,
			config:config,
			autofocus:true,
			onparse:onparse,
			onshow:function(){
				if(type=='admin'){
					var data=infrajs.getData(self.popups[save]);
					if(data.admin){
						ADMIN.active=true;
					}
				}

				var div=$('#'+this.div);
				var form=div.find('form');
				var layer=this;
				form.submit(function(e){
					e.preventDefault(); // <-- important
					var btn=form.find('[type=submit]');
					btn.attr('disabled',true);
					$(this).ajaxSubmit({
						url:'infra/plugins/autoedit/admin.hand.php',
						type:'POST',
						dataType:'json',
						complete:function(xhr){
							var ans=false,text=false,msg='';
							if(xhr){
								text=xhr.responseText;
								try{
									ans=eval('('+text+')');
								}catch(e){
									msg='Ошибка на сервере';
									if(infra.debug){
										msg+='<hr>'+e+'<hr>'+text;
									}
								}
							}else{
								msg='Ошибка связи';
							}
							if(!ans)ans={result:0,msg:msg,text:text};
							layer.config.ans=ans;
							if(ans.result){
								if(!ans.autosave)infrajs.autosave.clear(layer);//Обнулили сохранённые введённые значения пользователя
								if(!ans.noclose)popup.close();
								if(success)success(layer);
								if(ans.refresh_only_admin){
									if(infrajs.global) infrajs.global.set(layer.global);
								}else{
									ADMIN.refreshAll();
								}
							}else{
								if(!ans.msg)ans.msg='Что-то не получилось';
								ADMIN.refreshAll();
							}
						}
					});
					return false;
				});
			}
		}
	}
	self.popups[save].config.param=param;
	return popup.toggle(self.popups[save]);
}
ADMIN.tplesc='Окном можно закрыть клавишей ESC <img class="refreshAll" style="cursor:pointer; position:relative; margin-bottom:-3px;" src="infra/plugins/autoedit/images/refresh.png" title="Обновить всё" alt="Обновить" onclick="ADMIN.refreshAll()"> <span style="border-bottom:dashed 1px gray; cursor:pointer;" onclick="popup.close(true)">закрыть все окна</span>';
ADMIN.layer={
	tpl:'*autoedit/admin.tpl',
	reparse:true,
	global:'autoedit',
	config:{
		id:'',
		type:'autoedit',
		now:{autoedit:true},
		esc:ADMIN.tplesc
	},
	onparse:function(){

		var conf=this.config;
		var list=[];

		if(conf.layer.autoedit.files){
			infra.fora(conf.layer.autoedit.files,function(obj){
				infra.fora(obj.paths,function(path){
					var root=obj.root||'';
					list.push(root+path);
				});
			});
		}
		if(list.length)list=encodeURIComponent('["'+list.join('","')+'"]');
		else list='';

		//this.data='*autoedit/admin.php?type='+conf.type+'&id='+conf.id+'&list='+list; Не работало в ie из-за этого
		var layer=ADMIN.layer.config.layer;
		if(!layer.autoedit.files||layer.autoedit._takefromlayer){
			layer.autoedit._takefromlayer=true;
			layer.autoedit.files={paths:layer.data||layer.tpl}
		}
	},
	data:true
}
ADMIN.refreshAll=function(){
	infra.plugin.cache('sense','clear');
	infra.plugin.cache('load','clear');

	//infrajs.autosave.clear();
	infrajs.run(infrajs.getAllLayers(),function(layer){
		layer.reparseone=true;
	});
	infra.template.tpls=[];
	infrajs.check();
}

ADMIN.autoedit=function(layer){
	if(layer.length>1){
		ADMIN('allblocks','',layer);
	}else{
		layer=layer[0];
		if(!layer.autoedit)return;
		ADMIN.layer.config.layer=layer;
		window.ADMIN.selected_layer=layer;//Для просмотра слоя в firebug

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
				return ADMIN('editfolder',fast);
			}else{
				var r=name.split('.');
				var ext=r[r.length-1];
				if(infra.forr(['tpl','html'],function(e){
					if(e==ext)return true;
				}))return AUTOEDIT('rte',fast);

				if(infra.forr(['txt','json','js'],function(e){
					if(e==ext)return true;
				}))return ADMIN('corfile',fast);
			}
			return ADMIN('editfile',fast);
		}

		if(!layer.autoedit.title){
			var title=layer.data||layer.tpl;
		}else{
			var title=layer.autoedit.title||'Без заголовка';
		}
		popup.open(ADMIN.layer,'Настройки блока <b>'+title+'</b>');

		var layer=ADMIN.layer;
		var conf=layer.config;
		if(!conf.layer||!conf.layer.autoedit){
			popup.close();
		}
		conf.files=[];
		infra.fora(conf.layer.autoedit.files,function(o){
			var files={
				title:o.title,
				paths:[]
			};

			infra.fora(o.paths,function(p){
				var root=o.root||'';
				var f={
					root:root,
					path:p
				};	
				if(/\/$/.test(p)){
					f.folder=true;
					f.ext='dir';
				}else{
					f.file=true;
					var match=p.match(/\.([a-zA-Z]+)$/);
					if(match){
						f.ext=match[1]||'';
					}else{
						f.ext='tpl';
					}
				}
				files.paths.push(f);
			});
			conf.files.push(files);
		});
	}
}
ADMIN.clearAns=function(){
	for(var i in ADMIN.popups){
		var layer=ADMIN.popups[i];
		delete layer.config.ans;
	}
}
ADMIN.takefile=function(path,take){
	take=take|'';
	var src='*autoedit/admin.hand.php?type=takefile&id='+encodeURIComponent(path)+'&take='+take;
	infra.unload(src);
	var data=infra.loadJSON(src);
	if(data.result&&!data.noaction){
		if(!take){
			//popup.alert('<div style=\'width:300px\'><b>Метка о редактировании файла убрана!</b> Рекомендуется удалить скаченный ранее файл. В следующий раз, когда появится необходимость что-то изменить необходимо будет скачать последнюю версию файла с сайта.</div>');
		}else{
			//popup.alert('<div style=\'width:300px\'><b>Поставлена метка о редактировании файла!</b> Не забудьте после окончания работы эту метку убрать.</div>');
		}
	}else if(!data.result){
		popup.alert('Ошибка на сервере<br>'+data.msg);
	}
	ADMIN.clearAns();
	if(infrajs.global)infrajs.global.set('autoedit');
}


*/