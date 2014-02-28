/*
Copyright 2010 http://itlife-studio.ru

var slide=new Slide('path/to/images/');
slide.get(callback);
slide.pimg - предыдущая картинка
slide.img - текущая картинка
slide.process - процесс загрузки идёт или нет

*/
Slide=function(path,width,height){
	this.path=path;
	this.path=encodeURIComponent(this.path);
	this.width=width||200;
	this.height=height||100;
	
	this.mark=0;
	this.process=false;//Метка идёт ли загрузка
	this.img=document.createElement('span');
	this.pimg=document.createElement('span');
	this.def=false;
	this.rnd=true;
	this.now=0;
	this.get=function(callback,some){
		this.some=some;
		if(!this.images){
			this.images='infra/plugins/pages/list.php?onlyname=1&'+(this.rnd?'random=1&':'')+'e=jpg,png,gif&src='+this.path;
		}

		infra.load(this.images,'aj',function(er,images){
			if(!images.length)return;
			var img=false;
			if(some){
				var reg=new RegExp(some);
				infra.forr(images,function(ii){
					if(reg.test(ii)){
						img=ii;
					}
				});
			}
			if(!img&&this.def){
				some=this.def;
				var reg=new RegExp(some);
				infra.forr(images,function(ii){
					if(reg.test(ii)){
						img=ii;
					}
				});
			}
			
			if(!img){
				if(this.rnd){
					this.now=Math.floor(Math.random()*images.length);
					var img=images[this.now];
					if(images.length>1&&this.pimg==img){
						this.now=Math.floor(Math.random()*images.length);
						var img=images[this.now];
					}
					if(images.length>1&&this.pimg==img){
						this.now=Math.floor(Math.random()*images.length);
						var img=images[this.now];
					}
				}else{
					this.now++;
					if(this.now>=images.length)this.now=0;
					var img=images[this.now];
				}
			}
			this.process=true;
			var that=this;
			this.pimg=this.img;
			var path='*imager/imager.php?mark='+this.mark+'&h='+this.height+'&w='+this.width+'&crop=1&src='+this.path+img;
			infra.load(path,'i',function(err, img){
				if(!img)return that.process=false;
				that.img=img;
				that.process=false;
				callback();
			});
		}.bind(this));
	}
}
