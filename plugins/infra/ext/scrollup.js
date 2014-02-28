infra.scrollUpt;
infra.scrollUp=function(){
  var top = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
  top=Math.floor(Math.sqrt(top));
  if(top > 2) {
    //window.scrollBy(0,-100);
    window.scrollTo(0,top);
    infra.scrollUpt = setTimeout(infra.scrollUp,30);
  } else {
	    window.scrollTo(0,0);
	  clearTimeout(infra.scrollUpt);
  }
}
