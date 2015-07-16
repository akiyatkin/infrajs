<?php

    require_once __DIR__.'/../../infra/infra.php';
    infra_require('*infra/ext/template.php');

    $tpls = infra_loadJSON('*infra/tests/resources/templates.json');

    function getmicrotime()
    {
        list($usec, $sec) = explode(' ', microtime());

        return $usec;
        //return ((float)$usec + (float)$sec); 
    }

    if (empty($_GET['type'])) {
        echo '<table style="font-size:14px; font-family:monospace;">';
        $time = getmicrotime();
        infra_forr($tpls, function &($t, $key) use (&$time) {
                $r = null;
                if (isset($_GET['key']) && $_GET['key'] != $key) {
                    return $r;
                }
                echo '<tr><td>';
                echo $key;
                echo '</td><td>';
                echo htmlentities($t['tpl']);
                echo '</td><td nowrap="1">';
                if (@is_null($t['data'])) {
                    $data = array();
                } else {
                    $data = $t['data'];
                }

                //for($i=0,$l=10;$i<$l;$i++){
                    $r = infra_template_parse(array($t['tpl']), $data);
                //}
                echo ceil((getmicrotime() - $time) * 1000);
                echo 'мс';
                echo '</td><td>';

                if ($r === $t['res']) {
                    echo '"<b>'.htmlentities($r).'</b>"';
                } else {
                    echo '<span style="color:red; font-weight:bold"><b>"'.htmlentities($r).'"</b></span><br>"<b style="color:gray">'.htmlentities($t['res']).'</b>"';
                }
                echo '</td><td>';
                echo infra_json_encode($data);
                echo '</td><td>';
                echo @$t['com'];
                echo '</td><tr>';
                $r = null;

return $r;
            });
        echo '</table>';
    } else {
        $ans = array();
        $ans['title'] = 'Тест шаблонизатора. Без 3х известых ошибок.';
        $ans['class'] = 'bg-warning';
        $msg = infra_forr($tpls, function ($t, $key) {
            if ($key < 3) {
                return;
            }

if (@is_null($t['data'])) {
    $data = array();
} else {
                $data = $t['data'];
            }
            $r = infra_template_parse(array($t['tpl']), $data);

            if ($r !== $t['res']) {
                return 'Ошибка '.$t['tpl'];
            }
        });
        if (is_string($msg)) {
            $ans['msg'] = $msg;

            return infra_ans($ans);
        }

        return infra_ret($ans, 'Всё ок');
    }
