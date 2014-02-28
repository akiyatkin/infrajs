<?php
	@define('ROOT','../../../../');
	require_once(ROOT.'infra/plugins/infra/infra.php');
		
	infra_require('*infra/ext/template.php');
	$tpls=infra_loadJSON('*infra/tests/resources/templates.json');

	function getmicrotime(){ 
	    list($usec, $sec) = explode(" ", microtime()); 
	    return $usec;
	    //return ((float)$usec + (float)$sec); 
	} 
	echo '<table style="font-size:14px; font-family:monospace;">';
	$time=getmicrotime();
	infra_forr($tpls,function(&$time, $t,$key){
		if(isset($_GET['key'])&&$_GET['key']!=$key)return;
		echo '<tr><td>';
		echo $key;
		echo '</td><td>';
		echo htmlentities($t['tpl']);
		echo '</td><td nowrap="1">';
		if(@is_null($t['data']))$data=array();
		else $data=$t['data'];

		//for($i=0,$l=10;$i<$l;$i++){
			$r=infra_template_parse(array($t['tpl']),$data);
		//}
		echo ceil((getmicrotime()-$time)*1000);
		echo 'мс';
		echo '</td><td>';

		if($r===$t['res'])echo '"<b>'.htmlentities($r).'</b>"';
		else echo '<span style="color:red; font-weight:bold">"<b>'.htmlentities($r).'</b>" надо "<b>'.htmlentities($t['res']).'</b>"</span>';
		echo '</td><td>';
		echo infra_json_encode($data);
		echo '</td><td>';
		echo @$t['com'];
		echo '</td><tr>';
	},array(&$time));
	echo '</table>';
?>
