<?php

/*
parse
    make
         prepare(template); Находим все вставки {}
         analysis(ar); Бежим по всем скобкам и разбираем их что куда и тп 
             parseexp('exp')
                parseCommaVar('asd.as[2]')
                    parsevar('asd.as[2]')
        tpls=getTpls(ar) Объект свойства это шаблоны. каждый шаблон это массив элементов в которых описано что с ними делать строка или какая-то подстановка
        res=parseEmptyTpls(tpls);
 
    text=exec(tpls,data,tplroot,dataroot) парсится - подставляются данные выполняется то что указано в элементах массивов
        execTpl конкретный tpl
            getValue один шаг в шаблоне
                getCommaVar
                    getVar
                    getPath

 */

 /*
  * условия {asdf?:asdf} {asdf&asdf?:asdf} {asdf|asdf?:asdf}
  * {data:asd{asdf}}
  *
 */
/*
 * url нужен чтобы кэширвоать загрузку. текст передаётся если надо [text]
 * data не кэшируется передаётся объектом
 * tplroot строка что будет корневым шаблоном
 * repls дополнительный массив подстановок.. результат работы getTpls
 * dataroot путь в данных от которых начинается корень данных для первого шаблона
 */
/*
 * Функции берутся в следующем порядке сначало от this в данных потом от корня данных потом в спецколлекции потом в глобальной области
 **/
infra_require('*infra/ext/seq.php');

function infra_template_prepare($template)
{
    $start = false;
    $breaks = 0;
    $res = array();
    $exp = '';
    $str = '';
    for ($i = 0, $l = strlen($template);$i < $l;++$i) {
        $sym = $template[$i];
        if (!$start) {
            if ($sym === '{') {
                $start = 1;
            } else {
                $str .= $sym;
            }
        } elseif ($start === 1) {
            if (preg_match("/\s/", $sym)) {
                $start = false;//Игнорируем фигурную скобку если далее пробельный символ
                $str .= '{'.$sym;
            } else {
                $start = true;
            }
        }
        if ($start === true) {
            if ($sym === '{') {
                $breaks++;
            }
            if ($sym === '}') {
                $breaks--;
            }
            if ($breaks === -1) {
                //Текущий символ } выражение закрыто. Есть $str предыдущая строка и $exp строка текущегго выражения
                if ($str != '') {
                    $res[] = $str;
                }
                $res[] = array($exp);

                $breaks = 0;
                $str = '';
                $exp = '';
                $start = false;
            } else {
                $exp .= $sym;
            }
        }
    }
    if ($start === 1) {
        $str .= '{';
    }
    if ($str != '') {
        $res[] = $str;
    }
    if ($exp) {
        $res[sizeof($res) - 1] .= '{'.$exp;
    }

    return $res;
}
function infra_template_analysis(&$group)
{
    /*
     *  as.df(sdf[as.d()])
     *  as.df   (  sdf[    as.d  ()    ]  )
     *  as.df   (  sdf[  ( as.d  ())   ]  )
     * 'as.df', [ 'sdf[',['as.d',[]] ,']' ]
     *
     * 'as.df',[ 'sdf[as.d',[] ],']'
     * */
    infra_forr($group, function &($exp, $i) use (&$group) {
        $r = null;
        if (is_string($exp)) {
            return $r;
        } else {
            $exp = $exp[0];
        }

        //asdf.asdf(sadf.asdf)
        //['asdf.asdf',['asdf.asdf']]
        //
        //(asdf&&asdf)|(sadf&&asdf)
        //[['asdf&&asdf'],'|',['asdf&&asdf']]
        //
        // b&a[b].c()
        /*
        array('b&',
            array('type'=>'square',
                'suf','a',
                'val'=>array('b')),
            '.',
            array('type'=>'round',
                'suf','c',
                'val'=>array())
        */
        //
        if (@$exp[0] == '{' && @$exp[strlen($exp) - 1] == '}') {
            $group[$i] = $exp;

            return $r;
        }

        $group[$i] = infra_template_parseexp($exp);

/*
         * a[b(c)]()
         * a[(b(c))]()
         * a[  (b (c))  ] ()
         * 'a[', ['(b',['(c)'],')',] ,']',['()']
         * */
        //print_r($group[$i]);
        return $r;
    });
}
function infra_template_parse($url, $data = array(), $tplroot = 'root', $dataroot = '', $tplempty = 'root')
{
    $tpls = infra_template_make($url, $tplempty);

    $text = infra_template_exec($tpls, $data, $tplroot, $dataroot);

    return $text;
}
/*
function infra_template_runTpls(&$d,$call,$nar){
    infra_fora($d,function($call,$nar, &$d){
        if(is_string($d))return;
        if(is_string($d['tpl']))call_user_func_array($call,array_merge($nar,array($d['tpl'])));
        infra_template_runTpls($d['term'],$call,$nar);
        infra_template_runTpls($d['yes'],$call,$nar);
        infra_template_runTpls($d['no'],$call,$nar);
        if($d['var']&&$d['var'][0]&&$d['var'][0]['orig']) infra_template_runTpls($d['var'][0],$call,$nar);
    },array($call,&$nar));
};
function infra_template_parseEmptyTpls(&$tpls){
    $res=array();
    infra_foro($tpls,function(&$res,&$tpls, $t){
        infra_template_runTpls($t,function(&$res,&$tpls, $tpl){
            if(!$tpls[$tpl]){
                $r=infra_template_make(array($tpl),$tpl);
                array_unshift($res,$r);
            }
        },array(&$res,&$tpls));
    },array(&$res,&$tpls));
    array_unshift($res,$tpls);
    return $res;
};*/
/*function infra_template_parseEmptyTpls2($tpls){
    $res=array();
    foreach($tpls as $sub=>$v){
        for($i=0,$l=sizeof($tpls[$sub]);$i<$l;$i++){
            if($tpls[$sub][$i]['tpl']&&!$tpls[$tpls[$sub][$i]['tpl']]){//Нашли используемый подшаблон, которого нет
                $res[]=&infra_template_make(array($tpls[$sub][$i]['tpl']),$tpls[$sub][$i]['tpl']);
                //При объединении шаблонов, добавляемые подшаблоны будут с более высоким приоритетом чем те что уже есть, так что не боимся что будет заменён подшаблон, который далее будет добавлен первым как дополнительный
                //Но если дополнительные подшаблоны добавятся как шаблоны по умолчанию, в конец списка, то до таких подшаблонов дело никогда не дойдёт
            }
        }
    }
    $res[]=&$tpls;
    array_reverse($res);
    return $res;
}*/

