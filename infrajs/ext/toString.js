(function(){
	var toStr=function(){
		var layer=this;
		var str='';
		for(var i in layer){
			var s=[];
			var vals=layer[i];
			if(vals==undefined){
				s.push('undefined')
			}else{
				infra.fora(vals,function(val){
					if(!val){
						s.push(val);
					}else if(val.toStr===toStr){
						s.push('layer');
					}else if(val.length>50){
						s.push('long string');
					}else if(typeof(val)=='function'){
						s.push('function');
					}else if(typeof(val)=='object'){
						s.push('object');

					}else{
						s.push(val);
					}
				});
			}
			s=i+'='+s.join(',')+'\n';
			str+=s;
		}
		return str;
	}
	toStr=function(){
		return this.div+' '+this.data+' tpl:'+this.tpl+':'+this.tplroot+'\n'+(this.myenv&&this.myenv.toSource?this.myenv.toSource():'');
	}
	infra.listen(infra,'layer.oninit',function(){
		var layer=this;
		layer.toString=toStr;
	});
})();
