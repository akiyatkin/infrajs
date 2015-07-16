<?php

function &infra_db($debug = false)
{
    infra_cache_no();

    return infra_once('infra_db', function &($debug) {
        $config = infra_config();
        if (!$debug) {
            $debug = $config['debug'];
        }
        $ans = array();
        if (!$config['mysql']) {
            //if($debug)die('Нет конфига для соединения с базой данных. Нужно добавить запись mysql: '.infra_json_encode($config['/mysql']));
            return $ans;
        }
        $config = @$config['mysql'];

if (!$config['user']) {
    //if($debug)die('Не указан пользователь для соединения с базой данных');
            return $ans;
}
        try {
            @$db = new PDO('mysql:host='.$config['host'].';dbname='.$config['database'].';port='.$config['port'], $config['user'], $config['password']);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            if ($debug) {
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            }
                /*array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => true,
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING 
            )*/
            $db->exec('SET CHARACTER SET utf8');
        } catch (PDOException $e) {
            //if($debug)throw $e;
            $db = false;
            /*if(!$debug){
                print "Error!: " . infra_toutf($e->getMessage()) . "<br/>";
                die();
            }*/
        }

        return $db;
    }, array($debug));
}
function infra_stmt($sql)
{
    return infra_once('infra_stmt', function ($sql) {
        $db = infra_db();

        return $db->prepare($sql);
    }, array($sql));
}
