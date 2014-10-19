{root:}
	{data:searchgood}
	<script type="text/javascript">
		infra.wait(infrajs,'onshow',function(){
			var inp=$('[name=search]');
			var val=infra.State.getState().child.child.name;
			inp.val(val).change();
		});
	</script>
	{searchgood:}
		<style>
			/*.position {
				margin-bottom:40px;
			}*/
			.catgrouplist .count {
				font-size:10px;
				
				color:gray;
			}
			.catgrouplist .bigbtnover .count {
				color:#CCC;
			}
		</style>
		<p style="float:right">{parent:cat_childsp}</p>
		<h1>{title} <span style="font-size:12px;">{count} {~words(count,:позиция,:позиции,:позиций)}</span></h1>
		{~length(filters)?filters:cat_filters}
		{childs?:cat_groups}
		{pages>:1?:cat_numbers}
			{:cat_items}
		<p>{descr}</p>
		{text}
		{cat_items:}
		<div class="cat_items" style="padding:20px 0px;">
			<style>
				.cat_items .cat_item {
					margin-bottom:40px;
				}

				.cat_items .title {
					width:440px;
					height:20px;
				}
				/*.cat_items .cost {
					padding:4px 8px;
					background-color:#89B806;
					vertical-align:middle;
					color:white;
					width:86px;
					text-align:center;
					font-size:16px;
				}
				.cat_items .cost .unit {
					font-size:12px;
				}*/
				.cat_items .title a {
					display:block;
					background-color:#EFEFEF;
					/*height:100%;*/
					padding:4px 8px;
					text-decoration: none;
					color:#222222;
				}
				.cat_items .titleover a {
					background-color:#009EC3;
					color:white;
				}
				
				.cat_items .producer {
					padding:10px 4px;
					vertical-align:top;

				}
				.cat_items .cat_item {
					width:100%;
				}
			

				.cat_items .img {
					padding-right:20px;
				}
				.cat_items .img img {
					padding:2px;
					margin:auto;
				}
				.cat_items .img {
					width:120px;
					height:120px;
				}
				.cat_items .img div {
					box-shadow: 0px 0px 4px #009EC3;
					width:120px;
					height:120px;
					text-align:center;
					background-color:white;
				}
				.cat_items .params a {
					color:#009EC3;
					font-style:italic;
					font-size:12px;
				}
				.cat_items .params {
					padding:8px 8px;
					width:380px;
					height:50px;
				}
				.cat_items .catdescr {
					display:none;
					/*margin-top:10px;*/
				}
				.cat_items .catdescr p {
					font-size:12px;

				}

			</style>
			{list::cat_item}
			<script>
				
				infra.when(infrajs,'onshow',function(){
					var layer=infrajs.getUnickLayer('{unick}');
					var div=$('#'+layer.div);
					var counter={counter};	
					div.find('.cat_item .title').hover(function(){
						$(this).addClass('titleover');
					},function(){
						$(this).removeClass('titleover');
					});
					/*div.find('.cat_item .cart').hover(function(){
						$(this).addClass('cartover');
					},function(){
						$(this).removeClass('cartover');
					});*/
					div.find('.cat_item .descr').click(function(){
						$(this).next().toggle();
					});

					catalog.initPrice(div);
				});
			</script>
		</div>
		{cat_cart_add:}Позиция добавлена в заявку<br><a href="?office/cart" onclick="popup.close()">Перейти к заявке</a> <span onclick="popup.close()" class="a">продолжить поиск</span>
		{cat_cart_remove:}Позиция убрана из заявки<br><a href="?office/cart" onclick="popup.close()">Перейти к заявке</a> <span onclick="popup.close()" class="a">продолжить поиск</span>
		{cat_item:}
			<table class="cat_item" cellpadding="0" cellspacing="1">
			<tr>
				<td rowspan="3" class="img">
					<a href="?Каталог/{Производитель}/{article}/">
					<div>
						<img src="infra/plugins/imager/imager.php?mark=1&w=116&h=116&src={infra.conf.catalog.dir}{Производитель}/{article}/&or=*imager/empty" />
					</div>
					</a>
				</td>
				<td class="title" colspan="3">
					<a href="?Каталог/{Производитель}/{article}/">{Наименование}</a>
				</td>
			</tr>
			<tr>
				<td class="params">
					{Производитель} <b>{Артикул}</b> <div style="float:right"><a href="?Каталог/{Производитель}">{Производитель}</a> <a href="?Каталог/{group_title}">{group_title}</a></div>
					<div>
						{Наличие на складе?:nalichie}
						{more::cat_more}
					</div>
				</td>
				<td rowspan="2" colspan="2" class="producer">
					<a title="Посмотреть продукцию {Производитель}" href="?{state.parent}/{Производитель}">
						<img src="infra/plugins/imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{Производитель}/&or=*imager/empty" />
					</a>
					{:priceblock}
				</td>
			</tr>
			<tr>
				<td colspan="1" style="padding:0 8px;"><span class="a descr">Краткое описание</span>
					<div class="catdescr">
						<p>
						{Описание}
						</p>
						<a href="?Каталог/{Производитель}/{article}/">Полное описание</a>
					</div>
				</td>
			</tr>
			</table>
		{nalichie:}<span class="label {Наличие на складе=:В наличии?:label-primary?:label-info}">{Наличие на складе}</span><br>
		{cat_more:}{~key}:&nbsp;{.}{~last()|:comma} 
		{cat_forward:}Вперёд&nbsp;→
		{cat_forward_href:}<a onclick="infrajs.scroll=false" href="?{state}/p{~sum(page,:1)}">{:cat_forward}</a>
		{cat_back:}←&nbsp;Назад
		{cat_back_href:}<a onclick="infrajs.scroll=false" href="?{state}/p{~sum(page,:-{:1})}">{:cat_back}</a>
		{cat_numbers:}
			<table style="margin-top:20px" class="numbers" cellpadding="0" cellspacing="1">
			<tr>
				<td class="{page=:1?:lock?:back}">{page=:1?:cat_back?:cat_back_href}</td>
				{numbers::cat_num}
				<td class="{page=pages?:lock?:forward}">{page=pages?:cat_forward?:cat_forward_href}</td>
			</tr>
			</table>
			<script>
				infra.wait(infrajs,'onshow',function(){
					var layer=infrajs.getUnickLayer('{unick}');
					var div=$('#'+layer.div);
					var counter={counter};	
					div.find('.num').hover(function(){
						$(this).addClass('over');
					},function(){
						$(this).removeClass('over');
					});
					
				});
			</script>
			<style>
				.numbers td {
					font-size:18px;
					vertical-align: middle;
					text-align: center;
				}
				.numbers .back,
				.numbers .lock,
				.numbers .forward {
					padding-left:10px;
					padding-right:10px;
				}
				.numbers .over {
					background-color:#0089a9;
					color:white;
				}
				.numbers a,
				.numbers .space {
					padding:3px;
				}
				.numbers a {
					text-decoration: none;
					display:block;

				}
				.numbers .space,
				.numbers .sel a,
				.numbers .num a {
					width:38px;
				}
				.numbers .over a {
					color:white;
				}
				.numbers .num {
					cursor:pointer;

				}
				.numbers .sel {
					background-color:#0089a9;
					color:white;
				}
				.numbers .sel a {
					color:white;
				}
				.numbers .lock {
					cursor:default;
					color:gray;
				}
			</style>
		{cat_num:}<td class="{.??:space} {.&.!...page?:num} {.=...page?:sel}">{.?:cat_num_href?:cat_num_space}</td>
		{cat_num_space:}...
		{cat_num_href:}<a onclick="infrajs.scroll=false" href="?{state}/p{.}">{.}</a>
		{cat_filters:}
			<style>
				.cat_filters {
					font-size:12px;
					margin-bottom:10px;
				}
				.cat_filters .cancel {
					background-color:#F96A30;
					color:white;
					width:14px;
					height:14px;
					line-height:10px;
					font-size:10px;
					text-align:center;
					vertical-align:middle;
					cursor:pointer;
				}
			</style>
			<div class="cat_filters">
				Фильтр:
				{::cat_filter}
			</div>
			<script>
				infra.when(infrajs,'onshow',function(){
					$('.cat_filters .cancel').click(function(){
						var name=$(this).data('name');
						
						infra.session.set(['filtersadmit',name],null);
						infra.session.set(['filtersadmit','no',name],null);
						infra.session.set(['filtersadmit','yes',name],null,true);
						infrajs.global.set(['cat_search','cat_filters']);

						var layer=infrajs.run(infrajs.getAllLayers(),function(layer){
							if(layer.global=='cat_filters')return layer;
						});
						if(layer){
							infrajs.autosave.set(layer,'checks.'+name);
							infrajs.autosave.set(layer,'checks.yes.'+name);
							infrajs.autosave.set(layer,'checks.no.'+name);
							infrajs.check();
						}
					});
				});
			</script>
			{cat_filter:}<table cellspacing="1" cellpadding="0"><tr><td data-name="{name}" class="cancel">X</td><td>&nbsp;{name}:&nbsp;</td><td>{slide?:cat_fil_slide?:cat_fil_values}{yes?:cat_yes}{no?:cat_no}</td></tr></table>
			{cat_fil_slide:}от {~cost(min)} до {~cost(max)}{:optunit?: }{:optunit}
			{cat_fil_values:}{values::cat_fil_value}
			{cat_fil_value:}{.}{~last()|:comma}
			{cat_yes:}{~length(values)|slide?:comma} указано
			{cat_no:}{~length(values)|slide?:comma} не указано
			{comma:},
			{optunit:}{infra.conf.cart[:optunitname]}
			{optunitname:}unit{name}
		{cat_child:}
			<a href="?{state.parent}/{title}" title="Показать группу {~lower(title)}">
				<table cellspacing="0" cellpadding="0">
					<td class="img">
						{pos?:catchimg}
					</td>
					<td class="name">
						{title} <span class="count">{count}</span>
					</td>
				</table>
			</a>
		{catchimg:}<img src="infra/plugins/imager/imager.php?w=100&h=60&src={infra.conf.catalog.dir}{pos.producer}/{pos.article}/&or=*imager/empty">
		{tr:}<tr>{/tr:}</tr>
		{cat_childsp:}
			<a style="color:gray" href="?{state.parent}/{title}" title="Показать группу {~lower(title)}">{title}</a>{~last()|:br}
		{br:}<br>
	{cat_groups:}
		<div class="catgrouplist">
			{childs::cat_child}
			<style>
				.catgrouplist .name {
					vertical-align:middle;
					padding:10px 5px;
					font-size:16px;
				}
				.catgrouplist .img {
					width:106px;
					height:66px;
					vertical-align:middle;
					text-align:center;
					font-size:0;
				}
				.catgrouplist a {
					float:left; 
					width:350px; 
					display:block;
					text-decoration:none;
				}
			</style>
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
		</div>
	{itemcost:}{~cost(Цена)} <span class="unit">руб.</span>
	{itemnocost:}<a style="color:white" href="?Контакты менеджеров">Уточнить</a>
{producerSmall:}
	<div style="float:right; background-color:white; padding:5px; margin-left:5px; margin-bottom:5px;">
		<a title="Посмотреть продукцию {Производитель}" href="?{state.parent}/{Производитель}">
			<img  src="infra/plugins/imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{Производитель}/&or=*imager/empty" />
		</a>
	</div>
{group:}
		<a title="Посмотреть продукцию {Производитель}" href="?{state.parent}/{Производитель}">{Производитель}</a>, 
		<a title="Перейти к группе {group_title}" href="?{state.parent}/{group_title}">{group_title}</a>
