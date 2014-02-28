{//Обрабатывать шаблоны после onchange
	js:['*infrajs/props/proptpl.js','*infrajs/props/unick.js'],//Список испльзуемых дополнительных свойств
	config:{
		folder:'infra/data/Установить',
		sign:'',//Два знака $$ &$ или по умолчанию как установлено для всего
		title:location.host+' Информация',//Постоянная часть заголовка страницы
		reverse:1,//Выводить список страницы в обратном порядке, сначало самые последние
		firstpage:1,
		descr:1,//Описание сверху списка файлов показывать, типа когда зашли в папку
		firsttitle:'',
		list:1,//Показывать снизу статей навигацию по страницам
		listtop:0,
		page:1,//Показывать текст страницы
		breadcrumb:undefined,//показывать хлебные крошки в навигации по страницам по умолчанию равно list
		dir:1,//Показывать папки в списке или нет и уходить ли дальше по иерархии или нет
		forautoedit:''//Дополнительные файлы для редактирования в autoedit
	},
	tpl:['<div id="pages_top{unick}"></div><div id="pages_middle{unick}"></div><div id="pages_bottom{unick}"></div>'],
	onchange:function(){
		var conf=this.config;
		var state=this.state;
		var nextfolder='';
		if(conf.list&&conf.breadcrumb===undefined)conf.breadcrumb=1;
		conf.breadcrumb=Number(!!conf.breadcrumb);
		while(state.child){
			state=state.child;
			nextfolder+=state.state+'/';
			if(!conf.dir)break;
		}
		conf.nextfolder=nextfolder;
		conf.pagesrc=(conf.folder+conf.nextfolder).replace(/\/+$/,'');;
	},
	onshow:function(){
		var conf=this.config;
		document.title=conf.title+' '+conf.nextfolder.replace('/','');
	},
	datatpl:'*pages/pages.php?isexist=1&firstpage={config.firstpage}&list={config.list}&page={config.page}&folder={config.folder}&nextfolder={config.nextfolder}',
	istpl:'{config.nextfolder?1?config.firstpage}',
	layers:[
		{
			divtpl:'{parent.config.listtop?pages_bottom?pages_top}{parent.unick}',
			istpl:'{parent.config.pagesrc}',
			tpltpl:'*pages/get.php?{parent.config.pagesrc}',
			onchange:function(){
				var conf=this.parent.config;
				var page=conf.pagesrc;
				if(conf.forautoedit){
					page=[page];
					infra.fora(conf.forautoedit,function(p){
						page.push(p);
					});
				}
				this.autoedit={
					title:'Редактировать страницу',
					files:{
						paths:page
					}
				}
			}
		},{
			tpl:'*pages/pages.tpl',
			divtpl:'{parent.config.listtop?pages_top?pages_bottom}{parent.unick}',
			istpl:'{parent.config.list?1}{parent.config.breadcrumb?1}',
			datatpl:'*pages/pages.php?folder={parent.config.folder}&dir={parent.config.dir}&nextfolder={parent.config.nextfolder}&reverse={parent.config.reverse}',
			onparse:function(){
				if(!this.dataisnew)return;
				var conf=this.parent.config;
				var data=infra.load(this.data,'j');
				this.autoedit={
					title:'Страницы раздела '+data.name,
					files:{
						paths:conf.folder+data.folder
					}
				}
			}
		}
	]
}
