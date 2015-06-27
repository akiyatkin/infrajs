{root:}
<html>
<head>
	<link href="vendor/twbs/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
	<script src="?*infra/js.php"></script>
	<script src="vendor/components/jquery/jquery.js"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script>infra.Crumb.init()</script>
</head>
<body>
	<h1>Tests</h1>
	<table class="table">
	<thead>
		<tr class="bg-primary">
			<td>
			Filename
			</td>
			<td>
			Title
			</td>
			<td>
			Result
			</td>
			<td>
			Message
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
			<tr class="{class?class?(result?:bg-success?:bg-danger)}">
				<td>
				<a href="?*{src}">{name}</a>
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

			
		
	
	

