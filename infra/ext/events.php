<?php

function infra_fire(&$obj, $clsfn, $argso = array())
{
    global $infra;
    if ($obj !== $infra) {
        $clsfn = explode('.', $clsfn);
    } else {
        $clsfn = array($clsfn);
    }

    $cls = (sizeof($clsfn) > 1) ? array_shift($clsfn) : '';
    $fn = implode('.', $clsfn);

    if ($cls) {
        $depot = &infra_fire_depot($infra, $cls.'.'.$fn);
    } else {
        $depot = &infra_fire_depot($obj, $fn);
    }
    $depot['evt'] = array(
        'context' => &$obj,
        'args' => array_merge(array(&$obj), $argso),//Аргументы которые передаются в callback
    );
    //Если класс, то у непосредственно объекта вообще ничего не храниться

    foreach ($depot['listen'] as &$cal) {
        infra_fire_exec($depot, $cal);
    }
}
function infra_listen(&$obj, $fn, $callback)
{
    $depot = &infra_fire_depot($obj, $fn);
    $depot['listen'][] = $callback;
}
/*
infra.fire(layer1,'layer.onshow');
infra.fire(layer2,'layer.onshow');
infra.wait(infra,'layer.onshow',function(layer2){

});*/
function infra_when(&$obj, $fn, $callback)
{
    //depricated, для классов не подходит
    $depot = &infra_fire_depot($obj, $fn);
    $cal = function () use (&$depot) {
        foreach ($depot['wait'] as $k => $cal) {
            unset($depot['wait'][$k][0]);//должно удалиться и в listen так как ссылка;

            infra_fire_exec($depot, $depot['wait'][$k][1]);
            unset($depot['wait'][$k]);
            break;
        }
    };
    $depot['wait'][] = array(&$cal,&$callback);
    $depot['listen'][] = &$cal;
}
function infra_wait(&$obj, $fn, $callback)
{
    //depricated, для классов не подходит
    $depot = &infra_fire_depot($obj, $fn);
    if ($depot['evt']) {
        infra_fire_exec($depot, $callback);
    } else {
        $cal = function () use (&$depot) {
            foreach ($depot['wait'] as $k => $cal) {
                unset($depot['wait'][$k][0]);//должно удалиться и в listen так как ссылка;

                infra_fire_exec($depot, $depot['wait'][$k][1]);
                unset($depot['wait'][$k]);
                break;
            }
        };

        $depot['wait'][] = array(&$cal,&$callback);
        $depot['listen'][] = &$cal;
    }
}
function infra_handle(&$obj, $fn, &$callback)
{
    //depricated, для классов не подходит
    $depot = infra_fire_depot($obj, $fn);
    if ($depot['evt']) {
        infra_fire_exec($depot, $callback);
    }
    $depot['listen'][] = $callback;
}
function infra_unlisten(&$obj, $fn, &$callback)
{
    $depot = infra_fire_depot($obj, $fn);
    foreach ($depot['listen'] as &$cal) {
        if ($cal === $callback) {
            unset($cal);
        }
        break;
    }
}
function &infra_fire_depot(&$obj, $fn)
{
    $n = '__infra_fire_depot__';
    if (!isset($obj[$n])) {
        $obj[$n] = array();
    }
    if (!isset($obj[$n][$fn])) {
        $obj[$n][$fn] = array(//При повторном событии этот массив уже будет создан
        'listen' => array(),//Массив всех подписчиков
        'wait' => array(),
        'evt' => null,//Событие ещё не состоялось, обновляется при каждом событии
    );
    }

    return $obj[$n][$fn];
}
function infra_fire_exec(&$depot, &$callback)
{
    $r = call_user_func_array($callback, $depot['evt']['args']);
    if (!is_null($r)) {
        $depot['free'] = false;//Метка что событие оборвалось
        return $r;
    }
}
