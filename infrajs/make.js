/*
Загружаются все файлы в initjs.php
*/	
	infra.State.init();
//========================
// infra.State onchange
//========================

	infra.listen(infra.State,'onchange',function(){
		//scroll
		var scrollFromTop=0;
		var store=infrajs.store();
		if(store.counter==0)return;
		if(infra.conf&&infra.conf.scroll&&infra.conf.scroll.scrollFromTop)scrollFromTop=infra.conf.scroll.scrollFromTop;
		setTimeout(function(){
			if(infrajs.scroll!==false)window.roller.goTop(scrollFromTop);      
			delete infrajs.scroll;
		},1);
	});
	infra.handle(infra.State,'onchange',function(){
		//div
		infrajs.div_init();	
	});
	infra.listen(infra.State,'onchange',function(layer){
		//seo
		infrajs.seo_init();
	});

//========================
// infrajs oncheck
//========================
	//==========wait====//
	infra.wait(infrajs,'oninit',function(layer){
		//show
		infrajs.show_init();
	});
	infra.wait(infrajs,'oninit',function(){
		//config
		infrajs.configinit();
	})
	infra.wait(infrajs,'oninit',function(){
		//onsubmit
		infrajs.onsubmitinit();
	});
	infra.wait(infrajs,'oninit',function(){
		//parsed
		infrajs.parsedinit();
	});
	//==========listen====//
	infra.listen(infrajs,'oninit',function(){
		//loader
		infra.loader.show();
	});
	infra.listen(infrajs,'oninit',function(layer){
		//tpl
		var store=infrajs.store();
		store.divs={};
	});
	infra.wait(infrajs,'oninit',function(){
		//autoedit
		infrajs.autoeditInit();	
	});
	
//========================
//layer oninit
//========================
	
	infra.listen(infra,'layer.oninit',function(layer){
		//external
		infrajs.external.check(layer);
	});
	infra.listen(infra,'layer.oninit',function(layer){
		//config
		infrajs.configinherit(layer);
	});
	infra.listen(infra,'layer.oninit',function(layer){
		//infrajs
		var store=infrajs.store();
		layer['store']={'counter':store['counter']};
	});
	infra.listen(infra,'layer.oninit',function(layer){
		//unick
		infrajs.unickSet(layer);
	});
	infra.listen(infra,'layer.oninit',function(layer){//это из-за child// всё что после child начинает плыть. по этому надо state каждый раз определять, брать от родителя.
		//state
		if(!layer['dyn']){//Делается только один раз
			infrajs.setState(layer,'state',layer['state']);
			infrajs.setState(layer,'istate',layer['istate']);
		}
	});
	infra.listen(infra,'layer.oninit',function(layer){
		//state
		if(!layer['parent'])return;//слой может быть в child с динамическим state только если есть родитель
		infrajs.setState(layer,'istate',layer['dyn']['istate']);//Возможно у родителей обновился state из-за child у детей тоже должен обновиться хотя они не в child
		infrajs.setState(layer,'state',layer['dyn']['state']);
	});
	infra.listen(infra,'layer.oninit',function(layer){	
		//state child		
		if(!layer['child'])return;//Это услвие после setState 

		var st=layer['state']['child'];
		if(st) var state=st['name'];
		else var state='###child###';

		infra.fora(layer['child'],function(l){
			infrajs.setState(l,'state',state);
			infrajs.setState(l,'istate',state);
		});
	});
	infra.listen(infra,'layer.oninit',function(layer){//Должно быть после external, чтобы все свойства у слоя появились
		//state childs
		infra.forx(layer['childs'],function(l,key){//У этого childs ещё не взять external
			if(!l['state'])l['state']=infrajs.setState(l,'state',key);
			if(!l['istate'])l['istate']=infrajs.setState(l,'istate',key);
		});
		
	});	

	/*infra.listen(infra,'layer.oninit',function(layer){
		//state link
		if(!layer['link']&&!layer['linktpl'])layer['linktpl']='{istate}';
	});*/
	
//========================
// layer is check
//========================
	
	infrajs.isAdd('check',function(layer){//может быть у любого слоя в том числе и у не iswork, и когда нет старого значения
		//infrajs исключение
		if(!layer)return false;
		if(!infrajs.isWork(layer))return false;//Нет сохранённого результата, и слой не в работе, если работа началась с infrajs.check(layer) и у layer есть родитель
	});
	

	infrajs.isAdd('check',function(layer){
		//state
		if(!layer['istate']['obj'])return false;
	});
	
	infrajs.isAdd('check',function(layer){
		//tpl
		if(layer['onlyserver'])return false;
		
	});
	

