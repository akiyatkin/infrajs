<?php

namespace itlife\infrajs;

require_once __DIR__.'/../infra/Infra.php';

/*//Функции для написания плагинов
infrajs::store();
infrajs::storeLayer(layer)
infrajs::getWorkLayers();
infrajs::getAllLayers();

infrajs::run(layers,callback);
infrajs::runAddList('layers')
infrajs::runAddKeys('divs');

infrajs::isSaveBranch(layer,val);
infrajs::isParent(layer,parent);
infrajs::isWork(layer);

infrajs::is('rest|show|check',layer);
infrajs::isAdd('rest|show|check',callback(layer));

infrajs::check(layer);
infrajs::checkAdd(layer);


*/

global $infrajs;
/*if (!$infrajs) {
	$infrajs=array();
}*/
class Infrajs
{
	public static function &storeLayer(&$layer)
	{
		if (@!$layer['store']) {
			$layer['store'] = array('counter' => 0);
		}//Кэш используется во всех is функциях... iswork кэш, ischeck кэш используется для определения iswork слоя.. путём сравнения ))
		return $layer['store']; //Очищается кэш в checkNow
	}
	public static function &store()
	{
		//Для единобразного доступа в php, набор глобальных переменных
		global $infrajs_store;
		if (!$infrajs_store) {
			$infrajs_store = array(
				'timer' => false,
				'run' => array('keys' => array(),'list' => array()),
				'waits' => array(),
				'process' => false,
				'counter' => 0,//Счётчик сколько раз перепарсивался сайт, посмотреть можно в firebug
				'alayers' => array(),//Записываются только слои у которых нет родителя...
				'wlayers' => array(),//Записываются обрабатываемые сейчас слои
			);
		}

		return $infrajs_store;
	}

	public static function getAllLayers()
	{
		$store = &self::store();

		return $store['alayers'];
	}
	public static function &getWorkLayers()
	{
		$store = &self::store();

		return $store['wlayers'];
	}
	/*
		в check вызывается check// игнор
		два check подряд будет два выполнения.

		###mainrun всегда check всегда один на php, но для совместимости..., для тестов.. нужно помнить что каждый check работает с одним и тем же infra_html

		Гипотетически можем работать вне клиента.. дай один html дай другой... выдай клиенту третий
		без mainrun мы не считаем env
	*/
	public static function check(&$layers = null)
	{
		//Пробежка по слоям
		$store = &self::store();
		global $infrajs;
		//if($store['process'])return;//Уже выполняется
		//$store['process']=true;
		//процесс характеризуется двумя переменными process и timer... true..true..false.....false
		++$store['counter'];
			$store['ismainrun'] = is_null($layers);

		if (!is_null($layers)) {
			$store['wlayers'] = array(&$layers);
		} else {
			$store['wlayers'] = $store['alayers'];
		}

		infra_fire($infrajs, 'oninit');//сборка событий

		self::run(self::getWorkLayers(), function (&$layer, &$parent) use (&$store) {
			//Запускается у всех слоёв в работе которые wlayers
			if ($parent) {
				$layer['parent'] = &$parent;
			}
			infra_fire($layer, 'layer.oninit');
			if (!infrajs::is('check', $layer)) {
				return;
			}
			infra_fire($layer, 'layer.oncheck');
		});//разрыв нужен для того чтобы можно было наперёд определить показывается слой или нет. oncheck у всех. а потом по порядку.

		infra_fire($infrajs, 'oncheck');//момент когда доступны слои по getUnickLayer

		self::run(self::getWorkLayers(), function (&$layer) {
			//С чего вдруг oncheck у всех слоёв.. надо только у активных
			if (infrajs::is('show', $layer)) {
				//Событие в котором вставляется html
				infra_fire($layer, 'layer.onshow');//при клике делается отметка в конфиге слоя и слой парсится... в oncheck будут подстановки tpl и isRest вернёт false
				infra_fire($layer, 'onshow');
				//onchange показанный слой не реагирует на изменение адресной строки, нельзя привязывать динамику интерфейса к адресной строке, только черещ перепарсивание
			}
		});//у родительского слоя showed будет реальное а не старое


		infra_fire($infrajs, 'onshow');
		//loader, setA, seo добавить в html, можно зациклить check
		//$store['process']=false;
	}
	public static function checkAdd(&$layers)
	{
		//Два раза вызов добавит слой повторно
		//Чтобы сработал check без аргументов нужно передать слои в add
		//Слои переданные в check напрямую не сохраняются
		$store = &self::store();
		$store['alayers'][] = &$layers;//Только если рассматриваемый слой ещё не добавлен
	}

