{root:}
	<style>
		.adminka form {
			margin:0;
			padding:0 0 5px 0;
		}
		.adminka table th,
		.adminka table td {
			text-align:left;
		}
		.adminka h1 {
			margin-top:0;
			text-align:center;
		}
		.adminblock {
			border:1px red solid;
			cursor:pointer;
		}
		.adminka {
			/*font-size:12px;*/
		}
		.adminka input {
			font-size:12px;
		}
		.adminka .aebutton {
			cursor:pointer;
			text-decoration:none;
			border-bottom:dashed 1px gray;
		}
		.teditfolder thead td {
			font-weight:bold;
		}
		.teditfolder td {
			font-size:12px;
		}
		.teditfolder tr.over {
			background-color:lightblue;
		}
		.teditfolder tr {
		}
		.teditfolder td {
			padding:3px;
		}
		.adminka .param td {
			vertical-align:middle;
		}

	</style>
	<div class="adminka">
		{config.now.admin?data:type_admin}
		{config.now.version?data:type_version}
		{config.now.autoedit?data:type_autoedit}
		{config.now.editfolder?:type_editfolder}
		{config.now.takeinfo?data:type_takeinfo}
		{config.now.takeshow?data:type_takeshow}
		{config.now.allblocks?data:type_allblocks}
		{config.now.addfile?data:type_addfile}
		{config.now.deletefile?data:type_deletefile}
		{config.now.renamefile?data:type_renamefile}
		{config.now.copyfile?data:type_copyfile}
		{config.now.editfile?data:type_editfile}
		{config.now.corfile?data:type_corfile}
		{config.now.settings?data:type_settings}
		{config.now.createcache?data:type_createcache}
		
		{config.ans.msg?:showansmsg?:nextifshowimg}


		{config.ans.editfile:editfilelink}
		{config.ans.takeinfo:takeinfolink}
		{data.image?:showidimage}
	</div>
	<script type="text/javascript">

		var div=$('#{div}');
		var type="{config.type}";
		var id="{config.id}";
		if(type=='admin'){
			/*div.find('.refall').click(function(){
				ADMIN.refreshAll();
			});*/
			div.find('.submit').click(function(){
				$(div).find('form').submit();
			});
		}else if(type=='allblocks'){
			var layers=infrajs.getLayers();
			var layer=infrajs.run(layers,function(layer){
				if(layer.config&&layer.config.type=='allblocks'){
					return layer;
				}
			});
			if(layer){
				infra.fora(layer.config.list,function(block){
					div.find('.block'+block.num).click(function(){
						ADMIN.autoedit([block.layer]);
					});
				});
			}
		}else if(type=='editfile'){
		}else if(type=='autoedit'){
			/*div.find('.editfolder').click(function(){
				ADMIN('editfolder',$(this).text(),conf.layer.autoedit.folder);
			});
			div.find('.editfile').click(function(){
				ADMIN('editfile',$(this).text(),conf.layer.autoedit.file);
			});*/
		}else if(type=='editfolder'){
			div.find('table.teditfolder tbody tr').hover(function(){
				$(this).addClass('over');	
			},function(){
				$(this).removeClass('over');	
			});
		}
		div.find('.refreshAll').fadeTo(1,0.3).hover(function(){
			$(this).stop().fadeTo('fast',1);
		},function(){
			$(this).stop().fadeTo('fast',0.3);
		});
	</script>
	{val:}{$key}:"{.}",
	{nextifshowimg:}
		{data.msg?:showansmsg}
	{showansmsg:}
			<div>{config.ans.msg|data.msg|}</div>
	{showidimage:}
		<br><img src="{infra.theme(:*imager/imager.php)}?w=400&src={data.id}">
	{editfilelink:}
		<span class="aebutton" onclick="ADMIN('editfile','{.}')">{.}</span><br>
	{takeinfolink:}
		<span class="aebutton" onclick="ADMIN('takeinfo','{path}')">{$word(:_takedate,date)}</span>
{type_corfile:}
	{:form}
		{:infofile}
		<textarea style="font-family:Tahoma; font-size:12px; color:#444444; width:500px; height:300px" name="content">{content}</textarea>
		<br>
		{:submit}Сохранить{:/submit}
	{:/form}
{type_addfile:}
	{:form}
		Папка <b><span class="aebutton" onclick="ADMIN('editfolder','{data.id}')">{data.id}</span></b><br>
		<input style="margin:5px 0" type="file" name="file"><br>
		<div style="margin:5px 0">
			<input type="checkbox" name="rewrite" style=""> — перезаписать, если такой файл уже есть
		</div>
		<div style="margin-bottom:5px">
		{config.ans.edit?:oldedit}
		{data.take?:oldtake}
		</div>
		{:submit}ОК{:/submit}
	{:/form}
	{oldedit:}
		<span class="aebutton" onclick="ADMIN('editfile','{data.id}{config.ans.name}')">{config.ans.name}</span>
	{oldtake:}
		, <span class="aebutton" onclick="ADMIN('takeinfo','{data.id}{config.ans.name}')" style="color:red">{$date(:_takedate,config.ans.take.date)}</span>
{type_editfile:}
	<h1>Редактирование файла</h1>
	{:form}
	<input type="hidden" name="file" value="{file}">
	<input type="hidden" name="folder" value="{folder}">
	<table class="param">
		<tr><td>Папка:&nbsp;</td>
			<td><span class="aebutton" onclick="ADMIN('editfolder','{folder}')">{folder}</span></td></tr>
		<tr><td>Файл:&nbsp;</td>
			<td style="font-size:14px">
				<img alt=" " src="{infra.theme(:*autoedit/icons/)}{ext}.png" title="{ext}"> 
				{data.isfile?:editfilea?file}
				<span class="action">
					{data.isfile?:editfileload}
					{data.corable?:corable}
					{data.isfile?:editfiledel}
				</span>
			</td></tr>
			{data.isfile?:editfileinfo}
	</table>
	{data.isfile?:getfile}
	<div style="border-top:dotted 1px gray; margin-top:5px;"></div>
	<table style="margin-top:5px; margin-bottom:5px;">
		<tr><td style="vertical-align:middle">{data.isfile?:Заменить?:Создать}</td>
			<td><input type="file" value="Обновить" name="file"></td></tr>
		<tr><td colspan=2><small>{data.isfile?:editishelp?:имя загружаемого файла не принимается во внимание}</small></td></tr>
	</table>
	{:submit}Сохранить{:/submit}
	{:/form}
	{editishelp:}имя загружаемого файла должно быть <i>{file}</i>
	{editfileinfo:}
			<tr><td>Размер</td>
			<td>{size} Кб</td></tr>
		<tr><td>Последние изменения</td>
			<td>{$date(:_takedate,time)}</td></tr>
	{editfilea:}
		<a style="text-decoration:underline" title="Открыть файл в браузере" target="_blank" href="{infra.view.getRoot()}{path}">{file}</a>&nbsp;
	{editfileload:}
		<a href="{pathload}" onclick="ADMIN.takefile('{config.id}',true)"><img alt="load" title="Скачать" src="{infra.theme(:*autoedit/images/floppy.png)}"></a>
	{editfiledel:}
		<img alt="del" style="cursor:pointer" onclick="ADMIN('deletefile','{folder}{file}')" title="Удалить" src="{infra.theme(:*autoedit/images/delete.png)}"> 
	{corable:}
		<img alt="edit" style="cursor:pointer" onclick="ADMIN('corfile','{folder}{file}');" title="Редактировать" src="{infra.theme(:*autoedit/images/edit.png)}"> 
		{data.rteable?:rteable}
	{getfile:}
		<div style="margin:5px">
			{data.take?:getfilebad?:getfilegood}
		</div>
	{getfilebad:}
		<span style="font-weight:bold; color:red;">Файл редактируется <span onclick="ADMIN('takeinfo','{config.id}')" style="cursor:pointer; text-decoration:underline;">{$date(:_takedate,data.take)}</span></span><br>
		<span onclick="ADMIN.takefile('{config.id}',false);" style="cursor:pointer; text-decoration:underline">освободить файл</span> 
	{getfilegood:}
		<span style="color:darkgreen">Файл можно редактировать</span><br>
		<span onclick="ADMIN.takefile('{config.id}',true);" style="cursor:pointer; text-decoration:underline">захватить файл</span>
	{rteable:}
		<img alt="rte" style="cursor:pointer" onclick="AUTOEDIT('rte','{folder}{file}');" title="Визуальный редактор" src="{infra.theme(:*autoedit/images/rte.png)}"> 
{_takedate:}H:i d.m.Y
{type_editfolder:}
	<h1>Редактирование папки</h1>
	<b>{data.id}</b><br>
	<!--<span class="aebutton" onclick="name=prompt('Укажите имя нового файла, после этого Вы сможете его загрузить');if(name)ADMIN('editfile','{data.id}'+name);">cоздать файл</span> -->
	<span class="aebutton" onclick="ADMIN('addfile','{data.id}')">загрузить файл</span>,  
	<span class="aebutton" onclick="ADMIN('corfile','{data.id}Новый файл.tpl')">создать файл</span>, 
	<span class="aebutton" onclick="AUTOEDIT('mkdir','{data.id}')">создать папку</span>
	<table class="teditfolder" style="margin-top:10px">
		<thead>
			<tr onmouseover="$(this).find('.action').css('visibility','visible')" onmouseout="$(this).find('.action').css('visibility','hidden')">
				<td></td><td style="padding:5px">Файл</td><td>Кб</td><td colspan="2">Дата&nbsp;изменения</td><td>
			</tr>
		</thead>
		<tbody>
			{data.parent:edftop}
			{data.folders::folders}
			{data.list::file}
		</tbody>
	</table>
	{:close}Закрыть{:/close}
	{edftop:}
		<tr>
			<td><img src="{infra.theme(:*autoedit/icons/dir.png)}" title="dir"></td>
			<td class="folder" style="padding:3px">
				<span class="aebutton" onclick="ADMIN('editfolder','{data.parent}')">..</span>
			</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	{folders:}
		<tr style="color:{take?red}" onmouseover="$(this).find('.action').css('visibility','visible')" onmouseout="$(this).find('.action').css('visibility','hidden')">
			<td><img src="{infra.theme(:*autoedit/icons/)}dir.png" title="dir"></td>
			<td class="folder" style="padding:3px">
				<span class="aebutton" onclick="ADMIN('editfolder','{data.id}{name}/')">{name}</span>
			</td>
			<td>&nbsp;</td><td>{date?:date?}</td>
			<td style="padding:0;padding-top:2px">
				<span class="action" style="visibility:hidden">
					<img alt="del" style="cursor:pointer" onclick="AUTOEDIT('rmdir','{data.id}{name}/')" title="Удалить" src="{infra.theme(:*autoedit/images/delete.png)}"> 
					<img alt="name" style="cursor:pointer" onclick="AUTOEDIT('mvdir','{data.id}{name}/')" title="Переименовать" src="{infra.theme(:*autoedit/images/rename.png)}">
					<!--<img alt="copy" style="cursor:pointer" onclick="AUTOEDIT('cpdir','{data.id}{name}/')" title="Создать копию" src="{:*autoedit/images/copy.png}"> -->
				</span>
			</td>
		</tr>
	{file:}
		<tr style="color:{take?red}" onmouseover="$(this).find('.action').css('visibility','visible')" onmouseout="$(this).find('.action').css('visibility','hidden')">
			<td><img alt=" " src="{infra.theme(:*autoedit/icons/)}{ext}.png" title="{ext}"></td>
			<td class="file" style="padding:3px">
				<span class="aebutton" onclick="ADMIN('editfile','{data.id}{name}{ext?:point}{ext}');">{name}{ext?:point}{ext}</span>
			</td>
			<td>{size}</td><td>{$date(:d.m.Y,date)}</td>
			{mytake?:actions?:strtake}
		</tr>
	{point:}.
	{strtake:}
			<td>
				<span class="aebutton" onclick="ADMIN('takeinfo','{data.id}{name}{ext?:point}{ext}')">{$date(:_takedate,take)}</span>
			</td>
	{actions:}
			<td style="padding:0;padding-top:2px">
				<nobr class="action" style="visibility:hidden">
					<a href="{pathload}"><img alt="load" title="Скачать" src="{infra.theme(:*autoedit/images/floppy.png)}"></a>
					<img alt="del" style="cursor:pointer" onclick="ADMIN('deletefile','{data.id}{name}{ext?:point}{ext|}')" title="Удалить" src="{infra.theme(:*autoedit/images/delete.png)}"> 
					<img alt="name" style="cursor:pointer" onclick="ADMIN('renamefile','{data.id}{name}{ext?:point}{ext|}')" title="Переименовать/переместить" src="{infra.theme(:*autoedit/images/rename.png)}">
					<img alt="copy" style="cursor:pointer" onclick="ADMIN('copyfile','{data.id}{name}{ext?:point}{ext|}')" title="Создать копию" src="{infra.theme(:*autoedit/images/copy.png)}"> 
					{corable?:cancorfile}
					{rteable?:filerteable}
				</nobr>
			</td>
	{cancorfile:}
			<img alt="edit" style="cursor:pointer" onclick="ADMIN('corfile','{data.id}{name}{ext?:point}{ext}');" title="Редактировать" src="{infra.theme(:*autoedit/images/edit.png)}"> 
	{filerteable:}
		<img alt="rte" style="cursor:pointer" onclick="AUTOEDIT('rte','{config.id}{name}{ext?:point}{ext}');" title="Визуальный редактор" src="{infra.theme(:*autoedit/images/rte.png)}"> 
{type_takeshow:}
	{files.length?:listshow?:nolistshow}
	{listshow:}
		<table style="font-size:12px">
		<tr>
			<td></td><th>Файл</th><th>Дата отметки</th><th>Дата изменения</th><th>IP</th>
		</tr>
			{files::listtakefiles}
		</table>
	{listtakefiles:}
		<tr>
		<td><img alt=" " src="{infra.theme(:*autoedit/icons/)}{ext}.png" title="{ext}"></td>
		<td onclick="ADMIN('editfile','{path}')" style="cursor:pointer; text-decoration:underline;">{path}</td>
		<td onclick="ADMIN('takeinfo','{path}')" style="cursor:pointer; text-decoration:underline;">{$date(:_takedate,date)}</td>
		<td>{$date(:_takedate,modified)}</td>
		<td>{ip}</td>
		</tr>
	{nolistshow:}
		Сейчас нет редактируемых кем-то файлов
{type_takeinfo:}
	{data.take?:takeyes?:takeno}
	{takeno:} 
		<span class="aebutton" onclick="ADMIN('editfile','{data.path}')">{data.path}</span><br>
		Файл свободен для редактирования<br>
		<span class="aebutton" onclick="popup.close();ADMIN.takefile('{data.path}',true)">Занять</span>
	{takeyes:}
		<table style="font-size:12px">
			<tr><th>Файл</th><td><img alt=" " src="{infra.theme(:*autoedit/icons/)}{ext}.png" title="{ext}"> <span class="aebutton" onclick="ADMIN('editfile','{data.path}')">{data.path}</span></td></tr>
			<tr><th>Дата отметки</th><td>{$date(:_takedate,data.take.date)}</td></tr>
			<tr><th>IP:</th><td>{data.take.ip|}</td></tr>
			<tr><th>Браузер:</th><td>{data.take.browser|}</td></tr>
		</table>
		<span class="aebutton" onclick="popup.close();ADMIN.takefile('{data.take.path}',false)">Освободить</span>, <span onclick="popup.alert('<div style=\'width:300px\'><b>Файл редактируется или файл занят</b> &mdash; значит, что файл был кем-то скачен и не загружен обратно. Cейчас, возможно, в файл вносятся изменения. Если этот человек не Вы настоятельно рекомендуется прежде, чем скачивать файл выяснить кто не убрал отметку о редактировании файл. Иначе Ваши изменения могут быть затёрты.</div>');" style="cursor:pointer; text-decoration:underline">помощь</span> 
{type_autoedit:}
	<div style="width:{config.layer.autoedit.width|:auto}{config.layer.autoedit.width?:px}">
		<h1>{config.layer.autoedit.title|: }</h1>
		<div style="margin-bottom:10px">
			{config.layer.autoedit.descr|: }
		</div>
		{config.files::autoeditpath}
		<div style="margin-top:10px">
			{:close}Закрыть{:/close}
		</div>
	</div>

		{autoeditpath:}
			<table>
				{title?:autoedittitle}
				{paths::aef} 
			</table>	
		{autoedittitle:}
			<tr><td colspan=2><b>{title}</b></td></tr>
		{aef:}
			<tr>
				<td style="width:20px"><img alt=" " src="{infra.theme(:*autoedit/icons/)}{ext}.png" title="{ext}"></td>
				<td><span class="aebutton" onclick="ADMIN('{folder?:editfolder?:editfile}','{root}{path}')">{path}</span></td>
			</tr>
{type_version:}
	<h1>Версии основных файлов системы</h1>
	<table>
		{data.dates::ver}
	</table>
	{ver:}
		<tr><th>{$key}</th><td>{$date(:_takedate,.)}</td></tr>
{type_settings:}
	<h1>Настройки</h1>
	<h2>Файлы</h2>
	<span class="aebutton" onclick="ADMIN('takeshow')">Файлы, которые кем-то сейчас редактируются</span><br> 
	<span class="aebutton" onclick="ADMIN.autoedit(infrajs)">Файлы глобальных и не явных настроек</span><br>
	<span class="aebutton version" onclick="ADMIN('version')">Версии</span>
	<h2>Кэш</h2>
	<span class="aebutton" onclick="document.cookie='infra_makecache=1';location.reload();return false;">
		Сделать кэш текущей страницы на кэш сервере
	</span><br>
	<span class="aebutton" onclick="ADMIN('createcache');">
		Создание кэша в своём браузере
	</span>
{type_createcache:}
	<h1>Создание кэша из своего браузера</h1>
	<div>{data.iscache?:cacheyes?:cacheno}</div>
	<span class="aebutton" onclick="document.cookie='infra_makecachelocal={data.iscache?:0?:1}';location.reload();return false;">
		{data.iscache?:Выключить?:Включить} кэширование
	</span>
	<div style="margin:5px; width:430px; padding:5px; border:dashed 1px gray;">
		Для создания кэша необходимо чтобы сайт загрузился на нужной странице. Для этого необходимо перейти на нужную старницу и нажать F5 или включить кэширование в этом окне. 
		После этого нажать сочетание клавиш Shift+Ё или Shift+~. Появится сообщение об успешном сохранении кэша. Для сохранения кэша другой страницы нужно также перейти на неё и обновить перед нажатием указанных клавиш.
		Для корректного создания кэша после обновления страницы нельзя ничего вызывать кроме команды на создание кэша с помощью указанных клавиш. Если вызывались окна или были переходы на другие старницы необходимо снова обновить страницу для её чистой загрузки. Когда включен режим создания кэша существующий кэш не используется сайту для загрузки может потребоваться несколько больше времени. Указанные клавиши начинают работать когда кэширование было включено.
	</div>
	{cacheyes:}
		<span style="color:red">Включено</span>
	{cacheno:}
		Выключено
{!type_admin:}
	<h1>Администрирование</h1>
	{:form}
		<input type="hidden" name="admin" value="{admin?:1?:0}">
		<table>
			<tr><td style="height:22px; vertical-align:middle" colspan=2>Административный режим <b>{admin?:включён?:выключeн}</b> </td></tr>
			<tr style="display:{admin?:none?}">
				<td style="vertical-align:middle;">Логин</td><td><input type="text" name="login" value=""></td></tr>
			<tr style="display:{admin?:none?}">
				<td style="vertical-align:middle;">Пароль</td><td><input type="password" name="pass" value=""></td></tr>
		</table>
		<div style="margin-bottom:10px">
		{admin?:adminhelp}
		</div>
		<div style="margin-bottom:10px">
		{admin?:adminmenu}
		</div>
		{:submit}{admin?:Выйти?:Войти}{:/submit}
	{:/form}
	{adminmenu:}
		<span class="aebutton" onclick="ADMIN('allblocks')">Доступные блоки</span><br>
		<span class="aebutton" onclick="ADMIN('editfolder','*')">Папка с данными</span><br> 
		<!--
		<span class="aebutton" onclick="ADMIN('settings')">Другие опции</span>
		<span class="aebutton" onclick="location.href=location.href">Обновить сайт</span><br> 
		-->
		<span class="aebutton" onclick="ADMIN('takeshow')">Редактируемые файлы</span><br> 
		<span class="aebutton" onclick="AUTOEDIT('seo','{infra.conf.infrajs.seoforall?infra.State.get()}')">SEO параметры{infra.conf.infrajs.seoforall|: главной страницы}</span><br>
	{adminhelp:}
		<div style="padding:5px; border:dashed 1px gray;">
		Для редактирования данных наведите мышку на блок 
		<br>информацию в котором нужно поправить.
		<br>Если это возможно блок подсветится и после клика 
		<br>откроется окно co списком файлов для редактирования.
		</div>
{!type_allblocks:}
	<h1>Блоки на открытой странице</h1>
	{config.list::allblocks}
	{allblocks:}
		<span class="aebutton block{num}">{title|layer}</span><br>
{type_deletefile:}
	<h1>Удалить файл?</h1>
	{:form}
		{:infofile}
		{:submit}Удалить{:/submit}
	{:/form}
{type_renamefile:}
	<h1>Переименовать файл?</h1>
	{:form}
		{:infofile}
		{:fullpath}
		{:newfilename}
		{:submit}Переименовать{:/submit}
	{:/form}
{type_copyfile:}
	<h1>Создать копию файла?</h1>
	{:form}
		{:infofile}
		<b>Создание копии</b><br>
		{:fullpath}
		{:newfilename}
		{:submit}Cкопировать{:/submit}
	{:/form}
	{newfilename:}
		<!--Новое имя <input style="width:200px" type="text" name="newname" value="{data.name}"><br>-->
		<div style="margin:5px 0">
		<input style="width:200px" type="text" name="newname" value="{data.name}"> — Имя нового файла <br>
		</div>
	{fullpath:}
		<div style="margin:5px 0">
			<input type="checkbox" name="full" onclick="popup.reparse();"> — задать полный путь<br>
		</div>
		<div id="fullpath" style="margin-top:5px; display:{autosave.full|:none}">
			<input type="text" style="width:200px;" name="newfolder" value="{data.folder}"> — Папка<br>
		</div>
	{infofile:}
		<input type="hidden" name="oldfolder" value="{data.folder}">
		<input type="hidden" name="oldname" value="{data.name}">
		<table>
		<tr><td>Папка:&nbsp;</td><td><span class="aebutton" onclick="ADMIN('editfolder','{data.folder}')">{data.folder}</span></td></tr>
		<tr><td>Файл:&nbsp;</td><td><span class="aebutton" onclick="ADMIN('editfile','{data.id}')">{data.name}</span></td></tr>
		</table>








{form:}
	<!--<form enctype="multipart/form-data" method="POST" action="infra/plugins/autoedit/admin.hand.php">-->
	<form method="POST" enctype="multipart/form-data" action="infra/plugins/autoedit/admin.hand.php">
	<!--<form>-->
	<input type="hidden" name="type" value="{config.type}">
	<input type="hidden" name="id" value="{config.id}">
{/form:}
	{:close}Отмена{:/close}
	</form>
{submit:}<input style="padding:0px 10px;margin-right:10px;margin-top:5px" type="submit" value="{/submit:}">
{close:}
	<input type="button" style="padding:0px 10px;margin-right:10px;margin-top:5px" value="{/close:}" onclick="popup.close()">
{more:}
	<img src="{infra.theme(:*autoedit/more.png)}" alt="more" onclick="ADMIN.more=!ADMIN.more; infrajs(popup.showed_popups);" style="cursor:pointer; margin-right:3px;" title="Переключить расширенный режим">
