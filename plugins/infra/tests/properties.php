<!DOCTYPE html>
<html>
	<head>
		<title>Показываются ли ошибки</title>
		<script type="text/javascript">
			ROOT='../../../../';
		</script>
		<script type="text/javascript" src="../js.php"></script>
		<script>
		var good=function(){
			var res=document.getElementById('res');
			res.innerHTML='PASS';
			res.style.color="green";
		}
		var init=function(){

		}
		</script>
	</head>
	<body style="padding:50px 100px" onload="init()">
		<hr>
		<p>
			<table>
				<tr>
					<td>Название</td><td>Значение</td><td>Для транка</td><td>Для продакшина</td><td>Описание</td>
				</tr>
			{::option}
		</p>
	</body>
</html>
	{option:}
		<tr>
			<td>{name}</td>
			<td>{value}</td>
			<td style="color:{value=trunk?:green?:red}">{trunk}</td>
			<td style="color:{value=production?:green?:red}">{production}</td>
			<td>{description}</td>
		</tr>
<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	
	infra_require('*infra/ext/template.php');
	$ans=array();


	$conf=infra_config();
	$ans[]=array(
		'name'=>'debug',
		'value'=>$conf['debug'],
		'trunk'=>1,
		'production'=>0,
		'description'=>'В конфиге системы флаг включающий режим отладки и отключающий большинство кэшей'
	);

	//error_reporting(E_ALL);
	$ans[]=array(
		'name'=>'error_reporting',
		'value'=>error_reporting(),
		'trunk'=>32767,
		'production'=>0,
		'description'=>''
	);
	



	$h=ob_get_clean();
	$h=infra_template_parse(array($h),$ans);
	echo $h;
?>