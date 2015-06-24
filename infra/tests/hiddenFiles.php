<?php
$ans=array();
$ans['title']='Файлы с точкой в начале должны быть скрыты';
$data=file_get_contents(__DIR__.'/../.config.json');

$ans['result']=(int)!$data;

echo json_encode($ans);