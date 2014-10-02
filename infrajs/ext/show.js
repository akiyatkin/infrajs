//Show
//layer.showanimate

infrajs.show_init=function(){
	infrajs.htmlb={};
	infrajs.htmlb.IE=(function (){if(window.opera)return false; var rv = 0;if (navigator.appName == 'Microsoft Internet Explorer') {var ua = navigator.userAgent;var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");if (re.exec(ua) != null)rv = parseFloat(RegExp.$1);}return rv;})();
	infrajs.htmlb.opera=/opera/.test(navigator.userAgent)||window.opera;
	infrajs.htmlb.chrome=/Chrome/.test(navigator.userAgent)
	infrajs.htmlb.webkit=/WebKit/.test(navigator.userAgent);
	infrajs.htmlb.safari=(infrajs.htmlb.webkit&&!infrajs.htmlb.chrome);	
}

infrajs.show_animate=function(layer){
	if(!layer.showanimate)return;
	infrajs.run(layer,function(l){
		if(l.showanimate!=undefined)return;
		l.showanimate=true;
	});
}


infrajs.show_div=function(layer){
	var store=infrajs.store();
	if(!layer.showanimate)return;
	
	if(store.counter==1)return;
	if((infrajs.htmlb.IE&&infrajs.htmlb.IE>8))return;

	//if(infrajs.htmlsomelayeranimate)return;
	//infrajs.htmlsomelayeranimate=true;

	var obj = document.getElementById(layer.div);
	if(!obj)return;
	infrajs.htmlSetOpacity(obj,0);
	setTimeout(function(){//Ждём когда оттормозится, а то юзер не заметит эфекта
		infrajs.htmlShow(obj,1);
	},1);
}
infrajs.htmlSetOpacity=function(obj,op){
	if(op<0)op=0;
	else if(op>1)op=1;
	obj.style.opacity = op;
	obj.style.filter='alpha(opacity='+op*100+')';
}
infrajs.htmlShow=function(obj, x) {
	if(!obj)return;
	op = (obj.style.opacity)?parseFloat(obj.style.opacity):parseInt(obj.style.filter)/100;
	if(op < x) {
		clearTimeout(infrajs.htmlhT);
		op += 0.05;
		infrajs.htmlSetOpacity(obj,op);
		infrajs.htmlsT=setTimeout(function(){
			infrajs.htmlShow(obj,x);
		},10);
	}
}
infrajs.htmlHide=function(obj, x) {
	op = (obj.style.opacity)?parseFloat(obj.style.opacity):parseInt(obj.style.filter)/100;
	if(op > x) {
		clearTimeout(infrajs.htmlsT);
		op -= 0.3;
		infrajs.htmlSetOpacity(obj,op);
		infrajs.htmlhT=setTimeout(function(){
			infrajs.htmlHide(obj,x);
		},10);
	}else{
		infrajs.htmlSetOpacity(obj,1);
	}
}