<?php
	$type=$_GET['type'];
	$who=$_GET['p1'];
	if($type=='html'){
		echo '<div id="showContacts" class="showContacts">'.$who.'</div>';
	}
	if($type=='title'){
		echo ' class="showContacts" nohref="1" contactsWho="'.$who.'" title="Форма контактов"';
	}
?>
