<?php
    use itlife\infrajs\infra\ext\crumb;

$ans = array();
    $ans['title'] = 'Хлебные крошки';

    $obj = crumb::getInstance('test/check');
    $parent = crumb::getInstance('test');
    if (crumb::$childs['test/check'] !== $obj) {
        return infra_err($ans, 'Некорректно определяется крошка 1');
    }
    if (crumb::$childs['test'] !== $parent) {
        return infra_err($ans, 'Некорректно определяется крошка 2');
    }

    if ($obj->parent !== $parent) {
        return infra_err($ans, 'Некорректно определён parent');
    }

    crumb::change('test/hi');
    $obj = crumb::getInstance('test');

    if (!$obj->is) {
        return infra_err($ans, 'Не применилась крошка на втором уровне');
    }

$root = crumb::getInstance();

    crumb::change('');
    $crumb = crumb::getInstance('');
    $f = $crumb->query;

    crumb::change('test');

    $s = &crumb::getInstance('some');
    $s2 = &crumb::getInstance('some');
    $r = infra_isEqual($s, $s2);

    $s = crumb::$childs;
    $r2 = infra_isEqual($s[''], crumb::getInstance());

    $r = $r && $r2;

    $crumb = crumb::getInstance('test');
    $crumb2 = crumb::getInstance('test2');

    if (!($f == null && $r && !is_null($crumb->query) && is_null($crumb2->query))) {
        return infra_err($ans, 'Изменения крошек');
    }

    crumb::change('test/test');
    $inst = crumb::getInstance('test/test/test');

return infra_ret($ans, 'Всё ок');
