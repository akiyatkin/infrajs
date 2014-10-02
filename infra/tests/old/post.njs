if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');		
/*
<form method="POST">
<input name="testname" type="text" value='test'>
<input type="submit">
</form>
*/
this.init=function(){
	var view=infra.View.init(arguments);
	var POST=view.getPOST();
	view.end(POST);
}