	public static function isAdd($name, $callback)
	{
		//def undefined быть не может
		$store = &self::store();
		if (!isset($store[$name])) {
			$store[$name] = array();
		}//Если ещё нет создали очередь
		return $store[$name][] = $callback;
	}
	public static function &is($name, &$layer = null)
	{
		$store = &self::store();
		if (!$store[$name]) {
			$store[$name] = array();
		}//Если ещё нет создали очередь
		//Обновлять с новым check нужно только результат в слое, подписки в store сохраняются, Обновлять только в случае когда слой в работе
		if (!is_array($layer) && !$layer) {
			return $store[$name];
		}//Без параметров возвращается массив подписчиков
		$cache = &self::storeLayer($layer);//кэш сбрасываемый каждый iswork


		if (!self::isWork($layer)) {
			$cache[$name] = $oldval;
			if (!is_null($cache[$name])) {
				//Результат уже есть
						return $cache[$name];//Хранить результат для каждого слоя
			} else {
				return;
						//die('Слой ни разу не был в работе и у него запрос is');
			}
		}
		//слой проверили по всей очередь

		if (@!is_null($cache[$name])) {
			//Результат уже есть
			return $cache[$name];//Хранить результат для каждого слоя
		}

		$cache[$name] = true;//взаимозависимость не мешает, Защита от рекурсии, повторный вызов вернёт true как предварительный кэш
		for ($i = 0, $l = sizeof($store[$name]); $i < $l; ++$i) {
			$r = $store[$name][$i]($layer);
			if (!is_null($r) && !$r) {
				$cache[$name] = $r;
				break;
			}
		}

		return $cache[$name];
	}
	public static function &run(&$layers, $callback, &$parent = null)
	{
		$store = &infrajs::store();
		if (!$store['run']) {
			$store['run'] = array();
		}
		$props = &$store['run'];

		$r = &infra_fora($layers, function &(&$layer) use (&$parent, $callback, $props) {

			$r = &$callback($layer, $parent);
			if (!is_null($r)) {
				return $r;
			}

			$r = &infra_foro($layer, function &(&$val, $name) use (&$layer, $callback, $props) {
				$r = null;
				if (isset($props['list'][$name])) {
					$r = &infrajs::run($val, $callback, $layer);
					if (!is_null($r)) {
						return $r;
					}
				} else if (isset($props['keys'][$name])) {
					$r = &infra_foro($val, function &(&$v, $i) use (&$layer, $callback) {
						$r = &infrajs::run($v, $callback, $layer);
						if (!is_null($r)) {
							return $r;
						}
					});
					if (!is_null($r)) {
						return $r;
					}
				}

				return $r;
			});
			if (!is_null($r)) {
				return $r;
			}
		});
		return $r;
	}
	/*public static function &run(&$layers, $callback, &$parent = null)
	{
		$store = &infrajs::store();
		if (!$store['run']) {
			$store['run'] = array();
		}
		$props = &$store['run'];

		$r = &infra_fora($layers, function &(&$layer) use (&$parent, $callback, $props) {

			$r = &$callback($layer, $parent);
			if (!is_null($r)) {
				return $r;
			}

			$r = &infra_foro($layer, function &(&$val, $name) use (&$layer, $callback, $props) {
				$r = null;
				if (isset($props['list'][$name])) {
					$r = &infrajs::run($val, $callback, $layer);
					if (!is_null($r)) {
						return $r;
					}
				}

				return $r;
			});
			if (!is_null($r)) {
				return $r;
			}

			$r = &infra_foro($layer, function &(&$val, $name) use (&$layer, $callback, $props) {
				$r = null;
				if (isset($props['keys'][$name])) {
					$r = &infra_foro($val, function &(&$v, $i) use (&$layer, $callback) {
						$r = &infrajs::run($v, $callback, $layer);
						if (!is_null($r)) {
							return $r;
						}
					});
					if (!is_null($r)) {
						return $r;
					}
				}

				return $r;
			});
			if (!is_null($r)) {
				return $r;
			}

		});

		return $r;
	}*/
	public static function runAddKeys($name)
	{
		$store = &self::store();
		$store['run']['keys'][$name] = true;
	}
	public static function runAddList($name)
	{
		$store = &self::store();
		$store['run']['list'][$name] = true;
	}

