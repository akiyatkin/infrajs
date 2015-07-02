<?php
$ans = array(
	'title' => 'Проверка наличия важных папок'
);

$dirs = infra_dirs();

if(!file_exists($dirs['cache']))
{
	return infra_err($ans, 'Отсутствует важная папка под название cache');
}
if(!file_exists($dirs['data']))
{
	return infra_err($ans, 'Отсутствует важная папка под название data');
}
if(!file_exists($dirs['backup']))
{
	return infra_err($ans, 'Отсутствует важная папка под названием backup');
}
return infra_ret($ans, 'Все необходимые папки для функционирования сайта имеются');