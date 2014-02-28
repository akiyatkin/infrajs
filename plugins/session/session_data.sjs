var checkUser=function(user){
	if(!user)return 'форма не заполнена';
	if(!user.persona)return 'Не указано имя';
	if(!user.email)return 'Некорректный email';
	if(!user.phone)return 'Некорректный телефон';
}
/*
	Можно сделать get вместе со старым list, либо из совсем других данных, либо из li
*/
this.before=function(list,data,ses,ans){
	if(ses.isSet(list,'user')){
	}
	return true;
}

