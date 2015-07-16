<?php

$ans = array();
$ans['title'] = 'Check GD extension';
if (!function_exists('imagecreatetruecolor')) {
    return infra_err($ans, 'GD required');
}

return infra_ret($ans, 'ok');
