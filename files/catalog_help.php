<?

//В папке data должен лежать файл infra/data/.config.json пример этого файла можно взять в ?*infra/config.json


require_once(__DIR__.'../infra/infra.php');

infra_admin(true);

infra_load('*files/xls.php','r');//Подключили api для работы с Excel документами

define('CAT_PATH','*files/tests/test.xls');

//Обработки каталога нужно кэшировать используя эти функции
infra_admin_cache('unickname',function(){//Функция выполняется для разных args только один раз с последней авториации администратора на сайте
	return 'asdf';
},array(),isset($_GET['re']));
infra_cache(array('file/path'),'unickname',function(){//Функция выполняется для разных args только один раз после: авториации администратора на сайте и изменении указанных файлов. Если небыло авторизации или небыло изменений файлов вернётся кэш.
},array(),isset($_GET['re']));



echo '<h1>ПРИМЕР 0, Получить все артикулы и закэшировать результат</h1>';
$list=infra_cache(CAT_PATH,'all_arts',function(){//Кэширующая обёртка, all_arts - идентифицирующее имя для обработки function, кэш будет хранится по этому имени.
	echo 'Результат взят без кэша<br>';
	$data=xls_init(CAT_PATH);//Делаем стандартный разбор каталога. В переменной data весь каталог

	$list=array();//Массив в котором сохраним необходимые нам данные
	xls_runPoss($data,function(&$pos) use(&$list){
		$list[]=$pos['Артикул'];
	});

	return $list;
},array(),isset($_GET['re']));//array() - аргументы для обработки, в данном случае аргументы не требуются. Последний параметр ключ reparse означающий выполнение в любом случае если true
echo implode(', ',$list);

echo '<h1>ПРИМЕР 1, Найти все позиции у которых есть значение в колонке key</h1>';
$data=xls_init(CAT_PATH);
$list=array();
xls_runPoss($data,function(&$pos) use(&$list){
	//Ищим позиции у которых есть колонка 'key'
	if($pos['more']['key']){//В more попадают все колонки кроме Наименование, Артикул, Описание, Производитель
		$list[]=&$pos;
	}
	//return true;//выход из цикла
});
infra_forr($list,function&(&$val){
	unset($val['group']);//У каждой позиции есть рекурсивное свойство ссылка на группу. Для того чтобы закэшировать результат, или вывести в json формате в ответе. Нужно удалять рекурсивные такие свойства чтобы небыло зацикливания.
	$r=null;return $r;
});
echo '<pre>';
print_r($list);


echo '<h1>ПРИМЕР 2, Найти группу с именем group_two и её позиции, кэш</h2>';

$group = infra_cache(CAT_PATH,'getGroup',function(){//Функция выполняется для разных args только один раз после: авториации администратора на сайте и изменении указанных файлов. Если небыло авторизации или небыло изменений файлов вернётся кэш.
	$list=array();
	$data=xls_init(CAT_PATH);
	$group=xls_runGroups($data,function(&$group) use(&$list){
		if($group['title']=='group_two')return $group;
	});

	if($group){
		//Содержится рекурсивностьр
		unset($group['parent']);//Ссылка на родительскую группу
		unset($group['childs']);//Список вложенных групп.
		//unset($group['data']);//Список позиции.
		infra_forr($group['data'],function(&$val){
			unset($val['group']);//У каждой позиции есть рекурсивное свойство ссылка на группу. Для того чтобы закэшировать результат, или вывести в json формате в ответе. Нужно удалять рекурсивные такие свойства чтобы небыло зацикливания.
		});
	}
	return $group;
},array(),isset($_GET['re']));

echo '<pre>';
print_r($group);




