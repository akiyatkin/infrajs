/*
	use:
	infrajs.code_save('popup','contacts.show();');
	infra.wait(infrajs.layer,'onhide',function(){
		infrajs.code_remove('popup');
	});
*/
infrajs.code_types={};
infrajs.code_types_save=function(){
	var types=[];
	for(var i in infrajs.code_types)types.push(i);
	types=types.join('|');
	window.sessionStorage.setItem('savedtypescode',types);
}

infrajs.code_type_add=function(type){
	infrajs.code_types[type]=true;
	infrajs.code_types_save();
	
}
infrajs.code_type_remove=function(type){
	delete infrajs.code_types[type];
	infrajs.code_types_save();
}

infrajs.code_remove=function(type,code){
	if(!window.sessionStorage)return;
	infrajs.code_type_remove(type);
	if(code){
		var oldcode=window.sessionStorage.getItem('savedcode'+type);
		if(oldcode!=code)return;//Там уже чужой код сохранён
	}
	window.sessionStorage.removeItem('savedcode'+type);
}
infrajs.code_save=function(type,code){
	if(!window.sessionStorage)return;
	infrajs.code_type_add(type);
	window.sessionStorage.setItem('savedcode'+type,code);
}
infrajs.code_restore=function(){
	if(!window.sessionStorage)return;
	var types=window.sessionStorage.getItem('savedtypescode');
	if(!types)return;
	types=types.split('|');
	for(var i=0,l=types.length;i<l;i++){
		var type=types[i];
		var code=window.sessionStorage.getItem('savedcode'+type);
		if(code)eval(code);
	}
};