{producers:}
	<h1>Производители</h1>
	<div style="padding:10px; font-size:12px; margin-bottom:20px;">
		{data.producers::catprod1}
	</div>
	<div style="background-color:white; padding:10px; text-align:center; margin-bottom:20px;">
		{data.producers::catprod}
	</div>
	{data.text}
	<div style="margin-top:10px">
		<a href="?{state.parent}">Каталог</a>
	</div>
	{catprod1:}
	<a href="?{state.parent}/{~key}" title="{~key} {.}">{~key}</a>{~last()?:point?:comma} 
	{comma:}, 
	{point:}.
	{catprod:}
		<a href="?{state.parent}/{~key}" title="{~key} {.}"><img alt="{~key}" style="margin-bottom:10px" src="infra/plugins/imager/imager.php?w=100&src={infra.conf.catalog.dir}{~key}/&or=*imager/empty"></a>
{rubrics:}
	<style>
		.cat_rub {
			font-family: Tahoma;
			font-size: 14px;
			line-height: 19px;
		}
		.cat_rub .lev1 {
			padding: 9px 11px 8px 11px;
			background-color: #f17900;
			border-top: 1px solid #f28212;
		}
		.cat_rub .lev1 img {
			float: left;
			display: inline;
			margin-top: 6px;
		}
		.cat_rub .lev1 a {
			color: #fff;
			text-decoration: none;
			display: block;
			padding-left: 20px;
		}
		.cat_rub .lev1 a {
			text-decoration:underline;
		}
		.cat_rub .separator {
			border-top: 1px solid #cb5b1e;
			background-color: #fff;
			height: 1px;
			font-size: 0px;
		}
		.cat_rub .level {
			border-left: 1px solid #ccc;
			border-top: 1px solid #cb5b1e;
			margin-bottom: 1px;
		}
		.cat_rub .lev2 {
			padding: 3px 15px 3px 15px;
			background-color: #e2e2e2;
			border-top: 1px solid #fff;
			border-bottom: 1px solid #ccc;
		}
		.cat_rub .lev2 a {
			color: #cb5b1e;
			text-decoration: none;
			display: block;
			padding-left: 15px;
			height: 23px;
			padding-top: 1px;
		}
	</style>
	<div class="cat_rub">
		{data.childs::item}
	</div>
	{item:}
		<div class="lev1">
			<img src="{infra.theme(:*cart/minus.png)}" />
			<a href="?{state}/{title}">{title}</a>
		</div>
		{~last()|:sep}
	{sep:}<div class="separator"></div>
{stat:}
	<h1 title="c {~date(:d.m.Y,data.stat.time)}">Последние запросы набранные в строке поиска по каталогу</h1>
	<table class="common">
		<tr><td></td><td>Фразы</td></tr>
		{data.stat.users::statuser}
	</table>
	<p>
		<a href="?{state.parent}">Каталог</a>
	</p>
	{data.text}
	{statuser:}
		<tr>
			<td style="vertical-align:bottom; font-size:20px; text-align:left; color:gray;"><b title="от {~date(:d.m.Y,time)}">{cat_id}</b></td>
			<td>{list::statitem}</td>
		</tr>
	{statitem:}<a href="?{state.parent}/{val}" title="от {~date(:d.m.Y,time)}">{val}</a><sup>{count}</sup>{~last()|:statsep}
	{statsep:} |  

{groups:}
	{data.text}
	{data.childs::cat_group}
	
	<div style="border-top:1px gray dotted; margin-top:10px; padding-top:10px;">
		<a href="?{state}/Статистика">Статистика поиска</a> 
		| <a href="?{state}/Производители">Производители</a>
		| <a href="?{state}/Изменения">Последние изменения</a>
	</div>
	{cat_group:}
	<div>
		<h2>{title}</h2>
		<p>
			{descr.Описание группы}
		</p>
		<p>
			<a href="?{state}/{title}" title="Открыть группу {~lower(title)}">{title}</a>
		</p>
	</div>
	
{logo:}
	<img src="infra/plugins/imager/imager.php?w=300&src={infra.conf.catalog.dir}{Производитель}/{article}/" style="margin:0 0 5px 5px;">
{itemcost:}{~cost(Цена)} руб.
{itemnocost:}<a style="color:white" href="?Контакты менеджеров">Уточнить</a>
{country:}<div style="text-align:right; font-size: 11px; margin-top:5px;">
		{producer.Страна|}
	</div>








