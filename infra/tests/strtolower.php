<!DOCTYPE html>
<html>
	<head>
		<title>Привидение кирилице к малому регистру</title>
		<script type="text/javascript">
			ROOT='../../../../';
		</script>
		<script type="text/javascript" src="../initjs.php"></script>
		<script>
		var good=function(){
			var res=document.getElementById('res');
			res.innerHTML='PASS';
			res.style.color="green";
		}
				</script>
	</head>
	<body style="padding:50px 100px" onload="init()">
			{result?:pass?:error}
	</body>
</html>
{pass:}<h1 style="color:green">PASS</h1>
{error:}<h1 style="color:red">ERROR</h1>
	
<?php
	$html=ob_get_clean();
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
	
	infra_require('*infra/ext/template.php');
	$ans=array('result'=>0);


	$conf=infra_config();
	
	$s1=infra_tofs('Кирилица utf8');
	$s2=infra_tofs('кирилица utf8');

	$r=(infra_strtolower($s1)==$s2);
	$ans['result']=$r;
	



	
	$h=infra_template_parse(array($html),$ans);
	echo $h;
?>