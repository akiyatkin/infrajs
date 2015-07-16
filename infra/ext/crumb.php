<?php

namespace itlife\infrajs\infra\ext;

require_once __DIR__.'/seq.php';

class crumb
{
    public $name;
    public $parent;
    public $child;
    public $value;//Строка или null
    public $query;
    public static $childs = array();
    public $counter = 0;
    public static $globalcounter = 0;
    public $path;//Путь текущей крошки
    public static $params;//Всё что после первого амперсанда
    public static $get;
    public $is;
    protected function __construct($right)
    {
    }
    public function getRoot()
    {
        $root = $this;
        while ($root->parent) {
            $root = $root->parent;
        }

        return $root;
    }
    public function getInst($name = '')
    {
        $right = $this->path;

        return self::getInstance($name, $right);
    }
    public static function getInstance($name = '', $right = array())
    {
        $right = self::right(array_merge($right, self::right($name)));
        if (@$right[0] === '') {
            $right = array();
        }

        $short = self::short($right);

        if (empty(self::$childs[$short])) {
            $that = new self($right);

            $that->path = $right;
            $that->name = @$right[sizeof($right) - 1];
            $that->value = $that->query = $that->is = $that->counter = null;
            self::$childs[$short] = $that;

            if ($that->name) {
                $that->parent = $that->getInst('//');
            }
        }

        return self::$childs[$short];
    }
    public static function right($short)
    {
        return infra_seq_right($short, '/');
    }
    public static function short($right)
    {
        return infra_seq_short($right, '/');
    }
    public function getGET()
    {
        return self::$get;
    }
    public static function change($query)
    {
        $amp = explode('&', $query, 2);

        $eq = explode('=', $amp[0], 2);
        $sl = explode('/', $eq[0], 2);
        if (sizeof($eq) !== 1 && sizeof($sl) === 1) {
            //В первой крошке нельзя использовать символ "=" для совместимости с левыми параметрами для главной страницы, которая всё равно покажется
            $params = $query;
            $query = '';
        } else {
            $params = (string) @$amp[1];
            $query = $amp[0];
        }
        self::$params = $params;
        parse_str($params, self::$get);

        $right = self::right($query);
        $counter = ++self::$globalcounter;

        $inst = self::getInstance();
        $old = $inst->path;
        //crumb::$path=$right;
        //crumb::$value=(string)@$right[0];
        //crumb::$query=crumb::short($right);
        //crumb::$child=crumb::getInstance((string)@$right[0]);
        $that = self::getInstance($right);
        $child = null;

        while ($that) {
            $that->counter = $counter;
            $that->is = true;
            $that->child = $child;
            $that->value = (string) @$right[sizeof($that->path)];

            $that->query = self::short(array_slice($right, sizeof($that->path)));
            $child = $that;
            $that = $that->parent;
        };
        $that = self::getInstance($old);
        if (!$that) {
            return;
        }
        while ($that) {
            if ($that->counter == $counter) {
                break;
            }
            $that->is = $that->child = $that->value = $that->query = null;
            $that = $that->parent;
        };
    }
    public static function init()
    {
        //crumb::$child=crumb::getInstance();
        $query = urldecode(infra_toutf($_SERVER['QUERY_STRING']));
        self::change($query);
    }
    public function toString()
    {
        return $this->short($this->path);
    }
}