function &infra_template_stor()
{
    global $infra_template_store;
    $stor = &$infra_tempalte_store;
    if (!isset($stor['template'])) {
        $stor['template'] = array();
    }
    if (!isset($stor['template']['cache'])) {
        $stor['template']['cache'] = array();
    }

    return $stor['template'];
}
function &infra_template_make($url, $tplempty = 'root')
{
    $key = md5(print_r($url, true));
    $stor = &infra_template_stor();
    if (isset($stor['cache'][$key])) {
        return $stor['cache'][$key];
    }

    if (is_string($url)) {
        $template = infra_loadTEXT($url);
    } elseif (is_array($url)) {
        $template = $url[0];
    }
    if (!is_string($template)) {
        $template = '';
    }

    $ar = infra_template_prepare($template);
    infra_template_analysis($ar);

    $tpls = infra_template_getTpls($ar, $tplempty);
    if (!$tpls) {
        $tpls[$tplempty] = array();
    }//Пустой шаблон добавляется когда вообще ничего нет
    //$res=infra_template_parseEmptyTpls($tpls);
    $res = $tpls;

    $stor['cache'][$key] = $res;

    return $res;
}
function infra_template_exec(&$tpls, &$data, $tplroot = 'root', $dataroot = '')
{
    //Только тут нет conf
    if (is_null($tplroot)) {
        $tplroot = 'root';
    }
    if (is_null($dataroot)) {
        $dataroot = '';
    }

    $dataroot = infra_seq_right($dataroot);
    $conftpl = array('tpls' => &$tpls,'data' => &$data,'tplroot' => &$tplroot,'dataroot' => $dataroot);
    $r = infra_template_getVar($conftpl, $dataroot);
    $tpldata = $r['value'];
    //if(!$tpldata&&!is_array($tpldata)&&$tpldata!=='0'&&$tpldata!==0)return '';//Когда нет данных

    if (is_null($tpldata) || $tpldata === false || $tpldata === '') {
        return '';
    }//Данные должны быть 0 подходит


    $tpl = infra_fora($tpls, function &(&$t) use ($tplroot) {
        return $t[$tplroot];
    });

    if (is_null($tpl)) {
        return $tplroot;
    }//Когда нет шаблона
    $conftpl['tpl'] = &$tpl;

    $html = '';

    $html .= infra_template_execTpl($conftpl);

    return $html;
};
function infra_template_execTpl($conf)
{
    $html = '';

    //$dataroot=$conf['dataroot'];
    //dataroot меняется при подключении шаблона и при a().b для b dataroot будет a - так нельзя так как b от корня не может быть взят. с.b должно быть
    //var - asdf[asdf] но получить такую переменную нельзя нужно расчитать этот путь getPath asdf.qwer и где же хранить этот путь
    //lastroot нужен чтобы прощитать с каким dataroot нужно подключить шаблон это всегда путь от корня

    infra_forr($conf['tpl'], function &(&$d) use (&$conf, &$html) {
        $r = null;
        $var = infra_template_getValue($conf, $d);//В getValue будет вызываться execTpl но dataroot всегда будет возвращаться в прежнее значение

        if (is_string($var)) {
            $html .= $var;
        }
        if (is_float($var)) {
            $html .= $var;
        }
        if (is_int($var)) {
            $html .= $var;
        } else {
            $html .= '';
        }

return $r;
    });

    //$conf['dataroot']=$dataroot;
    return $html;
}
function &infra_template_getPath(&$conf, $var)
{
    //dataroot это прощитанный путь до переменной в котором нет замен
    /*
     * Функция прощитывает сложный путь
     * Путь содержит скобки и содежит запятые
     * asdf[asdf()]
     * */
    $ar = array();
    infra_forr($var, function &(&$v) use (&$conf, &$ar) { //'[asdf,asdf,[asdf],asdf]'
        if (is_string($v) || is_int($v)) {
            //name
            $ar[] = $v;
        } elseif (@is_array($v) && @is_array($v[0]) && @is_string($v[0]['orig'])) {
            //name[name]  [name,[{}],name]
            $ar[] = infra_template_getValue($conf, $v[0]);
        } elseif (is_array($v) && is_string($v['orig'])) {
            //name.name().name [name,{},name]
            global $infra_template_scope;
            //$t=array_merge($ar,$v);
            if ($ar) {
                //смутнопонимаемая ситуация... asdf().qewr().name после замены получаем zxcv.qewr().name потом tyui.name для того чтобы получить tyui нужно установить dataroot zxcv
                //сделать merge zxcv и qwer нельзя потому что qwer это сложный объект и тп... {orig:'a.b[c]'} а qwer это строка путь до знанчения тогда как zxcv нужно ещё прощитать взяв его от qwer 
                //в параметрах может потребоваться настоящий root
                //ghjk настоящий root
                //zxcv новый root чтобы корректно получить функцию qwer
                //ghjk нужный root для получения some
                //asdf().   qwer(some)   .name
                //
                //Нужно свести всё к одному руту
                //Редактировать $v['orig'] нельзя.. как указать root только для функции
                //С другой стороны если редактировать $v сейчас то и в следуюищй раз при парсе будет корректива заменящая на новую.. или возвращать изменения
                //В общем раз новый root нужет только для функции находим и подменяем путь до этой функции в структруе.. и потом возвращаем изменения
                $temp = $v['fn']['var'][0];
                $v['fn']['var'][0] = array_merge($ar, $temp);
                //Добавить в fn
            }
            $d = infra_template_getValue($conf, $v, true);//{some()} вывод пустой если функции нет, чтобы работало {some()?1?2}. Была ошибка выводилось 1 когда функции небыло, так как в условие попадала строка some
            if ($ar) {
                $v['fn']['var'][0] = $temp;
            }
            if (!isset($infra_template_scope['zinsert'])) {
                $infra_template_scope['zinsert'] = array();
            }
            $n = sizeof($infra_template_scope['zinsert']);
            $infra_template_scope['zinsert'][$n] = $d;

            $ar = array();
            $ar[] = 'zinsert';
            $ar[] = (string) $n;
        } else {
            $r = infra_template_getVar($conf, $v);
            $r = $r['value'];
            $ar[] = $r;
        }
        $r = null;

return $r;
    });

    return $ar;
}
function infra_template_getVar(&$conf, $var = array())
{
    //dataroot это прощитанный путь до переменной в котором нет замен
    //$var содержит вставки по типу ['asdf',['asdf','asdf'],'asdf'] то есть это не одномерный массив. asdf[asdf.asdf].asdf
    //var одна переменная

    if (is_null($var)) {
        //if($checklastroot)$conf['lastroot']=false;//Афигенная ошибка. получена переменная и далее идём к шаблону переменной для которого нет, узнав об этом lastroot не сбивается и шаблон дальше загружается с переменной в lastroot {$indexOf(:asdf,:s)}{data:descr}{descr:}{}	
        $value = '';
        $root = false;
    } else {
        global $infra_template_scope;
        $right = infra_template_getPath($conf, $var);

        $p = array_merge($conf['dataroot'], $right);

        $p = infra_seq_right($p);

        if (@(string) $p[sizeof($p) - 1] == '$key') {
            $value = $conf['dataroot'][sizeof($conf['dataroot']) - 1];

            if (!@$infra_template_scope['kinsert']) {
                $infra_template_scope['kinsert'] = array();
            }
            $n = sizeof($infra_template_scope['kinsert']);
            $infra_template_scope['kinsert'][$n] = $value;
            $root = array('kinsert',(string) $n);
        } elseif (@(string) $p[sizeof($p) - 1] == '~key') {
            $value = $conf['dataroot'][sizeof($conf['dataroot']) - 1];
            if (!@$infra_template_scope['kinsert']) {
                $infra_template_scope['kinsert'] = array();
            }
            $n = sizeof($infra_template_scope['kinsert']);
            $infra_template_scope['kinsert'][$n] = $value;
            $root = array('kinsert',(string) $n);
        } else {
            $value = infra_seq_get($conf['data'], $p);//Относительный путь от данных

            if (!is_null($value)) {
                $root = $p;
            }

            if (is_null($value) && sizeof($p)) {
                $value = infra_seq_get($infra_template_scope, $p);//Относительный путь
                if (!is_null($value)) {
                    $root = $p;
                }
            }
            if (is_null($value)) {
                $value = infra_seq_get($conf['data'], $right);//Абсолютный путь
                if (!is_null($value)) {
                    $root = $right;
                }
            }

            if (is_null($value) && sizeof($right)) {
                $value = infra_seq_get($infra_template_scope, $right);//Абсолютный путь
                if (!is_null($value)) {
                    $root = $right;
                }
            }

            if (is_object($value) && method_exists($value, 'toString')) {
                $value = $value->toString();
            }
            if (is_null($value)) {
                $root = $right;
            }//Афигенная ошибка. получена переменная и далее идём к шаблону переменной для которого нет, узнав об этом lastroot не сбивается и шаблон дальше загружается с переменной в lastroot {$indexOf(:asdf,:s)}{data:descr}{descr:}{}	
        }
    }

    return array(
        'root' => $root,//Путь от корня
        'value' => $value,
        //'right'=>$right//Путь которого достаточно чтобы найти переменную и путь о котором знает пользователь asdf[asdf] = asdf.qwer
    );
}

