<?php
/*
Copyright 2008 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
*ready for include
*using modified
History
13.05.2010 modified
*/

require_once(__DIR__.'/../../pages/xls/excel_parser/oleread.php');
require_once(__DIR__.'/../../pages/xls/excel_parser/reader.php');
require_once(__DIR__.'/../../pages/xls/xlstojs.inc.php');
require_once(__DIR__.'/../../infra/infra.php');
//readxls(ROOT.'infra/lib/excel_parser/catalog.xls')
//$url='infra/lib/excel_parser/catalog.xls';
if(!isset($_GET['src'])){
	?>
	Парсер Excel 
	<?php
}else{

	$src=infra_theme($_GET['src']);
	$data=pages_cache(array($src),'readxls',array(
		$src,
		$_GET['name'],
		$_GET['onelist'],
		$_GET['onlynew'],
		$_GET['showlists'],
		$_GET['list'],
		$_GET['descr'],
		$_GET['id'],
		$_GET['nokey'],
		$_GET['param'],
		$_GET['obj'],
		$_GET['reverse'],
		$_GET['isname']
	),(bool)$_GET['reparse']);
	/*echo '<pre>';
	print_r(infra_tophp($data123));
	exit;*/
	return infra_echo($data);
}