{pos:}
	<style>
	#position {
		font-family: Tahoma;
		font-size: 13px;
		color: #404040;
	}
		#position a {
			color: #cb5b1e;
		}
		#position a img {
			border: none;
		}
		#position .bigimage {
			border-top:1px dotted gray;
			text-align:center;
			padding-top:10px;
			padding-bottom:10px;
		}
		#position h1 {
			margin:0;padding:0;
			margin-top: 0px;
			padding-top: 0px;
			padding-bottom: 4px;
		}
		#position h2 {
			margin:0;padding:0;
			padding-bottom: 4px;
			margin-top: 15px;
			margin-bottom: 9px;
		}
		#position h3 {
			margin:0;padding:0;
			padding-bottom: 4px;
			margin-top: 15px;
			margin-bottom: 9px;
		}
		#position .files {
			margin:0;padding:0;
			list-style: none;
			margin-top: 6px;
		}
			#position .files li {
				line-height: 18px;
				padding-left: 25px;
			}
			#position .files .ico {
				/*background-image: url("images/pdf_icon.png");*/
				background-repeat: no-repeat;
				background-position: 0px 1px;
			}
		/*#position .information {
			line-height: 18px;
			margin-top: 25px;
			font-weight: bold;
		}*/
	</style>
	{data.result?data.pos:start}
{start:}
	<div id="position" class="cat_item">
		<div style="float:right">
		{:producer}
		</div>
		<h1>
			{Наименование}<br>{Производитель} {Артикул}
		</h1>
		{~length(images)?:images}
		<div style="color:gray; margin-bottom:30px">{Описание}</div>
		<table class="common" style="width:100%; margin-bottom:20px;">
			
			<tr>
			<td>
					<table style="float:left" cellpadding="0" cellspacing="0">
						<tr>
							<td class="price" style="padding:0 10px">{Цена?:itemcost?:itemnocost}</td>
							<td>
								<div data-article="{article}" data-producer="{Производитель}" class="basket_img"></div>
							</td>
						</tr>
					</table>
					<div class="posbasket" style="margin-bottom:3px; display:none">
						<small>Позиция в <a href="?office/cart">корзине</a></small>
					</div>
					
			</td><td style="width:100%"><div style="line-height:30px; font-size:16px; text-align: left;">
					Телефон менеджера <span style="font-size:24px">+7 ({data.phone.code}) {data.phone.number}</span>
				</div></td></tr>
			<tr><td colspan="2">
				
				<table>
					<tr><td>Синхронизация{Код?:space}{Код}:</td><td>{Синхронизация=:Да?:успешно?(Код?:ошибка?:нет)}{Наличие на складе?:nalichie}</td></tr>
				</table>
			</td></tr>
		</table>
		<table class="common" style="width:auto">
		{more::pos_more}
		</table>
		{texts::text}
		{~length(files)?:files}
		<p></p>
		{~parse(Подпись)}
		<p>
			Перейти к группе <a href="?{state.parent.parent}/{group_title}">{group_title}</a>
		</p>
	</div>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var div=$('#'+layer.div);
			catalog.initPrice(div);
		});
	</script>
	{nalichie:}, {~lower(Наличие на складе)}
	{space:} 
{pos_more:}<tr><td>{~key}:</td><td style="text-align:left">{.}</td></tr>
{files:}
	<h2>Файлы для {Продажа} {Производитель} {Артикул} </h2>
		<ul class="files">
			{files::file}
		</ul>
	{file:}
		<li class="ico" style="background-image:url('infra/plugins/infra/theme.php?*/autoedit/icons/{ext}.png')">
			<a href="{src}">{name}</a> {size}&nbsp;Mb
		</li>
{text:}
	{.}
{imgsrc:}{.}
{images:}
	<div style="text-align:center; background-color:white; padding:10px; ">
		{images::image}
	</div>
	<div class="bigimage"></div>
	{image:}
	<a onclick="return false" title="{..Наименование}" href="infra/plugins/imager/imager.php?src={:imgsrc}">
		 <img 
		title="{data.pos.Производитель} {data.pos.Артикул}"
		style="cursor:pointer"
		onclick="var img=document.getElementById('catimg{~key}'); if(img){ $(img).toggle(); return; }; 
				$('#position .bigimage').html('<img style=\'border-bottom:1px dotted gray;\' onclick=\'$(this).hide()\' id=\'catimg{~key}\' src=\'infra/plugins/imager/imager.php?mark=1&w=590&src={:imgsrc}\' />')" 
		src="infra/plugins/imager/imager.php?mark=1&h=100&src={:imgsrc}" />
		</a>
{producer:}
	<div style="float:right; background-color:white; padding:10px 10px 10px 10px; margin-left:5px; margin-bottom:5px;">
		<a title="Посмотреть продукцию {producer.Производитель}" href="?{state.parent.parent}/{producer.Производитель}">
		<a href="?{state.parent}/{~key}" title="{~key} {.}"><img alt="{~key}" style="margin-bottom:10px" src="infra/plugins/imager/imager.php?w=100&src={infra.conf.catalog.dir}{~key}/&or=*imager/empty"></a>
			<img style="margin-left:5px" src="infra/plugins/imager/imager.php?w=160&h=100&src={infra.conf.catalog.dir}{producer.Производитель}/&or=*imager/empty" />
		</a>
	</div>
<!--	<div style="text-align:right; font-size: 11px; margin-top:5px;">
		{producer.Страна|}
	</div>
	-->
