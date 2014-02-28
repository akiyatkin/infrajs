{
	onchange:function(){//Если используется onchangeone не работает обновление сайта
		var config=this.config;
		var conf=this.config;
		var src='*pages/list.php?onlyname=0&reverse=0&lim=0,'+config.count+'&e=mht&src='+config.folder;
		this.data=src;//Надо чтобы обновлялось при обновлении сайта без перезагрузки
		var list=infra.load(src,'j');
		this.autoedit={
			title:conf.title||conf.heading,
			descr:'Для редактирования нужно изменить файлы в папке. <br>Цифры в начале файла это дата ГГММДД.',
			files:{
				paths:conf.folder
			}
		}
		for(var i in list){
			var src='*pages/mht/mht.php?preview&src='+config.folder+list[i].dir+list[i].name;
			var data=infra.load(src,'j');
			data.name=list[i].name;
			data.dir=list[i].dir;
			list[i]=data;
		}
		infra.load('infra/lib/phpdate/phpdate.js');
		for(var i=0,l=list.length;i<l;i++){
			if(!list[i].title)list[i].title=list[i].name;
			if(list[i].date)list[i].sdate=phpdate('d F Y',list[i].date);
			list[i].image=encodeURIComponent(list[i].img);
		}
		this.config.list=list;
	},
	data:true,
	config:{
		//list
		count:5,
		subfolders:0,
		folder:'infra/data/Инфо/Новости/',
		//imager
		width:200,
		height:90,
		crop:1,
		goall:'все новости',
		//tpl
		more:true,
		date:{top:true,bottom:false},
		image_align:false
	},
	onshow:function(){
		$('#'+this.div).find('.some_news').each(function(){
			$(this).find('.more').appendTo($(this).find('.description>*:last'));
		});
	},
	tpl:'*pages/news/news.tpl',
	css:'*pages/news/news.css'
}
