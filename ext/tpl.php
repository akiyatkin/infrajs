<?php

//Обработка onshow и onhide, tpl, data, onlyclient, onlyserver, tplclientparse, parsed, datacheck, tplcheck
//infrajs_parsedAdd
//infrajs_parsed


namespace itlife\infrajs\ext;

global $infra;
global $infrajs;
class tpl
{
	public static function onlyclient(&$layer)
	{
		$parent = $layer;
		while ($parent) {
			if (@$parent['onlyclient']) {
				return true;
			}
			$parent = @$parent['parent'];
		}
	}
	public static function tplroottpl(&$layer)
	{
		$prop = 'tplroot';
		$proptpl = $prop.'tpl';
		if (!isset($layer[$proptpl])) {
			return;
		}
		$p = $layer[$proptpl];
		if (is_array($layer[$proptpl])) {
			$p = infra_template_parse($p, $layer);
			$layer[$prop] = array($p);
		} else {
			$layer[$prop] = infra_template_parse(array($p), $layer);
		}
	}
	public static function dataroottpl(&$layer)
	{
		$prop = 'dataroot';
		$proptpl = $prop.'tpl';
		if (!isset($layer[$proptpl])) {
			return;
		}
		$p = $layer[$proptpl];
		$layer[$prop] = infra_template_parse(array($p), $layer);
	}
	public static function tpltpl(&$layer)
	{
		$prop = 'tpl';
		$proptpl = $prop.'tpl';
		if (@!$layer[$proptpl]) {
			return;
		}
		$p = $layer[$proptpl];
		$ar = is_array($p);
		if (!$ar) {
			$p = array($p);
		}
		$p = infra_template_parse($p, $layer);
		if ($ar) {
			$layer[$prop] = array($p);
		} else {
			$layer[$prop] = $p;
		}
	}
	public static function jsontpl(&$layer)
	{
		$prop = 'json';
		$proptpl = $prop.'tpl';
		if (@!$layer[$proptpl]) {
			return;
		}
		$p = $layer[$proptpl];
		$ar = is_array($p);
		if (!$ar) {
			$p = array($p);
		}
		$p = infra_template_parse($p, $layer);
		if ($ar) {
			$layer[$prop] = array($p);
		} else {
			$layer[$prop] = $p;
		}
	}
	public static function &getData(&$layer)
	{
		//Используется в propcheck.js
		if (!isset($layer['json'])) {
			return $layer['data'];
		}
		$data = @$layer['json'];
		if (infra_isAssoc($data) === false) {
			//Если массив то это просто строка в виде данных
			$data = infra_loadTEXT($data[0]);
		} elseif (is_string($data)) {
			$data = &infra_loadJSON($data);//Забираем для текущего клиента что-то..
		}

		return $data;
	}
	public static function getTpl(&$layer)
	{
		$tpl = $layer['tpl'];
		if (is_string($tpl)) {
			$tpl = infra_loadTEXT($tpl);//M доп параметры после :
		} elseif (is_array($tpl)) {
			$tpl = $tpl[0];
		} else {
			$tpl = '';
		}
		if (!$tpl) {
			$tpl = '';
		}

		return $tpl;
	}

	public static function getHtml(&$layer)
	{
		//Вызывается как для основных так и для подслойв tpls frame. Расширяется в tpltpl.prop.js
		//if(@$layer['tplclient'])return '';
		$row = parsed::check($layer);
		//$row=$_SERVER['QUERY_STRING'],$layer['unick'];
		//Нельзя кэшировать слои в которых показываются динамические данные, данные пользователя определяется заголовком у данных
		//Кэш создаётся от любого пользователя.
		//Чтобы узнать что кэш делать не нужно... это знают данные они либо js либо php
		//При загрузки данных те должны выкидывать заголовки не кэшировать, либо не выкидывать если это просто парсер Excel
		//Нас интересует зависит ли html слоя от пользователя, если зависит кэшировать нельзя
		//Зависит если используется $_SESSION, infra_session, infra_admin
		//примечательно что конект к базе не запрещает кэширование этого слоя
		//Узнавать о всём этом мы будем по заголовкам
		//Так чтобы следующий слой взялся уже нормально заголовки нужно заменять... 
		//Тем более заменять заголовки нужно в любом случае если кэшируется чтобы и браузер кэшировал

		//Проблема при первом session_get конект к базе и вызов session_init в следующем подключении init не вызывается 
		//но для следующего подключения нам нужно понять что есть динамика// По этому загловки отправляются в том числе и руками в скритпах  Cache-Control:no-cache
		$dhtml = infra_admin_cache('infrajs_getHtml', function () use (&$layer) {
			global $infrajs;
			$infrajs['layer'] = &$layer;//в скриптах будет доступ к последнему вставленному слою
			//Здесь мог быть установлен infrajs['com'] его тоже нужно вернуть/ А вот после loadTEXT мог быть кэш и ничего не установится
			$html = tpl::_getHtml($layer);

			return array($html, $infrajs['com']);
		}, array($row));//Кэш обновляемый с последней авторизацией админа определяется строкой parsed слоя
		$html = $dhtml[0];

		$infrajs['com'] = $dhtml[1];//Применять надо здесь вне кэша getHTML

		return $html;
	}
	public static function _getHtml(&$layer)
	{
		//Вызывается как для основных так и для подслойв tpls frame. Расширяется в tpltpl.prop.js

		if (@$layer['data'] || @$layer['json'] || @$layer['tpls'] || @$layer['tplroot']) {
			$tpls = infra_template_make($layer['tpl']);//С кэшем перепарсивания
			global $infra,$infrajs;
			$infrajs['com'] = @$infra['com'];
			$repls = array();//- подшаблоны для замены, Важно, что оригинальный распаршеный шаблон не изменяется
			infra_fora($layer['tplsm'], function ($tm) use (&$repls) {//mix tpl
				$t = infra_template_make($tm);//С кэшем перепарсивания
				array_push($repls, $t);
				//for(var i in t)repls[i]=t[i];//Нельзя подменять в оригинальном шаблоне, который в других местах может использоваться без подмен
				//^ из-за этого обработчики указанные в tplsm срабатывают постоянно, так как нельзя поставить отметку о том что обработчик сохранён
			});

			$layer['data'] = &self::getData($layer);//подменили строку data на объект data


			$alltpls = array(&$repls,&$tpls);

			$html = infra_template_exec($alltpls, $layer, @$layer['tplroot'], @$layer['dataroot']);
		} else {
			$tpl = self::getTpl($layer);

			global $infra,$infrajs;
			$infrajs['com'] = @$infra['com'];
			$html = $tpl;
		}
		if (!$html) {
			$html = '';
		}

		return $html;
	}
	public static function jsoncheck(&$layer)
	{
		if (@$layer['data'] && !is_null(@$layer['jsoncheck'])) {
			$data = &infrajs_getData($layer);
			if (@$layer['jsoncheck']) {
				//Если true значит да только если данные есть
				if (!$data || (!is_null($data['result']) && !$data['result'])) {
					return false;
				}
			} elseif (@!$layer['jsoncheck']) {
				//Если false Значит да только если данных нет
				if (!$data || !$data['result']) {
					return;
				} else {
					return false;
				}
			}
		}
	}
}