//========================
// layer oncheck
//========================
	infra.listen(infra,'layer.oncheck',function(layer){//Свойство counter должно быть до tpl чтобы counter прибавился а потом парсились
		//counter
		if(!layer.counter)layer.counter=0;
	});
	infra.listen(infra,'layer.oncheck',function(layer){//Без этого не показывается окно cо стилями.. только его заголовок.. 
		//div
		infra.forx(layer.divs,function(l,key){
			if(!l.div)l.div=key;
		});
	});
	infra.listen(infra,'layer.oncheck',function(layer){//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя	
		//div
		if(!layer.div&&layer.parent)layer.div=layer.parent.div;
	});
	infra.listen(infra,'layer.oncheck',function(layer){
		//autosave
		infrajs.autosaveRestore(layer);
	});
	

	/*infra.listen(infra,'layer.oncheck',function(layer){//php {} возвращает как []
		//subs
		infra.foro(layer.subs,function(val,key,group){
			if(typeof(val)!=='object')group[key]={};
		});
	});*/
	infra.listen(infra,'layer.oncheck',function(layer){//external уже проверен
		//subs
		infrajs.subMake(layer);
	});
	
	infra.listen(infra,'layer.oncheck',function(layer){
		//config
		infrajs.configtpl(layer);
	});
	infra.listen(infra,'layer.oncheck',function(layer){
		//seo
		infrajs.seo_checkseolinktpl(layer);
	});
	/*infra.listen(infra,'layer.oncheck',function(layer){	
		//state link
		if(layer['linktpl'])layer['link']=infra.template.parse([layer['linktpl']],layer);
	});	*/
	
	infra.listen(infra,'layer.oncheck',function(layer){
		//envs
		infrajs.envEnvs(layer);
	});
	infra.listen(infra,'layer.oncheck',function(layer){
		//envframe
		infrajs.envframe(layer);
	});
	infra.listen(infra,'layer.oncheck',function(layer){
		//envframe
		infrajs.envframe2(layer);
	});
	infra.listen(infra,'layer.oncheck',function(layer){//external то ещё не применился нельзя
		//env myenvtochild
		infrajs.envmytochild(layer);
	});
	infra.listen(infra,'layer.oncheck',function(layer){//external то ещё не применился нельзя
		//envtochild
		infrajs.envtochild(layer)
	});
	
	
	
	
	infra.listen(infra,'layer.oncheck',function(layer){
		//div
		infrajs.divtpl(layer);
	});
	infra.listen(infra,'layer.oncheck',function(layer){
		//tpl
		infrajs.tplrootTpl(layer);
		infrajs.tpldatarootTpl(layer);
		infrajs.tplTpl(layer);
		infrajs.tplJson(layer);
	});
	

	infra.listen(infra,'layer.oncheck',function(layer){
		//autofocus
		infrajs.autofocussave(layer);
	});
	
	
	infra.listen(infra,'layer.oncheck',function(layer){
		//global
		infrajs.checkGlobal(layer);
	});
	
	infra.listen(infra,'layer.oncheck',function(layer){
		//show
		infrajs.show_animate(layer);
	});
//========================
// infrajs oncheck
//========================

