{seo:}
	<h1>Поисковая оптимизация - SEO</h1>
	<div id="selfpageseo" style="margin-bottom:10px"></div>
	<table class="common">
		{data.list::listlayer}
	</table>
	<script>
		infra.wait(infrajs,'onshow',function(){

			var store=infrajs.store();
			//слой который сейчас определяет seo это последний показываемый слой с seo параметрами
			var layer=false;
			infrajs.run(infrajs.getAllLayers(),function(l){
				if(!l.showed)return;
				if(!l.seo)return;
				layer=l;
			});
			if(!layer)return;
			var name=layer['seo']['name'];
			var linktpl='{{crumb}}';
			var link=infra.template.parse([linktpl],layer);			
			var	tpl='*seo/seo.tpl';
			var obj={
				title:link||'Главная',
				id:name+'|'+link
			}
			var html=infra.template.parse(tpl,obj,'selfpageseo');
			var div=document.getElementById('selfpageseo');
			div.innerHTML=html;
		});
	</script>
	{:seosavepopup}
	{listlayer:}
		<tr>
			<td><span onclick="infrajs.SEO('editname','{.}')" class="a">{.}</span></td>
		</tr>
	{selfpageseo:}Настройки текущей страницы: <span class="a" onclick="infrajs.SEO('edititem','{id}')">{title}</span>
{allitems:}
	<h1>Предусмотренные страницы</h1>
	<table><tr><td>Слой:</td><t{data.seo.user?:h?:d}>{data.id:clickname}</t{data.seo.user?:h?:d}	></tr></table>
	{~length(data.seo.defitems)?:ai_list?:noitemslist}
	{:seosavepopup}
	{:scriptinfo}
	{ai_list:}
		Согласно имеющимся данным предусмотренны следующие страницы:
		<table>
			{data.seo.items::ai_chitems}
		</table>
	{ai_chitems:}
		<tr class="inaction">
			<t{user?:h?:d}><span class="a" onclick="infrajs.SEO('edititem','{data.seo.name}|{link}')">{link|:Главная}</span></t{user?:h?:d}>
			<td><div class="action"></div></td></tr>
	{noitemslist:}Список всех страниц не доступен.
{editname:}
	<h1>{data.id}</h1>
	<table>
		<tr>
			<td><table class="common">{data.names::chname}</table></td>
			<td>
				<table>
					{data.data.items::chitems}
					<tr><td>{data.data.schema?:editnameadd}{~length(data.data.defitems)?data.data:clickall?:itemslistno}</td><td></td></tr>
				</table>
			</td><td></td>
		</tr>
	</table>
	{:seosavepopup}
	{editnameadd:}<span class="a" onclick="infrajs.SEO('additem','{data.data.name}')">Добавить</span>&nbsp;
	{:scriptinfo}
	{itemslistno:} полный список не предусмотрен
	{scriptinfo:}
		<style>
			#{div} .inaction {
				cursor:default;
			}
			#{div} .action {
				height:16px;
				visibility:hidden;
			}
			#{div} .action img {
				cursor:pointer;
			}
		</style>
		<script type="text/javascript">
				infra.wait(infrajs,'oncheck',function(){
					$('.inaction').hover(function(){
						$(this).find('.action').css('visibility','visible');
					},function(){
						$(this).find('.action').css('visibility','hidden');
					});
				});
		</script>
	{chname:}
		<tr><td style="font-weight:{.=data.id?:bold}">{:chnamelink}</td></tr>
		{chnamelink:}<span class="a" onclick="infrajs.SEO('editname','{.}')">{.}</span>
	{chitems:}
		<tr class="inaction">
			<t{user?:h?:d}><span class="a" onclick="infrajs.SEO('edititem','{data.data.name}|{link}')">{link|:Главная}</span></t{user?:h?:d}>
			<td><div class="action">{layer|:imgdel}</div></td>
		</tr>
	{imgdel:}<img onclick="infrajs.SEO('delitem','{data.data.name}|{link}')" src="?*imager/imager.php?src=*autoedit/images/delete">
{delitem:}
	<h1>Удалить запись о странице</h1>

	<table>
		<tr><td>Слой:</td><td>{data.seo.name?data.seo.name:clickname?:noname}</td></tr>
		<tr><td>Страница:</td><td>{data.seo.item?:clickitem?:noitem}</td></tr>
	</table>
	{data.seo.item?:delitemform}
	{delitemform:}
	{:form}	
			<p style="margin:5px">
				После удаления останутся значения по умолчанию.
			</p>
			{:submit}Удалить{:/submit}
	{:/form}
	{clickall:}
	 	<span class="a" onclick="infrajs.SEO('allitems','{name}')">Показать предусмотренные страницы</span>
	{clickname:}
		<span class="a" onclick="infrajs.SEO('editname','{.}')">{.}</span>
	{clickitem:}
		<span class="a" onclick="infrajs.SEO('edititem','{data.seo.name}|{data.seo.item.link}')">{data.seo.item.link|:Главная}</span>
	{noitem:}
		<b>{data.link}</b> не найдена
	{noname:}
		<b>{data.name}</b> не найден
{additem:}
	<h1>Добавить страницу</h1>
	<p>Слой: <b>{config.id}</b>.</p>
	{data.seo.schema?:additemtext?:additemno}
	{additemno:}<p>Добавить нельзя. Список страниц фиксировнный.</p>
	{additemtext:}
		{:form}	
		<div><textarea autosavebreak="1" style="font-family:Tahoma; font-size:12px; color:#444444; width:500px; height:200px" name="itemdata"></textarea></div>
			{:submit}Добавить{:/submit}
		{:/form}
		<script type="text/javascript">
			infra.wait(infrajs,'onshow',function(){

				var layer=infrajs.getUnickLayer("{unick}");
				var ta=$('#'+layer.div).find('textarea').get(0);
				var schema=layer.data.seo.schema;
				schema.title="{config.id}";
				schema.required=true;
				infra.require('*autoedit/autoedit.js');
				AUTOEDIT.jsonedit(ta,schema);
			});
		</script>
{edititem:}
	<h1>SEO описание страницы</h1>
	<table>
		<tr><td>Слой: </td><td>{data.seo.name?data.seo.name:clickname?:noname}</td></tr>
		<tr><td>Страница: </td><td><a href="?{data.link}">{data.link|:Главная}</a></td></tr>
		<tr><td>Уникальное описание: </td><td title="{data.link}">{data.seo.item.user?:et_unickyes?:et_unickno}</td></tr>
	</table>
	{data.seo.name?:et_go?:et_error}
	{et_error:}Что-то пошло не так.
	{et_go:}
		{data.seo.defitem|data.seo.item?:et_body?:et_create}
	{et_create:}
		<div><span class="a" onclick="infrajs.SEO('additem','{data.seo.name}');">Создать страницу</span> &mdash; нужно указать параметры страницы</div>
	{et_unickyes:}
		Есть, <span class="a" onclick="infrajs.SEO('delitem','{data.seo.name}|{data.link}')">очистить</a>
	{et_unickno:}
		Нет
	{et_body:}
		{:form}
			<h2>Заголовок - Title</h2>
			<center>
				<textarea name="seo[title]" style="font-family:Tahoma; font-size:12px; color:{data.seo.title?:green?:#444444}; width:500px; height:15px">{data.seo.item.title}</textarea>
				{data.seo.defitem.title:et_def}
			</center>

			<h2>Описание - Description</h2>
			<center>
				<textarea name="seo[description]" style="font-family:Tahoma; font-size:12px; color:{data.seo.description?:green?:#444444}; width:500px; height:45px">{data.seo.item.description}</textarea>
				{data.seo.defitem.description:et_def}
			</center>

			<h2>Ключевые слова - Keywords</h2>
			<center>
				<textarea name="seo[keywords]" style="font-family:Tahoma; font-size:12px; color:{data.seo.keywords?:green?:#444444}; width:500px; height:60px">{data.seo.item.keywords}</textarea>
				{data.seo.defitem.keywords:et_def}
			</center>
			{:submit}Сохранить{:/submit}
		{:/form}
		<div style='width:500px; margin-top:10px;'>
			<h2>Контент страницы</h2>
			{data.text|:et_notext}
		</div>
	{et_notext:}{data.tpl?:et_yestpl?:et_notpl}
	{et_yestpl:}<i>Cодержание для <b>{data.name}</b> не найдено <br><b>{data.tpl}</b></i>
	{et_notpl:}<i>Для слоя <b>{data.name}</b> не указано где искать содержание</i>
	{et_def:}<div style="font-size:12px; padding:2px; color:gray;text-align:left; width:500px; border:dotted 1px gray;">
					<div style=" ">
						{.}
					</div>
				</div>



