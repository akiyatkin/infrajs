/*
parse
	make
		 prepare(template); Находим все вставки {}
		 analysis(ar); Бежим по всем скобкам и разбираем их что куда и тп 
			 parseexp('exp')
				parseStaple
				parseCommaVar
					parsevar
		tpls=getTpls(ar) Объект свойства это шаблоны. каждый шаблон это массив элементов в которых описано что с ними делать строка или какая-то подстановка
		res=parseEmptyTpls(tpls);
 
	text=exec(tpls,data,tplroot,dataroot) парсится - подставляются данные выполняется то что указано в элементах массивов
		execTpl
			getValue 				Полностью обрабатывает d
				getCommaVar			Без условий только var tpl fn
					getOnlyVar		Только var tpl
					getVar(conf,d[var])	Только var
					getPath(conf,d[var])	[asdf,[asdf()]] превращает в [['asdf','some']]
 */

 /*
  * условия {asdf?:asdf} {asdf&asdf?:asdf} {asdf|asdf?:asdf}
  * {data:asd{asdf}}
  *
 */
/*
 * url нужен чтобы кэширвоать загрузку. текст передаётся если надо [text]
 * data не кэшируется передаётся объектом
 * tplroot строка что будет корневым шаблоном
 * repls дополнительный массив подстановок.. результат работы getTpls
 * dataroot путь в данных от которых начинается корень данных для первого шаблона
 */
/*
 * Функции берутся в следующем порядке сначало от this в данных потом от корня данных потом в спецколлекции потом в глобальной области
 **/
