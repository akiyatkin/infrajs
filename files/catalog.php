<?
@define('ROOT','../../../');
require_once(ROOT.'infra/plugins/infra/infra.php');
infra_load('*files/xls.php','r');//Подключили api для работы с Excel документами
define('CAT_PATH','*Каталог/Каталог.xls');

echo '123';

$list=array();
$data=xls_init(CAT_PATH);
echo '<pre>';
print_r($data);
?>
