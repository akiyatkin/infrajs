<?php
/*
Copyright 2008 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
History
13.05.2010 добавлены параметры nokey, param, obj. поддержка modified
*/
function readxlsmain($file){
	$d = new Spreadsheet_Excel_Reader();
	$d->setOutputEncoding('utf-8');
	$d->read($file);
	$data=array();
	$data['boundsheets']=&$d->boundsheets;
	$data['sheets']=&$d->sheets;
	return $data;
}
function readxls(
	$ifile,
	$name=0, //name - номер строки от начала данных в которой идут заголовки колонок и только после данные, Если есть showlists name будет означать возвращать данные массивом или объектом
	$onelist=false,
	$onlynew=false,
	$showlists=false,
	$list=false,
	$descr=false,
	$id=false,  //имя колонки в которой находится идентификатор имя которого будет ключём к строкам
	$nokey=false,  //каждая строка с данными - массив... заголовок согласно $name скрывается, ключей нет
	$param=false,//1 колонка имя, 2 колонка значение
	$obj=0,//Передать ответ в объекте
	$reverse=0,//строки на листах в обратном порядке
	$isname=0//Это означает name есть обязательно .. если name auto
){
	$file=infra_theme($ifile,'f');
	if(!$file){
		return array();
	}
	$list=infra_toutf($list);
	$id=infra_toutf($id);
	$name=$name;
	$obj=(int)$obj;
	$data=pages_cache(array($file),'readxlsmain',array($file));

	/*foreach($data['boundsheets'] as &$sheet){
		$sheet['name']=trim($sheet['name']);
	}
	foreach($data['sheets'] as &$sheet){
		$sheet['name']=trim($sheet['name']);
		foreach($sheet['cells'] as &$row){
			foreach($row as $k=>$cell){
				$row[$k]=trim($cell);
			}
		}
	}*/

	if($showlists){
		$r=array();
		for($i=0,$l=sizeof($data['boundsheets']);$i<$l;$i++){
			if($data['boundsheets'][$i]['name'][0]=='.')continue;
			if(!$name){
				array_push($r,$data['boundsheets'][$i]['name']);
			}else{
				$r[$data['boundsheets'][$i]['name']]=1;
			}
		}
		$result=$r;
	}else{
		$r=array();
		for($i=0,$l=sizeof($data['boundsheets']);$i<$l;$i++){
			if(!$data['sheets'][$i]['cells'])continue;
			$listname=trim($data['boundsheets'][$i]['name']);
			if(!$param&&$name){
				if($name=='auto'){
					$test=$data['sheets'][$i]['cells'];
					$temp=0;
					$find=false;
					foreach($test as $v){
						$temp++;
						if(sizeof($v)>2){
							$find=true;
							break;
						}
					}
					
					if(!$find){
						if($isname){
							$find=true;
							$temp++;
						}else{
							$temp=1;
						}
					}
				}else{
					$temp=$name;
				}
				
				$descrdata=array();
				while($temp>1){
					$descrd=array_shift($data['sheets'][$i]['cells']);
					$descrdata[]=$descrd;
					$temp--;
				}
				$names=array_shift($data['sheets'][$i]['cells']);
				//$data['sheets'][$i]['cells']=array_values($data['sheets'][$i]['cells']);
				if($descr){
					if($descr==1){
						$descrd=array();
						foreach($descrdata as $v){
							$ii=0;
							foreach($v as $jj){;
								if(!$ii){
									$ii=$jj;
								}else{
									$descrd[$ii]=$jj;
									break;
								}
							}
						}
					}else{
						$descrd=$descrdata;
					}
				}else{
					$result=$r;
				}
				
				$r[$listname]=array();
				for($j=0,$k=sizeof($data['sheets'][$i]['cells']);$j<$k;$j++){
					$r[$listname][$j]=array();
					foreach($names as $num=>$value){
						@$r[$listname][$j][$value]=$data['sheets'][$i]['cells'][$j][$num];
					}
				}
				if($descr){
					$d=$r[$listname];
					unset($r[$listname]);
					$r[$listname]['data']=$d;
					$r[$listname]['descr']=$descrd;
					
				}
			}else{
				$r[$data['boundsheets'][$i]['name']]=array_values($data['sheets'][$i]['cells']);
				foreach($r[$data['boundsheets'][$i]['name']] as $k=>$v){
					$r[$data['boundsheets'][$i]['name']][$k]=array_values($r[$data['boundsheets'][$i]['name']][$k]);
				}
				//$r[$data['boundsheets'][$i]['name']]
			}

		}
		if($reverse){
			foreach($r as $l=>$d){
				if($descr){
					$r[$l]['data']=array_reverse($r[$l]['data'],true);
				}else{
					$r[$l]=array_reverse($r[$l],true);
				}
			}
		}
		if($onelist||$list){
			if($list){
				$r=$r[$list];
			}else{
				foreach($r as $l){
					$r=$l;
					break;
				}
			}
		}
		$result=$r;

		if($param){
			if($onelist){
				$newres=array();
				for($i=(int)$name,$l=sizeof($result);$i<$l;$i++){
					$newres[$result[$i][0]]=$result[$i][1];
				}
				$result=$newres;
			}else{
				foreach($result as $k=>$list){
					$newres=array();
					for($i=(int)$name,$l=sizeof($list);$i<$l;$i++){
						$newres[$list[$i][0]]=$list[$i][1];
					}
					$result[$k]=$newres;
				}
				
			}
		}else{
			if($id&&$name){
				if($descr){
					$res=$result['data'];
				}else{
					$res=$result;
				}
				$newres=array();
				for($i=0,$l=sizeof($res);$i<$l;$i++){
					$newres[$res[$i][$id]]=$res[$i];
					//unset($newres[$res[$i][$id]][$id]);
				}
				if($descr){
					$result['data']=$newres;
				}else{
					$result=$newres;
				}
			}
			if($nokey){
				foreach($result as $k=>&$v){
					$result[$k]=array_values($result[$k]);
				}
			}
		}
	}
	return $result;
}
