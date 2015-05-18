<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');

	$ans = array(
		'title'=>'Узнаем значение debug'
	);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Режим браузера</title>
		<script>
			ROOT='../../../../';
		</script>
		<script src="../initjs.php"></script>
		<script>
			var good=function(){
				var res=document.getElementById('res');
				res.innerHTML='PASS';
				res.style.color="green";
			};
			var init=function(){

			};
		</script>
	</head>
	<body style="padding:50px 100px" onload="init()">
		<?php
			$conf=infra_config();
			$r=$conf['debug'];
			if($r){
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
				$conf = infra_config();
				if($conf['debug'] == 1){
					$ans['result'] = 1;
					return infra_ret($ans, "тест пройден");
				}
				else{
					$ans['result'] = 0;
					return infra_err($ans, "тест пройден");
				}
				//Test проходит проверку. но почему то Некорректный json
			?>
		</p>
	</body>
</html>