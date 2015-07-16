<?php

/*
infra_html() получить весь html
infra_html($html) Добавить html снизу всего 
infra_html($html,true) Установить новый html 
infra_html($html,$id) Добавить html в блок с id=$id 
 */
function htmlGetUnick()
{
    global $htmlGetUnick_last_time;
    if (!$htmlGetUnick_last_time) {
        $htmlGetUnick_last_time = 0;
    }
    $t = time();
    while ($t <= $htmlGetUnick_last_time) {
        $t++;
    }
    $htmlGetUnick_last_time = $t;

    return $t;
};
function infra_htmlclear($id)
{
    infra_html('', $id);
}
function infra_html($html = null, $id = null)
{
    global $infra_store_html;
    if (!$infra_store_html) {
        $infra_store_html;
    }

    $args = func_get_args();
    if (is_null($html)) {
        $html = $infra_store_html;

        return $html;
    }
    if (is_null($id)) {
        $infra_store_html .= $html;

        return $infra_store_html;
    }
    if ($id === true) {
        $infra_store_html = $html;

        return;
    }

    $t = '·';
    while (strpos($infra_store_html, $t) !== false) {
        //Смотрим нет ли указанного символа в шаблоне, если нет то можно его использовать в качестве временной замены
        $t = htmlGetUnick();
    }
    $storhtml = preg_replace("/[\r\n]/", $t, $infra_store_html);
    preg_match('/(.*?id *= *["\']'.$id.'["\'].*?>)(.*)/i', $storhtml, $m);
    if (sizeof($m) === 3) {
        $hl = $m[1];
        $hl = preg_replace('/'.$t.'/', "\n", $hl);
        $hr = $m[2];
        $hr = preg_replace('/'.$t.'/', "\n", $hr);
        $stor_html = $hl.$html.$hr;

        $infra_store_html = $stor_html;
        //$stor_html=($m[1]||'').preg_replace(new RegExp(t,'g'),'\n')+html+(m[2]||'').replace(new RegExp(t,'g'),'\n');
        return true;
    } else {
        return false;
    }
}
