<?php


$data=array();


$seo=infra_loadJSON('*seo/seo.php?type=item&id=Главная страница');
infra_require('*files/files.inc.php');


if (!empty($seo['item']['title'])) {
	$data['title']=$seo['item']['title'];
} else {
	$data['title']='Новостная лента '.$_SERVER['HTTP_HOST'];
}

if (!empty($seo['item']['description'])) {
	$data['description']=$seo['item']['description'];
} else {
	$data['description']='Новостная лента сайта '.$_SERVER['HTTP_HOST'];
}
$data['time']=infra_admin_time();

$data['link']='http://'.infra_view_getHost().'/'.infra_view_getRoot();




$conf=infra_config();



$exts=array('docx','tpl','mht','html');

$files=$conf['files'];

$folders=array(
	array('dir'=>$files['folder_blog'],'link'=>'?Блог/'),
	array('dir'=>$files['folder_events'],'link'=>'?События/'),
	array('dir'=>$files['folder_pages'],'link'=>'?')
);

$items=array();
infra_forr($folders, function ($fold) use ($exts, &$items) {
	if (!$fold['dir']) {
		return;
	}
	$ar=files_list($fold['dir'], 0, 100, $exts);
	if (!$ar) {
		return;
	}
	$ar=array_values($ar);
	infra_forr($ar, function (&$itm) use ($fold) {
		$itm = array(
			"title"=>strip_tags($itm['title']),
			"link"=>$itm['link'],
			"description"=>strip_tags($itm['preview']),
			"pubDate"=>$itm['date'],
			"link"=>$fold['link'].$itm['name']
		);
	});
	$items=array_merge($items, $ar);
});
usort($items, function ($i, $j) {
	if ($i['pubDate']<$j['pubDate']) {
		return 1;
	}
});

$data['items']=$items;

return infra_ans($data);
