<?php
/*
Copyright 2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
	
	var ses=infra.Session.init('base',view);

view объект - на клиенте создаваемый, как view=infra.View.init(); на сервере view=infra.View.init([request,response])
или infra.View.get(); если view до этого уже создавался
	
	//Основной приём работы с сессией
	ses.set('name','value');
	ses.get('name');

Данные сессии это объект и можно добавлять значения в иерархию этого объекта

	ses.set('basket.list.DF2323','12'); //В данном случае объект сессии если до этого был пустой 
	//примет вид {basket:{list:{DF2323:'12'}}}
	ses.get('basket'); //Вернётся объект {list:{DF2323:'12'}}

В данном случае точка специальный символ определяющий уровень вложенность для сохраняемого значения. Так как точка также может быть в имени свойства для этого используется следующий синтаксис.
	
	ses.set(['basket','list','KF.56','1');
	ses.get('basket.list'); //или
	ses.get(['basket','list']); //Вернёт объект {'KF.56':'1'}
*
*
*
* КУКИ
* time
* id
* pass
**/
/**/
@define('ROOT','../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_require('*infra/ext/seq.php');
infra_require('*session/session.inc.php');

global $infra_session_data;

function infra_session_initId(){
	//Инициализирует сессию если её нет и возвращает id
	$id=infra_session_getId();
	if(!$id)infra_session_set();
	return infra_session_getId();
}
function infra_session_getName($name){
	return 'infra_session_'.$name;
}
function infra_session_recivenews($list=array()){
	
	global $infra_session_time;
	if(!$infra_session_time)$infra_session_time=1;
	$data=array( //id и time берутся из кукисов на сервере 
		'time'=>$infra_session_time,
		'list'=>infra_json_encode($list)
	);
	global $infra_session_lasttime;
	$infra_session_lasttime=true;//Метка что вызов из php
	$oldPOST=$_POST;
	$oldREQ=$_REQUEST;
	$_POST=$data;
	$_REQUEST=$data;

	$src='*session/sync.php';

	infra_unload($src);
	$ans=infra_loadJSON($src);
	$infra_session_time=$ans['time'];
	//echo '<pre>';
	//print_r($ans);
	//exit;
	$_POST=$oldPOST;
	$_REQUEST=$oldREQ;
	return $ans;
}
function infra_session_syncreq($list=array()){ //новое значение, //Отправляется пост на файл, который записывает и возвращает данные
	$ans=infra_session_recivenews($list);
	if(!$ans)return;	
	//По сути тут set(news) но на этот раз просто sync вызываться не должен, а так всё тоже самое
	global $infra_session_data;
	$infra_session_data=infra_session_make($ans['news'],$infra_session_data);

}
function infra_session_getPass(){
	return infra_view_getCookie(infra_session_getName('pass'));
}
function infra_session_getId(){
	return (int)infra_view_getCookie(infra_session_getName('id'));
}
function infra_session_getTime(){
	return infra_view_getCookie(infra_session_getName('time'));
}
function infra_session_syncNow(){
	$ans=infra_session_recivenews();
	if(!$ans)return;
	//По сути тут set(news) но на этот раз просто sync вызываться не должен, а так всё тоже самое
	global $infra_session_data;
	$infra_session_data=infra_session_make($ans['news'],$infra_session_data);
}
function infra_session_sync($list=null){
	$session_id=infra_session_getId();
	
	if(!$session_id&&!$list)return;//Если ничего не устанавливается и нет id то sync не делается

	infra_session_syncreq($list);
}


function &infra_session_make($list,&$data=array()){
	infra_fora($list,function($li) use(&$data){
		$data=&infra_seq_set($data,$li['name'],$li['value']);
	});
	return $data;
}
function infra_session_get($name='',$def=null){
	infra_cache_no();
	infra_once('infra_session_getinitsync',function(){
		infra_session_sync();
	});
	$name=infra_seq_right($name);
	global $infra_session_data;
	$val=infra_seq_get($infra_session_data,$name);
	if(is_null($val))return $def;
	else return $val;
}
function infra_session_set($name='',$value=null){
	//if(infra_session_get($name)===$value)return; //если сохранена ссылка то изменение её не попадает в базу данных и не синхронизируется
	$right=infra_seq_right($name);

	if(is_null($value)){//Удаление свойства	
		$last=array_pop($right);
		$val=infra_session_get($right);
		if($last&&infra_isAssoc($val)===true){//Имеем дело с ассоциативным массивом
			$iselse=false;
			foreach($val as $i=>$valval){
				if($i!=$last){
					$iselse=true;
					break;
				}
			}
			if(!$iselse){//В объекте ничего больше нет кроме удаляемого свойства... или и его может даже нет
				//Зачит надо удалить и сам объект
				return infra_session_set($right,null);
			}else{
				array_push($right,$last);//Если есть ещё что-то то работает в обычном режиме
			}
		}
	}
	$li=array('name'=>$right,'value'=>$value);
	global $infra_session_data;
	
	infra_session_sync($li);
	$infra_session_data=infra_session_make($li,$infra_session_data);

}





function infra_session_getLink($email=false){
	$host=infra_view_getHost();
	$path=infra_view_getRoot(ROOT);
	if($email){
		$user=infra_session_getUser($email);
		if(!$user)return 'http://'.$host.'/'.$path;
		$pass=md5($user['password']);
		$id=$user['session_id'];
	}else{
		$pass=infra_view_getCookie(infra_session_getName('pass'));
		$id=infra_view_getCookie(infra_session_getName('id'));
	}
	$link='http://'.$host.'/'.$path.'infra/plugins/session/login.php?id='.$id.'&pass='.$pass;
	return $link;
}
/*
function infra_session_getValue($name,$def){//load для <input value="...
	$value=infra_session_get($name);
	if(is_null($value))$value=$def;
	$value=preg_replace('/"/','&quot;',$value);
	return $value;
}
function infra_session_getText($name,$def){ //load для <texarea>...
	$value=infra_session_get($name);
	if(is_null($value))$value=$def;
	$value=preg_replace('/</','&lt;',$value);
	$value=preg_replace('/>/','&gt;',$value);
	return $value;
}*/


?>