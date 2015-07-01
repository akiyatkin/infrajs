{POPTUBE:}
	{conf_ids::POPTUBEs}
{POPTUBEs:}
	<iframe style="margin:5px auto" width="640" height="480" src="http://www.youtube.com/embed/{.}?rel=0" frameborder="0" allowfullscreen></iframe>
{POPIMG:}
	<div><img style="margin:5px auto;" src="?*imager/imager.php?w=500&src={conf_ids[conf_sel]}"></div>
	{conf_ids::POPIMGs}
{POPIMGs:}
	<img style="margin:5px; cursor:pointer;" src="?*imager/imager.php?w=40&src={.}" onclick="var layer=infrajs.getUnickLayer('{unick}'); layer.conf_sel={$key}; infrajs.check(layer);"></div>
{LIST:}
	<style>
		.biga {
			text-decoration: none;
		}
		.biga:hover {
			text-decoration: underline;
		}
	</style>
	<table style="width:100%;margin-top:18px;">
		{data.list::news_item}
	</table>
	{news_item:}
			<tr><td rowspan="3" style="width:120px">
				<a href="?{crumb}/{name}" style="color:#333">
					{images.0.src:Nimg}
				</a>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<a href="?{crumb}/{name}" class="biga">
						<div style="margin-left:10px; font-weight:normal; font-size:14px; color:gray; float:right; text-transform: lowercase;">{$date(:j F Y,date)}</div>
						<span style="margin-top:10px; font-size:22px;">{name}</span>
					</a>
				</td>
			</tr>
			<tr><td>{preview}</td>
			</tr>
{comma:},
{FILES:}
	<table class="common">
	{data.list::Fitem}
	</table>
	{Fitem:}
		<tr><td><img src="?*imager/imager.php?src=*autoedit/icons/{ext}.png&w=16"></td><td>{name}<br><a href="?*rubrics/rubrics.php?id={name}&type={crumb.name}&load">Скачать</a></td><td>{size}&nbsp;Mb</td><td>{~date(:j.m.Y,date)}</td></tr>
		</div>
{PAGES:}
	<hr>
	{data.list::Pitem}
	
	{Pitem:}
		<div style="margin-bottom:30px">
			{date:Pdate}
			<h2 style="margin-top: 5px;">{heading|name}</h2>
			{preview}
			<a style="text-decoration: none; float: right;" href="?{crumb}/{name}">Читать полностью</a>
		</div>
		<hr>
	{Pdate:}<i style="color: #aaaaaa;">{:date}</i>
	{date:}{~date(:j F Y,.)}
	{j F Y:}j{:nbsp}F{:nbsp}Y{:nbsp}
	{nbsp:}&\n\b\s\p;
	{Pimg:}<img src="?*imager/imager.php?src={src}&w=100" class="right">
{EVENTS:}
	<style>
		.eborder_post {
			width: 100%;
			border-bottom:1px dashed gray; 
		}
		.elist {
			/*background-color: #fff;*/
			/*width: 489px;*/
			height: 100%;
			display: block;
		}
		.elist a {
			text-decoration: underline;
			/*color: red;*/
		}
		.elist .date {
			width: 74px;
			display: block;
			background-color: #ececec;
			float:right;
			border-radius: 10px;
			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			padding:5px;
		}
		.elist p {
			margin-bottom:0;
			margin-top:0;
		}
		.elist .date span {
			font-size: 11px;
			margin-left: 10px;
			color: #1b1b1b;
		}
		.elist h4 {
			margin:0;
			margin-bottom:5px;
		}
		.elist h4 {
			font-size: 14px;
			text-transform: uppercase;
		}
		.elist h4 a {
			color: red;
		}
		.elist h4 a:hover {
			color: #1b1b1b;
		}
	</style>
	<section class="elist">
		{data.list::Nart}
	</section>
	{Nart:}
		<table style="width:100%; margin:0 10px;">
			<tr>
				<td style="vertical-align:top; padding-top:20px; text-align:center; width:140px;">
					{images.0.src:Nimg}
				</td>
				<td style="padding:20px 20px">
					{preview}
					<table>
						{links::Nlink}
					</table>
					<a href="?{crumb}/{name}">
					<div class="date"><span>{$date(:j.m.Y,date)}</span></div>
					</a>
				</td>
			</tr>
		</table>
		<div class="eborder_post"></div>
	{Nimg:}<img style="border: 2px solid #E1E1E1;" src="?*imager/imager.php?w=100&src={$encode(.)}">
	{Nlink:}
		<tr><td> &mdash;&nbsp;</td><td><a href="{href}">{heading|title}</a></td></tr>