//Свойство help если есть будет найден элемент с классом help и у него будет клик на помощь, файл согласно title у элемента
infrajs.help={popup:{},width:false,img:false,
	show:function(name){
		infra.load('*popup/popup.js');
		var pl=infrajs.help.popup[name];
		if(!pl)pl={
			tpl:'*help/'+name+'.tpl',
			config:{
				title:'Помощь <b>'+name+'</b>',
				width:500
			}
		};
		infrajs.help.popup[name]=pl;
		popup.open(pl);
	},
	underline:true};//img - путь до картинки вопроса

infrajs.listen(infrajs,'layer.onshow',function(){
	var layer=this;
	if(!layer.help)return;
	var div=$('#'+layer.div); //Помощь
	var paths=[];
	div.find('.help[title]').click(function(){
		var name=this.title;//Без title Не работаем
		if(!name)return;
		infrajs.help.show(name);
	}).each(function(){
		if($(this).find('img').length||!$(this).html()){
			if(infrajs.help.img)$(this).html('<img src="'+infra.theme(infrajs.help.img)+'">');
		}else{
			
			if(infrajs.help.underline)$(this).css('border-bottom','1px dashed gray');
		}
		paths.push(this.title+'.tpl');
	});

	if(paths.length){
		if(!this.autoedit)this.autoedit={};
		if(!this.autoedit.help){
			this.autoedit.help=true;//Метка что список файлов уже добавлен
			if(!this.autoedit.title)this.autoedit.title='Блок с подсказками';
			if(!this.autoedit.files)this.autoedit.files=[];
			if(this.autoedit.files.constructor!=Array)this.autoedit.files=[this.autoedit.files];
			this.autoedit.files.push({
				title:'Текст подсказок',
				root:'*help/',
				paths:paths
			});
			this.autoedit.files.push({
				paths:'*help/'
			});
		}
	}
});
infra.style('.help { cursor:pointer; } .help img { vertical-align: middle;  }');
