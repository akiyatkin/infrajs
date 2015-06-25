<?php
namespace itlife\infrajs\infra\ext;
require_once(__DIR__.'/../infra.php');
require_once(__DIR__.'/seq.php');

class crumb {
	public $name;
	public $parent;
	static $child;
	static $value;//Строка или null
	static $query;
	static $childs=array();
	static $counter=0;
	static $path;//Путь текущей крошки
	static $params;//Всё что после первого амперсанда
	static $get;
	public $is;
	protected function __construct($right){}
	public function getInstance($name=''){
		if(!empty($this))$right=$this->path;
		else $right=array();
		$right=crumb::right(array_merge($right,crumb::right($name)));
		if(@$right[0]==='')$right=array();
		
		$short=crumb::short($right);
		if(empty(crumb::$childs[$short])){
			$that=new crumb($right);
			
			$that->path=$right;
			$that->name=@$right[sizeof($right)-1];
			$that->value=$that->query=$that->is=$that->counter=null;
			crumb::$childs[$short]=$that;
			

			if($that->name)$that->parent=$that->getInstance('//');
			
		}
		return crumb::$childs[$short];
	}
	static function right($short){
		return infra_seq_right($short,'/');
	}
	static function short($right){
		return infra_seq_short($right,'/');
	}
	static function change($query){
		$amp=explode('&',$query,2);

		$eq=explode('=',$amp[0],2);
		$sl=explode('/',$eq[0],2);
		if(sizeof($eq)!==1&&sizeof($sl)===1){
			//В первой крошке нельзя использовать символ "="
			$params=$query;
			$query='';
		}else{
			$params=(string)@$amp[1];
			$query=$amp[0];
		}
		crumb::$params=$params;
		parse_str($params,crumb::$get);

		$right=crumb::right($query);
		$counter=++crumb::$counter;
		$old=crumb::$path;
		crumb::$path=$right;
		crumb::$value=(string)@$right[0];
		crumb::$query=crumb::short($right);
		crumb::$child=crumb::getInstance((string)@$right[0]);
		$that=crumb::getInstance(crumb::$path);
		$child=null;

		while($that){
			$that->counter=$counter;
			$that->is=true;
			$that->child=$child;
			$that->value=(string)@$right[sizeof($that->path)];

			$that->query=crumb::short(array_slice($right,sizeof($that->path)));
			$child=$that;
			$that=$that->parent;
		};
		$that=crumb::getInstance($old);
		if(!$that)return;
		while($that){

			if($that->counter==$counter)break;
			$that->is=$that->child=$that->value=$that->query=null;
			$that=$that->parent;
		};
	}
	static function init(){
		//crumb::$child=crumb::getInstance();
		$query=urldecode($_SERVER['QUERY_STRING']);
		crumb::change($query);
	}
	public function toString(){
		return $this->short($this->path);
	}
}