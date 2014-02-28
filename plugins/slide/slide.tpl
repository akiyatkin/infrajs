{root:}
<style>
	#big_up_image {
		position: absolute;
	}
	#big_image {
		position: absolute;
	}
	.ie6 #big_image {
	}
	.ie6 #big_up_image {
	}
</style>
	<div style="width:{config.width}px; /*height:{config.height}px;*/">
		{data?data:a?config.href:a}
		{:div}
		{data?data:/a?config.href:/a}
	</div>
	{:script}
{a:}<a href="{.}">{/a:}</a>
{div:}
	<div id='big_image'>

	</div>
	<div id="big_up_image">
		<img class="{config.imgcls|}" src="infra/plugins/imager/imager.php?src={config.folder}&mark={config.mark|:0}&w={config.width}&h={config.height}&crop=1">
	</div>
{val:}{$key}:"{.}",
{script:}
<script type="text/javascript">
	//layer.config.pimg=false;
	infra.wait(infra,'oninit',function(){
		infra.load('*slide/slide.js');
		if(!Slide.stor)Slide.stor={ };
		if(!Slide.stor['{unick}']){
			Slide.stor['{unick}']={
				{config::val}
				/*folder:'{config.folder}',
				width:'{config.width}',
				height:layer.config.height,
				rnd:layer.config.rnd,
				next:layer.config.next,
				onshow:layer.config.onshow,
				imgcls:layer.config.imgcls,*/
				counter:0,
				block_ready:true,
				block_showed:true,
				pimg:false,
				slide:false,
				timer:false
			};
		}
		var stor=Slide.stor['{unick}'];
		stor.counter++;
		stor.pimg=false;
		stor.block_ready=true;
		stor.block_showed=true;


		var blockd=document.getElementById('big_image');
		var blocku=document.getElementById('big_up_image');
		if(!blocku)return;
			var img=blocku.getElementsByTagName('img');
			if(img) img=img[0];//Картинка установленная на сервере
			stor.pimg=img;

		stor.slide=new Slide(stor.folder,stor.width,stor.height);
		stor.slide.rnd=stor.rnd;
		stor.slide.mark=stor.mark||0;

		var next=stor.next*1000;
		var effect=1000;
		var speed=200;

		var counter=stor.counter;

		stor.callback=function(){ //callback - запускается постоянно
			var div=document.getElementById('big_image');
			if(!div)return;
			if(counter!==stor.counter)return;//Если это новое перепарсивание то старые счётчики проигнорируются так как this.counter у слоя изменится
			
			if(stor.block_ready&&stor.block_showed&&!stor.slide.process){ //выполняются действия только когда всё готово

				stor.block_ready=false;
				stor.block_showed=false;

				var pimg=stor.pimg;
				var img=stor.slide.img;
				if(img)img.className=stor.imgcls;
				stor.pimg=img;

				var equal=(pimg&&(pimg==img));

				if(!equal){
					if(pimg){
						blockd.appendChild(pimg);
					}
					img.style.display='none';
					blocku.appendChild(img);
					var fn='fadeIn';
					var e=effect;
				}else{
					var fn='show';
					var e=1;
				}
				if(!equal&&stor.onshow){
					var show=infra.load(stor.onshow,'j');
					show.onshow(stor);
				}

				$(img)[fn](e,function(){
					var div=document.getElementById('big_image');
					if(!div)return;
					if(counter!==stor.counter)return;
					if(pimg&&!equal){
						pimg.style.display='none';
					}

					stor.slide.get(stor.callback);//Запускаем подготовку следующих картинок


					stor.block_showed=true;

					stor.timer=setTimeout(function(){
						clearTimeout(stor.timer);
						if(stor.counter!==counter)return;
						if(stor.block_ready)return;
						if(!stor.block_showed)return;
						var div=document.getElementById('big_image');
						if(!div)return;

						stor.block_ready=true;
						stor.callback();
					},next);

				});
			}
		}

		//Первый запуск
		setTimeout(function(){
			stor.slide.get(stor.callback);
		},1);/**/
	});
</script>
