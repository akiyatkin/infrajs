<?php
@define('ROOT','../../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
$source='""';

$data=infra_json_decode($source);
if($data==="")echo 'GOOD';
else echo 'ERROR';


?>