/*
{
    orig:'asdf:asd',//Оригинальное выражение в фигурных скобках
    var:{'somevar','asdf',[1]},//путь до данных для этого подключаемого шаблона

    tpl:'root',//Имя шаблона который нужно подключить в этом месте
    multi:true//Нужно ли для каждого элемента этих данных подключать указанный шаблон

    term:{},//Выражение которое нужно посчитать
    yes:{},
    no:{}

    cond:'s',//тип условия в одном символе = !
    a:{},
    b:{}
}
 */
global $infra_template_moment;
function infra_template_bool($var = false)
{
    return ($var || $var === '0');
}
function infra_template_getCommaVar(&$conf, &$d, $term = false)
{
    //Приходит var начиная от запятых в $d
    if (@$d['fn']) {
        $func = infra_template_getValue($conf, $d['fn']);
        if (is_callable($func)) {
            $param = array();
            for ($i = 0, $l = sizeof($d['var']);$i < $l;++$i) {
                //Количество переменных
                if (infra_template_bool(@$d['var'][$i]['orig'])) {
                    $v = infra_template_getValue($conf, $d['var'][$i], $term);
                    $param[] = $v;
                } elseif ($d['var']) {
                    $v = infra_template_getOnlyVar($conf, $d, $term, $i);
                    $param[] = $v;
                }
            }
            //$param[]=&$conf;
            global $infra_template_moment;
            $infra_template_moment = $conf;

            return call_user_func_array($func, $param);
        } else {
            return;//что возвращается когда нет функции которую нужно вызвать
            /*if($term)return null;
            else return $d['orig'];*/
        }
    } else {
        $v = infra_template_getOnlyVar($conf, $d, $term);

        return $v;
    }
}
function infra_template_getOnlyVar(&$conf, &$d, $term, $i = 0)
{
    if (@is_array($d['tpl'])) { //{asdf():tpl}
        $ts = array($d['tpl'],$conf['tpls']);
        $tpl = infra_template_exec($ts, $conf['data'], 'root', $conf['dataroot']);

        $r = infra_template_getVar($conf, $d['var'][$i]);
        $v = $r['value'];

        $lastroot = $r['root'] ? $r['root'] : $conf['dataroot'];
        $h = '';
        if (!$d['multi']) {
            $droot = $lastroot;
            $h = infra_template_exec($conf['tpls'], $conf['data'], $tpl, $droot);
        } else {
            if ($v) {
                foreach ($v as $kkk => $vvv) {
                    $droot = array_merge($lastroot, array($kkk));
                    $h .= infra_template_exec($conf['tpls'], $conf['data'], $tpl, $droot);
                }
            }
            /*infra_foru($v,function(&$v,$k) use(&$d,&$h,&$conf,&$lastroot,&$tpl){
                $droot=array_merge($lastroot,array($k));
                $h.=infra_template_exec($conf['tpls'],$conf['data'],$tpl,$droot);
            });*/
        }
        $v = $h;
    } else {
        if (isset($d['var'][$i])) {
            $r = infra_template_getVar($conf, $d['var'][$i]);
        } else {
            $r = null;
        }

        $v = $r['value'];
        if (!$term && is_null($v)) {
            $v = '';
        }
    }

    return $v;
}
function infra_template_getValue(&$conf, &$d, $term = false)
{
    if (is_string($d)) {
        return $d;
    }

    if (@$d['cond'] && !isset($d['term'])) {
        $a = infra_template_getValue($conf, $d['a'], false);
        $b = infra_template_getValue($conf, $d['b'], false);
        if ($d['cond'] == '=') {
            return ($a == $b);
        } elseif ($d['cond'] == '!') {
            return ($a != $b);
        } elseif ($d['cond'] == '>') {
            return ($a > $b);
        } elseif ($d['cond'] == '<') {
            return ($a < $b);
        } else {
            return false;
        }
    } elseif (isset($d['var'])) {
        $v = infra_template_getCommaVar($conf, $d, $term);

        return $v;
    } elseif ($d['term']) {
        $var = infra_template_getValue($conf, $d['term'], true);
        if (is_null($var) || $var === false || $var === '' || $var === 0) {
            //Пустой массив не false
            $r = infra_template_getValue($conf, $d['no'], $term);
        } else {
            $r = infra_template_getValue($conf, $d['yes'], $term);
        }

        return $r;
    }
}

