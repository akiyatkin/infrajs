<div class="news_list">
	<style>
		.news_list p {
			margin:0;
			margin-bottom:5px;
		}
	</style>
	<h1>{config.heading}</h1>
	{config.data.list::list}
	<table cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td style="text-align:left">
			{config.data.prev?:prev?&nbsp;}
		</td>
		<td style="text-align:right">
			{config.data.next?:next?&nbsp;}
		</td>
	</tr>
	</table>
</div>
{list:}
	<p><a href="#{state}/{name}" class="title">{title|name}</a> {date?:date}</p>
	{img?:img}
	{preview}
	{img?</td></tr></table>?<br>}
{date:}
	<i>{strdate}</i>
{img:}
	<table cellpadding="0" cellspacing="0" style="margin-bottom:10px"><tr>
			<td>
				<a href="#{state}/{name}">
					<img style="margin-right:5px" src="infra/plugins/imager/imager.php?w=100&src={image}" />
				</a>
			</td>
			<td>
{next:}
	<a onclick="window.notscroll=true" href="#{state}{config.data.next}">След.</a>
{prev:}
	<a onclick="window.notscroll=true" href="#{state}{config.data.prev}">Пред.</a>
