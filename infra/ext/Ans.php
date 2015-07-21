<?php
namespace itlife\infrajs\infra\ext;

class Ans
{
	public static function err($ans, $msg = null)
	{
		$ans['result']=0;
		if ($msg) {
			$ans['msg']=$msg;
		}
		return self::ans($ans);
	}
	public static function log($ans, $msg = '', $data = null)
	{
		$ans['result']=0;
		if ($msg) {
			$ans['msg']=$msg;
		}
		$conf=infra_config();
		if ($conf['debug']&&!is_null($data)) {
			$ans['msg'].='<pre><code>'.print_r($data, true).'</code></pre>';
		}
		error_log(basename(__FILE__).$msg);
		return self::ans($ans);
	}
	public static function ret($ans, $msg = false)
	{
		if ($msg) {
			$ans['msg']=$msg;
		}
		$ans['result']=1;
		return self::ans($ans);
	}
	public static function ans($ans)
	{
		if (infra_isphp()) {
			return $ans;
		} else {
			header('Content-type:application/json');//Ответ формы не должен изменяться браузером чтобы корректно конвертирвоаться в объект js, если html то ответ меняется
			echo infra_json_encode($ans);
		}
	}
}
