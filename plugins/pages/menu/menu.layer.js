{
	tpl:'*pages/menu/menu.tpl',
	css:'*pages/menu/menu.css',
	config:{
		folder:''
	},
	onchange:function(){
		this.refresh=true;
		var conf=this.config;
		var folder=conf.folder;
		var obj=this.state.obj||{};
		var i=true;
		var last=false;
		var str=[];
		while(i){
			var last=i;
			i=false;
			for(var i in obj){
				obj=obj[i];
				break;
			}
			if(typeof(obj)!=='object')break;
			if(i)str.push(i);
		}
		do{
			var dirs='*pages/list.php?obj=1&sort=1&d=1&onlyname=2&f=0&src='+folder+str.join('/');
			var files='*pages/list.php?onlyname=0&sort=1&e=tpl,rtf,xml,htm,mht&f=1&src='+folder+str.join('/');
			this.param.nstate=str.join('.');
			if(str.length==0)break;
			str.pop();
		}while(!infra.load(dirs,'j').length&&!infra.load(files,'j').length);
		
		var ar=this.param.nstate.split('.');
		var t=[];
		this.param.parents={};
		for(var i=0,l=ar.length;i<l;i++){
			t.push(ar[i]);
			this.param.parents[ar[i]]=t.join('.');
		}
		this.data={
			dirs:dirs,
			files:files
		}
	},
	onshow:function(){
		$(this.div).find('li').hover(function(){
			$(this).addClass('select');
		},function(){
			$(this).removeClass('select');
		});
	}
}
