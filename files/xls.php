<?php
/*
* xls методы для работы с xls документами. 
*
* Помимо получения данных в первозданном виде, 
* модуль также реализует определённый синтаксис в Excel для построения иерархичной структуры с данными.
*
* **Подключение**

	infra_require('*files/xls.php');

* **Использование**

	//Получаем данные из Excel "как есть"
	$data=xls_parse('*Главное меню.xls');
	//или
	$data=xls_make('*Главное меню.xls');
	//Создаём объект с вложенными группами root->book->sheet данные на страницах ещё не изменялись, 
	//но сгрупировались
	//descr - всё что до head
	//head - первая строка в которой больше 2х заполненых ячеек
	//data - всё что после head
	xls_processDescr($data);//descr приводится к виду ключ значение
	xls_run($data,function($group){//Бежим по всем группам
		unset($group['parent']);//Удалили рекурсивное свойсто parent
		for($i=0,$l=sizeof($group['data']);$i<$l;$i++){
			$pos=$group['data'][$i];
			unset($pos['group']);//Удалили рекурсивное свойсто group
		}
	});
	$data=xls_init(path,conf)
*/	



infra_require('*infra/ext/seq.php');

/*var pathlib=require('path');
var util=require('util');
var csv=require('node-csv');
var crypto=require('crypto');
var fs=require('fs');
csv=csv.createParser(',','"','"');*/


function &xls_parseTable($path,$list){
	$data=xls_parse($path,$list);
}
function &xls_parseAll($path){

	$data=infra_cache(array($path),'xls_parseAll',function &($path){
		$file=infra_theme($path);
		
		$conf=infra_config();
		if(!$file&&@$conf['debug']) echo 'Не найден путь '.$path;

		$in=infra_srcinfo($path);
		
		$data=array();
		if($in['ext']=='xls'){
			require_once(__DIR__.'/excel_parser/oleread.php');
			require_once(__DIR__.'/excel_parser/reader.php');
			
			if(!$file)return $data;
			$d = new Spreadsheet_Excel_Reader();
			$d->setOutputEncoding('utf-8');
			$d->read($file);


			infra_forr($d->boundsheets,function&($v,$k) use(&$d,&$data){
				$data[$v['name']]=&$d->sheets[$k]['cells'];
				$r=null;return $r;
			});
		}else if($in['ext']=='xlsx'){
			$dirs=infra_dirs();
			$cacheFolder=$dirs['cache'].'xlsx/';
			$cacheFolder.=infra_hash($path).'/';//кэш
			infra_cache_fullrmdir($cacheFolder);//удалить старый кэш
			
			//разархивировать
		    $zip = new ZipArchive;
		    if ($zip->open(infra_theme($path))) {

		    	mkdir($cacheFolder);
				$zip->extractTo($cacheFolder);
				$zip->close();

				$contents = simplexml_load_file($cacheFolder.'xl/sharedStrings.xml');

				$contents = $contents->si;

				$workbook = simplexml_load_file($cacheFolder.'xl/workbook.xml');				
				$sheets=$workbook->sheets->sheet;
				
				$handle = opendir($cacheFolder.'xl/worksheets/');
				$i=0;
				$syms=array();
	            while($file = readdir($handle)){
					if($file{0}== '.')continue;
					$src=$cacheFolder.'xl/worksheets/'.$file;
					if(!is_file($src))continue;
					$files[]=$file;
				}
				closedir($handle);
				natsort($files);
				
				
				foreach($files as $file){
					$src=$cacheFolder.'xl/worksheets/'.$file;
					
					$list=$sheets[$i];
					$i++;
					$list=$list->attributes();
					$list=(string)$list['name'];
					
					

					$data[$list]=array();
					
					$sheet=simplexml_load_file($cacheFolder.'xl/worksheets/'.$file);
					$rows=$sheet->sheetData->row;
					foreach($rows as $row){
						
						$attr=$row->attributes();
						$r=(string)$attr['r'];
						$data[$list][$r]=array();
						$cells=$row->c;
						
						foreach($cells as $cell){
							
							if(!$cell->v)continue;

							$attr = $cell->attributes();
							if($attr['t']=='s'){
								$place=(integer)$cell->v;
								
								if(isset($contents[$place]->r)){
									$value='';
									foreach($contents[$place]->r as $con){
										$value.=$con->t;
									}
								}else{
									$value=$contents[$place]->t;
								}
							}else{
								$value=$cell->v;
								$value=(double)$value;
							}
							


							$attr = $cell->attributes();
							$c=(string)$attr['r'];//FA232
							preg_match("/\D+/",$c,$c);
							$c=$c[0];
							$syms[$c]=true;
							$data[$list][$r][$c]=(string)$value;
						}
					}
				}
				
				$syms=array_keys($syms);
				natsort($syms);
				/*usort($syms,function($a,$b){
					$la=strlen($a);
					$lb=strlen($b);
					if($la>$lb)return 1;
					if($la<$lb)return -1;
					if($a>$b)return 1;
					if($a<$b)return -1;
					return 0;
				});*/
				$symbols=array();
				foreach($syms as $i=>$s){
					$symbols[$s]=$i+1;
				}

				
				foreach($data as $list=>$listdata){
					foreach($listdata as $row=>$rowdata){
						$data[$list][$row]=array();
						foreach($rowdata as $cell=>$celldata){
							$data[$list][$row][$symbols[$cell]]=$celldata;
						}
						if(!$data[$list][$row])unset($data[$list][$row]);//Пустые строки нам не нужны
					}
				}
		    }
		    // Если что-то пошло не так, возвращаем пустую строку
		    //return "";
			//собрать данные
		}
		
		
		return $data;
	},array($path));
	return $data;
}
function &xls_parse($path,$list=false){
	$data=&xls_parseAll($path);
	if(!$list) $list=infra_foro($data,function(&$v,$k){return $k;});
	return $data[$list];
}

