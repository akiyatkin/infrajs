popup={};
popup.stack=[];//все окна которые находятся в обработке. 
popup.heap=[];//все когда либо показанные окна
popup.st=false;//активное окно
popup.counter=0;

popup.stackAdd=function(obj){
	var st=false;
	for(var i=0,l=popup.stack.length;i<l;i++){
		if(popup.stack[i].obj===obj){
			st=popup.stack.splice(i,1)[0];
			break;
		}
	}
	if(!st){
		for(var i=0,l=popup.heap.length;i<l;i++){
			if(popup.heap[i].obj===obj){
				st=popup.heap.splice(i,1)[0];
				break;
			}
		}
	}
	if(!st)st={counter:++this.counter,obj:obj};
	popup.stack.push(st);
	return st;
}
popup.stackGet=function(obj){
	var i,st,l;
	for(i=0,l=popup.stack.length;i<l;i++){
		st=popup.stack[i];
		if(st.obj===obj)return st;
	}
	for(i=0,l=popup.heap.length;i<l;i++){
		st=popup.heap[i];
		if(st.obj===obj)return st;
	}
}
popup.stackClear=function(){
	var st;
	while(st=popup.stack.pop()){
		popup.heap.push(st);
	}
},
popup.stackLast=function(){
	var st=popup.stack[popup.stack.length-1];
	return st;
}
popup.stackDel=function(obj){
	var st=false;
	for(var i=0,l=popup.stack.length;i<l;i++){
		if(popup.stack[i].obj===obj){
			st=popup.stack.splice(i,1)[0];
			popup.heap.push(st);
			break;
		}
	}
	return st;
}


popup.activate=function(st){
	if(popup.st==st)return;
	var check=[];
	if(popup.st){
		popup.justhide(popup.st);
		check.push(popup.st.layer);
	}
	popup.justshow(st);
	check.push(st.layer)
	popup.st=st;
	infrajs.checkAdd(st.layer);
	infrajs.check(check);
}
popup.show=function(obj){
	if(!obj)return;
	var st=popup.stackAdd(obj);
	st.layer=obj;
	popup.activate(st);
}
popup.text=function(obj){
	if(!obj)return;
	var st=this.stackAdd(obj);
	if(!st.layer)st.layer={tpl:[obj]}
	popup.activate(st);
}
popup.open=function(obj){//depricated??? Слой показывается с отступами от краёв, с кнопокй закрыть.
	if(!obj)return;
	var st=this.getStLayer(obj,obj,'*popup/open.tpl');
	popup.activate(st);
}
popup.alert=function(obj){
	if(!obj)return;
	var st=this.getStLayer(obj,{tpl:[obj]},'*popup/alert.tpl');
	popup.activate(st);
}
popup.success=function(obj){
	if(!obj)return;
	var st=this.getStLayer(obj,{tpl:[obj]},'*popup/success.tpl');
	popup.activate(st);
}
popup.progress=function(val){
	if(!popup.progressobj)popup.progressobj={};
	var st=this.stackAdd(popup.progressobj);
	if(!st.layer){
		st.strict=true;
		st.layer={
			tpl:'*popup/progress.tpl',
			tplroot:'root'
		}
		infra.listen(st.layer,'onshow',function(layer){
			var bar=$('#'+layer.div).find('.progress-bar');
			var set=function(){
				if(!layer.showed)return;
				var val=popup.progressobj.val;
				if(val<99)popup.progressobj.val++;
				if(val>=100){
					clearInterval(popup.progressobj.timer);
					popup.hide();
				}
				bar.css('width', val+'%').attr('aria-valuenow', val);
			}
			clearInterval(popup.progressobj.timer);
			popup.progressobj.timer=setInterval(function(){
				set();
			},300);
			set();
		});
	}
	popup.activate(st);
	if(!val)val=1;
	popup.progressobj.val=val;
}
popup.error=function(obj){
	if(!obj)return;
	var st=this.getStLayer(obj,{tpl:[obj]},'*popup/error.tpl');
	popup.activate(st);
}
popup.confirm=function(obj,callback){
	if(!obj)return;
	var st=this.getStLayer(obj,{tpl:[obj]},'*popup/confirm.tpl');
	st.layer.conf_ok=callback;
	popup.activate(st);
}
popup.getStLayer=function(obj,objtpl,tpl){
	var st=this.stackAdd(obj);
	if(!st.layer){
		var divid='stdivpopup'+st.counter;
		st.layer={
			tpl:tpl,
			tplroot:'root',
			conf_divid:divid,
			divs:{}
		}
		st.layer.divs[divid]=objtpl;
	}
	return st;
};