function infra_template_getTpls(&$ar, $subtpl = 'root')
{
    //subtpl - первый подшаблон с которого начинается если конкретно имя не указано
    $res = array();

    for ($i = 0;$i < sizeof($ar);++$i) {
        if (is_array($ar[$i]) && isset($ar[$i]['template'])) {
            //Если это шаблон
            $subtpl = $ar[$i]['template'];
            $res[$subtpl] = array();//Для пустых определённый шаблонво, кроме root по умолчанию, для него массив не появится
            continue;
        };
        if (!isset($res[$subtpl])) {
            $res[$subtpl] = array();
        }
        $res[$subtpl][] = $ar[$i];
    }

    global $itn;
    foreach ($res as $subtpl => $v) {
        //Удаляется последний символ в предыдущем подшаблонe
        $t = sizeof($res[$subtpl]) - 1;
        $str = @$res[$subtpl][$t];
        if (!is_string($str)) {
            continue;
        }
        ++$itn;

        $str = $res[$subtpl][$t];
        //if(strpos($str,"</span>")!==false){
        $ch = $str[strlen($str) - 1];

/*		echo $ch.':'.ord($ch).'<br>';
        if(trim($str)=="</span>"){
            echo '<textarea>'.$str.'</textarea>';
        }*/
        //$res[$subtpl][$t]=preg_replace('/\r$/','',$res[$subtpl][$t]);
        $res[$subtpl][$t] = preg_replace('/[\n\r\t]+$/', '', $res[$subtpl][$t]);
        //$res[$subtpl][$t]=preg_replace('/\s+$/','',$res[$subtpl][$t]);
    }

    return $res;
}
global $infra_template_replacement;
$infra_template_replacement = array();
global $infra_template_replacement_ind;
$infra_template_replacement_ind = array();

