		

	infra.template.test=function(k){
		infra.unload('*infra/ext/template.js');
		infra.require('*infra/ext/template.js');
		infra.template.test=arguments.callee;
		infra.unload('*infra/tests/resources/templates.json');
		var tpls=infra.loadJSON('*infra/tests/resources/templates.json');
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
	infra.template.test.good=true;
