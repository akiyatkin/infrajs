<?php
@define('ROOT','../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
$data=infra_loadJSON('*rss/data.php');

header('Content-Type:text/xml; charset=utf-8');
$html=infra_template_parse('*rss/rss.tpl',$data);
echo $html;
?>