function infra_template_parseStaple($exp)
{
    //С К О Б К И
    //Небыло проверок на функции
    //Если проверка была в выражении передаваемом в функции тоже могут быть скобки
    global $infra_template_replacement;
    global $infra_template_replacement_ind;
    $fn = '';
    $fnexp = '';
    $start = 0;
    $newexp = '';
    $specchars = array('?','|','&','[',']','{','}','=','!','>','<',':',',');//&
    for ($i = 0, $l = strlen($exp);$i < $l;++$i) { //Делается замена (str) на xinsert.. список знаков при наличии которых в str отменяет замену и отменяет накопление имени функции перед скобками
        /*
         * Механизм замен из asdf.asdf(asdf,asdf) получем временную замену xinsert0 и так каждые скобки после обработки в выражении уже нет скобок а замены расчитываются когда до них доходит дело
         * любые скобки считаются фукнцией функция без имени просто возвращает результат
         */
        $ch = $exp[$i];

        if ($ch == ')' && $start) {
            --$start;
            if (!$start) {
                $k = $fn.'('.$fnexp.')';
                $insnum = @$infra_template_replacement_ind[$k];
                if (is_null($insnum)) {
                    $insnum = sizeof($infra_template_replacement);
                    $infra_template_replacement_ind[$k] = $insnum;
                }
                $newexp .= '.xinsert'.$insnum;
                $infra_template_replacement[$insnum] = $fn;
                $r = infra_template_parseexp($fnexp, true, $fn);
                $infra_template_replacement[$insnum] = $r; //Получается переменная значение которой формула а именно функция //и мы вставляем сюда сразу да без запоминаний
                $fn = '';
                $fnexp = '';
                continue;
            }
        }
        if ($start) {
            $fnexp .= $ch;
        } else {
            if (in_array($ch, $specchars)) {
                $newexp .= $fn.$ch;
                $fn = '';
            } else {
                if ($ch !== '(') {
                    $fn .= $ch;
                }
            }
        }
        if ($ch === '(') {
            $start++;
        }
    }
    if (is_string($newexp)) {
        $exp = $newexp;
    }
    if (is_string($newexp) && is_string($fn)) {
        $exp .= $fn;
    }

    return $exp;
}
function infra_template_parseexp($exp, $term = false, $fnnow = null)
{
    // Приоритет () ? | & = ! : [] , .
    /*
     * Принимает строку варажения, возвращает сложную форму с orig обязательно
     */
    $res = array();
    global $infra_template_replacement;
    $res['orig'] = $exp;
    if ($fnnow) {
        $res['orig'] = $fnnow.'('.$res['orig'].')';
    }

    if ($fnnow) {
        $res['fn'] = infra_template_parseBracket($fnnow);
    }//в имени функции может содержать замены xinsert asdf[xinsert1].asdf. Массив как с запятыми но нужен только нулевой элемент, запятых не может быть/ Они уже отсеяны

    $exp = infra_template_parseStaple($exp);

//Сюда проходит выражение exp без скобок, с заменами их на псевдо переменные
    $l = strlen($exp);
    if ($l > 1 && $exp[$l - 1] == ':' && strpos($exp, ',') === false) {
        $res['template'] = substr($exp, 0, -1);//удалили последний символ
        return $res;
    }
    $cond = explode(',', $exp);
    if (sizeof($cond) > 1) {
        $res['var'] = array();
        infra_forr($cond, function &($c) use (&$res) {
            $res['var'][] = infra_template_parseexp($c, true);
            $r = null;

return $r;
        });

        return $res;
    }

    $cond = explode('?', $exp, 3);
    if (sizeof($cond) > 1) {
        $res['cond'] = true;
        $res['term'] = infra_template_parseexp($cond[0], true);
        if (sizeof($cond) > 2) {
            $res['yes'] = infra_template_parseexp($cond[1]);
            $res['no'] = infra_template_parseexp($cond[2]);
        } else {
            $res['yes'] = infra_template_parseexp($cond[1]);
            $res['no'] = infra_template_parseexp('$false');
        }

        return $res;
    }

    $cond = explode('&', $exp, 2);//a&b
    if (sizeof($cond) === 2) {
        $res['cond'] = true;
        $res['term'] = infra_template_parseexp($cond[0], true);
        $res['yes'] = infra_template_parseexp($cond[1]);
        $res['no'] = infra_template_parseexp('$false');

        return $res;
    }

    $cond = explode('|', $exp, 2);//a|b
    if (sizeof($cond) === 2) {
        $res['cond'] = true;
        $res['term'] = infra_template_parseexp($cond[0], true);
        $res['yes'] = infra_template_parseexp($cond[0]);
        $res['no'] = infra_template_parseexp($cond[1]);

        return $res;
    }

    $symbols = array('!','=','>','<');
    $min = false;
    $sym = false;
    for ($i = 0, $l = sizeof($symbols);$i < $l;++$i) {
        $s = $symbols[$i];
        $ind = strpos($exp, $s);
        if ($ind === false) {
            continue;
        }
        if ($min === false || $ind < $min) {
            $min = $ind;
            $sym = $s;
        }
    }

    if ($sym) {
        $cond = explode($sym, $exp, 3);
        $res['cond'] = $sym;
        $res['a'] = infra_template_parseexp($cond[0]);//a&b|c   (1&0)|1=true  1&(0|1)=true  a&b|c
        $res['b'] = infra_template_parseexp($cond[1]);

        return $res;
    }

    infra_template_parseBracket($exp, $res);

    return $res;
}
function infra_template_parseBracket($exp, &$res = null)
{
    if (is_null($res)) {
        $res = array();
        $res['orig'] = $exp;
    }

    $res['var'] = infra_template_parseCommaVar($exp);

    return $res;
}
function infra_template_parseCommaVar($var)
{
    //Разбиваем на запятые
    //в выражении var круглых скобок нет они заменены на xinsert (fn())
    //Возвращается массив, элементы либо ещё один главный объект либо массив переменной
    //
    //asdf.asdf,xinsert1,asdf[asdf.asdf][xinsert2]
    //[ ['asdf','asdf'],{'orig':'fn()'}, ['asdf',['asdf','asdf'], {'orig':'fn()'} ] ]
    //
    //a[c:b].asdf
    //['a',{var:['c'],tpl:'b'},'asdf']
    //
    //Если массив значит скобки, если объект значит сложное выражение в котором могут быть запятые
    //Первый массив - запятые
    //Второй массив - переменная
    //Далее это попадает в infra_template_getVar


if ($var == '') {
    $ar = array();
} else {
    $ar = explode(',', $var);
}//Запятые могут быть только на первом уровне, все вложенные запятые заменены на xinsert
    $res = array();

    infra_fora($ar, function &($v) use (&$res, &$var) {
        $r = infra_template_parsevar($v);

$res[] = $r;
        $r = null;

return $r;
    });
    infra_template_checkInsert($res);

    return $res;
}
function infra_template_checkInsert(&$r)
{
    infra_fora($r, function &(&$vv, $i, &$group) {//точки, скобки
        global $infra_template_replacement;
        if (is_string($vv)) {
            if (preg_match("/^xinsert(\d+)$/", $vv, $m)) {
                $group[$i] = $infra_template_replacement[$m[1]];
            }
        } elseif ($vv && $vv['orig']) {
            infra_template_checkInsert($vv['var']);
        }
        $r = null;

return $r;
    });
};
function infra_template_parsevar($var)
{
    //Ищим скобки as.df[asdf[y.t]][qwer][ert]   asdf[asdf][asdf]
    if ($var == '') {
        return;
    } //Замен xinsert уже нет //asdf.asdf[asdf] На выходе ['asdf','asdf',['asdf']]
    $res = array();

    $start = false;
    $str = '';
    $name = '';
    $open = 0;//Количество вложенных открытий
    for ($i = 0, $l = strlen($var);$i < $l;++$i) {
        $sym = $var[$i];

        if ($start && $sym === ']') {
            if (!$open) {
                $res[] = array(infra_template_parseexp($name, true));//data.name().. data[name]
                $start = false;
                $str = '';
                $name = '';
                continue;
            } else {
                --$open;
            }
        } elseif (!$start) {
            //:[] ищем двоеточее вне скобок
            if ($sym == ':') {
                $tpl = substr($var, $i + 1);
                //echo $tpl;
                $r = array();
                $r['orig'] = $var;
                $r['multi'] = ($tpl[0] === ':');
                if ($str) {
                    $res = array_merge($res, infra_seq_right($str));
                }
                $r['var'] = array($res);//В переменных к шаблону запятые не обрабатываются. res это массив с одним элементом в котором уже элементов много
                if ($r['multi']) {
                    $tpl = substr($tpl, 1);
                }
                $r['tpl'] = infra_template_make(array($tpl));
                if (!isset($r['tpl']['root'])) {
                    $r['tpl']['root'] = array('');
                }

                return array($r);
            }
        }

        if ($start) {
            $name .= $sym;
        }
        if ($sym === '[') {
            if ($start) {
                ++$open;
            } else {
                $res = array_merge($res, infra_seq_right($str));
                $start = true;
            }
        }
        if (!$start) {
            $str .= $sym;
        }
    }

    $res[] = $str;

    $r = array();
    foreach ($res as $v) {
        if (is_string($v)) {
            $rrr = false;
            if ($rrr) {
                $r[] = $rrr;
            } else {
                $t = infra_seq_right($v);

//a.b[b.c][c]
                //[a,b,[b,c],[c]]
                //b,[b,c]
                //b,[b,c]
                foreach ($t as $e) {
                    $r[] = $e;
                }
            }
        } else {
            $r[] = $v;
        }
    }

    return $r;
}
global $infra_template_scope;//Набор функций доступных везде ну и значений разных
$infra_template_scope = array(
    '~typeof' => function ($v = null) {
        if (is_null($v)) {
            return 'null';
        }
        if (is_bool($v)) {
            return 'boolean';
        }
        if (is_string($v)) {
            return 'string';
        }
        if (is_integer($v)) {
            return 'number';
        }
        if (is_array($v)) {
            return 'object';
        }
        if (is_callable($v)) {
            return 'function';
        }
    },
    '$typeof' => function ($v = null) {
        if (is_null($v)) {
            return 'null';
        }
        if (is_bool($v)) {
            return 'boolean';
        }
        if (is_string($v)) {
            return 'string';
        }
        if (is_integer($v)) {
            return 'number';
        }
        if (is_array($v)) {
            return 'object';
        }
        if (is_callable($v)) {
            return 'function';
        }
    },
    '$true' => true,
    '$false' => false,
    '~true' => true,
    '~false' => false,
    '~years' => function ($start) {
        $y = date('Y');
        if ($y == $start) {
            return $y;
        }

        return $start.'&mdash;'.$y;
    },
    '$date' => function ($a, $b = null) {
        global $infra_template_scope;

        return $infra_template_scope['~date']($a, $b);
    },
    '~date' => function ($format, $time = null) {
        //if(is_null($time))$time=time(); Нельзя выводить текущую дату когда передан null так по ошибке будет не то выводится когда даты просто нет.
        if ($time === true) {
            $time = time();
        }
        if ($time == '') {
            return '';
        }
        $st = (string) $time;
        if (strlen($st) == 6) {
            $y = $st{0}
            .$st{1};
            $m = $st{2}
            .$st{3};
            $d = $st{4}
            .$st{5};
            $time = mktime(12, 12, 12, $m, $d, $y);
        }
        if (strlen($st) == 8) {
            $y = $st{0}
            .$st{1}
            .$st{2}
            .$st{3};
            $m = $st{4}
            .$st{5};
            $d = $st{6}
            .$st{7};
            $time = mktime(12, 12, 12, $m, $d, $y);
        }
        $r = date($format, $time);
        if (strpos($format, 'F') != -1) {
            $trans = array(
                'January' => 'января',
                'February' => 'февраля',
                'March' => 'марта',
                'April' => 'апреля',
                'May' => 'мая',
                'June' => 'июня',
                'July' => 'июля',
                'August' => 'августа',
                'September' => 'сентября',
                'October' => 'октября',
                'November' => 'ноября',
                'December' => 'декабря',
            );
            $r = strtr($r, $trans);
        }

        return $r;

    },
    '$obj' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~obj'], $args);
    },
    '~obj' => function () {
        $args = func_get_args();
        $obj = array();
        for ($i = 0, $l = sizeof($args);$i < $l;$i = $i + 2) {
            if ($l == $i + 1) {
                break;
            }
            $obj[$args[$i]] = $args[$i + 1];
        }

        return $obj;
    },
    '$encode' => function ($str) {
        global $infra_template_scope;

        return $infra_template_scope['~encode']($str);
    },
    '~encode' => function ($str) {
        if (!is_string($str)) {
            return $str;
        }

        return urlencode($str);
    },
    '~decode' => function ($str) {
        if (!is_string($str)) {
            return $str;
        }

        return urldecode($str);
    },
    '$length' => function ($obj = null) {
        global $infra_template_scope;

        return $infra_template_scope['~length']($obj);
    },
    '~length' => function ($obj = null) {
        if (!$obj) {
            return 0;
        }
        if (is_array($obj)) {
            return sizeof($obj);
        } if (is_string($obj)) {
            return strlen($obj);
        }

        return 0;
    },
    '$inArray' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~inArray'], $args);
    },

