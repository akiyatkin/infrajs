<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*files/xls.php');
	function &cat_init($parent=false){
		if($parent)return _cat_init(true);
		$conf=infra_config();

		$cond=array($conf['cart']['dir'],$conf['cart']['1c'],$conf['cart']['prod']);
		
		$res=infra_cache($cond,'cat_init',function(){
			return _cat_init();
		},array());
		return $res;
	}
	function _cat_init($parent=false){

		$conf=infra_config();
		$p=array('more'=>true,'Известные колонки'=>array('Наименование','Артикул','Производитель','Цена','Валюта','Купить','Описание','Код','Продажа'));
		$p['Ссылка parent']=$parent;

		$data=xls_init($conf['cart']['dir'],$p);
		xls_runGroups($data,function(&$gr){
			$gr['name']=$gr['descr']['Наименование'];
			if(!$gr['name'])$gr['name']=$gr['title'];
		});
		$prods=xls_init($conf['cart']['prod']);
		

		$price=&xls_make($conf['cart']['1c']);
		xls_processPoss($price);
		$conn=array();
		xls_runPoss($price,function(&$conn,&$pos){
			$conn[$pos['Номенклатура.Код']]=&$pos;
		},array(&$conn));

		
				
		$pos['Подпись']=@$prods['descr']['Подпись'];
		xls_runPoss($data,function(&$pos,$i,&$group) use(&$conn,&$prods){
			$pos['path']=$group['path'];
			$code=@$pos['Код'];
			if(isset($conn[$code])){
				if(isset($conn[$code]['Номенклатура.Полное наименование']))$pos['Описание']=$conn[$code]['Номенклатура.Полное наименование'];
				if(isset($conn[$code]['Оптовая'])){
					$pos['sync']=true;
					$pos['Цена']=$conn[$code]['Оптовая'];
					$pos['Валюта']="руб.";
				}
				if(isset($conn[$code]['Номенклатура']))$pos['Описание']=$conn[$code]['Номенклатура'];
			}
			
			if(!isset($pos['Валюта']))return;
			if($pos['Валюта']==='$'||$pos['Валюта']==='usd'){
				$pos['Цена']=$prods['descr']['usd']*$pos['Цена'];
				$pos['Валюта']='руб.';
			}
			if($pos['Валюта']==='eur'){
				$pos['Цена']=$prods['descr']['eur']*$pos['Цена'];
				$pos['Валюта']='руб.';
			}
			if($pos['Валюта']!='руб.'){
				unset($pos['Цена']);
				unset($pos['Валюта']);
				return;
			}
			unset($pos['Валюта']);
			/*if($pos['Цена']){
				$cost=(string)$pos['Цена'];
				$ar=explode('.',$cost);
				if(sizeof($ar)==2){
					$rub=$ar[0];
					$cop=$ar[1];
					if(strlen($cop)==1){
						$cop.='0';
					}
					$pos['Цена']=$rub.','.$cop;
				}
			}*/

		});
		return $data;
	}
	function cat_getpos($prodart){
		$conf=infra_config();
		return infra_cache(array($conf['cart']['dir'],$conf['cart']['1c']),'cat_getpos',function($prodart){
			$data=&cat_init();
			return xls_runPoss($data,function(&$pos) use($prodart){
				if($prodart==$pos['Производитель'].' '.$pos['article']){
					return $pos;
				}
			});
		},array($prodart));
	}
	function cat_search($vval){
		/*
		$src={
				"is":"group",
				"list":[]
			}
		*/
		if(!$vval)$vval='Каталог';
		$vval=strip_tags($vval);
		$val=infra_strtolower($vval);
		$srh=infra_admin_cache('cat_search',function($val){
			$conf=infra_config();
			$srh=infra_cache(array($conf['cart']['dir'],$conf['cart']['1c']),'cat_search_cache',function($val){
				$data=cat_init();
				$srh=array('list'=>array(),'is'=>false);
				$srh['time']=time();
				$group=&xls_runGroups($data,function(&$val,&$group){
					if(infra_strtolower($group['title'])==$val)return $group;
				},array(&$val));
				if($val=='изменения'){
					$srh['is']='change';
					//Смотрим дату изменения папки для каждой позиции кэшируем на изменение XLS файлов как всё здесь...
					//И дату изменения файлов в папке
					//Позиции без папок игнорируются
					$poss=array();
					xls_runPoss($data,function(&$poss,&$pos){
						$conf=infra_config();
						$dir=infra_theme($conf['cart']['dir'].$pos['Производитель'].'/'.$pos['article'].'/');
						if(!$dir)return;

						$pos['time']=filemtime(ROOT.$dir);
						$list=infra_loadJSON('*pages/list.php?src='.infra_toutf($dir).'&onlyname=1');
						foreach($list as $f){
							$t=$dir.infra_tofs($f);
							$t=filemtime(ROOT.$t);
							if($t>$pos['time'])$pos['time']=$t;
						}
						$poss[]=&$pos;
					},array(&$poss));
					usort($poss,function($a, $b){
					    if($a['time']==$b['time'])return 0;
						return ($a['time']>$b['time'])?-1:1;
					});
					$srh['list']=$poss;					
				}else if($group){
					$srh['is']='group';
					$srh['name']=$group['title'];
					$poss=array();
					xls_runPoss($group,function(&$pos) use(&$poss){
						$poss[]=&$pos;
					});
					$srh['list']=$poss;
					
				}else{

					$dir=infra_theme(CATDIR.$val.'/');
					$poss=array();
					xls_runPoss($data,function(&$poss,&$val,&$pos){
						if(infra_strtolower(@$pos['Производитель'])==$val){
							$poss[]=&$pos;
						}
					},array(&$poss,&$val));

					if($dir||sizeof($poss)){
						$srh['is']='producer';
						$prods=xls_init($conf['cart']['prod']);

						$prod=&xls_runPoss($prods,function($val, &$prod){
							if(infra_strtolower($prod['Производитель'])==$val)return $prod;
						},array($val));

						
						if($prod){
							$name=$prod['Производитель'];
						}else if(sizeof($poss)){
							$name=$poss[0]['Производитель'];
						}else{
							$dir=infra_toutf($dir);
							$p=explode('/',$dir);
							$folder=$p[sizeof($p)-2];
							$name=$folder;
						}
						
						$srh['name']=$name;
						$srh['list']=$poss;
					}else{//ищим позиции подходящие под запрос
						$v=explode(' ',$val);
						foreach($v as &$s)$s=trim($s);
						xls_runPoss($data,function(&$pos,$i,&$group) use(&$v,&$poss){
							$str=$pos['Артикул'];
							$str.=implode(' ',$group['path']);
							$str.=' '.$pos['article'];
							$str.=' '.$pos['Наименование'];
							$str.=' '.$pos['Описание'];
							$str=infra_strtolower($str);
							foreach($v as $s)if(strstr($str,$s)===false)return;
							unset($pos['parent']);
							$poss[]=&$pos;
						});
						if(sizeof($poss)){
							$srh['is']='search';
							$srh['list']=$poss;
						}
						
					}
				}
				
				return $srh;
			},array($val),isset($_GET['re']));
			return $srh;
		},array($val),isset($_GET['re']));
		$srh['val']=$vval;
		return $srh;
	}
	function cat_option($opt){
		foreach($opt as $value=>$s)break;
		$opt=array('values'=>$opt);
		$min=$value;
		$max=$value;
		$yes=0;
		foreach($opt['values'] as $v=>$c){
			if($v)$yes+=$c;
		}
		
		$opt['yes']=$yes;

		$type=false;
		foreach($opt['values'] as $val=>$c){
			if(is_string($val)){
				$type='string';
				break;
			}
			if($val&&$val<$min)$min=$val;
			if($val>$max)$max=$val;
		}
		
		if(!$type){
			
			$len=sizeof($opt['values']);
			if($len>5){
				$opt['min']=$min;
				$opt['max']=$max;
				$type='slider';
				unset($opt['values']);
			}else{
				$type='string';
				arsort($opt['values']);
			}

		}else{
			arsort($opt['values']);
		}
		$opt['type']=$type;
		if($opt['type']=='string'){
			if(sizeof($opt['values'])>10){
				$opt['values_more']=array_slice($opt['values'],6,sizeof($opt['values'])-6,true);
				$opt['values']=array_slice($opt['values'],0,6,true);
			}
		}
		return $opt;
	}
	function cat_numbers($page,$pages,$plen){
		//$plen=11;//Только нечётные и больше 6 
		/*
		$pages=10
		$plen=6

		(1)2345-10 
		1(2)345-10 
		12(3)45-10
		123(4)5-10
		1-4(5)6-10
		1-5(6)7-10
		1-6(7)8910
		1-67(8)910
		1-678(9)10
		1-6789(10)

		$lside=$plen/2+1=4//Последняя цифра после которой появляется переход слева
		$rside=$pages-$lside-1=6//Первая цифра после которой справа появляется переход
		$islspace=$page>$lside//нужна ли пустая вставка слева
		$isrspace=$page<$rside
		$nums=$plen/2-2;//Количество цифр показываемых сбоку от текущей когда есть $islspace далее текущая


		*/
		if($pages<$plen){
			$ar=array_fill(0,$pages+1,1);
			$ar=array_keys($ar);
			array_shift($ar);
		}else{
			$plen=$plen-1;
			$lside=$plen/2+1;//Последняя цифра после которой появляется переход слева
			$rside=$pages-$lside-1;//Первая цифра после которой справа появляется переход
			$islspace=$page>$lside;
			$isrspace=$page<$rside+2;
			$ar=array(1);
			if($isrspace&&!$islspace){
				for($i=0;$i<$plen-2;$i++){
					$ar[]=$i+2;
				}
				$ar[]=0;
				$ar[]=$pages;
			}else if(!$isrspace&&$islspace){
				$ar[]=0;
				for($i=0;$i<$plen-1;$i++){
					$ar[]=$pages-$plen/2+$i-3;
				}
			}else if($isrspace&&$islspace){
				$nums=$plen/2-2;//Количество цифр показываемых сбоку от текущей когда есть $islspace далее текущая
				$ar[]=0;
				for($i=0;$i<$nums*2+1;$i++){
					$ar[]=$page-$plen/2+$i+2;
				}
				$ar[]=0;
				$ar[]=$pages;
			}
		}
		return $ar;
	}
?>
