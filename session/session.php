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

global $infra_session_data;
function infra_session_init(){
	infra_cache_no();
	infra_once('infra_session_init',function(){
		infra_session_sync();
	});
}
function infra_session_getName($name){
	return 'infra_session_'.$name;
}
global $infra_session_lasttime;
function infra_session_syncreq($list){ //новое значение, //Отправляется пост на файл, который записывает и возвращает данные
	global $infra_session_lasttime;
	if(!$infra_session_lasttime)$infra_session_lasttime=1;
	else $infra_session_lasttime=time()+10000;

	
	$data=array( //id и time берутся из кукисов на сервере 
		'time'=>$infra_session_lasttime,
		'list'=>infra_json_encode($list)
	);
	

	$oldPOST=$_POST;
	$oldREQ=$_REQUEST;
	$_POST=$data;
	$_REQUEST=$data;

	$src='*session/sync.php';

	infra_unload($src);
	$ans=infra_loadJSON($src);

	$_POST=$oldPOST;
	$_REQUEST=$oldREQ;

	if(!$ans)return;
	
	
	//По сути тут set(news) но на этот раз просто sync вызываться не должен, а так всё тоже самое
	global $infra_session_data;
	$infra_session_data=infra_session_make($ans['news'],$infra_session_data);
}
function infra_session_getPass(){
	return infra_view_getCookie(infra_session_getName('pass'));
}
function infra_session_getId(){
	return infra_view_getCookie(infra_session_getName('id'));
}
function infra_session_getTime(){
	return infra_view_getCookie(infra_session_getName('time'));
}
function infra_session_sync($list=false){
	$session_id=infra_session_getId();
	
	if(!$session_id&&!$list)return;//Если ничего не устанавливается и нет id то sync не делается

	infra_session_syncreq($list);
}


function infra_session_make($list,&$data=array()){
	if(is_null($data))$data=array();
	infra_fora($list,function(&$data, $li){
		$data=&infra_seq_set($data,$li['name'],$li['value']);
	},array(&$data));
	return $data;
}
function infra_session_get($name='',$def=null){
	infra_session_init();
	$name=infra_seq_right($name);
	global $infra_session_data;
	$val=infra_seq_get($infra_session_data,$name);
	if(is_null($val))return $def;
	else return $val;
}
function infra_session_set($name,$value){
	//if(infra_session_get($name)===$value)return; //если сохранена ссылка то изменение её не попадает в базу данных и не синхронизируется
	$li=array('name'=>infra_seq_right($name),'value'=>$value);
	global $infra_session_data;
	infra_session_make($li,$infra_session_data);
	infra_session_sync($li);
}
function infra_session_getLink(){
	$host=infra_view_getHost();
	$path=infra_view_getRoot(ROOT);
	$pass=infra_view_getCookie(infra_session_getName('pass'));
	$id=infra_view_getCookie(infra_session_getName('id'));
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