'~inArray' => function ($val, $arr) {
        if (!$arr) {
            return false;
        }
        if (is_array($arr)) {
            return in_array($val, $arr);
        }
    },
    '~match' => function ($exp, $val) {
        preg_match('/'.$exp.'/', $val, $match);

        return $match;
    },
    '~test' => function ($exp, $val) {
        $r = preg_match('/'.$exp.'/', $val);

        return !!$r;
    },
    '~lower' => function ($str) {
        return mb_strtolower($str);
    },
    '~upper' => function ($str) {
        return mb_strtoupper($str);
    },
    '~parse' => function ($str = '') {
        global $infra_template_moment;
        $conf = $infra_template_moment;
        if (!$str) {
            return '';
        }
        $res = infra_template_parse(array($str), $conf['data'], 'root', $conf['dataroot'], 'root');//(url,data,tplroot,dataroot,tplempty){
        return $res;
    },
    '$indexOf' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~indexOf'], $args);
    },
    '~indexOf' => function ($str, $v = null) {//Начиная с нуля
        if (is_null($v)) {
            return -1;
        }
        $r = mb_stripos($str, $v);
        if ($r === false) {
            $r = -1;
        }

        return $r;
    },
    '$last' => function () {
        global $infra_template_scope;

        return $infra_template_scope['~last']();
    },
    '~last' => function () {
        global $infra_template_moment;
        $conf = $infra_template_moment;
        $dataroot = $conf['dataroot'];

        $key = array_pop($dataroot);
        $obj = &infra_seq_get($conf['data'], $dataroot);
        if (!$obj) {
            return true;
        }
        foreach ($obj as $k => $v);
        $r = ($k == $key);

        return $r;
    },
    '$words' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~words'], $args);
    },
    '~words' => function ($count, $one = '', $two = null, $five = null) {
        if (is_null($two)) {
            $two = $one;
        }
        if (is_null($five)) {
            $five = $two;
        }
        if (!$count) {
            $count = 0;
        }
        if ($count > 20) {
            $str = (string) $count;
            $count = $str{strlen($str) - 1};
            $count2 = $str{strlen($str) - 2};
            if ($count2 == 1) {
                return $five;
            }//xxx10-xxx19 (иначе 111-114 некорректно)
        }
        if ($count == 1) {
            return $one;
        } elseif ($count > 1 && $count < 5) {
            return $two;
        } else {
            return $five;
        }
    },
    '$even' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~even'], $args);
    },
    '~even' => function () {
        global $infra_template_moment;
        $conf = $infra_template_moment;
        $dataroot = $conf['dataroot'];
        $key = array_pop($dataroot);
        $obj = &infra_seq_get($conf['data'], $dataroot);
        $even = 1;
        foreach ($obj as $k => $v) {
            if ($key == $k) {
                break;
            }
            $even = $even * -1;
        }

        return ($even == 1);
    },
    '~array' => function () {
        $args = func_get_args();
        $ar = array();
        for ($i = 0, $l = sizeof($args);$i < $l;++$i) {
            $ar[] = $args[$i];
        }

        return $ar;
    },
    '~multi' => function () {
        $args = func_get_args();
        $n = 1;
        for ($i = 0, $l = sizeof($args);$i < $l;++$i) {
            $n *= $args[$i];
        }

        return $n;
    },
    '$leftOver' => function ($a, $b) {
        global $infra_template_scope;

        return $infra_template_scope['~leftOver']($a, $b);
    },
    '~leftOver' => function ($first, $second) {//Кратное
        $first = (int) $first;
        $second = (int) $second;

        return $first % $second;
    },
    '$sum' => function ($a = 0, $b = 0, $c = 0, $d = 0) {
        global $infra_template_scope;

        return $infra_template_scope['~sum']($a, $b, $c, $d);
    },
    '~sum' => function () {
        $args = func_get_args();
        $n = 0;
        for ($i = 0, $l = sizeof($args);$i < $l;++$i) {
            $n += $args[$i];
        }

        return $n;
    },
    '$odd' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~odd'], $args);
    },
    '~odd' => function () {
        global $infra_template_scope;

        return !$infra_template_scope['~even']();
    },
    '$first' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~first'], $args);
    },
    '~first' => function () {//Возвращает true или false первый или не первый это элемент
        global $infra_template_moment;
        $conf = $infra_template_moment;

        $dataroot = $conf['dataroot'];
        $key = array_pop($dataroot);
        $obj = &infra_seq_get($conf['data'], $dataroot);

        foreach ($obj as $k => $v) {
            break;
        }

        return ($k == $key);
    },
    '$Number' => function () {
        $args = func_get_args();
        global $infra_template_scope;

        return call_user_func_array($infra_template_scope['~Number'], $args);
    },
    '~Number' => function ($key, $def = 0) {//Делает из переменной цифру, если это не цифра то будет def
        $n = (int) $key;
        if (!$n && $n != 0) {
            $n = $def;
        }

        return $n;
    },
    '~cost' => function ($cost, $text = false) {

$cost = (string) $cost;
        $ar = explode('.', $cost);
        if (sizeof($ar) == 1) {
            $ar = explode(',', $cost);
        }

        $cop = '';
        if (sizeof($ar) >= 2) {
            $cost = $ar[0];
            $cop = $ar[1];
            if (strlen($cop) == 1) {
                $cop .= '0';
            }
            if (strlen($cop) > 2) {
                $cop = substr($cop, 0, 3);
                $cop = round($cop / 10);
            }
            if ($cop == '00') {
                $cop = '';
            }
        }

        if ($text) {
            $inp = ' ';
        } else {
            $inp = '&nbsp;';
        }

        if (strlen($cost) > 4) {
            //1000
            $l = strlen($cost);
            $cost = substr($cost, 0, $l - 3).$inp.substr($cost, $l - 3, $l);
        }

        if ($cop) {
            if ($text) {
                $cost = $cost.','.$cop;
            } else {
                $cost = $cost.'<small>,'.$cop.'</small>';
            }
        }

        return $cost;
    },
);
$fn = function ($path) {
    return infra_theme($path, 'fu');
};
infra_seq_set($infra_template_scope, array('infra', 'theme'), $fn);

