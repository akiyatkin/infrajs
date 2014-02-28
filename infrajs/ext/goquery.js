(function(){
	var t='{data.go?:goquery_show?:goquery_error} {goquery_error:} К сожалению, произошла ошибка, попробуйте позже.  {goquery_show:} {data.query?:goquery_show_link?:goquery_show_main} {goquery_show_main:} <a href="?">Главная</a><br> Короткая ссылка:<br><textarea style="width:500px; height:34px; font-family:Tahoma; font-size:24px;">http:/'+'/{location.host}{location.pathname}</textarea> {goquery_show_link:} <a href="?{data.query}">{data.query}</a><br> Короткая ссылка:<br><textarea style="width:500px; height:34px; font-family:Tahoma; font-size:24px;">http://{location.host}{location.pathname}?p/{data.go}</textarea>';
	var layer={
		data:true,
		tpl:[t],
		datatpl:'*infra/php/goquery.php?query={infra.State.get()}'
	}
	infra.listen(infrajs,'onshow',function(){
		$('.showGoquery[showGoquery!=true]').attr('showGoquery','true').click(function(){
			infra.load('*popup/popup.js');
			popup.open(layer,'Короткая ссылка');
		});

	});
})();
