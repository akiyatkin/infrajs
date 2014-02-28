<div class="block_news">
	{config.list::some}
	<a href="#{state}" class="all_news">{config.goall}</a>
</div>
{some:}
	<div class="some_news" style="margin-bottom:15px">
		{config.date.top?:date}
		<a href="#{state}/{name}" class="name">{title}</a>
		{img?:img}
		<div class="description">
			{preview}
		</div>
		{config.more?:more}
		{config.date.bottom?:bottomdate}
	</div>
{date:}
	<div class="date">
		<img class="icon" src="infra/infra/theme.php?pages/news/news.png" />
		{@date:j F Y}
	</div>
{bottomdate:}
	<div class="date_bottom">{sdate}</div>
{img:}
	{config.image_align?:imgalign?:imgnotalign}
{imgalign:}
	{image?:imagea}
{imgnotalign:}
	<div>
		{image?:imageb}
	</div>
{more:}
	<a href="#{state}/{name}" class="more"><img src="infra/infra/theme.php?pages/news/more.png" /></a>
{imagea:}
	<a href="#{state}/{name}">
		<img class="{config.image_align}" style="margin-top:5px" src="infra/plugins/imager/imager.php?w={config.width}&h={config.height}&crop={config.crop}&src={img}" />
	</a>
{imageb:}
		<a href="#{state}/{name}">
			<img class="news_img" src="infra/plugins/imager/imager.php?w={config.width}&h={config.height}&crop={config.crop}&src={img}" />
		</a>
