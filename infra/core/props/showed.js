//Свойства showed Используется в admin.js
infra.listen(infra,'layer.onhide.cond',function(){
	if(!this.showed)return false;
});
infra.listen(infra,'layer.onhide.before',function(){
	this.showed=false;
});
infra.listen(infra,'layer.onshow.after',function(){
	this.showed=true;//В самом конце отмечается чтобы не затереть прошлое состояние.. а если onparse onshow сработали будет установлено true и так понятно
});
infra.listen(infra,'layer.onparse.cond',function(){
	if(!this.showed)return 'Не показан';
});