<?php

    require_once __DIR__.'/../../infra/infra.php';
    $ans = array(
        'title' => 'Тест на совпадение названия указанного файла и его путь',
    );

    $file = infra_nameinfo('*1 file@23.txt');
    $src = infra_srcinfo('*1 file@23.txt');

    if ($file['id'] != 23 && $src['src'] != '*1 file@23.txt') {
        return infra_err($ans, 'Такого файла не существует или не правидьно указан путь');
    }

        return infra_ret($ans, 'Путь указан правильно, файл найден');