$conf = &infra_config('secure');
infra_seq_set($infra_template_scope, array('infra', 'conf'), $conf);

$fn = function () { return infra_view_getPath(); };
infra_seq_set($infra_template_scope, array('infra', 'view', 'getPath'), $fn);

$fn = function () { return infra_view_getHost(); };
infra_seq_set($infra_template_scope, array('infra', 'view', 'getHost'), $fn);

$fn = function ($s) { return infra_seq_short($s); };
infra_seq_set($infra_template_scope, array('infra', 'seq', 'short'), $fn);

$fn = function ($s) { return infra_seq_right($s); };
infra_seq_set($infra_template_scope, array('infra', 'seq', 'right'), $fn);

//$fn=function(){ return infra_admin(); };
//infra_seq_set($infra_template_scope,array('infra','admin'),$fn);

$fn = function () { return infra_view_getRoot(); };
infra_seq_set($infra_template_scope, array('infra', 'view', 'getRoot'), $fn);
$fn = function ($src) { return infra_srcinfo($src); };
infra_seq_set($infra_template_scope, array('infra', 'srcinfo'), $fn);

$host = $_SERVER['HTTP_HOST'];
$p = explode('?', $_SERVER['REQUEST_URI']);
$pathname = $p[0];
infra_seq_set($infra_template_scope, array('location', 'host'), $host);
infra_seq_set($infra_template_scope, array('location', 'pathname'), $pathname);
/**/
