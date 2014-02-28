<?php
	@define('ROOT','../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	infra_load('*autoedit/admin.inc.php','r');

	$type=$_REQUEST['type'];
	$id=$_REQUEST['id'];
	//$ans=array('result'=>0);
	//@header('Content-type: application/json; charset=utf-8'); 
	//@header('Content-type: text/html; charset=utf-8'); 
	//@header('Content-type: text/plain; charset=utf-8'); 
	//@header('Content-type: json; charset=utf-8'); 
	//header('Content-Type:text/html');
	if(infra_admin()||$type=='admin'){
		$ans['noclose']=1;//Не закрывать окно по окончанию
		$ans['autosave']=1;//Не очищать autosave
		if($type=='admin'){
			$admin=(bool)$_REQUEST['admin'];
			$login=$_REQUEST['login'];
			$pass=$_REQUEST['pass'];
			if($admin){
				$ans['admin']=infra_admin(false);
				$ans['noclose']=0;
			}else{
				$ans['admin']=infra_admin(array($login,$pass));
				if(!$ans['admin']){
					$ans['msg']='Логин и пароль не совпадают';
				}
			}
			$ans['refresh_only_admin']=1;
		}else if($type=='corfile'){
			$file=$id;
			if(!autoedit_ext($file)) $file.='.tpl';
			$file=infra_theme($file,'sfnm');//Создали путь до файла
			
			//$file=infra_theme($id,'fsn');
			//if(!$file){
			//	$file=infra_tofs(preg_replace('/^\*/','infra/data/',$id));
			//}
			/*$isdir=infra_theme($file,'sdn');
			if($isdir){
				return err($ans,'Существует папка с именем как у файла'.$id);
			}

			$isfile=infra_theme($file,'sfn');
			if(!$isfile){
				$ans['msg'].='Файл был создан<br>';
				//return err($ans,'Файл не найден '.$id);
				$file=infra_theme($file,'sfnm');//Создали путь до файла
			}*/

			$r=file_put_contents(ROOT.$file,$_REQUEST['content']);
			//autoedit_setLastFolderUpdate($file);
			
			$ans['result']=$r;
			//$ans['noclose']=1;
			//$ans['autosave']=0;
			$ans['msg'].='Cохранено';
		}else if($type=='editfile'){
			$ofile=$_FILES['file'];
			$ifolder=infra_toutf($_REQUEST['folder']);
			$folder=infra_theme($ifolder,'sdnm');


			$ans['noclose']=1;
			if($folder){
				$oldname=infra_tofs($_REQUEST['file']);
				$file=$ifolder.infra_toutf($oldname);
				$oldfile=infra_theme($file,'sfn');//Цифры не ищутся когда путь прямой без *
				if(!$oldfile){
					$ans['mmmm']='Не найден старый файл '.$file;
					//return err($ans,'Не найден файл '.infra_toutf($file));
					//Значит старого файла и не было... ну такое тоже возможно... просто создаём новый

				}
				if($oldfile&&$ofile&&!$ofile['error']){//Делаем backup
					if(!autoedit_backup($oldfile))return;
				}
				if($ofile){//Новый файл
					if($ofile['error']){
						if($ofile['error']===4){
							return err($ans,'Не указан файл для загрузки на сервер');
						}else{
							return err($ans,'Ошибка при загрузке файла. Код: '.$ofile['error']);
						}
					}else{
						if($oldfile){
							
							$newname=infra_tofs($ofile['name']);
							$newfile=infra_theme($folder.$newname,'sfn');
							
							$newr=infra_nameinfo($newname);
							$oldr=infra_nameinfo($oldname);
							$oldr['name']=preg_replace("/\s\(\d\)$/",'',$oldr['name']);
							$newr['name']=preg_replace("/\s\(\d\)$/",'',$newr['name']);
							if($newr['name']!=$oldr['name']&&$newr['id']!=$oldr['id']&&$newr['file']!=$oldr['file']){
								return infra_echo($ans,'Имя загружаемого файла должно совпадать с именем<br>'.infra_toutf($oldname).'<br>'.$oldr['id'].'<br>'.$oldr['name'],0);
							}
							$file=$oldfile;
							$r=unlink(ROOT.$file);
							if(!$r) return err($ans,'Не удалось удалить старый файл '.infra_toutf($file));
						}else{
							$extload=preg_match('/\.\w{0,4}$/',$ofile['name'],$match);
							$extload=$match[0];
							$ext=preg_match('/\.\w{0,4}$/',$file,$match);
							$ext=$match[0];
							if(!$ext)$file.=$extload;
							$file=preg_replace('/^\*/','infra/data/',$file);
						}
						if(!is_file($ofile['tmp_name'])){
							return err($ans,'Не найден загруженный файл '.infra_toutf($ofile['name']));
						}
						$file=infra_tofs($file);
						$r=move_uploaded_file($ofile['tmp_name'],ROOT.$file);
						if(!$r) return err($ans,'Не удалось загрузить файл '.ROOT.infra_toutf($file));
						autoedit_setLastFolderUpdate($file);
						$ans['msg']='Файл загружен <span title="'.infra_toutf($file).'">'.infra_toutf($ofile['name']).'</span>';
					}
				}
			}else{
				return err($ans,'Не найдена папка');
			}
		}else if($type=='addfile'){
			$ifolder=infra_toutf($id);

			autoedit_createPath($ifolder);
			$folder=infra_theme($ifolder,'sd');//Создали если нет
			if(!$folder)return err($ans,'Не удалось создать дирректорию');

			$rewrite=$_REQUEST['rewrite'];
			$ofile=$_FILES['file'];
			if(!$ofile){
				return err($ans,'Нe указан файл для загрузки');
			}

			ini_set('upload_max_filesize','16M');//Не применяется
			if($ofile['error']){
				if($ofile['error']==4){
					return err($ans,'Вы не указали файл для загрузки');
				}
				if($ofile['error']===1){
					return err($ans,'Слишком большой размер файла. Файд должен быть не больше '.ini_get('upload_max_filesize'));
				}
				return err($ans,'Ошибка при загрузкe файла '.$ofile['error']);
			}

			$name=infra_toutf($ofile['name']);
			$ans['name']=$name;
			$file=$folder.infra_tofs($name);

			if(!$rewrite&&is_file(ROOT.$file)){
				$ans['edit']=$id.infra_toutf($name);
				return err($ans,'Указанынй файл уже есть');
			}

			$takepath=autoedit_takepath($file);
			$take=infra_plugin($takepath,'fsp');
			if($take&&is_file(ROOT.$file)){
				$ans['edit']=$id.infra_toutf($name);
				$ans['take']=$take;
				return err($ans,'Ошибка! Файл существует и сейчас редактируется!');
			}
			if(!is_file($ofile['tmp_name'])){
				return err($ans,'Не найден загруженный файл '.infra_toutf($ofile['name']));
			}
			if(!move_uploaded_file($ofile['tmp_name'],ROOT.$file)){
				return err($ans,'Не удалось загрузить файл '.infra_toutf($id.$name));
			}
			$ans['noclose']=0;
			$ans['autosave']=1;
			$ans['result']=1;

			/*	$file=infra_theme($id.$name,'sfn');
				$takepath=autoedit_takepath($file);
				$take=infra_plugin($takepath,'sfj');
				$ans['path']=$id.$name;
				$ans['take']=$take;*/
		}else if($type=='takefile'){
			$take=(bool)$_GET['take'];
			$ans['take']=$take;
			$file=infra_theme($id,'sfn');
			$file=infra_toutf($file);
			if(!$file){
				$ans['result']=1;
				$ans['noaction']=true;//Собственно всё осталось как было
			}else{
				$takepath=autoedit_takepath($file);
				if(!$take&&is_file(ROOT.$takepath)){
					$ans['result']=@unlink(ROOT.$takepath);
				}else if($take&&!is_file(ROOT.$takepath)){//Повторно захватывать не будем
					$save=array('path'=>$id,'date'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'browser'=>$_SERVER['HTTP_USER_AGENT']);
					$ans['result']=file_put_contents(ROOT.$takepath,infra_tojs($save));
				}else{
					$ans['noaction']=true;//Собственно всё осталось как было
					$ans['result']=true;
				}
			}
		}else if($type=='deletefile'){
			$ans['noclose']=0;//закрывать окно по окончанию
			$ans['autosave']=0;//Не очищать autosave

			$file=infra_theme($id,'sfn');
			if(!$file){
				return infra_echo($ans,'Файл не найден '.infra_toutf($id),0);
			}
			$takepath=autoedit_takepath($oldfile);
			$take=infra_plugin($takepath,'fsp');
			if($take){
				$ans['editfile']=$id;
				$ans['takeinfo']=$take;
				return infra_echo($ans,'Файл занят '.infra_toutf($id),0);
			}
			if(!autoedit_backup($file)){//Сообщение об ошибке есть там
				return;
				//return infra_echo($ans,'Не удалось сделать резервную копию '.infra_toutf($id),0);
			}
			$ans['result']=unlink(ROOT.$file);
		}else if($type=='renamefile'||$type=='copyfile'){
				$ans['noclose']=0;//закрывать окно по окончанию
				$ans['autosave']=0;//Не очищать autosave
				$oldfolder=infra_theme($_REQUEST['oldfolder'],'sdn');
				if(!$oldfolder){
					return err($ans,'Не найдена оригинальная папка '.infra_toutf($_REQUEST['oldfolder']));
				}
				$oldname=infra_tofs($_REQUEST['oldname']);
				$oldfile=infra_theme($oldfolder.$oldname,'sfn');
				if(!is_file(ROOT.$oldfile)){
					return err($ans,'Не найден оригинальный файл'.infra_toutf($_REQUEST['oldold']));
				}
				$takepath=autoedit_takepath($oldfile);
				$take=infra_plugin($takepath,'fsp');
				if($take){
					$ans['editfile']=$_REQUEST['oldfolder'].$_REQUEST['oldname'];
					$ans['takeinfo']=$take;
					return err($ans,'Файл занят');
				}
			if($type=='renamefile'||$type=='copyfile'){
				$newname=trim(infra_tofs($_REQUEST['newname']));
				if(!$newname){
					return err($ans,'Не указано имя нового файла '.infra_toutf($oldfile));
				}
				$isfull=(bool)$_REQUEST['full'];
				if($isfull){
					$ans['newfile']=$_REQUEST['newfolder'].$_REQUEST['newname'];
					$newfolder=infra_theme($_REQUEST['newfolder'],'sdn');
					if(!$newfolder){
						return err($ans,'Не найдена папка '.infra_toutf($newfolder));
					}
				}else{
					$ans['newfile']=$_REQUEST['oldfolder'].$_REQUEST['newname'];
					$newfolder=$oldfolder;
				}
				if(($newfoler==$oldfolder&&$newname==$oldname)){
					return err($ans,'Нужно указать новое имя файла '.infra_toutf($oldfile));
				}

				$newfile=$newfolder.$newname;
				$r=infra_theme($newfolder.$newname,'sfn');
				if($r){
					$ans['editfile']=$ans['newfile'];
					return err($ans,'Указанный файл '.infra_toutf($newfolder.$newname).' уже существует.');
				}
			}

			if($type=='renamefile'){
				$ans['result']=rename(ROOT.$oldfile,ROOT.$newfile);
			}
			if($type=='copyfile'){
				$ans['result']=copy(ROOT.$oldfile,ROOT.$newfile);
			}
		}else{
			return err($ans,'Действие неопределенно');
		}
	}else{
		return err($ans,'Вам нужно авторизоваться');
	}
	if(!isset($ans['result'])){
		$ans['result']=1;
	}
	return infra_echo($ans);
?>
