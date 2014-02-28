{root:}
<script type="text/javascript">
	infra.listen(infra,'oninit',function(){
		var layer=infrajs.getUnickLayer("{unick}");
		var conf=layer.config;
		if(!conf.id){
			if(infra.debug)alert('Google Analytics: необходимо указать номер счётчика в config - id');
			return;
		}
		if(conf.added)return;
		conf.added=true;
		window._gaq = window._gaq || []; 
		_gaq.push(['_setAccount', conf.id]); 
		(function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; 
			ga.async = true; 
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; 
			var s = document.getElementsByTagName('script')[0]; 
			s.parentNode.insertBefore(ga, s); 
		})(); 
		infra.listen(infrajs,'onshow',function(){
			_gaq.push(['setReferrerOverride',document.referrer]);
			_gaq.push(['_trackPageview',location.href]);
		});
	});
</script>
