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
	<a href="?{state.parent}/{$key}" title="{$key} {.}">{$key}</a>{$last()?:point?:comma} 
	{comma:}, 
	{point:}.
	{catprod:}
		<a href="?{state.parent}/{$key}" title="{$key} {.}"><img alt="{$key}" style="margin-bottom:10px" src="infra/plugins/imager/imager.php?w=100&src={infra.conf.catalog.dir}{$key}/&or=*imager/empty"></a>
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
			<img src="{infra.theme(:*catalog/minus.png)}" />
			<a href="?{state}/{title}">{title}</a>
		</div>
		{$last()|:sep}
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
{find:}
	<h1>Поиск по каталогу</h1>
	<form onsubmit="
		var val=$(this).find('[type=text]').val();
		val=infra.State.forFS(val);
		infra.State.go('{state.parent.name}/'+val);
		setTimeout(function(){
			$.getJSON(infra.theme('*catalog/catalog.php?type=stat&submit=1&val='+val));
		},1);
		return false;">
		<table style="width:730px;" cellspacing="0" cellpadding="0">
			<tr>
				<td>
					<input style="width:100%; margin:0; padding:4px; font-weight:bold; font-size:20px;" name="search" size="25" type="text" placeholder="Поиск по каталогу" value="">
				</td>
				<td style="text-align:right">
					<button style="
					font-size:19px; 
					padding:5px 26px; margin:0 0 0 20px;" href="?{state.parent.name}" onclick="$(this).parents('form').submit(); return false;">Искать</button>    
				</td>
			</tr>
		</table>
		<input type="submit" style="display:none;">
	</form>
{SEARCH:}
		<style>
			.href {
				text-decoration:none;
			}
			.position {
				margin-bottom:40px;
			}
		</style>
		<script>
			if(!window.catalog)window.catalog={ 
				search:'Значение поиска',
				path:['Путь','До','Группы']
			};

			infra.wait(infrajs,'oncheck',function(){

				var layer=infrajs.getUnickLayer({unick});
				window.catalog.search=infra.State.getState().child.child.name;

				infra.when(layer,'onhide',function(){
					window.catalog.search='';
					window.catalog.path=[];
				});

				infra.when(layer,'onshow',function(){
					var data=infrajs.getData(layer);
					if(!data.path)data.path=[];
					window.catalog.path=data.path;
				});
			});
		</script>
		{data.result?data:searchgood?data:searchbad}
		{searchbad:}
			<h1>{val}</h1>
			<p>
				К сожалению ничего не найдено.
			</p>
			<p>
				<a href="?{state.parent}">{state.parent.name}</a>
			</p>
			{text}
		{isproducer:}producer
		{isgroup:}group
		{issearch:}search
		{searchgood:}
		
				{parent:cat_childsp}
				{data.is=:isproducer?:Производитель}{data.is=:isgroup?:Группа}{data.is=:issearch?:Поиск}
				<h1 style="margin-bottom:5px; margin-top:5px;">{data.name}</h1>
				{~length(data.bread.prods)?:search_prods}
				{~length(data.bread.groups)?:search_groups}
				<div style="margin-bottom:5px">{data.count} {~words(data.count,:позиция,:позиции,:позиций)}</div>
			
			{:groupsonly}
			<div style="background-color:white; padding:20px 30px;">
				{list::cat_item}
			</div>
			<h1>{title}</h1>
			<p>{descr}</p>
			{text}
			{text?:groupsonly}
			{cat_childs:}
				<a style="font-size:16px; line-height:24px;" href="?{state.parent}/{title}" title="Показать группу «{title}»">{title}</a>{~last()|:br}
			{cat_childsp:}
				<a href="?{state.parent}{title!:Каталог?:cat_plink}" title="Показать группу «{title}»">{title}</a>{~last()|:br}
			{br:}<br>
			{cat_plink:}/{title}
		{search_groups:}
			<table cellspacing="0" cellpadding="0" style="margin:5px 0 10px 0;">
				{data.bread.groups::bread_group}
			</table>
		{search_prods:}
			<div style="font-size:12px;">
				Производители: {data.bread.prods::bread_prod}
			</div>
			<script type="text/javascript">
				infra.when(infrajs,'onshow',function(){
					var layer=infrajs.getUnickLayer('{unick}');
					if(!layer.config)layer.config={ };
					var data=infrajs.getData(layer);
					if(data.prodpage){
						layer.config.sel=layer.state.name;
					}
					$('#'+layer.div).find('.someprod').click(function(){
						var sel=$(this).data('name');
						if(layer.config.sel==sel){
							layer.config.sel='ПРОДУКЦИЯ';
						}else{
							layer.config.sel=sel;						
						}
						infrajs.run(infrajs.getAllLayers(),function(l){
							if(!layer.conf_prod)return;
							if(!l.config)l.config={ };
							l.config.sel=layer.config.sel;
						});
						infrajs.check();
					});
				});
			</script>
		{bread_prod:}
			<button style="padding:4px 8px; cursor:pointer; margin-right:6px; {.=data.sel?:bread_sel2}" data-name="{.}" class="someprod{.=data.sel?: sel}">
				{.}
			</button> 
		{bread_sel2:} border: 2px inset gray;
		{bread_sel:} font-weight:bold
		{bread_logo:}<a href="?{state.parent}/{data.sel}"><img class="right" style="margin:5px" src="infra/plugins/imager/imager.php?h=40&or=img/bg.png&src=*Каталог/{data.sel}/"></a>
		{bread_group:}
			{$even()?:s_tr}
			<td style="padding:2px 10px 2px 0;{title=state.name?:bread_sel}"><a href="?{state.parent}/{title}">{name}</a></td>
			{$odd()?:e_tr}
{groupsonly:}
	<style>
		.catgrouplist .img {
			vertical-align:middle;
			width:100px;
			padding:4px;
			height:90px;
			text-align:center;
			background-color:white;
		}
		.catgrouplist .name {
			text-align:left;
			font-family:Premjera;
			vertical-align:middle;
			font-size:20px;
			padding-left:4px;
		}
		.catgrouplist {
			border-bottom:1px solid #EEEEEE;
		}
		.catgrouplist a {
			display:block;
			width:300px;
			float:left;
		}
		
	</style>
	<div class="catgrouplist">
		{data.childs::groups_group}
		<div style="clear:both"></div>
	</div>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer({unick});
			$('#'+layer.div).find('.catgrouplist a').hover(function(){
				$(this).addClass('bigbtnover');
			},function(){
				$(this).removeClass('bigbtnover');
			});
		});
	</script>
	
