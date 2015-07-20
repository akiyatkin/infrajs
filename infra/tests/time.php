<?php

$ans['title']='Временная зона по умолчанию';
$msg=date_default_timezone_get();
return infra_ret($ans, $msg);
