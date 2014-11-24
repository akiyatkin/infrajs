{root:}
<html>
<head>
		<script type-"text/javascript" src="../../../infra/lib/jquery/jquery.js"></script>
		<link rel="stylesheet" href="../../../infra/lib/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="../../../infra/lib/bootstrap/css/bootstrap-theme.min.css">
		<script src="../../../infra/lib/bootstrap/js/bootstrap.min.js"></script>
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
			<tr class="{result?:bg-success?:bg-warning}">
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

			
		
	
	

