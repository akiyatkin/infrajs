{
	tpl:'*slide/slide.tpl',
	tplroot:'root',
	config:{
		onshow:false,//Путь до json файла с методом onshow который запускается с когда показывается новый слайд, с параметром config
		folder:'*Папка с фотографиями',
		href:"",
		width:599,
		height:298,
		imgcls:"",
		next:2,
		rnd:true
	},
	//onhide:function(){}, //this.config.pimg=false;//Нужно чтобы листание остановилось если слой скрыт// и нужно чтобы листание началось заного если слой быстро перепарсился
	datatpl:['{config.folder}Ссылка.tpl'],
	autoedittpl:{
		title:'Слайды',
		files:{
			paths:['{config.folder}','{conf.href?:yes?}{yes:}{config.folder}Ссылка.tpl']
		}
	}
}