function &xls_make2($path){
	$data=&xls_make($path);
	xls_runGroups($data,function(&$group){
		unset($group['parent']);
	});
	return $data;
}
function &xls_make($path){

	$datamain=xls_parseAll($path);
	
	if(!$datamain)return;
	$p=infra_srcinfo($path);
	$title=$p['name'];
	$title=infra_toutf($title);
	
	$parent=false;
	$groups=&_xls_createGroup($title,$parent,'book');

	foreach($datamain as $title=>$data){//Бежим по листам
		if($title{0}==='.')continue;//Не применяем лист у которого точка в начале имени
		$argr=array();//Чтобы была возможность определить следующую группу и при этом работать со ссылкой и не переопределять предыдущую
		$argr[0]=&_xls_createGroup($title,$groups,'list');
		if(!$argr[0])continue;
		$groups['childs'][]=&$argr[0];

		$head=false;//Заголовки ещё не нашли
		$pgpy=false;//ПГПЯ Признак группы пустая ячейка в строке... а этом свойстве будет индекс ПГПЯ
		$wasdata=false;//Были ли до этого данные
		$wasgroup=false;
		//var empty=0;//Количество пустых строк
		$first_index=0;
		
		foreach($data as $i=>$row){//Бежим по строкам 
			//infra_foro($data,function(&$row,$i) use(&$head,&$pgpy,&$wasdata,&$wasgroup,&$argr,&$first_index){
			$count=0;
			//$group=&$argr[0];//Группа может появится среди данных в листах
			//echo $group['title'].'<br>';
			foreach($row as $cell)if($cell)$count++;
			
			if(!$head){
				foreach($row as $b=>$rowcell){
					$row[$b]=preg_replace('/\n/','',$row[$b]);
					$row[$b]=preg_replace('/\s+$/','',$row[$b]);
					$row[$b]=preg_replace('/^\s+/','',$row[$b]);
				}
				$head=($count>2);//Больше 2х не пустых ячеек будет заголовком
				foreach($row as $first_index=>$first_value)break;
				if($head){//Текущий row и есть заголовок
					$argr[0]['head']=$row;
				}else{
					if($first_value=='ПГПЯ'){//Признак группы пустая ячейка номер этой ячейки
						$pgpy=$row[$first_index+1]-1;//Индекс пустой ячейки
					}else{
						if($first_value)$argr[0]['descr'][]=$row;
					}
				}
			}else{
				$isnewgroup=(isset($row[$first_index])&&($count==1)&&mb_strlen($row[$first_index])>1);//Если есть только первая ячейка и та длинее одного символа

				if(!$isnewgroup&&$pgpy&&mb_strlen($row[$first_index])!==1){//один символ в первой ячейке имеет специальное значение выхода на уровень вверх
				
					$roww=array_values($row);
					$isnewgroup=!$roww[$pgpy];
				}
				if($isnewgroup){
					
					if($wasdata&&@$argr[0]['parent']&&$argr[0]['parent']['type']!='book'){
						$argr=array(&$argr[0]['parent']);//Если уже были данные то поднимаемся наверх
					}
					$g=array();
					$g[0]=&_xls_createGroup($row[$first_index],$argr[0],'row',$row);//Создаём новую группу
					if(!$g[0])continue;
					$g[0]['parent']['childs'][]=&$g[0];
					$wasgroup=true;
					$wasdata=false;
					$g[0]['descr']=array_merge($g[0]['parent']['descr'],$g[0]['descr']);
					$g[0]['head']=&$g[0]['parent']['head'];
					$argr=array(&$g[0]);
					
					
					//$group=&$g;//Теперь ссылка на новую группу и следующие данные будут добавляться в неё
					//Новая ссылка забивает на старую, простое присвоение это новое место куда указывает ссылка
				}else{
					
					if($count===1&&strlen($row[$first_index])===1){//подъём на уровень выше

						if(@$argr[0]['parent']){
							$argr=array(&$argr[0]['parent']);
							//echo '<b>'.$group['title'].'</b><br>';
						}
					}else{
						$wasdata=true;
						$argr[0]['data'][]=$row;
					}
				}
			}
		}
	}
	
	return $groups;
}
function &xls_runPoss(&$data,$callback,$back=false){
	return xls_runGroups($data,function&(&$group) use($back,&$callback){
		return infra_forr($group['data'],function&(&$pos,$i) use(&$callback,&$group){
			return $callback($pos,$i,$group);
		},$back);
	},$back);
}

