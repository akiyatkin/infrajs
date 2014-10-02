<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_require('*cart/catalog.inc.php');

	$search=$_GET['search'];
	$conf=infra_config();
	$data=cat_search($search);
	$ans=infra_cache(array($conf['cart']['dir']),'filters',function($search,$l) use($data){
		$list=array();//Временный массив со списком только параметров
		$ans=array();
		$ans['is']=$data['is'];
		$ans['time']=$data['time'];

		$params=array();//параметры стандартные
		$params['Цена']=array();
		//$params['Производитель']=array();
		$params['Наличие на складе']=array();
		$conf=infra_config();
		if($conf['catalog']['1c']){
			$params['Синхронизация']=array();
		}
		$listgroups=array();
		$more=array();//параметры дополнительные
		$count=sizeof($data['list']);
		foreach($data['list'] as &$pos){
			unset($pos['Код']);
			unset($pos['Артикул']);
			unset($pos['Продажа']);
			unset($pos['Описание']);
			unset($pos['article']);
			unset($pos['Купить']);
			unset($pos['Наименование']);
			if(!$pos['more'])$pos['more']=array();

			foreach($pos['path'] as $p){
				$listgroups[$p]++;
			}
			
			$p=array(
				'more'=>$pos['more'],
				'params'=>array(
					'Производитель'=>$pos['Производитель']
				)
			);
			if($conf['catalog']['1c']){
				$p['params']['Наличие на складе']=$pos['Наличие на складе'];
				if($pos['Синхронизация']){
					$p['params']['Синхронизация']=$pos['Синхронизация'];
				}
			}
			if($pos['Цена']){
				$p['params']['Цена']=(int)$pos['Цена'];//Дробей в php нет, всё что после точки удаляется	
			}
			$list[]=$p;
		}
		
		
		foreach($list as &$pos){
			foreach($pos['more'] as $k=>$p){
				if(!cat_isSpecified($p))continue;
				$more[$k][$p]++;
			}
			foreach($pos['params'] as $k=>$p){
				if(!cat_isSpecified($p))continue;
				$params[$k][$p]++;
			}
		}
		foreach($params as $k=>$p){
			if($conf['catalog']['1c']){
				$showhard=in_array($k,array('Наличие на складе','Синхронизация'));
			}else{
				$showhard=array();
			}
			$opt=cat_option($params[$k],$count,$showhard);
			//====
			if(!$opt){
				unset($params[$k]);
				continue;
			}
			if(sizeof($opt['values'])==1){
				if($opt['yes']==$count){//Значение есть у всех позиций и только один вариант
					unset($params[$k]);
					continue;
				}
			}
			if(!$opt['values']&&$opt['type']!='slider'){
				if($opt['yes']==$count){//Слишком много занчений но при этом у всех позиций они указаны и нет no yes
					unset($params[$k]);
					continue;
				}
			}
			$opt['no']=$count-$opt['yes'];
			$opt['name']=$k;
			$params[$k]=$opt;
			//===
		}
		foreach($more as $k=>$p){
			$opt=cat_option($more[$k],$count);

			//===
			if(!$opt){
				unset($params[$k]);
				continue;
			}
			if(sizeof($opt['values'])==1){
				if($opt['yes']==$count){//Значение есть у всех позиций и только один вариант
					unset($params[$k]);
					continue;
				}
			}
			if(!$opt['values']&&$opt['type']!='slider'){
				if($opt['yes']==$count){//Слишком много занчений но при этом у всех позиций они указаны и нет no yes
					continue;
				}
			}
			$opt['no']=$count-$opt['yes'];
			$opt['name']=$k;
			$params[$k]=$opt;
			//===

			$params[$k]['more']=true;
			
		}

		$opt=cat_option($listgroups,$count);//список групп с отметкой сколько позиций в каждой группе
		if($opt&&$opt['values']){
			$opt['no']=0;
			$opt['group']=true;
			$opt['name']='Группы';
			
			$params['Группы']=$opt;
		}
		

		usort($params,function($p1,$p2){
			if($p1['yes']>$p2['yes'])return -1;
			if($p1['yes']<$p2['yes'])return 1;
			if($p1['type']=='slider')return -1;
			if($p2['type']=='slider')return 1;

			if(sizeof($p1['values'])>sizeof($p2['values']))return 1;
			if(sizeof($p1['values'])<sizeof($p2['values']))return -1;
			
			return 0;
		});
		$ans['count']=$count;
		$ans['params']=$params;
		return $ans;
	},array($search,sizeof($data['list'])),isset($_GET['re']));

	return infra_echo($ans);
?>
