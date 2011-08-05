js.replacepath('core/lib/session/session.js','*session/session.js');

js.bufferOn();

js.loadJS('*infrajs/props/external.js');
js.loadJS('*infrajs/props/parsed.js');//Настройка когда перепарсивать показанный слой
//Обязательно в начале ^ так как предоставляют общие механизмы

js.loadJS('*infrajs/props/autosave.js');
js.loadJS('*infrajs/props/autofocus.js');//Надо цеплять после autosave иначе ставится focus а потом autosave загружается получается что курсор в непустом поле мигает

js.loadJS('*infrajs/props/proptpl.js');//перед global.js так как подменяет data. И global должен добавить в Unload уже сформированный путь
js.loadJS('*infrajs/props/global.js');//Требуется parsed, //global используется толькое если данные нужно загрузить с сервера. Иначе должна быть проверка в toString config или ещё где
//Выверен порядок ^


js.loadJS('*infrajs/template.js');
js.loadJS('*infrajs/jslib.js');
js.loadJS('*infrajs/state.js');
js.loadJS('*infrajs/props/state.js');
js.loadJS('*infrajs/props/layers.js');
js.loadJS('*infrajs/props/preload.js');
js.loadJS('*infrajs/props/toString.js');
js.loadJS('*infrajs/props/counter.js');//tpl.js использует этот плагин.. подключение должно быть до tpl.js
js.loadJS('*infrajs/props/tpl.js');
js.loadJS('*infrajs/props/divs.js');
js.loadJS('*infrajs/props/is.js');
js.loadJS('*infrajs/props/js.js');
js.loadJS('*infrajs/props/loader.js');
js.loadJS('*infrajs/props/css.js');

js.loadJS('*infrajs/props/childs.js');
js.loadJS('*infrajs/props/child.js');
js.loadJS('*session/session.js');
js.loadJS('*contacts/showContacts.js');
js.loadJS('*autoedit/autoedit.prop.js');//Загрузили админку F2
js.loadJS('*infrajs/props/propcheck.js');
js.loadJS('*infrajs/props/deep.js');
js.loadJS('*infrajs/props/configinherit.js');
js.loadJS('*infrajs/props/unick.js');
js.loadJS('*infrajs/props/env.js');

//Порядок не важен
js.loadJS('*infrajs/props/title.js');
js.loadJS('*infrajs/props/onsubmit.js');
js.loadJS('core/lib/statist/statist.php');
js.loadJS('*infrajs/props/help.js');
js.loadJS('*infrajs/props/subs.js');
js.loadJS('*infrajs/props/autoview.js');
js.loadJS('core/lib/jquery/jquery.js');
js.loadJS('core/lib/jquery/jquery.history.js');

js.bufferOff();


infrajs.listen(infrajs.state,'onchangeready',function(){//таким образом делается запись статистики при изменении состояния страницы
	statist.sent();
});

if((!js.IE||js.IE>8)){
	(function(){
		var hT, sT, somelayeranimate;
		var setOpacity=function(objId,op){
			if(op<0)op=0;
			else if(op>1)op=1;
			var obj = document.getElementById(objId);
			if(!obj){
				if(js.debug)alert('Ошибка в setOpacity нет obj');
				return;
			}
			obj.style.opacity = op;
			obj.style.filter='alpha(opacity='+op*100+')';
		}
		var Show=function(objId, x) {
			var obj = document.getElementById(objId);
			if(!obj){
				if(js.debug)alert('Ошибка в Show нет obj');
				return;
			}
			op = (obj.style.opacity)?parseFloat(obj.style.opacity):parseInt(obj.style.filter)/100;
			if(op < x) {
				clearTimeout(hT);
				op += 0.5;
				setOpacity(objId,op);
				sT=setTimeout(function(){
					Show(objId,x);
				},10);
			}
		}
		var Hide=function(objId, x) {
			var obj = document.getElementById(objId);
			if(!obj){
				if(js.debug)alert('Ошибка в Hide нет obj');
				return;
			}
			op = (obj.style.opacity)?parseFloat(obj.style.opacity):parseInt(obj.style.filter)/100;
			if(op > x) {
				clearTimeout(sT);
				op -= 0.3;
				setOpacity(objId,op);
				hT=setTimeout(function(){
					Hide(objId,x);
				},10);
			}else{
				setOpacity(objId,1);
			}
		}

		
		infrajs.listen(infrajs,'layer.onshow.before',function(){
			if(somelayeranimate)return;
			somelayeranimate=true;

			var layer=this;
			var obj = document.getElementById(layer.div);
			if(!obj){
				if(js.debug)alert('Ошибка в layer.onshow.before нет obj для плавного показа '+layer);
				return;
			}
			setOpacity(layer.div,0);
			setTimeout(function(){//Ждём когда оттормозится, а то юзер не заметит эфекта
				Show(layer.div,1);
			},1);
		});
		infrajs.listen(infrajs,'layer.onshow.after',function(){
			somelayeranimate=false;
		});
	})();
}

infrajs.listen(infrajs,'onshow',function(){//Добавили автоскрол на самый верх при каждом переходе
	if(window.autoscroll){
		window.scrollTo(0,0); 
		//delete вызывает ошибку ie7 и ie6
		window.autoscroll=false;
	}
});


infrajs.listen(infrajs,'onshow',function(){//Добавляем классы для ячеек таблиц с классом common
	$('table.common').not('.commoned').addClass('commoned').each(function(){
		$(this).find('tr:odd').addClass('odd');
		$(this).find('tr:even').addClass('even');
		$(this).find('tr:first').addClass('top');
		$(this).find('tr:last').addClass('bottom');
		$(this).find('tr').each(function(){
			$(this).find('>:first').addClass('first');
			$(this).find('>:last').addClass('last');
		});
	});
	$("a:has(img)").addClass("aimg");
	$("a:has(h1,h2,h3,h4,h5)").addClass("aheading");
});

infrajs.autoedit={//Настроили глобальный конфиг
	title:'infrajs',
	files:[
		{title:'Логин и пароль администратора',paths:['*.admin.js']},
		{title:'Темы',paths:['*themes.js']},
		{title:'Контакты',paths:['*.contacts.js']},
		{title:'Настройка кэш сервера',paths:['*infra/.cacheserver.js']}
	]
}
