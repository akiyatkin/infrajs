window.AUTOEDIT=function(type,id){
	if(!id)id='';
	var self=arguments.callee;
	if(!self.popups)self.popups={};
	var save=type+'|'+id;
	if(!self.popups[save]){
		self.popups[save]={
			external:'*autoedit/autoedit.layer.js',
			config:{
				type:type,
				id:id,
				esc:ADMIN.tplesc
			}
		}
	}
	return popup.toggle(self.popups[save]);
}

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
	
	/*Сгенерировать описание слоя*/
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
			title='Администрация';//!

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

		}else if(type=='allblocks'){//!
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
			autosave:true,
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
ADMIN.clear=function(){
	$('.adminblock').removeClass('adminblock');
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
	var data=infra.load('*autoedit/admin.hand.php?type=takefile&id='+encodeURIComponent(path)+'&take='+take,'xj');
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

infra.listen(infrajs,'onshow',function(){
	infrajs.run(infrajs.getAllLayers(),function(layer){
		if(!layer.autoedit)return;
		if(!layer.div)return;
		if(!layer.showed)return;
		var attr='infrajs_admin'+layer.unick;
		var div=layer.autoedit.div||layer.div;
		var div=$('#'+div+'['+attr+'!=1]');//Именно для этого слоя мы ещё обработчик не сделали
		if(!div.length)return;

		div.hover(function(){
			if(!ADMIN.active)return;
			if(!layer.showed)return;
			var layers=[];
			if(!layers.length&&!layer.autoedit)return;

			ADMIN.clear();
			$(this).addClass('adminblock');
			return false;
		},function(){
			if(!ADMIN.active)return;
			if(!layer.showed)return;
			var layers=[];
			if(!layers.length&&!layer.autoedit)return;

			ADMIN.clear();
			return false;
		}).attr(attr,'1').click(function(e){
			if(!ADMIN.active)return;//false блокирует переход по внешней ссылке
			if(!layer.showed)return false;
			var layers=[];
			if(layer.autoedit)layers.push(layer);
			if(!layers.length)return false;
			ADMIN.autoedit(layers);
			return false;
		});
	});
});
