<div class="wpopup">
	<script>
		infra.loadCSS('*popup/popup.css');
	</script>
	
	<div class="title drag"><span class="close" onclick="popup.close()">[Закрыть]</span></div>
	<div class='pop_border'><div id="popup_body"></div></div>
	<div class="esc popup_esc">
		Окно можно закрыть клавишей ESC <img class="refreshAll" 
		style="cursor:pointer; position:relative; margin-bottom:-3px;" 
		src="infra/plugins/autoedit/images/refresh.png" 
		title="Обновить всё" alt="Обновить" onclick="infra.require('*autoedit/autoedit.js'); AUTOEDIT.refreshAll(); infrajs.check();"> <span style="border-bottom:dashed 1px gray; cursor:pointer;" onclick="popup.close(true)">закрыть все окна</span>
	</div>
</div>
<script>
		var w=popup.layer.config.width;
		if(w)w=w+'px';
		else w='auto';
		$('#'+popup.frame.div).css('width',w);
</script>