{groups_group:}
	<a href="{infra.conf.catalog.link}/{title}">
		<table>
			<tr>
				<td class="img">
					<img  src="infra/plugins/imager/imager.php?w=100&h=80&src={infra.conf.catalog.dir}{pos.producer}/{pos.article}/&or=*imager/empty">
				</td>
				<td class="name">
					{name}
				</td>
			</tr>
		</table>
	</a>
{groups:}
	<h1>{conf_title|:Продукция}</h1>
	{:groupsonly}
	<div style="margin-top:15px; margin-bottom:15px;">
		{data.childs::cat_group}
	</div>
	<div style="border-top:1px gray dotted; margin-top:10px; padding-top:10px;">
		<a href="?{state}/Поиск">Поиск</a>
		| <a href="?{state}/Статистика">Статистика поиска</a> 
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
{cat_item:}
	<div class="position">
		<div style="text-align:right">{time?~date(:j F Y,time)}</div>
		<h2>
			<a class="href" href="?{state.parent}/{Производитель}/{article}">{Наименование}</a>
		</h2>
		<table style="width:100%">
		<tr>
		<td style="width:160px; padding-right:10px;">
			<a class="href" href="?{state.parent}/{Производитель}/{article}">
				<div class="pic">
					<img src="infra/plugins/imager/imager.php?mark=1&w=160&src={infra.conf.catalog.dir}{Производитель}/{article}/&or=*imager/empty" />
				</div>
			</a>
		</td>
		<td style="padding-right:5px">
			{:producerSmall}
			<a class="href" href="?{state.parent}/{Производитель}/{article}">
				<h3 style="margin-top:0; margin-bottom:10px;">
					{Производитель} {Артикул}
				</h3>
			</a>
			<div>
				{Описание}
			{:group}
			</div>
		</td>
		</tr>
		</table>
	</div>

