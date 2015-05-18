<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
$source='""';

$data=infra_json_decode($source);
if($data==="")$ans['result'] = 1;
else $ans['result'] = 0;
return $ans;
?>