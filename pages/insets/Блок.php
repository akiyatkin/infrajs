<?php
	$type=$_GET['type'];
	$id=$_GET['p1'];
	$cls=$_GET['p2'];
	if($type=='html'){
		echo '<div class="'.$cls.'" id="'.$id.'"></div>';
	}
?>