{form:}
	<form action="{infra.theme(:*seo/seo.php)}?submit=1" method="post">
	<input type="hidden" name="type" value="{config.type}">
	<input type="hidden" name="id" value="{config.id}">
{/form:}
	{:close}Отмена{:/close}
	</form>
	{config.ans.msg|}
	{:seosavepopup}
{submit:}<input style="margin-right:10px;margin-top:5px;padding:0px 10px" type="submit" value="{/submit:}">
{close:}
	<input type="button" style="margin-right:10px;margin-top:5px;padding:0px 10px" value="{/close:}" onclick="popup.hide()">


{seotpl:}
	Страница: <b><a href="?{data.id}">{data.id|:Главная}</a></b>
	{:form}

	<h2>Заголовок - Title</h2>
	<center>
		<textarea name="def[title]" style="display:none">{title}</textarea>
		<textarea name="seo[title]" style="font-family:Tahoma; font-size:12px; color:{data.seo.title?:green?:#444444}; width:500px; height:34px">{data.seo.title|(title|)}</textarea>
	</center>

	<h2>Описание - Description</h2>
	<center>
		<textarea name="def[description]" style="display:none">{description}</textarea>
		<textarea name="seo[description]" style="font-family:Tahoma; font-size:12px; color:{data.seo.description?:green?:#444444}; width:500px; height:96px">{data.seo.description|(description|)}</textarea>
	</center>

	<h2>Ключевые слова - Keywords</h2>
	<center>
		<textarea name="def[keywords]" style="display:none">{keywords|}</textarea>
		<textarea name="seo[keywords]" style="font-family:Tahoma; font-size:12px; color:{data.seo.keywords?:green?:#444444}; width:500px; height:96px">{data.seo.keywords|(keywords|)}</textarea>
	</center>

	{:submit}Сохранить{:/submit}
	{:/form}
	
{seosavepopup:}
	<script>
		infrajs.popup_memorize('infra.require("*seo/seo.js");infrajs.SEO("{config.type}","{config.id}");');
	</script>