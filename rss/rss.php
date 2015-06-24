<?php

require_once(__DIR__.'../infra/infra.php');
$data=infra_loadJSON('*rss/data.php');

header('Content-Type:text/xml; charset=utf-8');
$html=infra_template_parse('*rss/rss.tpl',$data);
echo $html;