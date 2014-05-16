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
		$params['Производитель']=array();
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
			$opt=cat_option($params[$k],$count);
			if(!$opt){
				unset($params[$k]);
				continue;
			}
			if(!$opt['values']){
				if($opt['yes']==$count){//Слишком много занчений но при этом у всех позиций они указаны и нет no yes
					unset($params[$k]);
					continue;
				}
			}
			$params[$k]=$opt;
			$params[$k]['no']=$count-$params[$k]['yes'];
			if(!$params[$k]){
				unset($params[$k]);
				continue;
			}
			$params[$k]['name']=$k;
		}
		foreach($more as $k=>$p){
			$opt=cat_option($more[$k],$count);
			if(!$opt)continue;
			if(!$opt['values']){
				if($opt['yes']==$count){//Слишком много занчений но при этом у всех позиций они указаны и нет no yes
					continue;
				}
			}
			$params[$k]=$opt;
			$params[$k]['no']=$count-$params[$k]['yes'];
			if(!$params[$k]){
				unset($params[$k]);
				continue;
			}
			$params[$k]['more']=true;
			$params[$k]['name']=$k;
		}

		$opt=cat_option($listgroups,$count);//список групп с отметкой сколько позиций в каждой группе
		if($opt&&$opt['values']){
			$opt['no']=0;
			$opt['group']=true;
			$opt['name']='Группы';
			
			$params['Группы']=$opt;
		}
		

		usort($params,function($p1,$p2){
			if($p1['yes']==$p2['yes'])return 0;
			if($p1['yes']<$p2['yes'])return 1;
			else return -1;
		});
		$ans['count']=$count;
		$ans['params']=$params;
		return $ans;
	},array($search,sizeof($data['list'])),isset($_GET['re']));

	return infra_echo($ans);
?>