function _xls_createGroup($title='',&$parent,$type,&$row=false){
	$tparam='';
	$descr=array();
	$miss=false;
	$t=explode(':',$title);
	if(!$t[0]&&$parent){//Когда начинается с двоеточия
		array_shift($t);
		$title=implode(':',$t);
		foreach($parent['descr'] as $first_index=>$first_value)break;
		$index=infra_forr($parent['descr'],function&(&$row,$i) use($first_index, $title){
			if($row[$first_index]=='Описание'){
				$row[$first_index+1].='<br>'.$title;
				return $i;
			}
			$r=null;return $r;
		});
		if(!is_null($index)){
			$parent['descr'][$index]=array('Описание',$title);
		}else{
			array_push($parent['descr'],array('Описание',$title));
		}
		return false;
	}else{
		if(sizeof($t)>1){
			$title=array_shift($t);
			if($title=='Производитель'){//Производитель:KUKA будет означать что у текущей группы указан производитель
				$title=implode(':',$t);
				$tparam='';
				array_push($descr,array('Производитель',$title));
				$miss=true;
			}else{
				$tparam=implode(':',$t);
			}
		}
	}
	$title=preg_replace('/["+\']/',' ',$title);
	$title=preg_replace('/[\\/\\\\]/','',$title);
	$title=preg_replace('/^\s+/','',$title);
	$title=preg_replace('/\s+$/','',$title);
	$title=preg_replace('/\s+/',' ',$title);
	// title=title.toUpperCase();
	$res=array( 
		//'tparam'=>false,
		//'groups'=>false,//Количество групп вместе с текущей
		//'count'=>false,
		'row'=>&$row,//Вся строка группы
		'miss'=>$miss,//Группу надо расформировать, но мы не знаем ещё есть ли в ней позиции
		'type'=>$type,
		'parent'=>&$parent,
		'title'=>(string)$title,
		'head'=>array(),
		'descr'=>&$descr,
		'data'=>array(),
		'childs'=>array()
	);
	if($tparam)$res['tparam']=$tparam;//Параметр у группы Сварка:asdfasd что угодно
	return $res;
}