infra.require('*infra/ext/seq.js');
infra.template={
	store:function(name){
		if(!this.store.data)this.store.data={cache:{}};
		if(!name)return this.store.data;
		if(!this.store.data[name])this.store.data[name]={};
		return this.store.data[name];
	},
	prepare:function(template){
		var start=false;
		var breaks=0;
		var res=[];
		var exp='';
		var str='';
		for(var i=0,l=template.length;i<l;i++){
			var sym=template.charAt(i);
			if(!start){
				if(sym==='{')start=1;
				else str+=sym;
			}else if(start===1){
				if(/\s/.test(sym)){
					start=false;//Игнорируем фигурную скобку если далее пробельный символ
					str+='{'+sym;
				}else{
					start=true;
				}
			}
			if(start===true){
				if(sym==='{')breaks++;
				if(sym==='}')breaks--;
				if(breaks===-1){
					//Текущий символ } выражение закрыто. Есть $str предыдущая строка и $exp строка текущегго выражения
					if(str)res.push(str);
					res.push([exp]);

					breaks=0;
					str='';
					exp='';
					start=false;
				}else{
					exp+=sym;
				}
			}

		}
		if(start===1)str+='{';
		if(str)res.push(str);
		if(exp)res[res.length-1]+='{'+exp;
		return res;
	},
	analysis:function(group){
		/*
		 *  as.df(sdf[as.d()])
		 *  as.df   (  sdf[    as.d  ()    ]  )
		 *  as.df   (  sdf[  ( as.d  ())   ]  )
		 * 'as.df', [ 'sdf[',['as.d',[]] ,']' ]
		 *
		 * 'as.df',[ 'sdf[as.d',[] ],']'
		 * */
		infra.forr(group,function(exp,i){
			if(typeof(exp)=='string')return;
			else exp=exp[0];


			if(exp.charAt(0)=='{'&&exp.charAt(exp.length-1)=='}'){
				group[i]=exp;
				return;
			}
			group[i]=infra.template.parseexp(exp);
			/*
			 * a[b(c)]()
			 * a[(b(c))]()
			 * a[  (b (c))  ] ()
			 * 'a[', ['(b',['(c)'],')',] ,']',['()']
			 * */
			//print_r($group[$i]);

		});
	},
	parse:function(url,data,tplroot,dataroot,tplempty){
		var tpls=this.make(url,tplempty);
		var text=this.exec(tpls,data,tplroot,dataroot);
		return text;
	},
	/*runTpls:function(d,call){
		infra.fora(d,function(d){
			if(d.tpl)call(d.tpl);
			this.runTpls(d.term,call);
			this.runTpls(d.yes,call);
			this.runTpls(d.no,call);
			if(d['var']&&d['var'][0]&&d['var'][0]['orig'])this.runTpls(d['var'][0],call);
		}.bind(this));
	},
	parseEmptyTpls:function(tpls){
		var res=[];
		infra.foro(tpls,function(t){
			infra.template.runTpls(t,function(tpl){
				if(!tpls[tpl]){
					res.unshift(infra.template.make([tpl],tpl));
				}
			});
		});
		res.unshift(tpls);
		return res;
	},*/
	/*parseEmptyTpls:function(tpls){
		var res=[];
		for(var sub in tpls){
			if(!tpls.hasOwnProperty(sub))continue;
			for(var i=0,l=tpls[sub].length;i<l;i++){
				if(!tpls[sub].hasOwnProperty(i))continue;
				if(tpls[sub][i].tpl&&!tpls[tpls[sub][i].tpl]){//Нашли используемый подшаблон, которого нет
					res.unshift(infra.template.make([tpls[sub][i].tpl],tpls[sub][i].tpl));
					//При объединении шаблонов, добавляемые подшаблоны будут с более высоким приоритетом чем те что уже есть, так что не боимся что будет заменён подшаблон, который далее будет добавлен первым как дополнительный
					//Но если дополнительные подшаблоны добавятся как шаблоны по умолчанию, в конец списка, то до таких подшаблонов дело никогда не дойдёт
				}
			}
		}
		res.unshift(tpls);
		return res;
	},*/
	make:function(url,tplempty){//tplempty - имя для подшаблона который будет пустым в документе начнётся без имени
		var stor=this.store();
		//url строка и массив возвращают одну строку и кэш у обоих вариантов будет одинаковый
		if(stor.cache.hasOwnProperty(url.toString()))return stor.cache[url];
		if(typeof(tplempty)!=='string')tplempty='root';
		if(typeof(url)=='string')var template=infra.loadTEXT(url);
		else if(url) var template=url[0];


		var ar=this.prepare(template);
		this.analysis(ar);//[{},'asdfa',{},'asdfa']

		var tpls=this.getTpls(ar,tplempty);//{root:[{},'asdf',{}],'some':['asdf',{}]}
		var some=false;
		for(some in tpls)break;
		if(!some)tpls[tplempty]=[];//Пустой шаблон добавляется когда вообще ничего нет
		//var res=this.parseEmptyTpls(tpls);//[{root:[]}, [{some:[]}], [{asdf:[]}]]  
		var res=tpls;


		stor.cache[url.toString()]=res;
		return res;
	},
	exec:function(tpls,data,tplroot,dataroot){//Только тут нет conf
		if(typeof(tplroot)=='undefined')tplroot='root';
		if(typeof(dataroot)=='undefined')dataroot='';



		dataroot=infra.seq.right(dataroot);
		var conftpl={'tpls':tpls,'data':data,'tplroot':tplroot,'dataroot':dataroot};
		var r=infra.template.getVar(conftpl,dataroot);
		var tpldata=r['value'];
		if(typeof(tpldata)=='undefined'||tpldata===null||tpldata===false||tpldata==='')return '';//Когда нет данных

		var tpl=infra.fora(tpls,function(t){
			return t[tplroot];
		});
		if(!tpl)return tplroot;//Когда нет шаблона

		conftpl['tpl']=tpl;
		//css
		var tplcss=tplroot+'$css';
		var css=infra.fora(tpls,function(t){
			var css=t[tplcss];
			if(css){
				//delete t[tplcss]; Нельзя удалять так как добавляется в див при замене html в этом диве удалится и css инструкция
				return css;
			}
		});
		if(css){
			var conf={'tpls':tpls,'tpl':css,'data':data,'tplroot':tplcss,'dataroot':dataroot};
			css=this.execTpl(conf);
		}
		//
		//
		////parse depricated
		/*var tplsearch=tplroot+'$onparse';
		var search=infra.fora(tpls,function(t){
			var search=t[tplsearch];
			if(search){
				//delete t[tplcss]; Нельзя удалять так как добавляется в див при замене html в этом диве удалится и css инструкция
				return search;
			}
		});
		if(search){
			var conf={'tpls':tpls,'tpl':search,'data':data,'tplroot':tplsearch,'dataroot':dataroot};
			search=this.execTpl(conf);
			if(search){
				try{
					var fn=eval('(function (data){'+search+'})');
					var r=infra.seq.get(data,dataroot);
					fn.apply(r,[data]);//this это относительные данные, data в функции это корневые данные
				}catch(e){
					console.log('onparse: '+e);
				}
			}
		}*/
		//


		var html='';
		if(css) html='<style>'+css+'</style>';

		
		html+=this.execTpl(conftpl);
		return html;
	},
	execTpl:function(conf){
		var html='';

		infra.forr(conf['tpl'],function(d){
			var v=infra.template.getValue(conf,d);
			if(typeof(v)==='string')html+=v;
			if(typeof(v)==='number')html+=v;
			if(v&&typeof(v)==='object'&&v.toString()!=={}.toString()&&!d['term'])html+=v;
			else html+='';
		});
		return html;
	},
	getPath:function(conf,v){//dataroot это прощитанный путь до переменной в котором нет замен
		/*
		 * Функция прощитывает сложный путь
		 * Путь содержит скобки и содежит запятые
		 * asdf[asdf()]
		 * */
		var ar=[];
		infra.forr(v,function(v){//'[asdf,asdf,[asdf],asdf]'
			if(typeof(v)==='string'||typeof(v)==='number'){//name
				ar.push(v);
			}else if(v&&v.constructor===Array&&v[0]&&typeof(v[0]['orig'])!=='undefined'){//name[name().name]
				ar.push(infra.template.getValue(conf,v[0]));
			}else if(v&&typeof(v)=='object'&&typeof(v['orig'])!=='undefined'){//name.name().name



				if(ar.length){
					var temp=v['fn']['var'][0];
					v['fn']['var'][0]=ar.concat(temp);
					//Добавить в fn
				}
				var d=infra.template.getValue(conf,v,true);
				if(ar.length){
					v['fn']['var'][0]=temp;
				}
				var scope=infra.template.scope;
				if(!scope['zinsert'])scope['zinsert']=[];
				var n=scope['zinsert'].length;
				scope['zinsert'][n]=d;

				ar=[];
				ar.push('zinsert');
				ar.push(''+n);
			}else{//name[name.name]
				var r=infra.template.getVar(conf,v);
				ar.push(r['value']);
			}
		});
		return ar;
	},
	getVar:function(conf,v){
		//v содержит вставки по типу ['asdf',['asdf','asdf'],'asdf'] то есть это не одномерный массив. asdf[asdf.asdf].asdf
		var root,value;
		if(v==undefined){
			//if(checklastroot)conf['lastroot']=false;//Афигенная ошибка. получена переменная и далее идём к шаблону переменной для которого нет, узнав об этом lastroot не сбивается и шаблон дальше загружается с переменной в lastroot {$indexOf(:asdf,:s)}{data:descr}{descr:}{}	
			root=false;
			value='';
			return '';
		}else{
			var right=this.getPath(conf,v);//Относительный путь

			var p=infra.seq.right(conf['dataroot'].concat(right));

			var scope=infra.template.scope;
			if(p[p.length-1]=='$key'){
				value=conf['dataroot'][conf['dataroot'].length-1];
				
				if(!scope['kinsert'])scope['kinsert']=[];
				var n=scope['kinsert'].length;
				scope['kinsert'][n]=value;
				root=['kinsert',''+n];
			}else if(p[p.length-1]=='~key'){
				value=conf['dataroot'][conf['dataroot'].length-1];
				
				if(!scope['kinsert'])scope['kinsert']=[];
				var n=scope['kinsert'].length;
				scope['kinsert'][n]=value;
				root=['kinsert',''+n];
			}else{
				var value=infra.seq.get(conf['data'],p);//Относительный путь, от данных
				if(typeof(value)!=='undefined')root=p;

				//Что брать {:t}   от data или scope относительный или прямой путь
				
				if(typeof(value)=='undefined'&&p.length){//Относительный путь, от scope
					value=infra.seq.get(scope,p);
					if(typeof(value)!=='undefined')root=p;
				}

				if(typeof(value)=='undefined'){//Абслютный путь, от данных
					value=infra.seq.get(conf['data'],right);
					if(typeof(value)!=='undefined')root=right;
				}

				if(typeof(value)=='undefined'&&right.length){//Абсолютный путь, от scope
					value=infra.seq.get(scope,right);
					if(typeof(value)!=='undefined')root=right;
				}
				if(typeof(value)=='undefined')root=right;
			}
		}
		return {value:value,root:root};
	},
	/*
	{
		orig:'asdf:asd',//Оригинальное выражение в фигурных скобках
		var:{'somevar','asdf',[1]},//путь до данных для этого подключаемого шаблона

		tpl:'root',//Имя шаблона который нужно подключить в этом месте
		multi:true//Нужно ли для каждого элемента этих данных подключать указанный шаблон

		term:{},//Выражение которое нужно посчитать
		yes:{},
		no:{}

		cond:'s',//тип условия в одном символе = !
		a:{},
		b:{}
	}
	 */
	getCommaVar:function(conf,d,term){
		//Приходит var начиная от запятых в d [[data],[layer,tpl]] (data,layer.tpl)
		if(d['fn']){
			var func=this.getValue(conf,d['fn']);//как у функции сохранить this
			if(typeof(func)=='function'){

				var param=[];
				for(var i=0,l=d['var'].length;i<l;i++){//Количество переменных
					if(!d['var'].hasOwnProperty(i))continue;//когда такое

					if(d['var'][i]&&d['var'][i]['orig']){
						var v=this.getValue(conf,d['var'][i],term);
						param.push(v);
					}else if(d['var']){
						var v=this.getOnlyVar(conf,d,term,i);//Внутри функции требуется если возможно и просто строка имени переменной
						param.push(v);
					}
				} //$param[]=&$conf;
				infra.template.moment=conf;
				return func.apply(this,param);
			}else{
				return null;
				//if(term)return null;
				//else return d['orig'];
			}
		}else{
			var v=this.getOnlyVar(conf,d,term);
			return v;
		}
	},
	getOnlyVar:function(conf,d,term,i){
		if(!i)i=0;
		if(typeof(d['tpl'])=='object'){ //{asdf():tpl}
			var ts=[d['tpl'],conf['tpls']];
			var tpl=this.exec(ts,conf['data'],'root',conf['dataroot']);

			var r=this.getVar(conf,d['var'][i]);
			var v=r['value'];
			var lastroot=r['root']||conf['dataroot'];
			var h=''; 
			if(!d['multi']){
				var droot=lastroot.concat();
				h=this.exec(conf['tpls'],conf['data'],tpl,droot);
			}else{
				infra.foru(v,function(v,k){
					var droot=lastroot.concat([k]);
					h+=infra.template.exec(conf['tpls'],conf['data'],tpl,droot);
				});
			}
			v=h;
		}else{
			var r=this.getVar(conf,d['var'][i]);
			var v=r['value'];
			if(!term&&typeof(v)==='undefined'){
				v='';
			}
		}

		return v;
	},
	test:function(){
		infra.unload('*infra/tests/resources/templates.js');
		infra.require('*infra/tests/resources/templates.js');
		if(infra.template.test.good){
			infra.template.test.apply(this,arguments);
		}else{
			console.log('Ошибка, загрузки тестов');
		}
	},
	getValue:function(conf,d,term){//Передаётся элемент подшаблона
		if(typeof(d)=='string') return d;


		if(d['cond']&&typeof(d['term'])=='undefined'){
			var a=this.getValue(conf,d['a']);
			var b=this.getValue(conf,d['b']);
			if(d['cond']=='='){
				if(typeof(a)=='boolean'||typeof(b)=='boolean'){
					return (!a==!b);
				}else{
					return (a==b);
				}
			}else if(d['cond']=='!'){
				if(typeof(a)=='boolean'||typeof(b)=='boolean'){//Из-за разного поведения в php и в javascript
					return (!a!=!b);
				}else{
					return (a!=b);
				}
			}else if(d['cond']=='>'){
				return (a>b);
			}else if(d['cond']=='<'){
				return (a<b);
			}else{
				return false;
			}
		}else if(typeof(d['var'])!=='undefined'){
			var v=this.getCommaVar(conf,d,term);
			return v;
		}else if(d['term']){
			var v=this.getValue(conf,d['term'],true);
			if(typeof(v)=='undefined'||v===null||v===false||v===''||v===0){
				return this.getValue(conf,d['no'],term);
			}else{
				return this.getValue(conf,d['yes'],term);
			}
		}
	},
	getTpls:function(ar,subtpl){//subtpl - первый подшаблон с которого начинается если конкретно имя не указано
		if(!subtpl)subtpl='root';
		var res={};
		for(var i=0,l=ar.length;i<l;i++){
			if(!ar.hasOwnProperty(i))continue;
			if(typeof(ar[i])=='object'&&ar[i]['template']){
				subtpl=ar[i]['template'];
				res[subtpl]=[];//Для пустых шаблонов, чтобы появился массив, кроме root по умолчанию
				continue;
			};
			if(!res[subtpl])res[subtpl]=[];
			res[subtpl].push(ar[i]);
		}
		infra.foro(res,function(val,subtpl){//Удаляем переход на новую строчку в конце подшаблона
			var t=res[subtpl].length-1;
			var str=res[subtpl][t];
			if(typeof(str)!='string')return;
			res[subtpl][t]=str.replace(/[\r\n\t]+$/g,'');
			//res[subtpl][t]=str.replace(/\s+$/g,'');
		});
		return res;
	},
	replacement:[],
	replacement_ind:[],
	parseStaple:function(exp){
		//С К О Б К И
		//Небыло проверок на функции
		//Если проверка была в выражении передаваемом в функции, то тоже могут быть скобки
		var fn='';
		var fnexp='';
		var start=0;
		var newexp='';
		var specchars=['?','|','&','[',']','{','}','=','!','>','<',':',','];//&
		for(var i=0,l=exp.length;i<l;i++){
			/*
			 * Механизм замен из asdf.asdf(asdf,asdf) получем временную замену xinsert0 и так каждые скобки после обработки в выражении уже нет скобок а замены расчитываются когда до них доходит дело
			 * любые скобки считаются фукнцией функция без имени просто возвращает результат
			 */
			var ch=exp.charAt(i);
			if(ch===')'&&start){
				start--;
				if(!start){

						var k=fn+'('+fnexp+')';
						var insnum=this.replacement_ind[k];
						if(typeof(insnum)=='undefined'){
							insnum=this.replacement.length;
							this.replacement_ind[k]=insnum;
						}

						newexp+='.xinsert'+insnum;
						this.replacement[insnum]=fn;

						//explode(',',$fnexp);//Нельзя там могут быть скобки
						var r=this.parseexp(fnexp,true,fn);
						this.replacement[insnum]=r;
						//Получается переменная значение которой формула а именно функция
						//и мы вставляем сюда сразу да без запоминаний
						fn='';
						fnexp='';
						continue;
				}
			}
			if(start){
				fnexp+=ch;//Определение функции fn(fnexp
			}else{
				if(infra.forr(specchars,function(c){if(c==ch)return true})){
					newexp+=fn+ch;
					fn='';
				}else{
					if(ch!=='(')fn+=ch;//Определение функции fn(
				}
			}

			if(ch==='('){
				start++;
			}
			//else if(!start)newexp+=ch;

		}
		if(newexp)exp=newexp;
		if(newexp&&fn)exp+=fn;
		return exp;
	},
	parseexp:function(exp,term,fnnow){// Приоритет () , ? | & = ! : [] .
		/*
		 * Принимает строку варажения, возвращает сложную форму с orig обязательно
		 */
		var res={};
		res['orig']=exp;
		if(fnnow)res['orig']=fnnow+'('+res['orig']+')';
		else fnnow='';

		
		if(fnnow){
			res['fn']=this.parseBracket(fnnow);//в имени функции могут содержаться замены xinsert asdf[xinsert1].asdf. Запятые в имени не обрабатываются. Массив как с запятыми но нужен только нулевой элемент, запятых не может быть/ Они уже отсеяны
			
		}

			
		


		exp=this.parseStaple(exp);


		//Сюда проходит выражение exp без скобок, с заменами их на псевдо переменные
		var l=exp.length;
		if(l>1&&exp[l-1]===':'&&exp.indexOf(',')===-1){//Определение подшаблона
			res['template']=exp.slice(0,-1);//удалили последний символ
			return res;
		}

		var cond=exp.split(',');

		if(cond.length>1){//Найдена запятая {some,:print}
			res['var']=[];
			infra.forr(cond,function(c){
				res['var'].push(this.parseexp(c,true));
			}.bind(this));
			return res;
		}

		var cond=exp.split('?');
		if(cond.length>1){//Найден вопрос и вопрос до двоеточия {some?data:print} {data:val?int}  {data:val?int}
			var cond0=cond.shift();
			var cond1=cond.shift();
			var cond2=cond.join('?');
			res['cond']=true;
			res['term']=this.parseexp(cond0,true);
			if(cond2){
				res['yes']=this.parseexp(cond1);
				res['no']=this.parseexp(cond2);
			}else{
				res['yes']=this.parseexp(cond1);
				res['no']=this.parseexp('$false');
			}
			return res;
		}

		cond=exp.split('&');//a&b
		if(cond.length>1){
			var cond0=cond.shift();
			var cond1=cond.join('|');
			res['cond']=true;
			res['term']=this.parseexp(cond0,true);
			res['yes']=this.parseexp(cond1);
			res['no']=this.parseexp('$false');
			return res;
		}

		cond=exp.split('|');//a|b
		if(cond.length>1){
			var cond0=cond.shift();
			var cond1=cond.join('|');
			res['cond']=true;
			res['term']=this.parseexp(cond0,true);
			res['yes']=this.parseexp(cond0);
			res['no']=this.parseexp(cond1);
			return res;
		}

		var symbols=['!','=','>','<'];
		var min=false;
		var sym=false;
		for(var i=0,l=symbols.length;i<l;i++){
			if(!symbols.hasOwnProperty(i))continue;
			var s=symbols[i];
			var ind=exp.indexOf(s);
			if(ind===-1)continue;
			if(min===false||ind<min){
				min=ind;
				sym=s;
			}
		}
		if(sym){
			cond=exp.split(sym,3);
			var cond0=cond.shift();
			var cond1=cond.join(sym);
			res['cond']=sym;
			res['a']=this.parseexp(cond0);//a&b|c   (1&0)|1=true  1&(0|1)=true  a&b|c
			res['b']=this.parseexp(cond1);
			return res;
		}
		
		this.parseBracket(exp,res);

		return res;
	},
	parseBracket:function(exp,res){
		
		if(typeof(res)=='undefined'){
			var res={};
			res['orig']=exp;
		}

		res['var']=this.parseCommaVar(exp);
		
		return res;
	},
	parseCommaVar:function(v){//Ищим запятые
		//в выражении var круглых скобок нет они заменены на xinsert (fn())
		//Возвращается массив, элементы либо ещё один главный объект либо массив переменной
		//
		//asdf.asdf,xinsert1,asdf[asdf.asdf][xinsert2]
		//[ ['asdf','asdf'],{'orig':'fn()'}, ['asdf',['asdf','asdf'], {'orig':'fn()'} ] ]
		//
		//Если массив значит скобки, если объект значит сложное выражение в котором могут быть запятые
		//Первый массив - запятые
		//Второй массив - переменная
		//Далее это попадает в infra_template_getVar

		if(v=='')v=[];
		else v=v.split(',');//Запятые могут быть только на первом уровне, все вложенные запятые заменены на xinsert
		var res=[];
		infra.fora(v,function(v){//запятые
			var r=infra.template.parsevar(v);
			res.push(r);
		});
		this.checkInsert(res);
		return res;
	},
	checkInsert:function(rr){
		infra.fora(rr,function(vv,i,group){//точки, скобки
			if(typeof(vv)=='string'){
				var m=vv.match(/^xinsert(\d+)$/);
				if(m){
					group[i]=infra.template.replacement[m[1]];
				}
			}else if(vv&&vv['orig']){
				infra.template.checkInsert(vv['var']);
			}
		});
	},
	parsevar:function(v){//Ищим скобки as.df[asdf[y.t]][qwer][ert]   asdf[asdf][asdf]
		if(v=='')return undefined;
		//Замен xinsert уже нет 
		//asdf.asdf[asdf] На выходе ['asdf','asdf',['asdf']]
		var res=[];

		var start=false;
		var str='';
		var name='';
		var open=0;//Количество вложенных открытий
		for(var i=0,l=v.length;i<l;i++){
			var sym=v.charAt(i);
			//var sym=v[i];
			if(start&&sym===']'){
				if(!open){
					res.push([this.parseexp(name,true)]);
					start=false;
					str='';
					name='';
					continue;
				}else{
					open--;
				}
			}else if(!start){//:[] ищем двоеточее вне скобок
				if(sym==':'){
					var tpl=v.substr(i+1);
					var r={};
					r['orig']=v;
					r['multi']=(tpl.charAt(0)===':');
					if(str) res=res.concat(infra.seq.right(str));

					r['var']=[res];//В переменных к шаблону запятые не обрабатываются. res это массив с одним элементом в котором уже элементов много
					if(r['multi'])tpl=tpl.substr(1);
					r['tpl']=this.make([tpl]);
					if(!r['tpl']['root'])r['tpl']['root']=[''];
					if(!r['tpl']['root$css'])r['tpl']['root$css']=[''];
					if(!r['tpl']['root$onparse'])r['tpl']['root$onshow']=[''];
					return [r];
				}

			}

			if(start)name+=sym;
			if(sym==='['){
				if(start){
					open++;
				}else{
					res=res.concat(infra.seq.right(str));
					start=true;
				}
			}
			if(!start)str+=sym;
		}
		res.push(str);
		var r=[];
		for(var i in res){
			if(!res.hasOwnProperty(i))continue;
			var v=res[i];
			if(typeof(v)=='string'){
				var t=infra.seq.right(v);
				//a.b[b.c][c]
				//[a,b,[b,c],[c]]
				//b,[b,c]
				//b,[b,c]
				for(var e in t){
					if(!t.hasOwnProperty(e))continue;
				       	r.push(t[e]);
				}
			}else{
				r.push(v);
			}
		}
		return r;
	}, 
	scope:{//Набор функций доступных везде ну и значений разных $ - стандартная функция шаблонизатора, которых нет в глобальной области, остальные расширения совпадающие с глобальной областью javascript и в его синтаксисе
		'$typeof':function(v){
			return typeof(v);
		},
		'~typeof':function(v){
			return typeof(v);
		},
		'$true':true,
		'$false':false,
		'~true':true,
		'~false':false,
		'~years':function(start){
			y=new Date().getFullYear();
			if(y==start)return y;
			return start+'&mdash;'+y;
		},
		'~date':function(format,time){
			if(!time)return '';
			if(time===true)time=new Date();
			infra.require('vendor/itlife/phpdate/phpdate.js');
			return phpdate(format,time);
		},
		'$date':function(format,time){
			if(!time)return '';
			if(time===true)time=new Date();
			infra.require('vendor/itlife/phpdate/phpdate.js');
			return phpdate(format,time);
		},
		'$obj':function(){
			return infra.template.scope['~obj'].apply(this,arguments);
		},
		'~obj':function(){//создаём объект {$obj(name1,val1,name2,val2)}
			var args=arguments;
			var obj={};
			for(var i=0,l=args.length;i<l;i=i+2){
				obj[args[i]]=args[i+1];
			}
			return obj;
		},
		'$encode':function(str){
			return infra.template.scope['~encode'](str);
		},
		'~encode':function(str){
			return encodeURIComponent(str);
		},
		'~decode':function(str){
			return decodeURIComponent(str);
		},
		'$length':function(obj){
			return infra.template.scope['~length'](obj);
		},
		'~length':function(obj){
			if(!obj)return 0;
			if(obj.constructor===Array)return obj.length;
			if(obj&&typeof(obj)=='object'){
				var c=0;
				for(var i in obj){
					if(!obj.hasOwnProperty(i))continue;
					c++;
				}
				return c;
			}
			if(obj.length!=undefined)return obj.length;
			return 0;
		},
		'$inArray':function(){
			return infra.template.scope['~inArray'].apply(this,arguments);
		},
		'~inArray':function(val,arr){
			if(!arr)return false;
			if(arr.constructor===Array){
				return !!infra.forr(arr,function(v){
					if(v==val)return true;
				});
			}
			if(typeof(arr)=='object'){
				return !!infra.foro(arr,function(v){
					if(v==val)return true;
				});
			}
		},
		'~_regexps':{},
		'~match':function(exp,val){
			var obj=infra.template.scope['~_regexps'];
			if(!obj[exp])obj[exp]=new RegExp(exp);
			return String(val).match(obj[exp]);
		},
		'~test':function(exp,val){
			var obj=infra.template.scope['~_regexps'];
			if(!obj[exp])obj[exp]=new RegExp(exp);
			return obj[exp].test(String(val));
		},
		'~lower':function(str){
			if(!str)return '';
			return str.toLowerCase();
		},
		'~upper':function(str){
			if(!str)return '';
			return str.toUpperCase();
		},
		'$indexOf':function(){
			return infra.template.scope['~indexOf'].apply(this,arguments);
		},
		'~indexOf':function(str,v){
			str=str.toLowerCase();
			v=v.toLowerCase();
			return str.indexOf(v);
		},
		'~parse':function(str){
			var conf=infra.template.moment;
			if(!str)return '';
			var res=infra.template.parse([str],conf.data,'root',conf['dataroot'],'root');//(url,data,tplroot,dataroot,tplempty){
			return res;
		},
		'$last':function(){
			return infra.template.scope['~last']();
		},
		'~last':function(){
			var conf=infra.template.moment;
			var dataroot=conf['dataroot'].concat();
			var key=dataroot.pop();
			var obj=infra.seq.get(conf['data'],dataroot);

			var k;
			infra.foru(obj,function(v,key){
				k=key;
			});
			return (k===key);
		},
		'$words':function(){
			return infra.template.scope['~words'].apply(this,arguments);
		},
		'~words':function(count,one,two,five){
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
		},
		'$leftOver':function(a,b){
			return infra.template.scope['~leftOver'](a,b);
		},
		'~leftOver':function(first,second){//Кратное
			first=Number(first);
			second=Number(second);
			return first%second;
		},
		'$sum':function(a,b,c,d){
			a=Number(a);
			b=Number(b);
			c=Number(c);
			d=Number(d);
			return infra.template.scope['~sum'](a,b,c,d);
		},
		'~sum':function(){
			var args=arguments;
			var n=0;
			for(i=0,l=args.length;i<l;i++) n+=Number(args[i]);
			return n;
		},
		'~array':function(){
			var args=arguments;
			var ar=[];
			for(i=0,l=args.length;i<l;i++) ar.push(args[i]);
			return ar;
		},
		'~multi':function(){
			var args=arguments;
			var n=1;
			for(i=0,l=args.length;i<l;i++) n*=Number(args[i]);
			return n;
		},
		'$even':function(){
			return infra.template.scope['~even'].apply(this,arguments);
		},
		'~even':function(){
			var conf=infra.template.moment;
			var dataroot=conf['dataroot'].concat();
			var key=dataroot.pop();
			var obj=infra.seq.get(conf['data'],dataroot);

			var even=1;
			infra.foru(obj,function(v,k){
				if(k==key)return false;
				even=even*-1;
			});
			return (even==1);
		},
		'$odd':function(){
			return infra.template.scope['~odd'].apply(this,arguments);
		},

		'~odd':function(){
			return !infra.template.scope['~even']();
		},
		'$first':function(){
			return infra.template.scope['~first'].apply(this,arguments);
		},
		'~first':function(){
			var conf=infra.template.moment;
			var dataroot=conf['dataroot'].concat();
			var key=dataroot.pop();
			var obj=infra.seq.get(conf['data'],dataroot);
			return infra.foru(obj,function(v,k){
				if(k=key)return true;
				return false;
			});
		},
		'$Number':function(){
			return infra.template.scope['~Number'].apply(this,arguments);
		},
		'~Number':function(key,def){
			var n=Number(key);
			if(!n&&n!=0)n=def;
			return n;
		},
		'~cost':function(cost,text){
			

			if(!cost&&cost!=0)cost='';
			cost=String(cost);

			var ar=cost.split(/[,\.]/);
			if(ar.length>=2){
				var cost=ar[0];
				var cop=ar[1];
				if(cop.length==1){
					cop+='0';
				}
				if(cop.length>2){
					cop=cop.substring(0,3);
					cop=Math.round(cop/10);
				}
				if(cop=='00')cop='';

			}

			if(text)inp=' ';
			else inp='&nbsp;';
			
			if(cost.length>4){ //1000
				var l=cost.length;
				cost=cost.substr(0,l-3)+inp+cost.substr(l-3,l);
			}

			if(cop){
				if(text)cost=cost+','+cop;
				else cost=cost+'<small>,'+cop+'</small>';
			}

			return cost;
		},
		"infra":{
			"theme":function(path){
				return infra.theme(path);
			},
			"seq":{
				"short":infra.seq.short,
				"right":infra.seq.right
			},
			'srcinfo':infra.srcinfo,
			'conf':infra.conf,
			'view':{
				getPath:function(){
					return infra.view.getPath.apply(infra.view,arguments)
				},
				getHost:function(){
					return infra.view.getHost.apply(infra.view,arguments)
				},
				getRoot:function(){
					return infra.view.getRoot.apply(infra.view,arguments)
				}
			}
		},
		'location':location
	}
 }
	/**/
