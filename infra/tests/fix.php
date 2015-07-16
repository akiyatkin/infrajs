<?php

    require_once __DIR__.'/../../infra/infra.php';
    $result = true;

    $ans = array();
    $ans['title'] = 'fix.php';

    //back ret
    $ar = array('a','b','c','e');
    $count = 0;
    infra_forr($ar, function ($v) use (&$count) {
        ++$count;
        if ($v == 'b') {
            return new infra_Fix('del', true);
        }

    });
    if ($count == 2 && sizeof($ar) == 3 && $ar[1] == 'c') {
    } else {
        $result = false;
    }

    //back ret
    $ar = array('a','b','c','e');
    $count = 0;
    infra_forr($ar, function ($v) use (&$count) {
        ++$count;
        if ($v == 'b') {
            return new infra_Fix('del', true);
        }

    }, true);

    if ($count == 3 && sizeof($ar) == 3 && $ar[1] == 'c') {
    } else {
        $result = false;
    }

    //back
    $ar = array('a','b','c','e');
    $count = 0;
    infra_forr($ar, function ($v) use (&$count) {
        ++$count;
        if ($v == 'b') {
            return new infra_Fix('del');
        }

    }, true);

    if ($count == 4 && sizeof($ar) == 3 && $ar[1] == 'c') {
    } else {
        $result = false;
    }

    //simple
    $ar = array('a','b','c','e');
    $count = 0;
    infra_forr($ar, function ($v) use (&$count) {
        ++$count;
        if ($v == 'b') {
            return new infra_Fix('del');
        }

    });

    if ($count == 4 && sizeof($ar) == 3 && $ar[1] == 'c') {
    } else {
        $result = false;
    }

    //obj
    $ar = array('a' => 111,'b' => 222,'c' => 333,'e' => 444);
    $count = 0;
    infra_foro($ar, function ($v, $key) use (&$count) {
        ++$count;
        if ($key == 'b') {
            return new infra_Fix('del');
        }
    });
    if ($count == 4 && sizeof($ar) == 3 && !isset($ar['b'])) {
    } else {
        $result = false;
    }

    //obj back
    $ar = array('a' => 111,'b' => 222,'c' => 333,'e' => 444);
    $count = 0;
    infra_foro($ar, function ($v, $key) use (&$count) {
        ++$count;
        if ($key == 'b') {
            return new infra_Fix('del');
        }
    }, true);
    if ($count == 4 && sizeof($ar) == 3 && !isset($ar['b'])) {
    } else {
        $result = false;
    }

    //obj back ret
    $ar = array('a' => 111,'b' => 222,'c' => 333,'e' => 444);
    $count = 0;
    infra_foro($ar, function ($v, $key) use (&$count) {
        ++$count;
        if ($key == 'b') {
            return new infra_Fix('del', true);
        }
    }, true);
    if ($count == 3 && sizeof($ar) == 3 && !isset($ar['b'])) {
    } else {
        $result = false;
    }

    //obj ret
    $ar = array('a' => 111,'b' => 222,'c' => 333,'e' => 444);
    $count = 0;
    infra_foro($ar, function ($v, $key) use (&$count) {
        ++$count;
        if ($key == 'b') {
            return new infra_Fix('del', true);
        }
    });
    if ($count == 2 && sizeof($ar) == 3 && !isset($ar['b'])) {
    } else {
        $result = false;
    }

    if (!$result) {
        return infra_err($ans, 'err');
    }

    return infra_ret($ans, 'ret');
