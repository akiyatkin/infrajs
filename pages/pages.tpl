{root:}
	{data.result?:list}
{list:}
	<div class="list_pages">
		<style>
			.list_pages {
				margin-bottom:20px;
			}
				.list_pages .some {
					margin-top:7px;
				}
				.list_pages .folder {
					width:16px;
					height:16px;
					margin-right:3px;
					margin-top:1px;
					float:left;
					font-size:1px;
					background-image:url('{*pages/images/folder.png}');
					background-repeat: no-repeat
				}
				.ie6 .list_pages .folder {
					background-image:	none;
					filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='{*pages/images/folder.png}',sizingMethod='crop')
				}
				.list_pages .file {
					width:16px;
					height:16px;
					margin-right:3px;
					float:left;
					font-size:1px;
					background-image:url('{*pages/images/file.png}');
					background-repeat: no-repeat
				}
				.ie6 .list_pages .file {
					background-image:	none;
					filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='{*pages/images/file.png}',sizingMethod='crop')
				}
		</style>
		<div style="clear:{parent.config.clear?left}"></div>
			<div class="some" style="display:{parent.config.breadcrumb?block?none}">
				{state:crumbstate}
				{data.path:path}<!--Часть адреса в state не известно берётся из data -->
			</div>
			<div style="display:{parent.config.list?block?none}">
			{data.folders::dirs}
			{data.pages::pages}
			</div>
	</div>
{path:}
		{::crumb}
	{crumb:}
		 - <a style="color:#638aa5" href="#{parent.config.sign?parent.config.sign}{state}/{path}">{name}</a>
{crumbstate:}
	{parent?parent:crumbstate}
	{parent? - }<a style="color:#638aa5" href="#{parent.config.sign?parent.config.sign}{name}">{state|Главная}</a>
{dirs:}
	<div class="some">
		<a style="color:#638aa5" href="#{parent.config.sign?parent.config.sign}{state}/{data.folder}{}"><div class="folder"></div>{}</a>
	</div>
{pages:}
	<div class="some">
		<div class="file"></div><a href="#{parent.config.sign?parent.config.sign}{state}/{data.folder}{}">{}</a>
	</div>
