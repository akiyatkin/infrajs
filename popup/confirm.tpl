<div class="modal-header">
	<button class="close" type="button" onclick="popup.close()">x</button>
	<h4 class="modal-title" id="myModalLabel">Внимание</h4>
</div>
<div class="modal-body" id="{conf_divid}">
	
</div>
<div class="modal-footer">
	<button class="btn btn-default popup-confirm-ok" type="button" onclick="popup.close()">ОК</button>
	<button class="btn btn-default" type="button" onclick="popup.close()">Отмена</button>
</div>
<script>
	infra.wait(infrajs,'onshow',function(){
		var layer=infrajs.getUnickLayer('{unick}');
		var div=$('#'+layer.div);
		div.find('.popup-confirm-ok').click(function(){
			layer.conf_ok();
		});
	})
</script>