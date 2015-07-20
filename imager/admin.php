<?php
/*
Copyright 2008-2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
*/

infra_require('*imager/imager.inc.php');
    infra_admin(true);

if (!function_exists('runfolder')) {
    function runfolder($dir, $f = 1, $d = 0, $sub = false, $exts = false, &$filelist = array(), $pre = '')
    {
        if (is_dir($dir) && $dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file[0] == '.') {
                    continue;
                }
                if ($file[0] == '~') {
                    continue;
                }
                $path = $dir.$file;
                if (is_file($path) && $exts) {
                    preg_match('/\.(\w{0,4})$/', $file, $math);//Расширение при поиске не учитываем
                        $ext = strtolower($math[1]);
                    if (!in_array($ext, $exts)) {
                        continue;
                    }
                }

//$count++;
                    //if($count<$lims)continue;
                    //if($count>=($lims+$limc))break;


                    if (!$f && is_file($path) && (!$d || !is_dir($path))) {
                        continue;
                    }//Файлы не надо


//if(!$f && is_file($path))continue;//Файлы не надо
                    if (is_dir($path)) {
                        if ($sub) {
                            runfolder($path.'/', $f, $d, $sub, $exts, $filelist, $pre.$file.'/');
                        }
                        if (!$d) {
                            continue;
                        }//Папки не надо
                    }
                if ($d && preg_match("/\.files$/", $file)) {
                    continue;
                }
                    //$weblife->modified(false,$path);
                    array_push($filelist, $pre.$file);
            }
            closedir($dh);
        }

        return $filelist;
    }
}
    $dirs = infra_dirs();
    $dircache = $dirs['cache'].'imager_resize/';
    $files = runfolder($dircache, 0, 1);
    $countcache = sizeof($files);

    $dirorig = $dirs['data'].'imager/.notwater/';
    $files = runfolder($dirorig, 1, 0);
    $countorig = sizeof($files);

    $iswater = infra_theme($dirs['data'].'imager/mark.png');
    $ishwater = infra_theme($dirs['data'].'imager/.mark.png');
    $water = $iswater || $ishwater;
    if (isset($_GET['action'])) {
        $act = $_GET['action'];
        if ($act == 'togglemark') {
            if ($iswater) {
                $new = preg_replace('/mark\.png$/', '.mark.png', $iswater);
                rename($iswater, $new);
            } elseif ($ishwater && !$iswater) {
                $new = preg_replace('/\.mark\.png$/', 'mark.png', $ishwater);
                rename($ishwater, $new);
            }
        } elseif ($act == 'removemarks') {
            //$dir='infra/data/';
            $dir = '';
            session_start();
            if (isset($_REQUEST['restart'])) {
                unset($_SESSION['imager']);
            }
            if (!isset($_SESSION['imager'])) {
                //Шаг один
                $files = runfolder($dir, 1, 0, true, array('jpg', 'gif', 'png'));
                //Если на пробежке обламаемся сессия создана не будет и при обновлении продолжим...
                $_SESSION['imager'] = array();
                $_SESSION['imager']['origs'] = array();
                $_SESSION['imager']['files'] = $files;
            }
            foreach ($_SESSION['imager']['files'] as $k => $file) {
                $src = $dir.$file;
                $info = imager_readInfo($src);//Долгая операция
                $orig = $info['orig'];
                if ($orig) {
                    if (!isset($_SESSION['imager']['origs'][$orig])) {
                        $_SESSION['imager']['origs'][$orig] = array();
                    }
                    $_SESSION['imager']['origs'][$orig][] = $dir.$file;
                }
                unset($_SESSION['imager']['files'][$k]);//Чтобы при обнолении страницы, не бегать снова 
            }
            //Теперь у нас есть только массив origs
            foreach ($_SESSION['imager']['origs'] as $orig => $srcs) {
                $origf = infra_theme($orig);
                if (!$origf) {
                    //if(preg_match("/^core\/data\//",$orig))continue;//старая версия сайта ничего с этим не поделать
                    //die('Не найден оригинал '.infra_toutf($orig)." для картинки ".infra_toutf(print_r($srcs,true)).'<br>\n');
                    echo 'Не найден оригинал '.infra_toutf($orig).' для картинки '.infra_toutf(print_r($srcs, true)).'<br>\n';
                    continue;
                }

                foreach ($srcs as $src) {
                    $r = copy($origf, $src);
                    if (!$r) {
                        die('Не удалось скопировать на место оригинал '.infra_toutf($src));
                    }
                }
                $r = unlink($origf);
                if (!$r) {
                    die('Не удалось удалить востановленный оригинал');
                }
                unset($_SESSION['imager']['origs'][$orig]);//Пометили что этот оригинал уже востановили
            }

            $files = runfolder($dirorig, 1, 0);
            if (sizeof($files) > 0) {
                //Если остались не востановленные оригиналы.. делаем их backup
                $dirs = infra_dirs();
                if (!is_dir($dirs['backup'])) {
                    mkdir($dirs['backup']); //Режим без записи на жёсткий диск
                }
                $dirbackup = $dirs['backup'].'imager_orig/';
                $dirbackup .= date('j.d.Y').'_'.time().'/';
                $r = rename($dirorig, $dirbackup);
                if (!$r) {
                    die('Не удалось сделать backup оставшихся оригиналов');
                }
            }
            unset($_SESSION['imager']);
        } elseif ($act == 'delcache') {
            $files = runfolder($dircache, 1, 0, true);
            foreach ($files as $file) {
                unlink($dircache.$file);
            }
            $files = runfolder($dircache, 0, 1);
            foreach ($files as $file) {
                rmdir($dircache.$file.'/');
            }
        }
        header('location: admin.php');
        exit;
    }

?>

<div style="margin:50px 100px; font-family: Tahoma; font-size:14px">
	Config.imager.watermark: <b><?php $conf = infra_config(); echo($conf['imager']['watermark'] ? 'true' : 'false');?></b> - глобальный запрет и создавать или нет папку data/imager/<br>
	Количество оригиналов иллюстраций с водяным знаком: <b><?php echo $countorig?></b>. 
	<br><a href="?action=removemarks">Удалить на иллюстрациях водяной знак</a>. <small>Если будет ошибка на ограничение времени выполенния скрипта, нужно обновлять страницу пока скрипт не закончит работу.</small><br>
<!--	<a title="Нажимать нельзя" style="font-size:10px; color:gray;" href="?action=delorig">Удалить оригиналы</a><br>-->
Количество иллюстарций с изменёнными размерами в кэше: <b><?php echo $countcache?></b>. <a title="Можно нажимать" href="?action=delcache">Удалить кэш</a><br>
<hr>
Есть файл водяного знака: <b><?php echo ($water) ? 'Да' : 'Нет';?></b><br>
Водяной знак на иллюстрациях: <a title="Изменить" style="font-weight:bold; color:<?php echo ($iswater) ? 'green' : 'red'; ?>" href="?action=togglemark"><?php echo ($iswater) ? 'добавляется' : 'не добавляется';?></a><br>
</div>
