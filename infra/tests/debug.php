<?php

    $ans = array();
    $ans['title'] = 'Тест на значение debug. Режим отладки должен быть отключён.';

    $conf = infra_config();
    if ($conf['debug']) {
        return infra_err($ans, 'Значение debug = true');
    }

    return infra_ret($ans, 'Значение debug = false');
