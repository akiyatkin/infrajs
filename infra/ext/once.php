<?php

global $infra_once;
$infra_once = array();
function infra_hash($args, $r = false)
{
    //Функция которая передаётся аргументом надо передвать как use
    if (is_array($args)) {
        $a = array();
        foreach ($args as $k => $v) {
            $a[$k] = infra_hash($v, true);
        }
    } else {
        if (is_callable($args)) {
            $a = 'func!';
        }//Заглушка для функции
        else {
            $a = $args;
        }
    }
    if ($r) {
        return serialize($a);
    }

    return md5(serialize($a));
}
function &infra_once($name, $call, $args = array(), $re = false)
{
    global $infra_once;

    $strargs = infra_hash($args);
    $name = $name.$strargs;

    if (!is_callable($call)) {
        $re = false;
        $infra_once[$name] = array('result' => $call);
    }
    if (isset($infra_once[$name]) && !$re) {
        return $infra_once[$name]['result'];
    }
    $infra_once[$name] = array('exec' => true);

    $v = array_merge($args, array($re));

    $v = call_user_func_array($call, $v);

    $infra_once[$name]['result'] = $v;

    return $infra_once[$name]['result'];
}
/*

infra_once('somefunc',function(){
    
},array($name));

infra_once('somefunc',$value,array($name));

*/
