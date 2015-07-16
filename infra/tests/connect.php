<?php

    $db = infra_db(true);
    $ans = array(
        'title' => 'Проверка соединения с базой данных',
    );
    if (!$db) {
        return infra_err($ans, 'Нет соединения с базой данных');
    }

    return infra_ret($ans, 'Есть соединение с базой данных');