function xls_processPoss(&$data,$ishead=false){ //
	//используется data head

	
	xls_runGroups($data,function(&$data) use($ishead){	
	
		if(@$data['head']){
			$head=&$data['head'];
		}else{
			return; //Значит и данных нет
		}
		
		infra_forr($data['data'],function&(&$pos,$i,&$group) use(&$head,&$data){

			$p=array();

			infra_foro($pos,function&($propvalue,$i) use(&$p,&$head){
				$propname=@$head[$i];
				if(!$propname)return;
				if($propname{0}=='.')return;//Колонки с точкой скрыты
				if($propvalue=='')return;
				if($propvalue{0}=='.')return;//Позиции у которых параметры начинаются с точки скрыты

				$propvalue=trim($propvalue);
				//$propvalue=preg_replace('/\s+$/','',$propvalue);
				//$propvalue=preg_replace('/^\s+/','',$propvalue);
				if(!$propname)return;
				$p[$propname]=$propvalue;
				$r=null;return $r;
			});
			$p['group']=&$data;//Рекурсия
			$group[$i]=&$p;
			$r=null;return $r;
		});
		if(!$ishead){
			unset($data['head']);
		}
	});

}
function xls_print($data){
	echo '<pre>';
	xls_runGroups($data,function(&$group){ unset($group['parent']); });
	xls_runPoss($data,function(&$pos){ unset($pos['group']); });
	print_r($data);
}
function xls_processPossFilter(&$data,$props){//Если Нет какого-то свойства не учитываем позицию
	xls_runGroups($data,function(&$data) use(&$props){	
		$d=array();
		infra_forr($data['data'],function&(&$pos) use(&$props,&$d){
			if(!infra_forr($props,function($name) use($pos){
				if(!$pos[$name])return true;
			})){
				$d[]=&$pos;
			}
			$r=null;return $r;
		});
		$data['data']=$d;
	});
}

function xls_processPossBe(&$data,$check1,$check2){//Если у позиции нет поля check1.. то оно будет равнятся полю check2
	//используется data
	xls_runPoss($data,function(&$pos) use($check1,$check2){	
		if(is_null($pos[$check1]))$pos[$check1]=$pos[$check2];
		if(is_null($pos[$check2]))$pos[$check2]=$pos[$check1];
	});
}
function xls_processPossFS(&$data,$props){
	xls_runPoss($data,function(&$pos) use(&$props){	
		infra_foro($props,function($name,$key) use(&$pos){
			if(isset($pos[$key])){
				$pos[$name]=infra_forFS($pos[$key]);
			}
		});
	});
};
function xls_processPossMore(&$data,$props){
	xls_runPoss($data,function(&$pos,$i,&$group) use(&$props){	
		$p=array();
		$more=array();				
		
		
		$prop=array();
		infra_forr($props,function&($name) use(&$prop){
			$prop[$name]=true;
			$r=null;return $r;
		});
		
		infra_foro($pos,function&(&$val,$name) use(&$p,&$prop,&$more){
			if($prop[$name])$p[$name]=&$val;
			else $more[$name]=&$val;
			$r=null;return $r;
		});
		if($more)$p['more']=&$more;
		$group['data'][$i]=&$p;
	});
}

