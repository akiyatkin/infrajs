<?php
/*
Copyright 2008-2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
*/
	//require_once('../../../../vendor/autoload.php');
	//require_once(__DIR__.'/infra.php');
	if(isset($_REQUEST['json'])){//Для данных для слоя
		$ans=array('result'=>1);
		$ans['admin']=infra_admin();
		return infra_echo($ans);
	}
?>
<script src="?*infra/js.php"></script>
<script>infra.Crumb.init()</script>
<a href="?*infra/tests.php">Тесты</a>
<?php
	if(isset($_REQUEST['login'])){
		infra_admin(true);
?>
		<div style="padding:50px 100px">
			<p>Вы администратор</p>
			<p><a href="?">Проверить</a></p>
		</div>
<?php
	}else if(isset($_REQUEST['logout'])){
		infra_admin(false);		
?>

		<div style="padding:50px 100px">

			<p>Вы обычный посетитель</p>
			<p><a href="?">Проверить</a></p>
		</div>

<?php
	}else{
		$r=infra_admin();
		if($r){
?>
		<div style="padding:50px 100px">
			<p>Вы администратор</p>
			<p><a href="?logout">Выход</a></p>
		</div>
<?php	
		}else{
?>
		<div style="padding:50px 100px">
			<p>Вы обычный посетитель</p>
			<p><a href="?login">Вход</a></p>
		</div>
<?php	
		}
	}
?>
