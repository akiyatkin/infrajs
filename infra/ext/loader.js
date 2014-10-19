//Обработка loader
(function(){
	//Используется в onsubmit.js
	infra.loader={
		src:'*infra/ajax-loader.gif',
		hide:function(){
			if(!this.img)return;
			this.img.style.display='none';
		},
		show:function(){
			if(!document.body)return;
			if(!this.img){
				var img=document.createElement('img');
				img.src=infra.theme(this.src);
				img.style.position='absolute';
				img.style.zIndex='6000';
				document.body.appendChild(img);
				this.img=img;
			}
			this.img.style.display='block';
			this.center();
		},
		center:function(){
			/*var iebody=(document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body;
			var dsoctop=document.all? iebody.scrollTop : pageYOffset;
			this.img.style.left='0';

			var iebody=(document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body;
			var dsoctop=document.all? iebody.scrollTop : pageYOffset;

			this.img.style.top=dsoctop+'px';
			*/
			var pw=$(this.img).width();
			var dw=$(document).width();
			var m=dw/2-pw/2;
			m=Math.round(m);
			this.img.style.left=m+'px';

			var iebody=(document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body;
			var dsoctop=document.all? iebody.scrollTop : pageYOffset;
			var ph=$(this.img).height();
			var h1=$(window).height();
			var h2=$(document).height();
			var h=(h1<h2)?h1:h2;
			var top=(h/2)+dsoctop-ph/2;
			if(top<dsoctop)top=dsoctop;
			this.img.style.top=top+'px';
		}
	}
})();
