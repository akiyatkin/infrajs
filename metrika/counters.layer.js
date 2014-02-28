{
	tpl:'*metrika/counters.tpl',
	autoedittpl:{
		title:'Счётчики',
		descr:'Необходимо в файле указать индетификационные номера для каждого счётчика.<br> <a href="{infra.theme(:*metrika/counters.json)}">пример файла</a>',
		files:{
			paths:'*counters.json'
		}
	},
	data:true,
	external:'*metrika/counters.layer.php'
}
