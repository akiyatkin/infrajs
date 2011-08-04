var infra = {};
this.infra = infra;

/* Константы, определяются клиентом и браузером отдельно. Приведены дефолтные значения. */
infra.ROOT = ''; // Корень сайта, от которого читается запрашиваемый путь
infra.NODE = false; // Находимся ли мы сейчас на node.js или в браузере
infra.DEBUG = false; // Вывод отладочной информации

/* Обработка ошибок */

/* Вывод ошибки */
infra.error = function(error, callback, name, context, args, msgs, test) {
	if (infra.DEBUG) {
		if(!callback) callback=''; if(!name) name='';
		if(!context) context=''; if(!args) args=''; if(!msgs) msgs=[];
		var em = 'Ошибка в '+name+'\n'+error.name+':'+error.message+'\ncallback:\n'+callback+'\nargs:\n'+args+'\ncontext:'+context+'\nИНФО:\n'+msgs.join('\n')
		if (!infra.NODE) {
			if (!test) alert(em);
		} else {
			if (!test) console.error(em);
		}
		throw error;
	}
}

/* Запуск функции */
infra.exec = function(callback, name, context, args, msgs, test) {
	args=args||[];
	try {
		var r=callback.apply(context,args);
		return r;
	} catch(e) {
		infra.error(e, callback, name, context, args, msgs, test)
	}
}

/* Циклы */
/* Одинаковое api для загрузки, слоев и расширений */
/* События */

/* Подключение контролера (check) */
/* Загрузка расширений, могут быть разные для браузера и для клиента */
