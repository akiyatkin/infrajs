//autofocus:(bool),//Выставлять фокус на первый пустой input в слое
infrajs.autofocus_moveCaretToEnd=function(inp){
	try{
		if (inp.createTextRange){
			var r = inp.createTextRange();
			r.collapse(false);
			r.select();
		}else if (inp.selectionStart){
			var end = inp.value.length;
			inp.setSelectionRange(end,end);
			inp.focus();
		}else if(typeof(inp.selectionStart) == "number"){
			inp.selectionStart = inp.selectionEnd = inp.value.length;
		}
	}catch(e){}
}
infrajs.autofocus=function(layer){//onshow
	if( typeof(layer)=='string' ) {
		var div=$(layer);
		var layer={};
	} else {
		if(!layer.autofocus)return;
		var div=$('#'+layer.div);
		div.find('input, textarea').focus(function(){
			var inp=$(this);
			layer.autofocuswas=inp.attr('name');	
		});
	}

	if(layer.autofocuswas){
		var inp=div.find('[name="'+layer.autofocuswas+'"]');
		
	}else{
		var inp=$('[autofocus]:first');
		if (!inp.length) {
			var inp=div.find('input:first[value=""][type=text]'); //На первый пустой инпут
			if(!inp.length){
				var inp=div.find('input:first[type=text]');
			}
		}
	}
	//inp.prop('autofocus',true);
	inp.focus();
	if(inp.length){
		infrajs.autofocus_moveCaretToEnd(inp.get(0));
	}
}
infrajs.autofocussave=function(layer){//oncheck
	//autofocus
	if(!layer.autofocus)return;
	if(!layer.showed)return;
	var div=$('#'+layer.div);
	var inp=div.find('input:focus');
	if(!inp.length)return;
	layer.autofocuswas=inp.attr('name');//Если происходит асинхронный ответ и тп...
}