//========================
// layer is show
//========================	
	infrajs.isAdd('show',function(layer){
		//infrajs
		if(!infrajs.is('check',layer))return false;
	});
	infrajs.isAdd('show',function(layer){
		//is
		infrajs.istplparse(layer);
		return infrajs.isCheck(layer);
	});
	infrajs.isAdd('show',function(layer){
		//div
		layer.showmsg='Не показываем если не указан div';
		//infrajs.isSaveBranch(layer,true);
		var r=true;
		if(layer['parent']){
			r=infrajs.isSaveBranch(layer['parent']);
			if(typeof(r)=='undefined')r=true;
		}
		
		infrajs.isSaveBranch(layer,r);

		if(!layer.div)return false;//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
	});
	
	infrajs.isAdd('show',function(layer){//Родитель скрывает ребёнка если у родителя нет опции что ветка остаётся целой
		//infrajs
		if(!layer.parent)return;
		if(infrajs.is('show',layer.parent))return;
		if(infrajs.isSaveBranch(layer.parent))return;//Какой-то родитель таки не показывается.. теперь нужно узнать скрыт он своей веткой или чужой
		return false;
	});
	infrajs.isAdd('show',function(layer){
		//popup
		if(layer.popupis===false)return false;
	});
	
	infrajs.isAdd('show',function(layer){
		//tpl
		if(layer.tpl)return;
		infrajs.isSaveBranch(layer,true);//Когда нет шаблона слой скрывается, но не скрывает свою ветку
		return false;
	});
	infrajs.isAdd('show',function(layer){//tpl должен существовать, ветка скрывается
		//tpl
		if(!layer.tplcheck)return;
		var res=infra.loadTEXT(layer.tpl);
		if(res)return;//Без шаблона в любом случае показывать нечего... так что вариант показа когда нет результата не рассматриваем
		infrajs.isSaveBranch(layer,false);
		return false;
	});
	infrajs.isAdd('show',function(layer){//ветка скрывается
		//tpl
		return infrajs.tplJsonCheck(layer);
	});
	infrajs.isAdd('show',function(layer){//isShow учитывала зависимости дивов layerindiv ещё не работает
		//div
		var r=infrajs.divCheck(layer);
		return r;
	});
	infrajs.isAdd('show',function(layer){
		//env
		if(!layer.env){
			infrajs.getHtml(layer);//чтобы установилось свойство infrajs['com'] после загрузки шаблонов
			if(infrajs['com']&&infrajs['com']['env']){
				var envs=infrajs['com']['env'];
				if(!layer['myenv'])layer['myenv']={};
				infra.forr(envs,function(env){
					var old=layer['myenv'][env];
					layer['myenv'][env]=true;
					infra.when(infrajs,'onshow',function(){
						layer['myenv'][env]=false;
					});
				});

			}
			return;
		}
		return infrajs.envCheck(layer);
	});
	

//========================
// layer is rest
//========================
	infrajs.isAdd('rest',function(layer){//Будем проверять все пока не найдём
		//infrajs
		
		if(!infrajs.isWork(layer))return true;//На случай если забежали к родителю а он не в работе
		if(!infrajs.is('show',layer))return true;//На случай если забежали окольными путями к слою который не показывается (вообще в check это исключено, но могут быть другие забеги)
		
		if(layer['parent']&&infrajs.isWork(layer['parent'])&&!infrajs.is('rest',layer['parent'])){
			return false;//Парсится родитель парсимся и мы
		}

		if(!layer.showed)return false;//Ещё Непоказанный слой должен перепарситься..		
	});
	infrajs.isAdd('rest',function(layer){
		//tpl parsed
		if(!infrajs.isWork(layer))return true;//На случай если забежали к родителю а он не в работе
		if(!infrajs.is('show',layer))return true;//На случай если забежали окольными путями к слою который не показывается (вообще в check это исключено, но могут быть другие забеги)

		if(layer._parsed!=infrajs.parsed(layer)){
			return false;//'свойство parsed изменилось';	
		}
	});
	infrajs.isAdd('rest',function(layer){
		//divparent
		if(!infrajs.isWork(layer))return true;//На случай если забежали к родителю а он не в работе
		if(!infrajs.is('show',layer))return true;//На случай если забежали окольными путями к слою который не показывается (вообще в check это исключено, но могут быть другие забеги)

		var r=infrajs.divparentIsRest(layer);
		return r;
	});
	
	

	
	
