<?php
	$type=$_GET['type'];
	$msg=$_GET['p1'];
	if($type=='html'){
		//echo '<div class="showContacts">'.$who.'</div>';
	}
	if($type=='title'){
		echo ' onclick="if(window.statist)statist.sent(\''.$msg.'\')" ';
	}
?>