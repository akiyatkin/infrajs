if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/default.js','r');
this.init=function(){
	var view=infra.View.init(arguments);
	if(!infra.admin(true))return;
	var GET=view.getGET();
	
	infra.load('*session/session.js');
	var type=GET.type||'';
	try{
	var ses=infra.Session.init(type);
	}catch(e){
		return view.end(e);
	}
	view.bug(
		'<b>Нужно в GET передать параметр type=сессия</b>',
		'GET: '+ses.source(GET),
		'COOKIE: '+ses.source(view.getCOOKIE()),
		'type: '+ses.type,
		'id: '+ses.getId(),
		'STORAGE: '+ses.source(ses.storageLoad()),
		'DATA: '+ses.source(ses.data)
	);	
	if(GET.onlyserver)return view.end('');
	view.end('<script>ROOT="'+ROOT+'";</script><script src="'+ROOT+'infra/plugins/infra/infra.js"></script>		<div id="ans"></div><script>ROOT="'+ROOT+'"; infra.load("*infra/default.js"); infra.load("*session/session.js");			var ses=infra.Session.init("'+type+'",infra.View.init()); var html=""; html+="<br>COOKIE: "+document.cookie;    html+="<br>type: "+ses.type;   html+="<br>id: "+ses.getId();  html+="<br>STORAGE: "+ses.source(ses.storageLoad());     html+="<br>DATA: "+ses.source(ses.data);     document.getElementById("ans").innerHTML=html;		</script>');
	
}
