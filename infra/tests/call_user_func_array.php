<?php
    require_once __DIR__.'/../../infra/infra.php';

    $ans = array();
    $ans['title'] = 'Тест амперсанда &';

    $a = 1;
    $b = &$a;
    $a = 2;
    if ($b !== 2) {
        return infra_err($ans, 'Амперсанд глючит в простой ситуации');
    }

    function &funcamp(&$t)
    {
        return $t;
    }

    $c = 5;
    $newc = &funcamp($c);
    $c = 6;
    if ($newc !== 6) {
        return infra_err($ans, 'Амперсанд глючит в функции');
    }

    $megac = &call_user_func('funcamp', $c);
    $c = 7;
    if ($megac === 7) {
        return infra_err($ans, 'Амперсанд в call_user_func вдруг заработал, это очень странно!');
    }

    $funcamp2 = function &(&$arg) {
        return $arg;
    };
    $d = 5;
    $superc = &call_user_func_array($funcamp2, array(&$d));
    $d = 8;
    if ($superc == 8) {
        return infra_err($ans, 'Амперсанд в call_user_func_array вдруг работает! Это неожиданно!');
    }

    return infra_ret($ans, 'Ссылки работают, ну или ведут себя предстказуемо.');
