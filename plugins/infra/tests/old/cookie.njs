if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');		
this.init=function(){
	var view=infra.View.init(arguments);
	view.setCOOKIE('name1','value1');
	view.setCOOKIE('name2','value2');
	return view.end(view.getCOOKIE());
}
