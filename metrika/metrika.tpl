{root:}
<script type="text/javascript">
	window.yandex_metrika_callback=function(){
		window['yaCounter{config.id}']= new Ya.Metrika({
				id:'{config.id}', 
				defer:true,//Не отправлять hit при инициализации
				clickmap:'{config.clickmap}', 
				trackLinks:'{config.trackLinks}', 
				accurateTrackBounce:'{config.accurateTrackBounce}'
		});
	}
	infra.listen(infra,'oninit',function(){
		infra.listen(infrajs,'onshow',function(){
			var yaCounter=window['yaCounter{config.id}'];
			if(!yaCounter)return;
			yaCounter.hit(location.href, document.title, document.referrer);
		});
	});
</script>
<script type="text/javascript" src="http://mc.yandex.ru/metrika/watch.js"></script>
