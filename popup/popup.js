infra.wait(infrajs,'oninit',function(){
	infrajs.parsedAdd('popupis');
});
infrajs.popup_memorize=function(code){
	infrajs.code_save('popup',code);
	infra.when(popup.layer,'onhide',function(){
		infrajs.code_remove('popup',code);
	});
}
Popup=function(frame){
	this.layeronshow=function(layer){
		if(!layer.popup)return;
		var popup=layer.config.popup;
		var body=popup.body;
		$('#'+layer.div).find('.drag').drag(function(event,dd){
			var top=dd.offsetY;
			if(top<0)top=0;
			var left=dd.offsetX;
			if(left<0)left=0;
			$(body).css({
				top: top,
				left: left-20//Скачки были... с 20 скачков нет. Вероятно это зависит от шаблона
			});
		}).css('cursor','move');
		popup.center();
		setTimeout(function(){
			popup.center();
		},1);
	};
	this.frame=frame||{
		external:'*popup/popup.layer.js',
		popupis:false,
		popup:true,
		div:'for_all_popup_div',
		config:{
			popup:this
		}
	}

	this.init=function(){
		var body=document.createElement('div');
		body.id='for_all_popup_div';
		this.body=body;
		body.style.zIndex='5000';
		body.style.position='absolute';
		/*body.style.backgroundColor='red';*/
		document.body.appendChild(body);
		this.center();
		
		//infra.load('infra/lib/jquery/jquery.event.drag-1.5.min.js');
		infra.require('*popup/jquery.event.drag.js');
		var that=this;
		$(document).bind('keydown',function(event){
			if (event.keyCode == 27){
				that.close();
			}
		});
		
	}
	this.showed_popups=[];//Стек показанных окон
	this.indexOf=function(val,ar){
		var r=infra.forr(ar,function(v,i){
			if(val==v)return i;
		});
		if(typeof(r)=='undefined')return -1;
		return r;
	}
	this.all_layers=[];
	this.open=function(layer){
		//if(!layer.config)layer.config={};
		//if(title)layer.config.title=title;
		//if(!this.frame.unick)this.frame.unick=js.getUnick();
		//layer.div='popup_body'+this.frame.unick;//Обязательно такой id
		layer.div='popup_body';
		
		if(this.init){
			this.init();
			delete this.init;
		}

		var i=this.indexOf(layer,this.all_layers);
		if(i==-1){
			this.all_layers.push(layer);
			infrajs.checkAdd(layer);
		}


		var i=this.indexOf(layer,this.showed_popups);
		if(i!=-1){//Окно найдено в списке показанных
			this.showed_popups.splice(i,1);//Удалили из середины.. следом оно всё равно будет добавлено сверху
		}

		
			
		
		if(this.showed_popups.length){
			//var nowpop=this.showed_popups[this.showed_popups.length-1];
			var l=this.layer;
			this.hide(true);//но при этом окно осталось в списке показанных
			this.showed_popups.push(layer);
			this.show(layer,l);
		}else{
			this.showed_popups.push(layer);
			this.show(layer);
		}
	},
	this.show=function(layer,layer2){
		this.frame.popupis=true;
		this.layer=layer;
		this.layer.popupis=true;
		if(layer2){
			infrajs.check([this.frame,layer2,this.layer]);
		}else{
			infrajs.check([this.frame,this.layer]);
		}
	},
	this.hide=function(r){
		if(!this.layer)return;
		this.frame.popupis=false;
		this.layer.popupis=false;
		if(!r){
			infrajs.check([this.frame,this.layer]);
		}else{
			//infrajs.check([this.layer]);
		}
		this.layer=false;
	},
	this.get=function(){
		return this.layer;
	},
	this.reparse=function(){
		var layer=this.get();
		if(layer){
			layer.reparseone=true;
			infrajs.check(layer);
		}
	},
	this.toggle=function(layer){//Если окно 
		var i=this.indexOf(layer,this.showed_popups);	
		if(i==-1||i!=this.showed_popups.length-1)return this.open(layer);
		else return this.close(layer); 
	},
	this.setClose=function(){//depricated для совместимости со старой версией
		return this.close();
	},
	this.close=function(layer){
		if(!layer){
			var popup=this.showed_popups.pop();//Взяли верхнее из стэка и закрыли
		}else if(layer===true){
			while(this.showed_popups.length){
				this.close();
			}
			return;
		}else{
			var i=this.indexOf(layer,this.showed_popups);	
			if(i==-1)return;
			this.showed_popups.splice(i,1);
			if(this.showed_popups.length-2==i)return;
			var popup=layer;
		}
		if(!popup)return;

		$('#'+popup.div).find(':focus').blur();//из-за drag focus не снимается при закрытия и onchange не срабатывает. корректируем это поведение
		
		if(this.showed_popups.length){
			var l=this.layer;
			this.hide(true);
			var prevpopup=this.showed_popups[this.showed_popups.length-1];
			this.show(prevpopup,l);
		}else{
			this.hide();
		}
	},
	this.center=function(){
		if(!this.layer)return;
		var div=this.body;
		var pw=$(div).width();
		var dw=$(document).width();
		//var m=dw/2-pw/2;
		var m=200;
		m=Math.round(m);
		$(div).css('left',m+'px');
		
		
		var iebody=(document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body;
		var dsoctop=document.all? iebody.scrollTop : pageYOffset;
		
		var ph=$(div).height();
		
		var h1=$(window).height();
		var h2=$(document).height();
		var h=(h1<h2)?h1:h2;
		
		//var top=(h/2)+dsoctop-ph/2;
		var top=dsoctop+100;
		if(top<dsoctop)top=dsoctop;
		$(div).css('top',top+'px');
	},
	this.alert_layer={};
	this.alert_popup=false;
	this.alert=function(msg,data){
		this.alert_layer.tpl=[msg];
		this.alert_layer.data=data;
		this.alert_popup=this.alert_popup||this.alert_layer;
		this.alert_popup=this.open(this.alert_popup);
	}
	this.confirm_layer={
		tpl:['{config.msg}<div style="margin-top:10px"><input class="popup_callback" value="OK" onclick="popup.close()" type="button"><input onclick="popup.close()" value="Отмена" type="button"></div><script>var layer=infrajs.getUnickLayer("{unick}");	$("#"+layer.div).find(".popup_callback").click(layer.confirm_callback);</script>'],
		config:{
			'msg':'Подтверждаете?'
		}
	};
	this.confirm_popup=false;
	this.confirm=function(msg,callback){
		popup.confirm_layer.config['msg']=msg;
		popup.confirm_layer.confirm_callback=callback;
		popup.confirm_popup=popup.confirm_popup||popup.confirm_layer;
		popup.confirm_popup=popup.open(popup.confirm_popup);
	}
}
popup=new Popup();