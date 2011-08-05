/*

*/

infra.trim = function(str){
	//if(window.jQuery)return jQuery.trim(str);
	str=str.replace(/[\n\r\t]+/,'');
	return str;
};
infra.split = function(s,str){
	 /* делаем, стандартно
	 '' - [] - ['']
	 '.' - [''] - ['','']
	 '..' - ['',''] - ['','','']
	 '.a.' - ['','a',''] - ['','a','']
	 'a..a' - ['a','','','a'] - ['a','','','a']
	 '..a..' - ['','','a','',''] - ['','','a','','']
	 */

	//if(typeof(str)!=='number'||typeof(str)!=='string')return [];
		  if(!str)return [];
	var r=str.split(s);
	var some=false;
	for(var i=0,l=r.length;i<l;i++){
		if(r[i]!==''){
			some=true;
			break;
		}
	}
	if(!some){
		r.pop();
	}
	return r;
};
infra.words = function(count,one,two,five){
	if(!count)count=0;
	if(count>20){
		var str=count.toString();
		count=str[str.length-1];
		count2=str[str.length-2];
		if(count2==1)return five;//xxx10-xxx19 (иначе 111-114 некорректно)
	}
	if(count==1){
		return one;
	}else if(count>1&&count<5){
		return two;
	}else{
		return five;
	}
};
infra.template={
	tpls:{},
	parse:function(url,data,tplroot,repls,dataroot){
		var tpls=this.getTpls(url);
		var html=this.make(tpls,data,tplroot,repls,dataroot);
		return html;
	},
	prepareVarname:function(d){
		if(d&&typeof(d.varname)=='string'){
			var varname=d.varname;
			var e=d.varname.split('=');
			if(e.length>1){
				d.equal='=';

				d.condval=infra.split('.',e[1]);

				varname=e[0];

			}else{
				e=d.varname.split('!');
				if(e.length>1){
					d.equal='!';

					d.condval=infra.split('.',e[1]);

					varname=e[0];
				}
			}

			e=infra.split('[',varname);
			if(e.length>1){
				d.property=e[1].replace(/\]$/,'');
				d.property=infra.split('.',d.property);
				varname=e[0];
			}
			d.varname=infra.split('.',varname);
		}
	},
	prepare:function(tpl){
		tpl=String(tpl);
		var parsed=[];//Разбитая строка на шаблоны и переменные
		var exp=false;//Находимся внутри выражения в фигурных скобках
		var varname=[];//Копится имя переменной
		var res=[];//Результат
		var name='';//Имя свойства data
		var d='';//Полученная замена объект
		var tplname=false;//Имя найденной шаблона
		var multi;//Метка мульти шаблон или нет
		var quest;//Условие
		var bracket=0;//Считаем скобки внутри выражения
		var func=false;//Односимвольное имя функции, которую нужно выполнить над переменной. Несколько переменных разделяются запятыми.

		tpl+='';//шаблон может быть цифрой для вывода, надо чтобы цифра воспринималась как строка
		for(var i=0,l=tpl.length;i<l;i++){
			var ch=tpl.charAt(i);
			if(exp&&ch=='}'&&!bracket){//Конец выражению
				if(!quest&&!tplname&&!varname.length)res.pop();//пусто {} надо удалить скобку первую.

				if(quest){
					var next=quest.second||quest.first;
					if(tplname)next.tplname=tplname.join('');
					next.varname=varname?varname.join(''):'';
					next.func=func;
					this.prepareVarname(next);
					quest.isquest=true;
					d=quest;
				}else if(tplname){//Значит шаблон
					if(!tplname.length&&varname.length){//Начало нового шаблона дальше
						d={
							newtpl:true,
							tplname:varname.join('')
						}
					}else{
						d={
							multi:multi,
							tplname:tplname.join(''),
							varname:varname.join(''),
							func:func
						};
						this.prepareVarname(d);
					}
				}else{
					d={
						varname:varname.join(''),
						func:func
					};
					this.prepareVarname(d);
				}
				
				if(res.length){//Пустые строчки не сохраняем
					parsed[parsed.length]=infra.trim(res.join(''));
				}
				parsed[parsed.length]=d;
				res=[];
				exp=false;
				varname=[];
				func=false;
				tplname=false;
				multi=false;
				quest=false;
				continue;
			}
			
			if(exp){
				if(!quest&&!varname.length&&(ch==' '||ch=='\n'||ch=='\r')){//Первый символ после открытой скобки.. если \s ложное срабатывание значит... пропускаем
					exp=false;
				//}else if(!quest&&!varname.length&&(ch==' '||ch=='\n'||ch=='\r')){//Односимвольная функция
				}else if(!quest&&!varname.length&&this.functions[ch]){//Функция относится только к varname %* ^&!+_-#$~@, - символы с которых переменные не начинаются, занято :?|   //Возвожмность определять функцию в шаблоне??? {^:}
					func=ch;
				}else{
					if(!quest&&!tplname&&!varname.length){
						res.pop();//Нужно забрать первую скобку... теперь мы знаем что это точно исследуемое выражение
					}
					if(!tplname&&ch==':'){//{data:tplname}
						tplname=[];
					}else if(!func&&tplname&&ch==':'){//multiшаблон
						multi=true;
					}else{
						if(!func&&(ch=='?'||ch=='|')){
							quest=quest||{};
							if(ch=='|')quest.pipe=true;
							if(!quest.first){
								if(tplname){
									quest.tplname=tplname.join('');
								}
								quest.varname=varname.join('');
								this.prepareVarname(quest);
								varname=[];
								tplname=false;
								quest.first={};
							}else{
								if(tplname){
									quest.first.tplname=tplname.join('');
								}
								quest.first.varname=varname.join('');
								this.prepareVarname(quest.first);
								varname=[];
								tplname=false;
								quest.second={};
							}
						}else{
							if(tplname){
								tplname[tplname.length]=ch;
							}else{
								varname[varname.length]=ch;
							}
						}
					}
				}
			}
			if(exp&&ch=='}'&&bracket){
				bracket--;
			}
			if(!exp){
				res[res.length]=ch;
			}
			if(ch=='{'){
				if(exp)bracket++;
				exp=true;
			}
		}
		parsed[parsed.length]=infra.trim(res.join(''));//Последняя строка
		return parsed;
	},
	getTpls:function(url){
		var save=(typeof(url)=='string')?url:url[0];
		if(this.tpls[save])return this.tpls[save];
		if(typeof(url)=='string'){
			var str=infra.load(url);
		}else{
			var str=url[0];
		}
		if(!str)str='';
		var parsed=this.prepare(str);//Основной разбор
		var tpls=[];
		if(typeof(parsed[0])=='object'&&parsed[0].newtpl){
			var nowtplname=parsed[0].tplname;
			parsed.shift();
		}else{
			var nowtplname='root';
		}
		var p=[];
		tpls[nowtplname]=p;
		for(var i=0,l=parsed.length;i<l;i++){
			if(typeof(parsed[i])==='object'){
				if(parsed[i].newtpl){
					nowtplname=parsed[i].tplname;
					p=[];
					tpls[nowtplname]=p;
					continue;
				}
			}
			p[p.length]=parsed[i];
		}
		this.tpls[save]=tpls;//кэш 
		return tpls;
	},
			/*
			   @ - вывод даты
			   $ - дополнительные данные $key
			   % - encode
			   * - тематический путь
			   : - разделение шаблонов
			   ? - условие
			   | - условие
			   = - проверка равенства
			   ! - проверка неравенства
			   */
		//{type=admin?asdf}
	functions:{
		'*':function(v){
			if(!v||typeof(v)!=='string')v='';
			if(!/^\*/.test(v))v='*'+v;//Путь для js.theme Должен начинаться со звёздочки
			return infra.theme(v);
		},
		'@':function(v,format){//date
			infra.loadJS('core/lib/phpdate/phpdate.js');
			return phpdate(format,v);
		},
		'~':function(v,words){
			var words=words.split(',');
			return infra.words(v,words[0],words[1],words[2]);
		},
		'%':function(v){
			if(typeof(v)!=='string')return v;
			v=encodeURI(v);//x5service картинки начали показываться только так. Замены пробела было не достаточно
			//v=v.replace(/\s/,'%20');
			return v;
		}
	},//вывод даты
	objtostring:{}.toString(),
	val:function(data,names){//Берём данные в data по адресу names
		var right=[];
		infra.fora(names,function(name){//any.some..ik = any.ik, any,some,,ik = any,ik при нахождении пустого имени нужно удалить педыдушее и это
			if(name==='')right.pop();
			else right.push(name);
		});
		var d=data;
		infra.fora(right,function(name){
			if(d&&(typeof(d)=='object'||typeof(d)=='function')){
				d=d[name];
			}else{
				d=undefined;
				return false;
			}
		});
		if(d===null)d=undefined;//запрещаем Null
		return d;
	},
	valVar:function(names,data,mdata,data_root,property){//получаем значение переменной
		var res=this.val(data,[data_root,names]);
		if(res===undefined)res=this.val(data,[names]);
		if(res===undefined)res=this.val(mdata,[names]);
		if(property){
			if(!res)return undefined;
			var prop=this.valVar(property,data,mdata,data_root);
			if(!prop)return undefined;
			return res[prop];
		}
		return res;
	},
	valVarEchoBase:function(par,res,varname){
		if(res===undefined){
			res=varname.join('.');
			if(res==='false')res=false;
			if(res==='true')res=true;
			if(res==='undefined')res=undefined;
		}
		return res;
	},
	valVarEcho:function(par,res,varname){
		res=this.valVarEchoBase(par,res,varname);

		var func=par.func;
		if(func){
			res=this.functions[func].apply(this,[res,par.tplname]);//{@unixtime:d.m.Y}
		}
		if(typeof(res)==='object'){
			if(res.constructor===Array){//Массивы не выводим
				res='';
			}else if(res.toString()===this.objtostring){//Стандартные объкты не выводим
				res='';
			}else if(!par.varname.join('')){//Если имя путое до объекта то не выводим.. {} так объект не вывести даже если у него есть toString
				res='';
			}
		}else if(typeof(res)==='function'){
			res='';
		}else if(typeof(res)==='boolean'){
			res='';
		}
		return res;
	},
	valEcho:function(par,data,mdata,data_root){//Вывод переменной
		var res=this.valVar(par.varname,data,mdata,data_root,par.property);
		return this.valVarEcho(par,res,par.varname);
	},
	valCond:function(res,par,data,mdata,data_root){
		if(!par.equal){
			return !!res;
		}else{
			res=this.valVarEchoBase(par,res,par.varname);
			var val=this.valVar(par.condval,data,mdata,data_root);
			val=this.valVarEchoBase(par,val,par.condval);
			if(par.equal=='='){
				if(res!=val)return false;//Сравниваем не с тем, что должно бы было вывестись.. объекты тут будут разными несмотря на вывод пустых строк 
			}else if(par.equal=='!'){
				if(res==val)return false;
			}
			return true;
		}
	},
	makePrepare:function(tpls,data,t,repls,datafortpl,mdata,dataroot){
		
	},
	make:function(tpls,data,t,repls,droot,mdata,data_now){//droot - данные для родительского подшаблона текущего подшаблона, data_now то что уже только к этому шаблону передано
		//tpls - перепарсеный шаблон... массив подшаблонов... результат работы функции prepare
		//data - данные от самого корня... какие есть всегда одни и теже для подшаблонов
		//t - имя шаблона по которому сейчас нужно сделать html с подставленными переменными из data
		//repls - массив подмен для основных шаблонов tpls/ Сначало смотрится repls
		//droot - путь через точку, или массив с попорядку указанными свойствами в data... до последнего от которого парсился родительский шаблон шаблона t
		//mdata - дополнительные данные для шаблона, если не будут найдены данные в data они возьмутся в mdata.. mdata системная переменная генерируется автоматически
		//data_now - путь до данных для текущего шаблона от считанных от пути до данных для предыдущего шаблона

		if(t==undefined)t='root';
		data_now=data_now||[];
		if(typeof(data_now)=='string'){
			data_now=data_now.split('.');
		}

		droot=droot||[];
		if(typeof(droot)=='string'){
			droot=droot.split('.');
		}

		repls=repls||{};

		//if(!repls[t]&&!tpls[t])return '';
		
		temptemplate=false;
		if(!repls[t]&&!tpls[t]){//подстановка шаблона из переменной// Когда записано :data - будет означать вывести шаблон из переменной data
			var val=this.valVar(t?t:[],data,mdata,droot); //Если t '' вся строка будет воспринята как root.some.. и как следствие пустое t это верхний уровень (это решается тут) 
			if(typeof(val)!=='string')val='';
			tpls[t]=this.prepare(val);//Так как это переменная в следующий раз это будет уже другое значение
			temptemplate=true;//Удалить после обработки
		}
		

		//Сначало складываем путь родительского шаблона с путём до данных для этого шаблона. Если данных нет берём путь без учёта пути до данных для родительского шаблона
		var data_root=[];
		infra.fora([droot,data_now],function(v){
			data_root.push(v);
		});
		var datafortpl=this.valVar(data_root,data,mdata,droot);
		if(datafortpl===undefined){//Смотрим что будет считаться данными для шаблона
			data_root=data_now;//Берём от корня
			var datafortpl=this.valVar([],data,mdata,droot);
			if(datafortpl===undefined)return '';//Данные для шаблона нет
		}

		
		if(!mdata){
			mdata={
				window:window,//depricated
				$first:true,
				$last:true,
				$key:t,
				$index:0,
				$even:false,
				$odd:true,
				$length:1,
				$length1:true,
				$level:0,
				$number:1,
				$index0:true,
				$level0:true,
				$deep:-1//Глубина
			}
		}

		var parsed=repls[t]?repls[t]:tpls[t];
		if(temptemplate){	
			delete tpls[t];//Так как это переменная в следующий раз это будет уже другое значение
		}
		if(!parsed)return '';//Указан шаблон которого на самом деле нет
		var res=[];
		var d,par,is,val;
		
		delete mdata['$deep'+mdata.$deep];
		mdata.$deep++;
		mdata['$deep'+mdata.$deep]=true;





		var tname=t+'-onparse';
		var tpl=repls[tname]?repls:tpls;
		if(tpl[tname]){//depricated
			try{
				if(typeof(tpl[tname])!=='function'){
					var script=this.make(tpls,data,tname,repls,droot);
					tpl[tname]=new Function('data','more',script);
				}
				tpl[tname].apply(data,[datafortpl,mdata]);//this - layer, первый параметр переменная data и more с $key, $index и тд
					//Чтобы после показа подшаблона получить доступ к данным этого подшаблона нужно в функции onparse сохранить ссылку на эти данные и далее в <script></script> уже их использовать
			}catch(e){
				if(infra.DEBUG)alert('Ошибка в шаблоне '+tname+'\n'+e+'\n'+tpl[tname]+'\n'+data);
			}
		}
		var tname=t+'$onparse';
		var tpl=repls[tname]?repls:tpls;
		if(tpl[tname]){
			try{
				if(typeof(tpl[tname])!=='function'){
					var script=this.make(tpls,data,tname,repls,droot);
					tpl[tname]=new Function('data','more',script);
				}
				tpl[tname].apply(data,[datafortpl,mdata]);//this - layer, первый параметр переменная data и more с $key, $index и тд
					//Чтобы после показа подшаблона получить доступ к данным этого подшаблона нужно в функции onparse сохранить ссылку на эти данные и далее в <script></script> уже их использовать
			}catch(e){
				if(infra.DEBUG)alert('Ошибка в шаблоне '+tname+'\n'+e+'\n'+tpl[tname]+'\n'+data);
			}
		}
		
		
		var tname=t+'$css';
		var tpl=repls[tname]?repls:tpls;
		if(tpl[tname]){
			var css=this.make(tpls,data,tname,repls,droot);
			infra.style(css);
			//delete tpl[tname]; Не можем удалять так как елси была подмена в repls оригинального стиял, то при повторной проверки замены уже не будет и выполнится оригинальный.. Тобишь замена должан и дальше оставаться но уже не выполняться
			tpl[tname].length=0;//Не важно откуда этот css пришёл из mix или оригинального шаблона.. он будет удалён и больше не выполниться
		}
		
		var tname=t+'-css';//depricated
		var tpl=repls[tname]?repls:tpls;
		if(tpl[tname]){//depricated
			var css=this.make(tpls,data,tname,repls,droot);
			infra.style(css);
			//delete tpl[tname];
			tpls[tname].length=0;//Не важно откуда этот css пришёл из mix или оригинального шаблона.. он будет удалён и больше не выполниться
		}
		
		
		
		for(var i=0,l=parsed.length;i<l;i++){
			if(typeof(parsed[i])=='object'){
				if(parsed[i].func){
					par=parsed[i];

					var val=this.valEcho(par,data,mdata,data_root);
					res[res.length]=val
					continue;
				}
				if(parsed[i].isquest){//Если условие то выбираем из двух переменных и передаём дальше уже нужную подмену
					
					/*
					   Может быть условие, а может быть условие при передачи объекта шаблону.
					   При передачи переменной шаблону, шаблон не показется если данных нет.
					   */
					if(parsed[i].tplname===undefined||parsed[i].func){//Проверка на верность переменной
						var val=this.valVar(parsed[i].varname,data,mdata,data_root,parsed[i].property);
						var is=this.valCond(val,parsed[i],data,mdata,data_root);
						var val=this.valVarEcho(parsed[i],val,parsed[i].varname);
					}else{//Проверка на наличие шаблона {:tpl?asd} ????
						is=(!!tpls[parsed[i].tplname]||!!repls[parsed[i].tplname]);
					}
					
					if(is){
						if(parsed[i].pipe){
							par=parsed[i];
						}else{
							par=parsed[i].first;
						}
					}else if(parsed[i].pipe){
						if(parsed[i].second){
							var is=this.valVar(parsed[i].first.varname,data,mdata,data_root,parsed[i].property);
							var val=this.valVarEcho(parsed[i].first,is,parsed[i].varname);
							if(is){
								par=parsed[i].first;
							}else{
								par=parsed[i].second;
							}
						}else{
							par=parsed[i].first;
						}
					}else if(parsed[i].second){
						par=parsed[i].second;
					}else{
						continue;
					}
					
				}else{
					par=parsed[i];
				}
				
				if(par.tplname===undefined||par.func){//Если переменная
					var val=this.valEcho(par,data,mdata,data_root);
					res[res.length]=val
				}else{//Если шаблон

					var val=this.valVar(par.varname,data,mdata,data_root,par.property);
					var is=this.valCond(val,par,data,mdata,data_root);//проверяем и пустоту объектов
					if(!is)continue;//Если нет данных проскакиваем дальше
					//var val=this.valVarEcho(par,val);

					if(par.multi){
						
						var index=0;
						var length=0;
						if(typeof(val)=='string'||typeof(val)=='number'){
							var moredata={
								window:window,
								$first:true,
								$last:true,
								$length:1,
								$even:!!(index%2),
								$odd:!(index%2),
								$key:false,
								$deep:mdata.$deep,
								$index:0,
								$number:1,
								$level:mdata.$level+1
							}
							moredata['$length'+moredata.$length]=true;
							moredata['$index'+moredata.$index]=true;
							moredata['$level'+moredata.$level]=true;
							moredata['$deep'+moredata.$deep]=true;
							var vname=[];
							infra.fora(par.varname,function(v){
								vname.push(v);
							});
							res[res.length]=this.make(tpls,data,par.tplname,repls,data_root,moredata,vname);
						//}else if(jslib.isArray(val)){//Бежим по массиву
						}else if(val&&typeof(val)=='object'&&val.constructor===Array){
							length=val.length;
							for(var j=0;j<length;j++){
								var moredata={
									window:window,
									$first:!index,
									$last:((index+1)==length),
									$length:length,
									$even:!!(index%2),
									$odd:!(index%2),
									$key:j,
									$deep:mdata.$deep,
									$index:index++,
									$number:index,
									$level:mdata.$level+1
								}
								moredata['$length'+moredata.$length]=true;
								moredata['$index'+moredata.$index]=true;
								moredata['$level'+moredata.$level]=true;
								moredata['$deep'+moredata.$deep]=true;
								var vname=[];
								infra.fora(par.varname,function(v){
									vname.push(v);
								});
								vname.push(j);
								res[res.length]=this.make(tpls,data,par.tplname,repls,data_root,moredata,vname);
							}
						}else if(typeof(val)=='object'){//Бежим по объекту
							for(var last in val)length++;
							for(var j in val){
								var moredata={
									window:window,
									$first:!index,
									$last:((index+1)==length),
									$length:length,
									$even:!!(index%2),
									$odd:!(index%2),
									$key:j,
									$deep:mdata.$deep,
									$index:index++,
									$number:index,
									$level:mdata.$level+1
								}
								moredata['$length'+moredata.$length]=true;
								moredata['$index'+moredata.$index]=true;
								moredata['$level'+moredata.$level]=true;
								moredata['$deep'+moredata.$deep]=true;
								
								var vname=[];
								infra.fora(par.varname,function(v){
									vname.push(v);
								});
								vname.push(j);
								res[res.length]=this.make(tpls,data,par.tplname,repls,data_root,moredata,vname);
							}
						}else{
							continue;
						}
						
					}else{//Разовый инклуд шаблона
						var vname=[];
						infra.fora(par.varname,function(v){
							vname.push(v);
						});
						res[res.length]=this.make(tpls,data,par.tplname,repls,data_root,mdata,vname);
					}
				}
			}else{
				res[res.length]=parsed[i];
			}
		}
		
		delete mdata['$deep'+mdata.$deep];
		mdata.$deep--;
		mdata['$deep'+mdata.$deep]=true;
		
		var html=res.join('');
		return html;
	}
}