{group:}
	<div style="margin-top:5px; font-size:12px;">
		<a title="Посмотреть продукцию {Производитель}" href="?{state.parent}/{Производитель}">{Производитель}</a>, 
		<a title="Перейти к группе {group_title}" href="?{state.parent}/{group_title}">{group_title}</a>
	</div>

{producerSmall:}
	<div style="float:right; background-color:white; padding:5px; margin-left:5px; margin-bottom:5px;">
		<a title="Посмотреть продукцию {Производитель}" href="?{state.parent}/{Производитель}">
			<img  src="infra/plugins/imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{Производитель}/&or=*imager/empty" />
		</a>
	</div>







{pos:}
	<style>
	#position {
		font-family: Tahoma;
		font-size: 13px;
		color: #404040;
	}
		#position a {
			/*color: #cb5b1e;*/
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
	<script>
			if(!window.catalog)window.catalog={ 
				search:'Значение поиска',
				path:['Путь','До','Группы']
			};

			infra.wait(infrajs,'oncheck',function(){

				var layer=infrajs.getUnickLayer({unick});
				window.catalog.search=infra.State.getState().child.child.name;

				infra.when(layer,'onhide',function(){
					window.catalog.search='';
					window.catalog.path=[];
				});

				infra.when(layer,'onshow',function(){
					var data=infrajs.getData(layer);
					if(!data.path)data.path=[];
					window.catalog.path=data.path;
				});
			});
		</script>
	<div id="position">
		<div style="float:right">
		{:producer}
		</div>
		<h1>
			{Наименование}<br>{Производитель} {Артикул}
		</h1>
		{~length(images)?:images}
		{Цена?:poscost}
		<div style="color:gray; margin-bottom:30px">{Описание}</div>
		{texts::text}
		{~length(files)?:files}
		<p></p>
		{~parse(Подпись)}
		<p>
			Перейти к группе <a href="?{state.parent.parent}/{group_title}">{group_title}</a>
		</p>
	</div>
{poscost:}
	<div class="alert alert-success">
		Цена: <span style="font-size:20px">{~cost(Цена)} руб.</span><br>
		{Наличие?: Есть в наличие.} По вопросам приобретения обращайтесь по телефонам в <a href="?Контакты">контактах</a>.
	</div>
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
		onclick="var img=document.getElementById('catimg{$key}'); if(img){ $(img).toggle(); return; }; 
				$('#position .bigimage').html('<img style=\'border-bottom:1px dotted gray;\' onclick=\'$(this).hide()\' id=\'catimg{$key}\' src=\'infra/plugins/imager/imager.php?mark=1&w=590&src={:imgsrc}\' />')" 
		src="infra/plugins/imager/imager.php?mark=1&h=100&src={:imgsrc}" />
		</a>
{producer:}
	<div style="float:right; background-color:white; padding:10px 10px 10px 10px; margin-left:5px; margin-bottom:5px;">
		<a title="Посмотреть продукцию {producer.Производитель}" href="?{state.parent.parent}/{producer.Производитель}">
			<img style="margin-left:5px" src="infra/plugins/imager/imager.php?w=160&h=100&src={infra.conf.catalog.dir}{producer.Производитель}/" />
		</a>
	</div>
<!--	<div style="text-align:right; font-size: 11px; margin-top:5px;">
		{producer.Страна|}
	</div>
	-->


	
	
{s_tr:}<tr>
{e_tr:}</tr>