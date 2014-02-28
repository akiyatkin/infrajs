<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Режим браузера</title>
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
		var init=function(){

		}
		</script>
	</head>
	<body style="padding:50px 100px" onload="init()">
		<?php
			$conf=infra_config();
			$r=$conf['debug'];
		if(!$r){
			echo '<h1 style="color:green">PASS</h1>';
		}else{
			echo '<h1 style="color:red">ERROR</h1>';
		}
		?>
		<p>
			config.debug
		</p>
		<p>
			<?php
				$conf=infra_config();
				if($conf['debug']){
					echo 'ON';
				}else{
					echo 'OFF';
				}
			?>
		</p>
	</body>
</html>