//========================
// layer onshow
//========================
	infra.listen(infra,'layer.onshow',function(layer){//Должно идти до tpl
		//counter
		layer.counter++;
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//tpl
		layer._parsed=infrajs.parsed(layer);	//Выставляется после обработки шаблонов в которых в событиях onparse могла измениться data
	});
	infra.listen(infra,'layer.onshow',function(layer){//До того как сработает событие самого слоя в котором уже будут обработчики вешаться
		//tpl
		if(infrajs.ignoreDOM(layer))return;
		layer.html=infrajs.getHtml(layer);		
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//css
		if(infrajs.ignoreDOM(layer))return;
		infrajs.csscheck(layer);	
	});
	infra.listen(infra,'layer.onshow',function(layer){//До того как сработает событие самого слоя в котором уже будут обработчики вешаться
		//tpl
		
		var div=document.getElementById(layer.div);
		if(div)div.style.display='';
		if(infrajs.ignoreDOM(layer))return;
		if(!div){//Мы не можем проверить это в isshow так как для проверки надо чтобы например родитель показался, Но показ идёт одновременно уже без проверок.. сейчас.  По этому сейчас и проверяем. Пользователь не должне допускать таких ситуаций.
			if(!layer.divcheck&&infra.conf.debug){//Также мы не можем проверить в layer.oninsert.cond так как ситуация когда див не найден это ошибка, у слоя должно быть определено условие при которых он не показывается и это совпадает с тем что нет родителя. В конце концов указываться divparent
				console.log('Не найден контейнер для слоя:'+'\ndiv:'+layer.div+'\ntpl:'+layer.tpl+'\ntplroot:'+layer.tplroot+'\nparent.tpl:'+(layer.parent?layer.parent.tpl:''));
			}
			return false;
		}
		if(div){
			infrajs.layer=layer;//в скриптах будет доступ к последнему вставленному слою
			//^ нельзя этим пользоваться.. при первой загрузке infrajs.layer не определён
			infra.html(layer.html,layer.div);
			delete infrajs.layer;//Чтобы небыло ошибок
			delete layer.html;//нефиг в памяти весеть
		}
	});

	
	infra.listen(infra,'layer.onshow',function(layer){
		//tpl
		//слой который показан и не перепарсивается сюда не попадает, но и скрывать из этого дива никого не надо будет ведь этот слой и был показан.
		var store=infrajs.store();
		store.divs[layer.div]=layer;
	});
	
	infra.listen(infra,'layer.onshow',function(layer){
		//popup
		layer.showmsg='popup';
		popup.layeronshow(layer);
	});
	infra.listen(infra,'layer.onshow',function(layer){//Анимация только для первого показываемого слоя, вначале это корневой.. потом это текстовый в центре ожидается
		//show
		infrajs.show_div(layer);
		
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//autofocus
		layer.showmsg='autofocus';
		infrajs.autofocus(layer);
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//autosave
		infrajs.autosaveHand(layer);
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//onsubmit
		infrajs.setonsubmit(layer);
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//seo
		infrajs.seo_now(layer);
	});
	infra.listen(infra,'layer.onshow',function(layer){
		//autoview
		infrajs.autoview(layer);
	});
	
	
//========================
// layer onhide
//========================

	infra.listen(infra,'layer.onhide',function(layer){//onhide запускается когда слой ещё виден
		//tpl
		var store=infrajs.store();
		var l=store.divs[layer.div];//Нужно проверить не будет ли див заменён самостоятельно после показа. Сейчас мы знаем что другой слой в этом диве прямо не показывается. Значит после того как покажутся все слои и див останется в вёрстке только тогда нужно его очистить.

		if(l)return;//значит другой слой щас в этом диве покажется и реальное скрытие этого дива ещё впереди. Это чтобы не было скачков
		infra.htmlclear(layer.div);
	});


//========================
// infrajs onshow
//========================
	infra.handle(infrajs,'onshow',function(){
		//loader
		infra.loader.hide();
	});
	infra.listen(infrajs,'onshow',function(){
		//state
		infra.State.setA(document);//Пробежаться по всем ссылкам и добавить спeциальный обработчик на onclick... для перехода по состояниям сайта.
	});
	infra.listen(infrajs,'onshow',function(){
		//show
		infrajs.htmlsomelayeranimate=false;
	});
	infra.listen(infra,'onshow',function(){
		//popup
		if(popup.showed_popups.length){
			popup.center();
		}
	});
	infra.listen(infrajs,'onshow',function(){
		//autoedit
		infrajs.autoeditLink();
	});
	infra.listen(infrajs,'onshow',function(){
		//common
		infra.tableCommon();
	});
	infra.listen(infrajs,'onshow',function(){
		//autoedit
		if(!window.AUTOEDIT)return;
		AUTOEDIT.active=false;
		if(!popup.layer)return;
		if(!popup.layer.showed)return;
		if(!popup.layer.config)return;
		if(!popup.layer.data)return;
		if(!popup.layer.data.admin)return;
		if(popup.layer.config.type!='admin')return;
		AUTOEDIT.active=true;
		AUTOEDIT.setHandlers();
	});
	
	infra.wait(infrajs,'onshow',function(){
		//code
		infrajs.code_restore();
	});
	infra.listen(infrajs,'onshow',function(){
		//autoedit
		infrajs.autoedit_SaveOpenedWin();
	});
