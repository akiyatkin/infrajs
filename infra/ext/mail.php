<?php

function infra_mail_encode($str)
{
    return '=?UTF-8?B?'.base64_encode($str).'?=';
}
function infra_mail_check($email)
{
    if (!$email) {
        return false;
    }

    return preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/', $email);
}
function infra_mail_sent($subject, $email_from, $email_to, $body)
{
    $p = explode(',', $email_from);
    $email_from = $p[0];
    $p = explode('<', $email_from);
    if (sizeof($p) > 1) {
        $name_from = trim($p[0]);
        $p = explode('>', $p[1]);
        $email_from = trim($p[0]);
    } else {
        $name_from = '';
        $email_from = trim($p[0]);
    }

    $subject = infra_mail_encode($subject);
    $from = infra_mail_encode($name_from).' <'.$email_from.'>';

    $conf = infra_config();
    if ($conf['admin']['from']) {
        $headers = 'From: '.$conf['admin']['from']."\r\n";
    } else {
        $headers = 'From: '.$from."\r\n";
    }
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
    $headers .= 'Reply-To: '.$email_from."\r\n";

    $p = explode(',', $email_to);
    for ($i = 0, $l = sizeof($p);$i < $l;++$i) {
        $email_to = $p[$i];
        $p2 = explode('<', $email_to);
        if (sizeof($p2) > 1) {
            $name_to = trim($p2[0]);
            $p3 = explode('>', $p2[1]);
            $email_to = trim($p3[0]);
        } else {
            $name_to = '';
            $email_to = trim($p2[0]);
        }
        $to = infra_mail_encode($name_to).' <'.$email_to.'>';
        $r = @mail($to, $subject, $body, $headers);
        if (!$r) {
            break;
        }
    }

    return $r;
}
function infra_mail_toSupport($subject, $from, $body)
{
    //письмо в Техническую поддержку 
    $conf = infra_config();
    $emailto = $conf['admin']['support'];
    if (!$emailto) {
        $emailto = $conf['admin']['email'];
    }

    return infra_mail_sent($subject, $from, $emailto, $body);
}
function infra_mail_fromSupport($subject, $to, $body)
{
    //письмо от админa
    $conf = infra_config();
    $from = $conf['admin']['support'];
    if (!$from) {
        $from = $conf['admin']['email'];
    }

    return infra_mail_sent($subject, $from, $to, $body);
}
function infra_mail_fromAdmin($subject, $to, $body)
{
    //письмо от админa
    $conf = infra_config();
    $from = $conf['admin']['email'];

    return infra_mail_sent($subject, $from, $to, $body);
}
function infra_mail_toAdmin($subject, $from, $body, $debug = false)
{
    //письмо админу
    $conf = infra_config();
    if ($debug) {
        if ($conf['admin']['support']) {
            $emailto = $conf['admin']['support'];
        } else {
            $subject = 'Нет support в .config.json '.$subject;
            echo $subject;
            exit;
        }
    } else {
        $emailto = $conf['admin']['email'];
    }

    return infra_mail_sent($subject, $from, $emailto, $body);
}
function infra_mail_admin($subject, $body, $debug = false)
{
    //письмо админу от админа
    $conf = infra_config();
    $from = $conf['admin']['email'];
    if ($debug) {
        if ($conf['admin']['support']) {
            $to = $conf['admin']['support'];
        } else {
            $subject = 'Нет support в .config.json '.$subject;
            echo $subject;
            exit;
        }
    } else {
        $to = $from;
    }

    return infra_mail_sent($subject, $from, $to, $body);
}
