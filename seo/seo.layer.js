{
	tpl:'*seo/seo.tpl',
	tplroottpl:'{config.type}',
	autosavenametpl:'{config.type}|{config.id}',
	autofocus:true,
	jsontpl:'*seo/seo.php?id={config.id}&type={config.type}',
	config:{
		type:'type',
		id:'id',
		ans:false//Ответ после отправки на сервер хранится тут
	},
	onsubmit:function(layer){
		var ans=this.config.ans;
		if(ans.result)infrajs.autosave.clear(layer);//Обнулили сохранённые введённые значения пользователя
		infra.require('*autoedit/autoedit.js');
		AUTOEDIT.refreshAll();
		if(ans.result&&!ans.noclose)popup.hide();
		if(ans.js){
			eval(ans.js);
		}
	}
}