function xls_merge(&$gr,&$addgr){//Всё из группы addgr нужно перенести в gr
	

	//$gr['miss']=0;
	/*	Группа Мебель в Каталог.xls не содержит позиций
		Excel Мебель.xls содержит позиции только в подгруппах листах
		Была ошибка Группа Мебель пропадала с сайта.
		Для кники устанавливается miss по умолчанию
		а группа из Каталог.xls без объединения оставалась пустой и удалялась.
		Сначало делается объединение а потом проверяется какие пустые группы удалить.
		ну и в момент объединения miss долже стать 0
		потому что мы почему-то объединяем в book а должны в лист Каталог.xls

	*/
	infra_forr($addgr['childs'],function&(&$val) use(&$gr){
		$val['parent']=&$gr;
		$gr['childs'][]=&$val;
		$r=null;return $r;
	});
	
	infra_foro($addgr['descr'],function&($des,$key) use(&$gr){
		//if($key=='Описание')return;//Всё кроме Описания
		if(is_null(@$gr['descr'][$key])){
			$gr['descr'][$key]=$des;
		};
		$r=null;return $r;
	});
	
	if(@$gr['tparam'])$gr['tparam'].=','.$addgr['tparam'];
	else $gr['tparam']=@$addgr['tparam'];
	
	for($i=0,$l=sizeof($addgr['data']);$i++;$i<$l){
		$pos=&$addgr['data'][$i];
		$pos['group']=&$gr;
		$gr['data'][]=&$pos;
	}
	return;
}
function &xls_runGroups(&$data,$callback,$back=false,$i=0,&$group=false){
	if(!$back){
		$r=&$callback($data,$i,$group);
		if(!is_null($r))return $r;
	}
	
	$r=&infra_forr($data['childs'],function&(&$val,$i) use($callback,$back,&$data){
		return xls_runGroups($val,$callback,$back,$i,$data);
	},$back);
	if(!is_null($r))return $r;
	
	if($back){
		$r=&$callback($data,$i,$group);
		if(!is_null($r))return $r;
	}
	return $r;
}
function xls_processGroupFilter(&$data){
	$all=array();
	xls_runGroups($data,function(&$gr) use(&$all){
		$title=infra_strtolower($gr['title']);
		//echo $title.'<br>';
		if(!isset($all[$title])){
			$all[$title]=array('orig'=>&$gr,'list'=>array());
		}else{//Ну вот и нашли повторение
			$all[$title]['list'][]=&$gr;
			//xls_merge($all[$title],$gr);
			//у некой прошлой группы появляются новые childs.. но мы всё ещё бежим по какому-то его childs и новые добавленные будут проигнорированны
			//return new infra_Fix('del');
		}
	});
	infra_foro($all,function(&$des){
		infra_forr($des['list'],function&(&$gr) use($des){
			xls_merge($des['orig'],$gr);
			infra_forr($gr['parent']['childs'],function&(&$g) use(&$gr){
				if(infra_isEqual($g,$gr))return new infra_Fix('del',true);
				$r=null;return $r;
			});
			$r=null;return $r;
		});
		$r=null;return $r;
	});
	/*//$cat=$data['childs'][0];
	$cat=$data;
	unset($cat['parent']);
	infra_forr($cat['childs'],function(&$g){
		//if(!is_string($g['parent']))
		$g['parent']=&$g['parent']['title'];
		//unset($g['parent']);
		$g['childs']=sizeof($g['childs']);
		$g['data']=sizeof($g['data']);
	});
	echo '<pre>';
	print_r($cat);
	exit;
	/*
	xls_runGroups($data,function(&$gr,$i,&$group){//Удаляем пустые группы
		if(!$group) return;//Кроме верхней группы
		if(!sizeof($gr['childs'])&&!sizeof($gr['data'])){
			array_splice($group,$i,1);
		}
	},array(),true);
	*/
}
function xls_processDescr(&$data){//
	xls_runGroups($data,function&(&$gr){
		$descr=array();
		infra_forr($gr['descr'],function&($row) use(&$descr){
			$row=array_values($row);
			@$descr[$row[0]]=$row[1];
			$r=null;return $r;
		});
		$gr['descr']=&$descr;
		$r=null;return $r;
	});
}
function xls_processGroupCalculate(&$data){
	xls_runGroups($data,function&(&$data){
		$data['count']=sizeof($data['data']);
		$data['groups']=1;
		infra_forr($data['childs'],function&(&$d) use(&$data){
			$data['count']+=$d['count'];
			$data['groups']+=$d['groups'];
			$r=null;return $r;
		});
		$r=null;return $r;
	},true);
};