	public static function isWork($layer)
	{
		//val для отладки, делает метку что слой в работе
		$store = &self::store();
		$cache = &self::storeLayer($layer);//work
		return $cache['counter'] && $cache['counter'] == $store['counter'];//Если слой в работе метки будут одинаковые
	}
	public static function isParent(&$layer, &$parent)
	{
		while ($layer) {
			if (infra_isEqual($parent, $layer)) {
				return true;
			}
			$layer = &$layer['parent'];
		}

		return false;
	}
	public static function isSaveBranch(&$layer, $val = null)
	{
		$cache = &self::storeLayer($layer);
		if (!is_null($val)) {
			$cache['is_save_branch'] = $val;
		}

		return @$cache['is_save_branch'];
	}

	public static function init($index, $div, $src)
	{
		\itlife\infra\Infra::init();
		infra_require('*infrajs/make.php');
		infra_admin_modified();//Здесь уже выход если у браузера сохранена версия
		@header('Infrajs-Cache: true');//Афигенный кэш, когда используется infrajs не подгружается даже
		$query=infra_toutf($_SERVER['QUERY_STRING']);
		$html = infra_admin_cache('index.php', function ($index, $div, $src, $query) {
			@header('Infrajs-Cache: false');//Афигенный кэш, когда используется infrajs не подгружается даже

			global $infrajs;
			//if (is_string($index)) {
			$h = infra_loadTEXT($index);
			//} else {
			//		$h = $index[0];
			//}
			infra_html($h);//Добавить снизу
			$conf = infra_config();
			if ($conf['infrajs']['server']) {
				$layers = &infra_loadJSON($src);

				infra_fora($layers, function &(&$layer) use ($div) {
					$layer['div'] = $div;
					$r = null;

					return $r;
				});

				//$crumb=infra\ext\Crumb::getInstance();

				infrajs::checkAdd($layers);

				infrajs::check();//В infra_html были добавленыs все указаные в layers слои
			}
			$html = infra_html();

			if ($conf['infrajs']['client']) {
				$script = '<script src="?*infra/js.php"></script>';

				$html = str_replace('<head>', '<head>'."\n\t".$script, $html);

				$script = '';
				$script .= <<<END
\n<script src="?*infrajs/initjs.php?loadJSON={$src}"></script>
<script type="text/javascript">
	var layers=infra.loadJSON('{$src}');
	infra.fora(layers,function(layer){
		layer.div='{$div}';
	});
	infrajs.checkAdd(layers);
	infra.listen(infra.Crumb,'onchange',function(){
		infrajs.check();
	});
</script>
END;
				$html = str_replace('</body>', "\n\t".$script.'</body>', $html);

				//$html .= $script;
			}

			return $html;
		}, array($index, $div, $src, $query));//Если не кэшировать то будет reparse

		//@header('HTTP/1.1 200 Ok'); Приводит к появлению странных 4х символов в начале страницы guard-service
		echo $html;
	}
	public static function controller($layer)
	{
		\itlife\infra\Infra::init();

		infra_require('*infrajs/make.php');
		infra_admin_modified();//Здесь уже выход если у браузера сохранена версия
		@header('Infrajs-Cache: true');//Афигенный кэш, когда используется infrajs не подгружается даже
		$query=infra_toutf($_SERVER['QUERY_STRING']);
		$args=array($layer, $query);
		$html = infra_admin_cache('index.php', function ($layer, $query) {
			@header('Infrajs-Cache: false');//Афигенный кэш, когда используется infrajs не подгружается даже
			$strlayer=json_encode($layer);
			global $infrajs;
			
			$conf = infra_config();
			
			if ($conf['infrajs']['server']) {

				infrajs::checkAdd($layer);

				infrajs::check();//В infra_html были добавленыs все указаные в layers слои
			}
			$html = infra_html();

			if ($conf['infrajs']['client']) {
				$script = '<script src="?*infra/js.php"></script>';

				$html = str_replace('<head>', '<head>'."\n\t".$script, $html);
				$script = '';
				$script .= <<<END
\n<script src="?*infrajs/initjs.php"></script>
<script type="text/javascript">
	infrajs.checkAdd({$strlayer});
	infra.listen(infra.Crumb, 'onchange', function(){
		infrajs.check();
	});
</script>
END;
				$html = str_replace('</body>', "\n\t".$script.'</body>', $html);

				//$html .= $script;
			}

			return $html;
		}, $args);//Если не кэшировать то будет reparse

		//@header('HTTP/1.1 200 Ok'); Приводит к появлению странных 4х символов в начале страницы guard-service
		echo $html;
	}
}
