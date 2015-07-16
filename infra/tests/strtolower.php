<?php

    require_once __DIR__.'/../../infra/infra.php';
    $ans = array(
        'title' => 'Проверка функции strtolower',
    );
    $s1 = infra_tofs('Кирилица utf8');
    $s2 = infra_tofs('кирилица utf8');

    if (infra_strtolower($s1) != $s2) {
        return infra_err($ans, 'infra_strtolower не работает');
    }

    return infra_ret($ans, 'infra strtolower работает');
