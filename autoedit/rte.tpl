{root:}
	<style>
		#{div} {
			font-size:12px;
		}
		#{div} .aebutton {
			cursor:pointer;
			text-decoration:none;
			border-bottom:dashed 1px gray;
		}
		#{div} .imgsel img {
			margin:4px;
			margin-bottom:8px;
			cursor:pointer;

		}
		#{div} .imgsel img.select {
			border:solid 2px red;
			margin:2px;
		} 
		#{div} .help {
			border:dotted gray 1px; 
			padding:10px; 
			margin:10px; 
			display:none;
		}
		#{div} .imgblock {
			display:none;
		}
		#{div} .imgsize .show {
			margin:10px 0;
		}
		#{div} .imgsize .show img {
			border:solid 1px gray;
		}
		#{div} .imgsize {
			border:dotted gray 1px; 
			display:none;
			padding:10px; 
		}
		#{div} h2 {
			font-size:11px;
			font-weight:normal;
		}
		#{div} h1 {
			margin-top:0;
			text-align:center;
		}
		#{div} form {
			margin:0;
			padding:0 0 5px 0;
		}
		#{div} .wym_skin_default .wym_iframe iframe {
			height:150px;
		}
	</style>
	<div style="overflow:hidden; margin-bottom:10px;" class="wymcont">
		<textarea autosavebreak="1" class="rte" style="font-family:Tahoma; font-size:12px; color:#444444; width:{config.width}px; height:{config.height}px" name="{config.name}">{.}</textarea>
	</div>
	<div id="selimg{unick}"></div>
	<script type="text/javascript">
		console.log('rte');
		infra.when(infrajs,'onshow',function(){ //Надо при первом следующем
			var layer=infrajs.getUnickLayer("{unick}");
			var div=$('#'+layer.div);
			var area=div.find('textarea.rte');
			
			area.css('visibility','hidden');
			
			var conf=layer.config;
			var type=conf.type;
			var counter={counter};
			infra.listen(infra,'layer.onhide',function(l){
				if(layer!=l)return;
				if(l.counter!=counter)return;
				
				var html = conf.wym.xhtml();
					
				
				if(conf.syncval==html)return;
				div.find('textarea.rte').val(html).change();//В этот момент происходит запись в autosave
			});
			/*div.find('.wymcont').css('visibility','visible');//чтобы не прыгал редактор при загрузки
			return;*/
			area.parents('form').submit(function(){
				if(conf.wym){
					var html = conf.wym.xhtml();
					area.val(html).change();
				}
			});



			var wymfolder='infra/lib/wymeditor/wymeditor/';


			//Редактор
			infra.require(wymfolder+'jquery.wymeditor.js');
			infra.require(wymfolder+'plugins/embed/jquery.wymeditor.embed.js');
			//WYMeditor.XmlHelper.prototype.escapeEntities = function(string){ return string};
			var escOnce=WYMeditor.XmlHelper.prototype.escapeOnce;
			WYMeditor.XmlHelper.prototype.escapeOnce = function(string){
				string=escOnce.bind(this)(string);
				string=decodeURI(string);
				return string;
			};			
			area.wymeditor({
				basePath:wymfolder,
				wymPath:wymfolder+'jquery.wymeditor.min.js',
				jQueryPath:'infra/lib/jquery/jquery.js',
				iframeBasePath:'./',
				boxHtml:"<div class='wym_box'>"
				  + "<div class='wym_area_top' style='font-size:11px'>"
				  + WYMeditor.CLASSES
				  + WYMeditor.CONTAINERS
				  + WYMeditor.TOOLS
				  + "</div>"
				  + "<div class='wym_area_left'></div>"
				  + "<div class='wym_area_right'>"
				  + "</div>"
				  + "<div class='wym_area_main'>"
				  + WYMeditor.HTML
				  + WYMeditor.IFRAME
				  + WYMeditor.STATUS
				  + "</div>"
				  + "<div class='wym_area_bottom'>"
				  + "</div>"
				  + "</div>",
				preInit:function(wym){
					conf.wym=wym;//дочернии слои могут обращаться сюда за объектом wym
				},
				postInit:function(wym){
					conf.syncval=wym.xhtml();
					/*var count=layer.count;
					setTimeout(function(){
						if(layer.count!=count)return;
						setTimeout(arguments.callee,10000);

						var d=wym.xhtml();
						if(d==conf.syncval)return;
						conf.syncval=d;
						area.val(d).change();
					},10000);*/

					div.find('.wymcont').css('visibility','visible');//чтобы не прыгал редактор при загрузки

					//we make all sections in area_top render as dropdown menus:
					jQuery(wym._box)
						//first we have to select them:
						.find(".wym_area_top .wym_classes, .wym_area_top .wym_containers")
						//then we remove the existing class which make some of them render as a panels:
						.removeClass("wym_panel")
						//then we add the class which will make them render as a dropdown menu:
						.addClass("wym_dropdown")
						//finally we add some css to make the dropdown menus look better:
						.css("width", "90px")
						.css("float", "right")
						.css("margin-left", "5px")
						.css("margin-right", "13px")
						.css("margin-top", "5px")
						.find("ul")
						.css("width", "120px");
					//Заблокируем изменение якоря
					jQuery(wym._box).find(".wym_tools, .wym_area_top .wym_classes, .wym_area_top .wym_containers").
						find('a').attr('href','javascript:').attr('nohref',1);
					//add a ">" character to the title of the new dropdown menus (visual cue)
					jQuery(wym._box).find(".wym_classes ")
						.find(WYMeditor.H2)
						.append("<span>&nbsp;&gt;</span>");
					$(wym._doc).find('head').append('<style> img.right { border-right:1px dotted red; float:right; margin:0 0 5px 5px;}'+
					'img.left { border-left:1px dotted red; float:left; margin:0 5px 5px 0; } .alert { color:#FF6600 } </style>');


					$(wym._box).find('iframe').height(conf.height+'px');
	 
					
					//construct the button's html
					var html = "<li class='wym_tools_newbutton'>"
							 + "<a name='NewButton' href='#'"
							 + " style='background-image:"
							 + " url(?*autoedit/images/paint.png)'>"
							 + "Do something"
							 + "</a></li>";
					//add the button to the tools box
					jQuery(wym._box).find(wym._options.toolsSelector + wym._options.toolsListSelector).append(html);
					//handle click event
					jQuery(wym._box)
					.find('li.wym_tools_newbutton a').click(function() {
						wym.wrap( '<span class="alert">', '</span>' );
						return(false);
					});

					
					//construct the button's html
					var html = "<li class='wym_tools_newbutton'>"
							 + "<a name='NewButton' href='#'"
							 + " style='background-image:"
							 + " url(?*autoedit/images/eraser.png)'>"
							 + "Do something"
							 + "</a></li>";

					//add the button to the tools box
					jQuery(wym._box)
					.find(wym._options.toolsSelector + wym._options.toolsListSelector)
					.append(html);
					//handle click event
					jQuery(wym._box)
					.find('li.wym_tools_newbutton a').click(function() {
						wym.unwrap();
						return(false);
					});
					popup.render();
					//var html='<span>Показать</span>';
					//jQuery(wym._box).find(wym._options.toolsSelector + wym._options.toolsListSelector).append(html);

				},
				updateSelector: ".submit, input[type=submit]",
				lang: 'ru',
				/*styles:
					'<style> img.right { float:right; margin:0 0 5px 5px;}'+
					'img.left { float:left; margin:0 5px 5px 0;} </style>',*/
				classesItems: [
					{ 'name': 'alert', 'title': 'Яркий цвет', 'expr': '*'},
					{ 'name': 'left', 'title': 'Картинка слева', 'expr': 'img[@class!=right]'},
					{ 'name': 'right', 'title': 'Картинка справа', 'expr': 'img[@class!=left]'}
				],
				containersItems: [
					{ 'name': 'P', 'title': 'Paragraph', 'css': 'wym_containers_p'},
					{ 'name': 'H1', 'title': 'Heading_1', 'css': 'wym_containers_h1'},
					{ 'name': 'H2', 'title': 'Heading_2', 'css': 'wym_containers_h2'},
					{ 'name': 'H3', 'title': 'Heading_3', 'css': 'wym_containers_h3'}
				],
				/*editorStyles: [
					{ 'name': '.hidden-note','css': 'color: #999; border: 2px solid #ccc;'},
					{ 'name': '.border', 'css': 'border: 4px solid #ccc;'},
					{ 'name': '.date','css': 'background-color: #ff9; border: 2px solid #ee9;'},
					{ 'name': '.important','css': 'color: red; font-weight: bold; border: 2px solid red;'},
					{ 'name': '.special','css': 'background-color: #fc9; border: 2px solid red;'}
				],*/
				toolsItems: [
					{ 'name': 'Bold', 'title': 'Strong', 'css': 'wym_tools_strong'}, 
					{ 'name': 'Italic', 'title': 'Emphasis', 'css': 'wym_tools_emphasis'},
					{ 'name': 'CreateLink', 'title': 'Link', 'css': 'wym_tools_link'},
					{ 'name': 'Unlink', 'title': 'Unlink', 'css': 'wym_tools_unlink'},
					{ 'name': 'InsertOrderedList', 'title': 'Ordered_List', 'css': 'wym_tools_ordered_list'},
					{ 'name': 'InsertUnorderedList', 'title': 'Unordered_List', 'css': 'wym_tools_unordered_list'},
					{ 'name': 'ToggleHtml', 'title': 'HTML', 'css': 'wym_tools_html'},
					//{ 'name': 'InsertImage', 'title': 'Image', 'css': 'wym_tools_image'},
					{ 'name': 'InsertTable', 'title': 'Table', 'css': 'wym_tools_table'},
					{ 'name': 'Paste', 'title': 'Paste_From_Word', 'css': 'wym_tools_paste'}
					//{ 'name': 'Undo', 'title': 'Undo', 'css': 'wym_tools_undo'},
					//{ 'name': 'Redo', 'title': 'Redo', 'css': 'wym_tools_redo'}
				]
			});
			area.change(function(){ //Востановление первоначального занчения когда окно скрылось другим а потом к нему
				if(conf.wym){
					layer.showed=false;
					infrajs.check(layer);
					//var h=$(this).val();
					//var d=conf.wym.xhtml();
					//if(d==h)return;
					//conf.wym.html(h);
				}
			});
		});
	</script>
{selimg:}
	<span class="aebutton toggleImg">Добавить иллюстрацию</span>
	<div class="imgblock" style="background-color:#eeeedd; padding:10px; display:{autosave.imgblock?:block?:none}">
		<div class="imgsel">
		{data::rteimg}
		</div>
		<span class="addimg aebutton">Загрузить на север новую иллюстрацию</span>, 
		<span onclick="$('#{div} .help').slideToggle('fast',function(){ popup.render(); }); " class="aebutton" style="color:gray">помощь</span>
		<div class="help">
			Чтобы добавить иллюстрацию в текст нужно
			<ol>
				<li>Загрузить необходимую иллюстрацию на сервер, чтобы она появилось здесь в общем списке</li>
				<li>Выбрать иллюстрацию в списке</li>
				<li>Указать необходимый размер</li>
				<li>Установить курсор в тексте, где нужно добавить иллюстрацию</li>
				<li>Нажать кнопку "вставить в текст"</li>
			</ol>
		</div>
		<div class="imgsize">
			<b class="imgname"></b> Ширина <input type="text" value="{config.w|}" name="imgwidth" style="width:30px" title="Ширина">, Высота <input type="text" style="width:30px" title="Высота" value="" name="imgheight">,
			Обрезать края <input type="checkbox" name="imgcrop"><br>
			<input type="button" class="add" style="cursor:pointer" value="вставить в текст">
			<span class="aebutton del" title="Иллюстрация будет удалена с сайта безвозвратно">удалить c сервера</span>
			<div class="show">
				<img src="">
			</div>
		</div>
	</div>
	<script>

		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer("{unick}");//Так можно получить только слой который был добавлен к постоянному списку
			
			var div=$('#'+layer.div);
			var conf=layer.config;
			var wym=layer.parent.config.wym;
			

			//Обработка кликов
			
			
			

			div.find('.addimg').click(function(){
				if(!conf.addimglayer)conf.addimglayer={
					config:conf,
					autosavename:'{autosavename}',
					tpl:'*autoedit/rte.tpl',
					tplroot:'addimg',
					onsubmit:function(){
						var ans=this.config.ans;
						if(ans.result){
							popup.hide();
							//infra.unload(layer.data);//Рас уж мы добавили картинку списко нужно обновить
							//infrajs.check(layer);
							AUTOEDIT.refreshAll();
							infrajs.check([layer]);
						}else{
							infrajs.check([this,layer]);
						}
					}
				}
				infra.require('*popup/popup.js');
				popup.open(conf.addimglayer);
			});

			div.find('.toggleImg').click(function(){
				var imgblock=div.find('.imgblock');
				imgblock.slideToggle('fast',function(){
					var r=imgblock.is(':visible');
					infrajs.autosave.set(layer,'imgblock',r);
					popup.render();
				});
			});

			var change=false;
			var wait=false;
			var preview=function(){
				if(wait)return;
				var name=div.find('.imgsel .select').attr('orig');
				var width=div.find('[name=imgwidth]').val()||'';
				var height=div.find('[name=imgheight]').val()||'';
				var crop=div.find('[name=imgcrop]').attr('checked')||'';
				if(crop)crop=1;
				div.find('.imgname').text(name);


				var newchange=height+'x'+width;
				if(change&&newchange!=change){
					change=newchange;//Ждём пока изменения не будут производится
					wait=true;
					setTimeout(function(){
							wait=false;
							preview();
					},3000);
					return;
				}
				wait=false;
				change=newchange;//Ждём пока изменения не будут производится


				div.find('.imgsize .show').slideDown('fast',function(){
					popup.render();
				});
				var src=infra.theme('*imager/imager.php?src='+conf.folder+name+'&w='+width+'&h='+height+'&crop='+crop);
				div.find('.imgsize .show img').attr('src',src);
			};
			div.find('.imgsize .del').click(function(){
				var name=div.find('.imgname').text();
				var folder=conf.folder;
				var file=folder+name;
				//ADMIN('deletefile',folder+name); 
				if(!conf.delimglayer){
					conf.delimglayer={
						config:conf,
						autosavename:'autosave',
						tpl:'*autoedit/rte.tpl',
						tplroot:'delimg',
						global:['files'],
						onsubmit:function(){
							var ans=this.config.ans;
							if(ans.result){
								popup.hide();
								infra.unload(layer.data);
								infrajs.global.set(layer.global);
							}else{
								infrajs.check(layer);
							}
						}
					}
					infra.require('*popup/popup.js');
				}
				conf.delimglayer.config.name=name;
				conf.delimglayer.config.folder=folder;
				conf.delimglayer.config.is=file;
				popup.open(conf.delimglayer);
			});
			var fastpreview=function(){
				change=false;
				wait=false;
				preview();
			}

			div.find('.imgsize input').keyup(preview).click(preview).change(fastpreview);

			div.find('.imgsize .show img').click(fastpreview);
			div.find('.imgsel img').click(fastpreview);

			div.find('.add').click(function() {
				fastpreview();
				var html=div.find('.imgsize .show').html();
				html=$.trim(html);
				wym.wrap(html,'');
			});
		});
	</script>
{rteimg:}
	<img title="{.}" alt="{.}" onclick="$('#{div}').find('.imgsize').slideDown('fast',function(){ popup.render(); }); $(this).parent().find('.select').removeClass('select'); $(this).addClass('select');" orig="{.}" 
	src="{infra.theme(:*imager/imager.php)}?src={config.folder}{.}&w=100&h=100&crop=1">
{addimg:}
	<h1 title="{counter}">Добавить иллюстрацию</h1>
	<form action="{infra.theme(config.addimg)}" method="post">
		<input style="margin:5px 0" type="file" name="file">
		<div style="margin:5px 0">
			<input type="checkbox" name="rewrite"> — перезаписать, если такой файл уже есть
		</div>
		{:submit}ОК{:/submit}
		{:close}Отмена{:/close}
	</form>
	{config.ans.msg}
{delimg:}
	<h1>Удалить иллюстрацию?</h1>
	{config.folder}<b>{config.name}</b><br>
	<img src="{infra.theme(:*imager/imager.php)}?src={config.folder}{config.name}&w=200&h=100">
	<form action="{infra.theme(config.delimg)}{config.name}">
		{:submit}Удалить{:/submit}
		{:close}Отмена{:/close}
	</form>
	{config.ans.msg}
{submit:}<input style="padding:0px 10px;margin-right:10px;margin-top:5px" type="submit" value="{/submit:}">
{close:}<input type="button" style="padding:0px 10px;margin-right:10px;margin-top:5px" value="{/close:}" onclick="popup.hide()">