function xls_processClassEmpty(&$data,$clsname){
	xls_runGroups($data,function(&$gr) use($clsname){
		$poss=array();
		for($i=0,$l=sizeof($gr['data']);$i<$l;$i++){
			if(!isset($gr['data'][$i][$clsname])||!$gr['data'][$i][$clsname])continue;
			$poss[]=$gr['data'][$i];
		}
		$gr['data']=$poss;
	});
}
function xls_processClass(&$data,$clsname,$musthave=false){
	$run=function(&$data,$run,$clsname,$musthave, $clsvalue=''){
		if($data['type']=='book'&&$musthave){
			$data['miss']=true;
			$clsvalue=infra_forFS($data['title']);
		}else if($data['type']=='list'&&@$data['descr'][$clsname]){//Если в descr указан класс то имя листа игнорируется иначе это будет группой каталога, а классом будет считаться имя книги
			$data['miss']=true;//Если у листа есть позиции без группы он не расформировывается
			$clsvalue=infra_forFS($data['descr'][$clsname]);
		}else if($data['type']=='row'&&@$data['descr'][$clsname]){
			$clsvalue=infra_forFS($data['descr'][$clsname]);
		}
		infra_forr($data['data'],function&(&$pos) use($clsname,$clsvalue){
			if(!isset($pos[$clsname])){
				$pos[$clsname]=$clsvalue;//У позиции будет установлен ближайший класс
			}else{
				$pos[$clsname]=infra_forFS($pos[$clsname]);
			}
			$r=null;return $r;
		});
		
		infra_forr($data['childs'],function&(&$data) use($run,$clsvalue,$clsname,$musthave){
			$run($data,$run,$clsname,$musthave, $clsvalue);
			$r=null;return $r;
		});
	};
	$run($data,$run,$clsname,$musthave);
	return $data;
}
function xls_processGroupMiss(&$data){

	$numArgs=func_num_args();
	if($numArgs>1){
		trigger_error(sprintf('%s: expects at least 1 parameters, %s given', __FUNCTION__, $numArgs), E_USER_WARNING);
		return false;
	}

	xls_runGroups($data,function(&$gr,$i,&$group){
		if(@$gr['miss']&&@$gr['parent']){
			//Берём детей missгруппы и переносим их в родительскую
			infra_forr($gr['childs'],function&(&$g) use(&$gr){
				$g['parent']=&$gr['parent'];
				$r=null;return $r;
			});
			array_splice($group['childs'],$i,1,$gr['childs']);

			infra_forr($gr['data'],function&(&$p) use(&$gr){
				$p['group']=&$gr['parent'];
				$gr['parent']['data'][]=$p;
				$r=null;return $r;
			});

			//infra_forr($gr['childs'],function(&$gr,&$childs, &$d){
		//		array_splice($childs,($i++)-1,0,array(&$d));
		//		$d['parent']=&$gr['parent'];
		//	},array(&$gr,&$childs));
		//	$arr[]=&$gr;
		}
	},true);//Если бежим вперёд повторы несколько раз находим, так как добавляем в конец// Если бежим сзади рушится порядок
}
function _xls_sort($a,$b){
	return ($a < $b) ? -1 : ($a > $b) ? 1 : 0;
}
function _xls_sortName($a,$b){
	$a=$a['Наименование'];
	$b=$b['Наименование'];
	return ($a < $b) ? -1 : ($a > $b) ? 1 : 0;
}
function xls_pageList(&$poss,$page,$count,$sort,$numbers){
	$all=sizeof($poss);
	$pages=ceil($all/$count);
	if($page>$pages)$page=$pages;
	if($page<1)$page=1;
	if($numbers<1)$numbers=1;
	$numbers--;
	//page pages numbers first last
	$first=floor($numbers/2);
	$tfirst=$first;
	$last=$numbers-$first;
	$show=array();

	while($tfirst){
		$p=$page-$tfirst;
		if($p<1){
			$last++;
			$first--;
		}
		$tfirst--;
	}
	while($last){
		$p=$page+$last;
		if($p<=$pages){
			$show[]=$p;
		}else{
			$first++;
		}	
		$last--;
	}
	while($first){
		$p=$page-$first;
		if($p>0){
			$show[]=$p;
		}
		$first--;
	}
	$show[]=(int)$page;
	//usort($show,'_xls_sort');
	sort($show);

	if($sort=='name'){
		usort($poss,'_xls_sortName');
	}
	infra_forr($poss,function&(&$p,$i){
		$p['num']=$i+1;
		$r=null;return $r;
	});
	$next=$page+1;
	$prev=$page-1;
	if($prev<1)$prev=1;
	if($next>$pages)$next=$pages;
	$r=array(
		'next'=>$next,
		'prev'=>$prev,
		'show'=>$show,//Список страниц
		'page'=>$page,//Текущая страница
		'sort'=>$sort,//сортировка
		'list'=>array(),//Список позиций на выбранной странице
		'pages'=>$pages//Всего страниц
	);

	$start=($page*$count-$count);
	for($i=$start,$l=$start+$count;$i<$l;$i++){
		if(!$poss[$i])break;
		$r['list'][]=&$poss[$i];
	}
	return $r;
}
function xls_preparePosFiles(&$pos,$pth,$props=array()){
	if(!@$pos['images'])$pos['images']=array();
	if(!@$pos['texts'])$pos['texts']=array();
	if(!@$pos['files'])$pos['files']=array();
	$dir=array();
	if(infra_forr($props,function&($name) use(&$dir,&$pos){
		$rname=infra_seq_right($name);
		$val=infra_seq_get($pos,$rname);
		if(!$val)return true;
		$dir[]=$val;
		$r=null;return $r;
	})){
		return;
	}
	
	if($dir){
		$dir=implode('/',$dir).'/';
		$dir=$pth.$dir;
	}else{
		$dir=$pth;
	}
	
	$dir=infra_theme($dir);
	if(!$dir) return false;


	if(is_dir($dir)){
		$paths=glob($dir.'*');
	}else if(is_file($dir)){
		$paths=array($dir);
		$p=infra_srcinfo($dir);
		$dir=$p['folder'];
	}

	infra_forr($paths,function&($p) use(&$pos,$dir){
		
		$d=explode('/',$p);
		$name=array_pop($d);
		$n=infra_strtolower($name);
		$fd=infra_nameinfo($n);
		$ext=$fd['ext'];



		//if(!$ext)return;
		if(!is_file($dir.$name))return;
		//$name=preg_replace('/\.\w{0,4}$/','',$name);

		/*$p=pathinfo($p);
		$name=$p['basename'];
		$ext=strtolower($p['extension']);*/
		$dirs=infra_dirs();
		$dir=preg_replace('/^'.str_replace('/','\/',$dirs['data']).'/',"*",$dir);
		$name=infra_toutf($dir.$name);
		if($name{0}=='.')return;
		$im=array('png','gif','jpg');
		$te=array('html','tpl','mht','docx');
		if(infra_forr($im,function($e) use($ext){if($ext==$e)return true;})){
			$pos['images'][]=$name;
		}else if(infra_forr($te,function($e) use($ext){if($ext==$e)return true;})){
			$pos['texts'][]=$name;
		}else{
			if($ext!='db'){
				$pos['files'][]=$name;
			}
		}
		$r=null;return $r;
	});
	$pos['images']=array_unique($pos['images']);
	$pos['texts']=array_unique($pos['texts']);
	$pos['files']=array_unique($pos['files']);
}
/*
 * Нет рекурсии, нет подсчёта количества.. .Какие нужны колонки, что подготовить к вставки в адрес передаются свойством
 * По умолчанию
$config=array(
		'more'=>false,
 		'Переименовать колонки'=>array(),
 		'Удалить колонки'=>array(),
		'Подготовить для адреса'=>array('Артикул'=>'article','Производитель'=>'producer'),//Ничего
		'Ссылка parent'=>false,//Нет ссылки
		'group_title'=>true,
		'parent_title'=>true,
		'root'=>'Каталог',
		'Обязательные колонки'=>array('article','producer'),
		'Сохранить head'=>false,
		'Имя файла'=>'Производитель',//'Группа'
		'Известные колонки'=>array('Наименование','Артикул','Производитель')//Остальные в свойстве more
	);
 * */
