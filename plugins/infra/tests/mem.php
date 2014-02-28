<?php

@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
$mem=infra_memcache();
echo "<h1>Сервер memcache: ";
if($mem){
	echo '<span style="color:green">доступен</span>';
}else{
	echo '<span style="color:gray">недоступен</span>';
}
?>