popup.hide=function(obj){
	if(!obj&&popup.st)obj=popup.st.obj;
	if(!obj)return;
	var st=popup.stackDel(obj);
	var next=popup.stackLast();

	//anti activate
	var check=[];
	popup.st=next;
	popup.justhide(st);
	check.push(st.layer);
	if(next){
		popup.justshow(next);
		check.push(next.layer);
	}
	infrajs.check(check);
}
popup.toggle=function(obj){//Если окно
	var st=popup.stackGet(obj);
	if(!st||this.st!==st)return this.open(obj);
	else return this.hide(obj); 
}


popup.justhide=function(st){
	st.layer.popupis=false;
	if(!popup.st)popup.div.modal('hide');
}
popup.justshow=function(st){
	popup.init();
	var cont=popup.div.find('#popup_content');
	var divid='popupinst'+st.counter;
	var place=cont.find('#'+divid);
	st.layer.popupis=true;
	if(!place.length){
		cont.append('<div id="'+divid+'"></div>');
		//infrajs.checkAdd(st.layer);
		st.layer.div=divid;
	}
	
	var opt={show:true,keyboard:false};
	if(st.strict){
		opt.backdrop='static';
	}else{
		opt.backdrop=true;
	}
	
	//popup.refreshBackdrop(opt);
	
	
	popup.div.modal(opt);//Нужно запускать постоянно так как она может быть скрыто средствами bootstrap modal
}
popup.render=function(){
	//Подтягиваем фон согласно размера окна
	//popup.div.data('bs.modal').adjustBackdrop();
	popup.div.data('bs.modal').adjustDialog();
}
popup.refreshBackdrop=function(opt){
	var mod=popup.div.data('bs.modal');
	if(mod){
		if(mod.options.backdrop!=opt.backdrop){
			mod.options.backdrop=opt.backdrop;
			if(popup.st){
				mod.$element.off('click.dismiss.bs.modal');
				mod.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
					if (this.ignoreBackdropClick) {
						this.ignoreBackdropClick = false;
						return;
					}
					if (e.target !== e.currentTarget) return
					this.options.backdrop == 'static'
					? this.$element[0].focus()
					: this.hide()
				}, mod));
			}
		}
	}
}

popup.getLayer=function(){
	if(!this.st)return;
	return this.st.layer;
},
popup.div=false;//Здесь хранится jquery объект окна
popup.init=function(){
	this.init=function(){};
	
	$.ajax({
		type: "GET",
		url:infra.theme('*popup/popup.tpl'),
		async:false,
		dataType:'html',
		success:function(text){
			popup.div=$(text);
			$(document.body).append(popup.div);
		}
	});
	popup.div.on('showed.bs.modal',function(){
		$('body').css('padding-right',0);
	});
	popup.div.on('hide.bs.modal',function(){
		popup.div.removeClass('fade');//скрытие обычное иначе глюки с затемнением
	});
	popup.div.on('hidden.bs.modal',function(){
		popup.div.addClass('fade');
		if(popup.st){//Есть активное окно значит close не был вызван
			popup.hide();
		}else{

		}
	});
	$('body').on('keydown', function(e){
		if(e.which == 27){
			if(!popup.st)return;
			if(popup.st.strict)return;
			popup.hide();
		}
	});
}




popup.hideAll = function(){ //Закрываем все окна в стеке
	if(!popup.st)return;
	var st=popup.st;
	popup.st=false;
	popup.stackClear();
	popup.justhide(st);
	infrajs.check(st.layer);
}
popup.isShow=function(){
	return !!this.st;
}
popup.closeAll=function(){//depricated
	return this.hideAll();
}
popup.center=function(){//depricated
	popup.render();
}

infrajs.popup_memorize=function(code){
	if(!popup.st)return;
	infrajs.code_save('popup',code);
	popup.div.on('hidden.bs.modal',function(){
		infrajs.code_remove('popup',code);
	});
	//infra.when(popup.st.obj,'onhide',function(){
		//infrajs.code_remove('popup',code);
	//});
}
popup.close=function(){//depricated
	return this.hide.apply(this,arguments);
}