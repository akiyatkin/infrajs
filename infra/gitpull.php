<?php
@define('ROOT','../../../');
$output=array();
chdir(ROOT);

$com='git pull';
$output[]='<b>'.$com.'</b>';
exec($com,$output,$return);
$output[]='return '.$return;

$com='git submodule update';
$output[]='<b>'.$com.'</b>';
exec($com,$output,$return);
$output[]='return '.$return;

$com='git status';
$output[]='<b>'.$com.'</b>';
exec($com,$output,$return);
$output[]='return '.$return;

echo '<pre>';
print_r($output);
?>
