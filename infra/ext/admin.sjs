if(typeof(ROOT)=='undefined')var ROOT='../../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');

infra.admin=function(ans){
	infra.load('*infra/forr.js','r');
	infra.load('*session/session.js','r');
	/*
		- infra.admin(true); - запросить авторизацию и если нет вывести стандартный ans
		- infra.admin(ans); - запросить авторизацию и если нет вывести ans
		- infra.admin(false); - запросить авторизацию если ещё её нет и при любом ответе пройти дальше а ответ просто вернуть
		- infra.admin(); узнать текущую авторизацию
		
		Скрипт только для админа начинается со строк
		if(!infra.admin(ans))return;
		if(!infra.admin(true))return;
		if(!infra.admin(false))return view.end(ans);
		Если нет параметра просто проверка
		if(infra.admin())admin_do();
		
		infra.admin.set(false); сбросить

		infra.admin.set(true); установить

	*/
	if(ans===true)ans={'msg':'Требуется авторизация','result':0};//Дефолтный ответ
	
	
	var ises=infra.Session.init('face');//и на клиенте
	var admin=ises.get('infra.admin');

	if(infra.NODE){//Проверить верность мы можем только на сервере.. на клиенте верим
		var data=infra.load('*.admin.js','kfsj');
		if(!data){
			return infra.error('<h1>Вам нужно создать файл infra/infra/data/.admin.js</h1>{"login":"логин","password":"секрет","email":"admin@email.ru"}');
		}
		var sesadm=infra.load('*session/sesadm.sjs');
		var key=sesadm.key(data['login']+data['password']);
		if(admin&&admin!=key){//Проверили что ключ правильный
			admin=false;
			ises.set('infra.admin',admin);
		}
	}
	
	if(typeof(ans)=='undefined')return !!admin;
	if(admin)return !!admin;
	if(!infra.NODE) return !!admin;
	
	
	//Мы знаем теперь, что не админы и что требуется проверка
	
	
	var view=infra.View.get();
	if(!ises.get('infra.adminoff')){
		var h=view.getAUTH();
		admin=(h['login']==data['login']&&h['password']==data['password']);
	}
	if(admin){
		if(admin!=key)ises.set('infra.admin',key);
		return true;
	}else{
		ises.set('infra.adminoff',false);
		view.code(401,'Restricted Area');
		if(ans)view.end(ans);
		return false;
	}	
}
infra.admin.set=function(what){
	infra.load('*session/session.js','r');
	var ises=infra.Session.init('face');
	if(what){
		if(infra.NODE){//Проверить верность мы можем только на сервере.. на клиенте верим
			var data=infra.load('*.admin.js','fsj');
			if(!data) {
				return infra.error('<h1>Вам нужно создать файл infra/infra/data/.admin.js</h1>{"login":"логин","password":"секрет","email":"admin@email.ru"}');
			}
			var sesadm=infra.load('*session/sesadm.sjs');
			var key=sesadm.key(data['login']+data['password']);
			ises.set('infra.admin',key);
		}
	}else{
		ises.set('infra.adminoff',true);
		ises.set('infra.admin',false);
	}
}

