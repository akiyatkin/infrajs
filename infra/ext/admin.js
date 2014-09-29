infra.admin=function(){
	var ans=infra.loadJSON('*infra/admin.php?json');
	return ans.admin;
}