{root:}
	<!DOCTYPE html>
	<title>Доступные тесты</title>
	<body style="padding:50px 100px">
		<h1>Доступные тесты</h1>
		{::plugin}
	</body>
{plugin:}
	<h2>{folder}</h2>
	{list::item}
{item:}
	<a href="../{folder}/tests/{name}">{name}</a>{$last()?:point?:comma}
{point:}.
{comma:},