function &xls_init($path,$config=array()){//Возвращает полностью гототовый массив
	//if(infra_isAssoc($path)===true)return $path;//Это если переданы уже готовые данные вместо адреса до файла данных
	
	$parent=false;
	
	$ar=array();
	$isonefile=true;
	infra_fora($path,function($path) use(&$isonefile,&$ar){
		$p=infra_theme($path);

		if($p&&!is_dir($p)){
			if($isonefile===true)$isonefile=$p;
			else $isonefile=false;
			$ar[]=$path;
		}else if($p){
			$isonefile=false;
			$ar=infra_loadJSON('*pages/list.php?e=xls,xlsx&onlyname=1&src='.$path);
			infra_forr($ar,function&(&$file) use($path){
				$file=infra_theme($path.$file,'f');
				$r=null;return $r;
			});
		}
	});

	if(!@$config['root']){
		if($isonefile){
			$d=infra_srcinfo($isonefile);
			$config['root']=infra_toutf($d['name']);
		}else{
			$config['root']='Каталог';
		}
	}

	$data=_xls_createGroup($config['root'],$parent,'set');//Сделали группу в которую объединяются все остальные
	$data['miss']=true;//Если в группе будет только одна подгруппа она удалится... подгруппа поднимится на уровень выше
		
	infra_forr($ar,function&($path) use(&$data){
		$d=&xls_make($path);

		if(!$d)return;
		$d['parent']=&$data;
		$data['childs'][]=&$d;
		$r=null;return $r;
	});
	
	
	
	xls_processDescr($data);
	
	if(!isset($config['Сохранить head']))$config['Сохранить head']=false;
	xls_processPoss($data,$config['Сохранить head']);

	if(@!is_array($config['Переименовать колонки']))$config['Переименовать колонки']=array();
	if(@!is_array($config['Удалить колонки']))$config['Удалить колонки']=array();
	if(!isset($config['more']))$config['more']=false;
	
	xls_runPoss($data,function(&$pos) use(&$config){
	
		foreach($config['Удалить колонки'] as $k){
			if(isset($pos[$k]))unset($pos[$k]);
		}
		foreach($config['Переименовать колонки'] as $k=>$v){
			if(isset($pos[$k])){
				$pos[$v]=$pos[$k];
				unset($pos[$k]);
			}
		}
	});



	
	if(!isset($config['Имя файла']))$config['Имя файла']='Производитель';//Группа остаётся, а производитель попадает в описание каждой позиции

	

	if(@$config['Имя файла']=='Производитель')xls_processClass($data,'Производитель',true);//Должен быть обязательно miss раставляется
	
	xls_runPoss($data,function(&$pos,$i,&$group){// пустая позиция
		if(sizeof($pos)==2){ //group_title Производитель
			unset($group['data'][$i]);
			return;
		}
	});


	
	xls_processGroupFilter($data);//Объединяются группы с одинаковым именем, Удаляются пустые группы
	

	xls_processGroupMiss($data);//Группы miss(производители) расформировываются

	
	
	
	//xls_processGroupCalculate($data);//Добавляются свойства count groups сколько позиций и групп группы должны быть уже определены... почищены...				
	
	xls_runGroups($data,function(&$gr,$i,&$parent){//Имя листа или файла короткое и настоящие имя группы прячется в descr. но имя листа или файла также остаётся в title
		$gr['name']=$gr['descr']['Наименование'];//name крутое правильное Наименование группы
		if(!$gr['name'])$gr['name']=$gr['title'];//title то как называется файл или какое имя используется в адресной строке
		if(!$gr['tparam'])$gr['tparam']=$parent['tparam'];//tparam наследуется Оборудование:что-то, что-то

		if($gr['descr']['Производитель']){
			for($i=0,$il=sizeof($gr['data']);$i<$il;$i++){
				if(!empty($gr['data'][$i]['Производитель']))continue;
				$gr['data'][$i]['Производитель']=$gr['descr']['Производитель'];
				$gr['data'][$i]['producer']=infra_forFS($gr['descr']['Производитель']);
			}
		}

	});


	if(@!is_array($config['Подготовить для адреса']))$config['Подготовить для адреса']=array('Артикул'=>'article','Производитель'=>'producer');
	xls_processPossFS($data,$config['Подготовить для адреса']);//Заменяем левые символы в свойстве
	

	if(empty($config['Обязательные колонки']))$config['Обязательные колонки']=array('article','producer');
	xls_runGroups($data,function(&$group) use($config){
		if(empty($group['data']))return;
		for($i=0,$l=sizeof($group['data']);$i<$l;$i++){
			foreach($config['Обязательные колонки'] as $propneed){
				if(empty($group['data'][$i][$propneed])){
					unset($group['data'][$i]);
					break;
				}
			}
		}
		$group['data']=array_values($group['data']);
	});


	if(@!$config['Известные колонки'])$config['Известные колонки']=array('Производитель','Наименование','Описание','Артикул');
	$config['Известные колонки'][]='group';
	foreach($config['Подготовить для адреса'] as $k=>$v){
		$config['Известные колонки'][]=$v;
		$config['Известные колонки'][]=$k;
	}
	if(@$config['more']){
		xls_processPossMore($data,$config['Известные колонки']);//позициям + more		
	}

	if(!isset($config['group_title']))$config['group_title']=true;
	
	if(@$config['group_title']){
		xls_runPoss($data,function(&$pos){
			$pos['group_title']=$pos['group']['title'];
		});
	}

	if(!isset($config['parent_title']))$config['parent_title']=true;
	if(@$config['parent_title']){
		xls_runGroups($data,function(&$group){
			$group['parent_title']=$group['parent']['title'];
		});
	}

	if(@$config['Ссылка parent']){//group parent data childs
		xls_runPoss($data,function(&$pos){
			$pos['parent']=&$pos['group'];
			unset($pos['group']);
		});
	}else{
		xls_runGroups($data,function(&$group){
			unset($group['parent']);
		});

		xls_runPoss($data,function(&$pos,$i){
			unset($pos['group']);
		});
	}

	xls_runGroups($data,function(&$data,$i,&$group){//path
		unset($data['row']);
		
		if(!$group){
			$data['path']=array();
		}else{
			$data['path']=$group['path'];
			$data['path'][]=$data['title'];
		}
	});
	xls_runPoss($data,function(&$pos,$i,&$group){
		$pos['path']=$group['path'];
	});
	
	return $data;
};