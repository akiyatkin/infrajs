<?php

$ans = array();
$ans['title'] = 'Файлы с точкой в начале должны быть скрыты';

$data = @file_get_contents('?*.config.json');
if ($data) {
    $ans['result'] = false;
    echo json_encode($ans);
}

$dirs = infra_dirs();
$src = infra_view_getSchema().infra_view_getHost().'/'.infra_view_getRoot().$dirs['data'].'.config.json';
$data = @file_get_contents($src);
if ($data) {
    $ans['result'] = false;
    echo json_encode($ans);
}
$ans['result'] = true;
echo json_encode($ans);
