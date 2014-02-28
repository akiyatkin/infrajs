{
	"tplroot":"root",
	"tpl":"*contacts/contacts.tpl",
	"autofocus":true,
	"autosavename":"user",
	"divs":{},
	onsubmit:function(layer){
		var conf=layer.config;
		var div=$('#'+layer.div);
		if(!conf.ans){
			div.find('.answer').html('<b class="alert">Произошла ошибка.<br>Cообщение не отправлено...</b>');
		}
		if(conf.ans.result>0){
			div.find('textarea').val('').change();
		}
	}
}
