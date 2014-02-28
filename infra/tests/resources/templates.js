		

	infra.template.test=function(k){
		infra.load('*infra/ext/template.js','fex');
		infra.template.test=arguments.callee;
		var tpls=infra.load('*infra/tests/templates.json','fjx');
		infra.forr(tpls,function(t,key){
			if(typeof(k)!=='undefined'&&key!==k)return;
			
			h=key+' '+t['tpl'];
			if(typeof(t['data'])=='undefined') var data={};
			else var data=t['data'];

			var tp=infra.template.make([t['tpl']]);
			if(typeof(k)!=='undefined')console.log(tp);
			var r=infra.template.exec(tp,data);
			var er=(r!==t['res']);

			
			if(!er)h+=' "'+r+'"';
			else h+=' "'+r+'" надо "'+t['res']+'"';

			var com=(t['com']||'');
			if(com)com=' > '+com;
			else com=' ';
			if(er) console.error(h,com);
			else console.log(h);
			

			if(typeof(k)!=='undefined')console.log(data);
		});
	}
