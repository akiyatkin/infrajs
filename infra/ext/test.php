<?php

infra_test(true);

$plugin = $_SERVER['QUERY_STRING'];
$code = infra_loadTEXT('*'.$plugin.'/.test.js');
echo $code;
