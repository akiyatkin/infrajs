/*
* xls объект с методами для работы с xls документами. 
*
* Помимо получения данных в первозданном виде, 
* модуль также реализует определённый синтаксис в Excel для построения иерархичной структуры с данными.
*
* **Подключение**

	var xls=infra.load('*files/xls.sjs');

* **Использование**

	//Получаем данные из Excel "как есть"
	var data=xls.parse('*Главное меню.xls');
	//или
	var data=xls.make('*Главное меню.xls');
	//Создаём объект с вложенными группами root->book->sheet данные на страницах ещё не изменялись, 
	//но сгрупировались
	//descr - всё что до head
	//head - первая строка в которой больше 2х заполненых ячеек
	//data - всё что после head
	xls.processDescr(data);//descr приводится к виду ключ значение
	xls.run(data,function(group){//Бежим по всем группам
		delete group.parent;//Удалили рекурсивное свойсто parent
		for(var i=0,l=group.data.length;i<l;i++){
			var pos=group.data[i];
			delete pos.group;//Удалили рекурсивное свойсто group
		}
	});

	
	
* Требуется node-csv и системная утилита xls2csv (perl)
*/	
if(typeof(ROOT)=='undefined')var ROOT='../../../';
if(typeof(infra)=='undefined')require(ROOT+'infra/plugins/infra/infra.js');
infra.load('*infra/default.js','r');//system forr admin mail
var pathlib=require('path');
var util=require('util');
var csv=require('node-csv');
var crypto=require('crypto');
var fs=require('fs');
csv=csv.createParser(',','"','"');
var xls={
	/* Список листов в документе по адресу path */
	list:function(path){
		return infra.cache([path],this._list,[path]);
	},
	_list:function(pathh){
		var path=infra.theme(pathh);
		if(!path)return 'Некорректный путь '+pathh;
		
		var com='xls2csv -b cp1251 -a UTF-8 -x "'+__dirname+'/'+ROOT+path+'" -W';
		var res=infra.system(com);
		res=res.split('\n');
		res.splice(0,4);
		res.splice(res.length-2,res.length-1);
		return res;
	},
/* Данные с листа list в документе по адресу path. Если list не передан вернуться данные с первого листа
* [['row1cel1','row1cel2'],['row2cel1','row2cel2']]
*/
	parse:function(path,list){
		//return this._parse(path,list);
		return infra.cache([path],this._parse,[path,list]);
	},
	_parse:function(pathh,list){//path - путь до xls документа
		var path=infra.theme(pathh);
		if(!path)return 'Некорректный путь '+pathh;
	
		var str=path+list;
		var md5=crypto.createHash('md5');
		md5.update(str);
		var md5=md5.digest('hex');
		
		var tf='infra/cache/files/csv/'+md5;
		var cmd='xls2csv -b cp1251 -a UTF-8 -x "'+__dirname+'/'+ROOT+path+'" -c "'+__dirname+'/'+ROOT+tf+'"';
		if(list)cmd+=' -w "'+list+'"';
		infra.system(cmd);
		
		//console.log(r);
		var res=infra.sync(fs,fs.readFile)(__dirname+'/'+ROOT+tf,'UTF-8');

		if(!res) {
			console.log('Ошибка xls2csv пустой результат (консольная команда записала в файл, а файла нет либо он пустой. Возможно нет нужных папок и нужно перезапустить node.'+tf);
			return [];//[['Артикул','Наименование','Описание'],['Ошибка xls2csv вернул пустой результат'],[tf,cmd,r]];
		}
		res=csv.parse(res);
		var r=infra.sync(fs,fs.unlink)(__dirname+'/'+ROOT+tf);
		return res;
	},
	/*
		Только для одного документа - парсит все листы. 
		Возвращается объект {list1:[['cel','cel'],['cel','cel']],list2:[]} каждый list это результат работы parse
	*/
	parseAll:function(path){
		
		var res=xls.list(path);
		var data={};

		infra.fora(res,function(list){
			data[list]=xls.parse(path,list);
		});
		return data;
	},
	_createGroup:function(title,parent,type,row){
		if(!title)title='';

		var tparam='';
		var descr=[];
		var miss=false;
		var t=title.split(':');
		if(!t[0]){
			t.shift();
			title=t.join(':');
			if(!infra.forr(parent.descr,function(row){
				if(row[0]=='Описание'){
					row[1]+='<br>'+title;
					return true;
				}
			})){
				parent.descr.push(['Описание',title]);
			}
			return false;
		}else{
			if(t.length>1){
				title=t[0];
				if(title=='Производитель'){
					title=t[1];
					tparam='';
					descr.push(['Производитель',title]);
					miss=true;
				}else{
					tparam=t[1];
				}
			}
		}
		title=title.replace(/["+']/g,' ');
		title=title.replace(/[\/\\]/g,'');
		title=title.replace(/^\s+/g,'');
		title=title.replace(/\s+$/g,'');
		title=title.replace(/\s+/g,' ');
		// title=title.toUpperCase();

		return {
			tparam:tparam,
			groups:false,//Количество групп вместе с текущей
			count:false,
			row:row,//Вся строка группы
			miss:miss,//Группу надо расформировать, но мы не знаем ещё есть ли в ней позиции
			type:type,parent:parent,
			title:String(title),
			head:[],descr:descr,data:[],childs:[]
		};
	},
	
/*
* Cоздание объекта из данных переданных в Excel. Получается:
*
*	var data={
		
		childs:[],
		title:'',
		descr:[],
		head:[],
		data:[]
		...
	}

*descr
*ПГПЯ (Признак группы пустая ячейка) - указывается номер ячейки, которая если будет пустой, то вся строка засчитается группой. 1,2,3
*
* @param {string} path путь до Excel файла с данными
* @return {object}
*/
	make:function(path){

		var data=this.parseAll(path);
		if(!data)return;
		var title=pathlib.basename(path,pathlib.extname(path));
		title=title.replace(/^\d*\s*/,'');
		var groups=this._createGroup(title,false,'book');
		infra.foro(data,function(data,title){
			if(title.charAt(0)=='.')return;//Не применяем лист у которого точка в начале имени
			var group=xls._createGroup(title,groups,'list');
			if(!group)return;
			groups.childs.push(group);
			
			var head=false;//Заголовки ещё не нашли
			var wasdata=false;//Были ли до этого данные
			var wasgroup=false;
			//var empty=0;//Количество пустых строк
			var pgpy=false;//Признак группы пустая ячейка в строке... а этом свойстве будет номер ПГПЯ
			infra.forr(data,function(row,i){//Бежим по строкам 
				var count=0;
				infra.forr(row,function(cell){if(cell)count++});
				//if(row[0].charAt(0)=='.')return false;//Строки с точкой в начале пропускаем
				if(!head){
					infra.forr(row,function(val,i,b){
						b[i]=b[i].replace(/\s+$/,'');
						b[i]=b[i].replace(/^\s+/,'');
					});
					head=(count>2);//Больше 2х не пустых ячеек будет заголовком

					if(head){//Текущий row и есть заголовок
						group.head=row;
					}else{
						if(row[0]=='ПГПЯ'){
							pgpy=row[1]-1;//Номер пустой ячейки
						}else{
							if(row[0])group.descr.push(row);
						}
					}
				}else{
					
					var isnewgroup=(row[0]&&(count==1)&&row[0].length!==1);//Если есть только первая ячейка и та длинее одного символа
					if(!isnewgroup&&pgpy&&row[0].length!==1){
						isnewgroup=!row[pgpy];
					}
					
					
						
					//	infra.bug(row);
					//}
					if(isnewgroup){
						var parent=(wasdata&&group.parent)?group.parent:group;//Если уже были данные то поднимаемся наверх
						var g=xls._createGroup(row[0],parent,'row',row);//Создаём новую группу
						if(!g)return;
						wasgroup=true;
						wasdata=false;
						
						//g.descr=g.parent.descr.concat(g.descr);
						
						g.head=g.parent.head;
						g.parent.childs.push(g);
						group=g;//Теперь ссылка на новую группу и следующие данные будут добавляться в неё
					}else{
						if(count==1&&row[0].length==1){
							if(group.parent&&group.parent.parent)group=group.parent;
						}else{
							wasdata=true;
							group.data.push(row);
						}
					}
				}
			});
		});
		return groups;
	},
/*  
* Функция для обработки всех групп в иерархии созданной функцией make 
*
* Используется data.childs
*
* @param {Оbject} data Объект после make.
* @param {Function} callback обработчик для всех групп. Первый аргумент группа.
* @param {bool} back направление обхода data
* @return {mix} первый не undefined return из callback
*/
	run:function(data,callback,back,i,group){
		if(!back){
			var r=callback(data,i,group);
			if(typeof(r)!='undefined')return r;
		}
		var r=infra.forr(data.childs,function(val,i,group){
			return this.run(val,callback,back,i,group);
		}.bind(this),back);
		if(typeof(r)!='undefined')return r;
		
		if(back){
			var r=callback(data,i,group);
			if(typeof(r)!='undefined')return r;
		}
	},
/*  
* Колонки с точкой скрываем, позиции у которых параметры начинаюстя с точки скрываем.
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processPoss:function(data){//
		//используется data head
		var head=false;
		this.run(data,function(data){	
			if(data.head.length)head=data.head;
			infra.forr(data.data,function(pos,i,group){
				var p={};
				infra.forr(pos,function(propvalue,i){
					var propname=head[i];
					if(!propname)return;
					if(propname.charAt(0)=='.')return;//Колонки с точкой скрыты
					if(propvalue=='')return;
					if(propvalue.charAt(0)=='.')return;//Позиции у которых параметры начинаются с точки скрыты
					p[propname]=propvalue;
				});
				p['group']=data;
				group[i]=p;
			});
			delete data.head;
		});
	},
/*  
* Удаляем позиции, у которых нет указанных свойств props.
* Используется data.data
*
* @param {object} data Объект после make.
* @param {array} props массив свойств, которые обязательно должны быть у позиции, иначе позиция удаляется.
* @return {object} data
*/
	processPossFilter:function(data,props){//Если Нет какого-то свойства не учитываем позицию

		this.run(data,function(data){	
			var d=[];
			infra.forr(data.data,function(pos){
				if(!infra.forr(props,function(name){
					if(typeof(pos[name])=='undefined')return true;
				})){
					d.push(pos);
				}
			});
			data.data=d;
		});
	},
/*  
* Каждой позиции добавляется свойство path массив групп в которых вложена данная позиция.включается группа в которой позиция непосредственно находится
*
* Используется data.path, data.data
* Добавляется data.data.path
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processPossPath:function(data){//
		//используется path групп
		this.run(data,function(data){	
			infra.forr(data.data,function(pos){
				pos.path=[];
				infra.forr(data.path,function(n){
					pos.path.push(n);
				});
				pos.path.push(data.title);
			});
		});
	},
/*  
* Если у позиции нет поля check1.. то оно будет равнятся полю check2
*
* Используется data.data
*
* @param {object} data Объект после make.
* @param {string} check1 - если это свойства у позиции будет undefined то оно станет равнятся значению свойства check2
* @param {string} check2 - если это свойства у позиции будет undefined то оно станет равнятся значению свойства check1
* @return {object} data
*/
	processPossBe:function(data,check1,check2){//Если у позиции нет поля check1.. то оно будет равнятся полю check2
		//используется data
		this.run(data,function(data){	
			infra.forr(data.data,function(pos){
				if(typeof(pos[check1])=='undefined')pos[check1]=pos[check2];
				if(typeof(pos[check2])=='undefined')pos[check2]=pos[check1];
			});
		});
	},
/*  
* Приводит строку необратимо к виду который можно использовать в адресной строке или в файловой системе. Заменяются двойные пробелы, символы \:/.
*
* @param {string} str строка, которую нужно привести к подходящему виду
* @return {string}
*/
	forFS:function(str){
		str=str.replace(/['"\:\/\\\\#\$&]/g,' ');//Заменяем и точку, чтобы можно было сохранять в сессии как ключ
		str=str.replace(/^\s+/g,'');
		str=str.replace(/\s+$/g,'');
		str=str.replace(/\s+/g,' ');
		return str;
	},
/*  
* Указанные свойства props готовятся для FS
*
* Используется data.data
*
* @param {object} data Объект после make.
* @param {array} props свойства у позиций, которые нужно пропустить через forFS
* @return {object} data
*/
	processPossFS:function(data,props){
		this.run(data,function(data){	
			infra.forr(data.data,function(pos,i,group){
				infra.fora(props,function(name){
					pos[name]=this.forFS(pos[name]);
				}.bind(this));
			}.bind(this));
		}.bind(this));
	},
/*  
* Все свойства кроме указанных в props перемещаются во вложенное свойство-объект more. Используется чтобы всё неизвестное собрать в одном место.
*
* Используется data.data
* Добавляется data.data.more
*
* @param {object} data Объект после make.
* @param {array} props известные свойства у позиций
* @return {object} data
*/
	processPossMore:function(data,props){
		this.run(data,function(data){	
			infra.forr(data.data,function(pos,i,group){
				var p={};
				var more={};				
				
				group[i]=p;
				
				var prop={};
				infra.forr(props,function(name){
					prop[name]=true;
				});
				
				infra.foro(pos,function(val,name){
					if(prop[name])p[name]=val;
					else more[name]=val;
				});
				p['more']=more;
				group[i]=p;
			});
		});
	},
/*  
* Всех детей из группы addgr перенести в gr. Перенести все descr. Перенести все data. У позиций изменяется ссылка group. У групп изменяется ссылка parent
* Группа addgr удаляется из иерархии
*
* Используется data.data, data.childs, data.descr, data.parent, data.tparam
*
* @param {object} gr группа реципиент
* @param {array} addgr группа доннар
* @return {object} data
*/
	merge:function(gr,addgr){

		var list=addgr.parent.childs;
		var i=infra.forr(list,function(v,i){if(v===addgr)return i});
		list.splice(i,1);//Удалили addgr там где группа была до этоо, заменив на новую
		
		infra.forr(addgr.childs,function(val){
			val.parent=gr;
			gr.childs.push(val);
		});
		infra.fori(addgr.descr,function(des,key){
			if(typeof(gr.descr[key])=='undefined'){
				gr.descr[key]=des;
			};
		});
		gr.tparam+=','+addgr.tparam;
		infra.forr(addgr.data,function(val){
			val.group=gr;
			gr.data.push(val);
		});
	},
/*  
* Бежим и запоминаем группы, как только нашли совпадение, перенесли все позиции в первую, старую группу удалили (merge)
*
* Используется data.data, data.childs, data.descr, data.parent, data.tparam, data.title
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processGroupFilter:function(data){
		var all={};
		this.run(data,function(gr,i,group){
			if(!all[gr.title]){
				all[gr.title]=gr;
			}else{//Ну вот и нашли повторение
				//var group=all[gr.title].parent.childs
				//var i=infra.forr(group,function(v,i){if(v===all[gr.title])return i});
				//group.splice(i,1);
				
				this.merge(gr,all[gr.title]);//Добавляем в первое совпадение
				all[gr.title]=gr;
			}
		}.bind(this),true);

		this.run(data,function(gr,i,group){//Удаляем пустые группы
			if(!group){
				return;//Кроме верхней группы
			}
			if(!gr.childs.length&&!gr.data.length){
				group.splice(i,1);
			}
		},true);
	},
/* Приводит descr к ключ значение  
*
* Используется data.descr, когда ещё массив
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processDescr:function(data){//
		this.run(data,function(gr){
			var descr={};
			infra.forr(gr.descr,function(row){
				descr[row[0]]=row[1];
			});
			gr.descr=descr;
		}.bind(this));
	},
	processRemoveParent:function(data){//depricated
		this.run(data,function(d){
			delete d.parent;
		});
	},
/* Добавляется количество групп (groups) и количество позиций (count). В количество групп входит и сама группа, если вложенных групп нет будет 1
*
* Используется data.childs, data.data
* Добавляется data.count, data.groups
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processGroupCalculate:function(data){
		this.run(data,function(data){
			data.count=data.data.length;
			data.groups=1;
			infra.forr(data.childs,function(d){
				data.count+=d.count;
				data.groups+=d.groups;
			});
		},true);
	},
/* Добавляется количество групп (groups) и количество позиций (count). В количество групп входит и сама группа, если вложенных групп нет будет 1
*
* Используется data.childs, data.parent
* Добавляется data.path
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processGroupPath:function(data){
		this.run(data,function(data){
			data.path=[];
			if(data.parent&&data.parent.parent){
				infra.forr(data.parent.path,function(name){
					data.path.push(name);
				});
				data.path.push(data.parent.title);
			}
		});
	},
/*
* Класс=Производитель
* 
* Используются data.title
* Добавляются свойства data.miss
*			
* у одной книги (имя книги)
* на одном листе (descr, значение)
* у каждой позиции (Колонка, значение в ячейке)
* После обработки класс будет указан у каждой ячейки как новое свойство.
* Листы, которые являются классами должны быть расформированы как группы, например лист KEMPPI после обозначения у всех позиций как группа будет удалён (miss) а все группы в листе уйдут на уровень вверх.
*
* @param {object} data Объект после make.
* @param {string} clsname имя свойства которое будет считаться классом, например "Производитель".
* @param (bool) musthave если производитель не найден будет взято имя книги, иначе имя должно быть в строке колонки clsname, или в descr листа под имененем clsname
* @return {object} data
*/
	processClass:function(data,clsname,musthave){
		
		var that=this;
		var run=function(data,clsvalue){
			if(data.type=='book'&&musthave){
				data.miss=true;
				clsvalue=that.forFS(data.title);
			}else if(data.type=='list'&&data.descr[clsname]){//Если в descr указан класс то имя листа игнорируется иначе это будет группой каталога, а классом будет считаться имя книги
				data.miss=true;//Если у листа есть позиции без группы он не расформировывается
				clsvalue=that.forFS(data.descr[clsname]);
			}else if(data.type=='row'&&data.descr[clsname]){
				clsvalue=that.forFS(data.descr[clsname]);
			}
			
			infra.forr(data.data,function(pos,i,group){
				if(!pos[clsname]){
					pos[clsname]=clsvalue;//У позиции будет установлен ближайший класс
				}else{
					pos[clsname]=that.forFS(pos[clsname]);
				}
			});
			
			infra.forr(data.childs,function(data,i,group){
				run(data,clsvalue);
			});
			

		}
		run(data);
		
		return data;
	},
/*
* Удаляются группы с меткой miss. Все подгруппы и позиции перемещаются на уровень вверх.
* 
* Используются data.childs, data.parent, data.data
*			
*
* @param {object} data Объект после make.
* @return {object} data
*/
	processGroupMiss:function(data){
		var arr=[];
		this.run(data,function(gr,i,neighbours){
			if(gr.miss&&gr.parent&&!gr.data.length){
				infra.forr(gr.childs,function(d){
					neighbours.splice(i++,0,d);
					d.parent=gr.parent;
				});
				arr.unshift(gr);
			}
		},true);//Если бежим вперёд повторы несколько раз находим, так как добавляем в конец// Если бежим сзади рушится порядок
		infra.forr(arr,function(gr){
			var i=infra.forr(gr.parent.childs,function(val,i){if(val===gr)return i;});
			if(typeof(i)!='undefined'){
				gr.parent.childs.splice(i,1);
			}
			infra.forr(gr.data,function(d){
				d.group=gr.parent;
				gr.parent.data.push(d);
			});
		},true);
		
		
		//infra.bug(data);
		//this.run(data,function(gr,i,group){
		//	console.log(group);
		//	if(gr.del){
		//		
		//		group.splice(i,1);
		//	}
		//},true);
	},
	_sort:function(a,b){
		return (a < b) ? -1 : (a > b) ? 1 : 0;
	},
	_sortName:function(a,b){
		
		a=a['Наименование'];
		b=b['Наименование'];

		return (a < b) ? -1 : (a > b) ? 1 : 0;
	},
/*
Подготовка списка к выводу на странице, фильтр сортировка и отбор из массива согласно текущей странице, сортировки и тп.

Вход
- полный список позиций в сортировке по умолчанию
- сортировка
- страница
- Количество позиций на странице
- количество показываемых номеров страниц

Выход
- показанная страница
- выполненная сортировк
- номер последней странице.
- список из номеров страниц, которые нужно показать.
- список позиций poss

* 
* Используются data.childs, data.parent, data.data, data.data.Наименование (для sort=name)
*			
* return {
	show:array,//Список страниц
	page:int,//Текущая страница
	sort:str,//сортировка
	list:array,//Список позиций на выбранной странице
	pages:int//Всего страниц
}
*
* @param {array} poss массив чего-то, например data.data
* @param {int} page текущая страница
* @param {int} count элементов на странице
* @param {string} sort 'name' или 'def'
* @param {int} numbers количество номеров страниц 4,5,|6|,7,8
* @return {object} r
*/
	preparePossPage:function(poss,page,count,sort,numbers){
		var all=poss.length;
		var pages=Math.ceil(all/count);
		if(page>pages)page=pages;
		if(page<1)page=1;
		if(numbers<1)numbers=1;
		numbers--;
		//page pages numbers first last
		var first=Math.floor(numbers/2);
		var tfirst=first;
		var last=numbers-first;
		var show=[];

		while(tfirst){
			var p=page-tfirst;
			if(p<1){
				last++;
				first--;
			}
			tfirst--;
		}
		while(last){
			var p=page+last;
			if(p<=pages){
				show.push(p);
			}else{
				first++;
			}	
			last--;
		}
		while(first){
			var p=page-first;
			if(p>0){
				show.push(p);
			}
			first--;
		}
		show.push(page);
		show.sort(this._sort);

		if(sort=='name'){
			poss.sort(this._sortName);
		}
		infra.forr(poss,function(p,i){
			p.num=i+1;
		});
		var r={
			show:show,//Список страниц
			page:page,//Текущая страница
			sort:sort,//сортировка
			list:[],//Список позиций на выбранной странице
			pages:pages//Всего страниц
		};
	
		var start=(page*count-count);
		for(var i=start,l=start+count;i<l;i++){
			var p=poss[i];
			if(!p)break;
			r.list.push(p);
			delete p.group;
		}

		return r;
	},
/*
* Разбор папки с данными у позиции pos
*
* Добавляются data.data.images, data.data.texts, data.data.files
* Используется то что указано в props
*
* @param {int} pos - позиция из объекта data после make
* @param {string} pth папка каталога например 'infra/data/Каталог/'
* @param {int} props список свойств по порядку которые определяют путь до папки позиции например ['Производитель','Артикул']
* @return {object} r
*/
	preparePosFiles:function(pos,pth,props){
		pos.images=[];
		pos.texts=[];
		pos.files=[];
		var dir=[];
		if(infra.forr(props,function(name){
			if(!pos[name])return true;
			dir.push(pos[name]);
		})){
			if(callback)callback();			
			return;
		}
		dir=dir.join('/')+'/';
		dir=pth+dir;
		dir=infra.theme(dir,'d');
		if(!dir) return false;

		var paths=infra.sync(fs,fs.readdir)(__dirname+'/'+ROOT+dir)||[];
		paths.sort();
		infra.forr(paths,function(p){
			var ext=pathlib.extname(p).replace('.','');
			if(infra.forr(['png','gif','jpg'],function(e){if(ext.toLowerCase()==e)return true})){
				pos.images.push(p);
			}else if(infra.forr(['html','tpl','mht'],function(e){if(ext.toLowerCase()==e)return true})){
				pos.texts.push(p);
			}else{
				pos.files.push(p);
			}
		});
	},
/*
* Возвращает полностью гототовый массив для каталога. Содержит рекурсивные функции data.parent и data.data.group
*
* @param {mix} path путь до папки с файлами или путь до файла или массив того и другова. 
* @return {object} r
*/
	init:function(path,musthaveproducers){//Возвращает полностью гототовый массив
		var config={};
		if(typeof(musthaveproducers)=='object')config=musthaveproducers;

		if(typeof(musthaveproducers)=='undefined')musthaveproducers=true;
		if(typeof(path)=='object'&&path.constructor!=Array)return path;
		var data=this._createGroup('Каталог',false,'set');//Сделали группу в которую объединяются все остальные
		data.miss=true;//Если в группе будет только одна подгруппа она удалится... подгруппа поднимится на уровень выше
		
		var ar=[];		
		infra.fora(path,function(path){
			if(infra.theme(path,'f')){
				ar.push(path);
			}else if(infra.theme(path,'d')){
				var files=infra.sync(fs,fs.readdir)(__dirname+'/'+ROOT+infra.theme(path,'d'));
				files.sort();
				infra.forr(files,function(file){
					if(file[0]=='.')return;
					var ext=pathlib.extname(file);
					if(ext=='.xls')ar.push(path+file);
				});
			}
		}.bind(this));
		
		infra.forr(ar,function(path){
			var d=this.make(path);
			if(!d)return;
			d.parent=data;
			data.childs.push(d);
		}.bind(this));


		//if(data.childs.length==1)data=data.childs[0];

		this.processDescr(data);
		
		this.processPoss(data);


		this.processClass(data,'Производитель',musthaveproducers);//Должен быть обязательно

		this.processPossBe(data,'Артикул','Наименование');//Если есть что-то из этого то второе будет такимже если второго нет

		this.processPossFilter(data,['Артикул']);//Обязательно должны быть
		this.processPossBe(data,'Артикул','Наименование');//Если есть что-то из этого то второе будет такимже если второго нет
		this.processPossFS(data,'Артикул');//Заменяем левые символы в свойстве

		//infra.forr(data.childs,function(d){console.log(d.title)});
		//this.run(data,function(d){ console.log(d.descr)});
		this.processGroupMiss(data);//Группы miss(производители) расформировываются
		//this.run(data,function(d){ infra.bug(d.title) });		
		if(config.upper){
			xls.run(data,function(group){
				group.title=String(group.title).toUpperCase();
			});
		}

		this.processGroupFilter(data);//Объединяются группы с одинаковым именем, Удаляются пустые группы
		
		this.processGroupCalculate(data);//Добавляются свойства count groups сколько позиций и групп группы должны быть уже определены... почищены...				
		
		this.processGroupPath(data);
		this.processPossPath(data);

		this.processPossMore(data,['path','group','Производитель','Наименование','Описание','Артикул']);//позициям + more		

		return data;
	},

	group:function(path,name){//depricated
		var data=this.init(path);
		
		var d=this.run(data,function(data){
			if(data.title==name){
				return data;
			}
		});
		return d;
	},
/*
* Возвращает подгруппы [{title:,descr:,groups:,count:,childs:},...]
*
* @param {mix} path путь до папки с файлами или путь до файла или массив того и другова.
* @param {string} name имя группы если не указано значит первый уровень
* @return {array} groups
*/
	groups:function(path,name){//depricated Возвращает группы первого уровня
		var data=this.init(path);

		var d=this.run(data,function(data){
			if(data.title==name){
				return data;
			}
		});
		if(!d)d=data;

		d={title:String(d.title),descr:d.descr,groups:d.groups,count:d.count,childs:d.childs}
		infra.forr(d.childs,function(d,i,group){
			group[i]={title:d.title,descr:d.descr,groups:d.groups,count:d.count}
		});
		return d;
	},
	pos:function(path,prod,art){//depricated
		return infra.cache(path,this._pos.bind(this),[path,prod,art]);
	},
	_pos:function(path,prod,art){
		var data=this.init(path);
		return this.run(data,function(data){
			return infra.forr(data.data,function(pos){
				if(pos['Производитель']==prod&&pos['Артикул']==art){
					delete pos.group.data;
					delete pos.group.childs;
					delete pos.group.parent;
					return pos;
				}
			});
		});
	},
/*
* возвращает структуру каталога, без данных
*
* @param {mix} path путь до папки с файлами или путь до файла или массив того и другова.
* @return {object} group
*/
	struct:function(path){//возвращает структуру, без данных
		var data=xls.init(path);
		res={childs:{}};
		
		var run=function(data,res){
			if(data.title){
				res.childs[data.title]={type:data.type,data:data.data.length,childs:{}};
				var r=res.childs[data.title];
			}else{
				var r=res;
			}
		
			infra.forr(data.childs,function(val,i,group){
				run(val,r);
			}.bind(this));
		}
		run(data,res);
		
		return res;
	}
}
module.exports=xls;

