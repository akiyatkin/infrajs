infra.tableCommon=function(){
	$('table.common').not('.commoned').addClass('commoned').each(function(){
		var body=$(this).find('>tbody');
		if(!body.length)body=$(this);		
		body.find('>tr:odd').addClass('even');
		body.find('>tr:even').addClass('odd');
		body.find('>tr:first').addClass('top');
		body.find('>tr:last').addClass('bottom');
		body.find('>tr').each(function(){
			$(this).find('>*').addClass('com');
			$(this).find('>:first').addClass('first');
			$(this).find('>:last').addClass('last');
		});
	});
	$("a:has(img)").addClass("aimg");
	$("a:has(h1,h2,h3,h4,h5)").addClass("aheading");
};
