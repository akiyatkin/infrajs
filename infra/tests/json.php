<?php

    require_once __DIR__.'/../../infra/infra.php';
    $ans = array();
    $ans['title'] = 'Тест на декодирование JSON';
    $source = '""';

    $data = infra_json_decode($source);
    if ($data !== '') {
        return infra_err($ans, 'Не может декодировать');
    }

    return infra_ret($ans, 'Декодировано');
