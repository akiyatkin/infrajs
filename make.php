<?php

use itlife\infrajs\Infrajs;
use itlife\infrajs\ext;

//========================
global $infrajs,$infra;
/*if (!$infrajs) {
		$infrajs = array();
}*/
//========================
//infrajs oninit
//========================
//=======wait=====//

infra_wait($infrajs, 'oninit', function () {
	ext\external::init();
	ext\Crumb::init();
	ext\subs::init();
	ext\layers::init();
	ext\unick::init();
	ext\env::init();
	ext\div::init();
	ext\config::init();
	ext\parsed::init();
	ext\session::init();
	ext\Htaccess::init();
});
//========================
//layer oninit
//========================
infra_listen($infra, 'layer.oninit', function (&$layer) {
	//external
	ext\external::check($layer);
});
infra_listen($infra, 'layer.oninit', function (&$layer) {
	//config
	ext\config::configinherit($layer);
});
infra_listen($infra, 'layer.oninit', function (&$layer) {
	//infrajs
	$store = &infrajs::store();
	$layer['store'] = array('counter' => $store['counter']);
});
infra_listen($infra, 'layer.oninit', function (&$layer) {
	//unick
	ext\unick::check($layer);
});

infra_listen($infra, 'layer.oninit', function (&$layer) {
		//это из-за child// всё что после child начинает плыть. по этому надо crumb каждый раз определять, брать от родителя.
	//crumb
	if (!isset($layer['dyn'])) {
		//Делается только один раз
		ext\Crumb::set($layer, 'crumb', $layer['crumb']);
	}

});
infra_listen($infra, 'layer.oninit', function (&$layer) {
	//crumb
	if (empty($layer['parent'])) {
		return;
	}

	ext\Crumb::set($layer, 'crumb', $layer['dyn']['crumb']);//Возможно у родителей обновился crumb из-за child у детей тоже должен обновиться хотя они не в child
});

infra_listen($infra, 'layer.oninit', function (&$layer) {

	//crumb child
	if (@!$layer['child']) {
		return;//Это услвие после Crumb::set
	}

	$crumb = &$layer['crumb']->child;
	if ($crumb) {
		$name = $crumb->name;
	} else {
		$name = '###child###';
	}

	infra_fora($layer['child'], function (&$l) use (&$name) {
		ext\Crumb::set($l, 'crumb', $name);
	});
});
infra_listen($infra, 'layer.oninit', function (&$layer) {
	//Должно быть после external, чтобы все свойства у слоя появились
	//crumb childs
	infra_forx($layer['childs'], function (&$l, $key) {
		//У этого childs ещё не взять external
		if (empty($l['crumb'])) {
			ext\Crumb::set($l, 'crumb', $key);
		}
	});
});
//========================
//layer is check
//========================

infrajs::isAdd('check', function (&$layer) {
	//может быть у любого слоя в том числе и у не iswork, и когда нет старого значения

	//infrajs это исключение
	if (!$layer) {
		return false;
	}//Может быть когда вернулись с check к родителю который ещё ниразу небыл в работе
	if (!infrajs::isWork($layer)) {
		return false;
	}//Нет сохранённого результата, и слой не в работе, если работа началась с infrajs.check(layer) и у layer есть родитель, который не в работе

});
infrajs::isAdd('check', function (&$layer) {
	//crumb
	if (!$layer['crumb']->is) {
		return false;
	}

});

//========================
//layer oncheck
//========================

infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//counter
	if (@!$layer['counter']) {
		$layer['counter'] = 0;
	}
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя
	//div
	if (@!$layer['div'] && @$layer['parent']) {
		$layer['div'] = $layer['parent']['div'];
	}
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//Без этого не показывается окно cо стилями.. только его заголовок..
	//div
	infra_forx($layer['divs'], function (&$l, $div) {
		if (@!$l['div']) {
			$l['div'] = $div;
		}
	});
});

//infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//autosave на сервере нет такого объекта у слоёв autosave и это не приводит к запрету кэширования
	//if(infrajs_tplonlyclient($layer))return;
	//infrajs_autosaveRestore($layer);
//});


/*infra_listen($infra, 'layer.oncheck', function (&$layer) {//Заменяем пустые слои иначе они считаются пустыми массивами в которых слоёв нет
	//subs
	if(@!$layer['subs'])return;
	infra_foro($layer['subs'], function (&$val){
		if(!$val||!is_array($val))$val=array('_'=>'notempty');
	});
});*/
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//external уже проверен
	//subs
	ext\subs::check($layer);
});

infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//external уже проверен
	//config
	ext\config::configtpl($layer);
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя
	//div
	if (@!$layer['div'] && @$layer['parent']) {
		$layer['div'] = $layer['parent']['div'];
	}
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//Без этого не показывается окно cо стилями.. только его заголовок..
	//div
	infra_forx($layer['divs'], function (&$l, $div) {
		if (@!$l['div']) {
			$l['div'] = $div;
		}
	});
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//div
	ext\div::divtpl($layer);

});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//tpl
	ext\tpl::tplroottpl($layer);
	ext\tpl::dataroottpl($layer);
	ext\tpl::tpltpl($layer);
	ext\tpl::jsontpl($layer);
});

infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//external то ещё не применился у вложенных слоёв, по этому используется свойство envtochild
	//env envs
	ext\env::checkinit($layer);
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//external то ещё не применился нельзя
	//env envtochild
	ext\env::envtochild($layer);

});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//env envframe
	ext\env::envframe($layer);
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//env envframe
	ext\env::envframe2($layer);
});
infra_listen($infra, 'layer.oncheck', function (&$layer) {
	//external уже есть
	//env myenvtochild
	ext\env::envmytochild($layer);
});

//========================
// infrajs oncheck
//========================

//========================
//layer is show
//========================
infrajs::isAdd('show', function (&$layer) {
	//infrajs
	if (!infrajs::is('check', $layer)) {
		return false;
	}
});
infrajs::isAdd('show', function (&$layer) {
	//is
	ext\is::istpl($layer);

	return ext\is::check($layer);
});
infrajs::isAdd('show', function (&$layer) {
	//tpl
	if (@$layer['tpl']) {
		return;
	}
	//infrajs::isSaveBranch($layer,true);//Когда нет шаблона слой скрывается, но не скрывает свою ветку

	$r = true;
	if (!empty($layer['parent'])) {
		//Пустой слой не должен обрывать наследования если какой=то родитель скрывает всю ветку
		$r = infrajs::isSaveBranch($layer['parent']);
		if (is_null($r)) {
			$r = true;
		}
	}
	infrajs::isSaveBranch($layer, $r);

	return false;
});
infrajs::isAdd('show', function (&$layer) {
	//Родитель скрывает ребёнка если у родителя нет опции что ветка остаётся целой
	//infrajs
	if (@!$layer['parent']) {
		return;
	}
	if (infrajs::is('show', $layer['parent'])) {
		return;
	}
	if (infrajs::isSaveBranch($layer['parent'])) {
		return;
	}//Какой-то родитель таки не показывается, например пустой слой, теперь нужно узнать скрывает родитель свою ветку или нет
	//echo $layer['tplroot'].':'.$layer['parent']['tplroot'].'<br>';

	return false;
});
infrajs::isAdd('show', function (&$layer) {
	//div
	return ext\div::divcheck($layer);
});
infrajs::isAdd('show', function (&$layer) {
	//div
	if (@!$layer['div']) {
		return false;
	}//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
});

infrajs::isAdd('show', function (&$layer) {
	//tpl depricated
	if (is_string(@$layer['tpl']) && @$layer['tplcheck']) {
		//Мы не можем делать проверку пока другой плагин не подменит tpl
		$res = infra_loadTEXT($layer['tpl']);
		if (!$res) {
			return false;
		}
	}
});
infrajs::isAdd('show', function (&$layer) {
	//tpl depricated
	if (ext\tpl::onlyclient($layer)) {
		return;
	}

	return ext\tpl::jsoncheck($layer);
});

infrajs::isAdd('show', function (&$layer) {
	//counter должно быть до getHtml
	++$layer['counter'];
});
infrajs::isAdd('show', function (&$layer) {
	//env
	if (@!$layer['env']) {
		if (ext\tpl::onlyclient($layer)) {
			return;
		}
		ext\tpl::getHtml($layer);
		global $infrajs;
		if (isset($infrajs['com']['env'])) {
			$vals = $infrajs['com']['env'];
			if (!isset($layer['myenv'])) {
				$layer['myenv'] = array();
			}
			infra_forr($vals, function ($val) use (&$layer) {
				$layer['myenv'][$val] = true;
			});
		}

		return;
	}

	return ext\env::check($layer);
});
//infrajs::isAdd('show', function (&$layer) {
	//tpl
	//if(@$layer['onlyclient'])return false;
//});
//========================
//layeext/
//====::init====================

infra_listen($infra, 'layer.onshow', function (&$layer) {
	//tpl
	if (ext\tpl::onlyclient($layer)) {
		return;
	}
	$layer['html'] = ext\tpl::getHtml($layer);
});
infra_listen($infra, 'layer.onshow', function (&$layer) {
	//css
	if (ext\tpl::onlyclient($layer)) {
		return;
	}
	ext\css::check($layer);
});
infra_listen($infra, 'layer.onshow', function (&$layer) {
	//tpl
	if (ext\tpl::onlyclient($layer)) {
		return;
	}
	global $infrajs;

	$r = infra_html($layer['html'], $layer['div']);
	if (!$r && (!isset($layer['divcheck']) || !$layer['divcheck'])) {
		echo 'Не найден div '.$layer['div'].' infra_html<br>';
	}
	unset($layer['html']);//нефиг в памяти весеть
});

infra_listen($infra, 'layer.onshow', function (&$layer) {
	//seojson
	if (ext\tpl::onlyclient($layer)) {
		return;
	}
	ext\seojson::check($layer);
});
//========================
//infrajs onshow
//========================

//Add externals
$conf=infra_config();
foreach ($conf['infrajs_phpexts'] as $path) {
	infra_require($path);
}
