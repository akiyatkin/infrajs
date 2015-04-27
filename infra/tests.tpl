{root:}
<html>
<head>
	<link href="../../../vendor/twbs/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
	<script src="../../../vendor/components/jquery/jquery.js"></script>
	<script src="../../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
	<table class="table">
	<thead>
		<tr class="bg-primary">
			<td>
			Имя файла
			</td>
			<td>
			Название теста
			</td>
			<td>
			Результат
			</td>
			<td>
			Сообщение
			</td>
		</tr>
	</thead>
	<tbody>
		{::someres}
	</tbody>
	</table>
</body>
</html>
{someres:}
		<tr class="bg-info"><th colspan="4">{~key}</th></tr>
		{::sometest}
		{sometest:}
			<tr class="{class?class?(result?:bg-success?:bg-warning)}">
				<td>
				<a href="../../../{src}">{name}</a>
				</td>
				<td>
				{title}
				</td>
				<td>
				{result}
				</td>
				<td>
				{msg}
				</td>
			</tr>

			
		
	
	

