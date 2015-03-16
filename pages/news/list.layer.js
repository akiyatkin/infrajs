{
	data:true,
	onchange:function(){
		var conf=this.config;
		var state=this.state;
		if(typeof(this.state.obj)=='object'){
			//по идеи дальше должен захватить слой pages, но если этого не произойдёт покажется первая страница списка...
			//is устанавливать в false нельзя потому что это закроет и всех детей
			var num=1;
		}else if(typeof(this.state.obj)!=='object'){
			var num=this.state.obj;
		}else{
			var num=1;
		}
		this.is=num;
		num--;
		var list=infra.load('*pages/list.php?onlyname=2&reverse=1&lim='+(num*conf.count)+','+conf.count+'&e=mht&src='+conf.folder,'j');
		var count=0;
		infra.require('vendor/akiyatkin/phpdate/phpdate.js');
		for(var i=0,l=list.length;i<l;i++){
			count++;
			var src='*pages/mht/mht.php?preview&src='+conf.folder+list[i];
			var data=infra.load(src,'j');
			data.name=list[i];
			data.image=encodeURIComponent(data.img);
			data.strdate=phpdate('j F Y',data.date);
			list[i]=data;
		}
		var next=num+2;
		var prev=num;
		if(count<conf.count)next=false;
		else next='='+next;
		if(prev==1)prev='/';
		else if(prev<=0)prev=false;
		else prev='='+prev;
		this.config.data={list:list,next:next,prev:prev};
		
		if(this.autoedit)return;
		var conf=this.config;
		this.autoedit={
			title:conf.heading,
			descr:'Для редактирования нужно изменить файлы в паке. <br>Цифры в начале файла это дата ГГММДД.',
			files:{
				paths:conf.folder
			}
		}
	},
	tpl:'*pages/news/list.tpl',
	config:{
		count:10,
		folder:'infra/data/Установить/',
		date:true,
		heading:'Заголовок'
	}
}
