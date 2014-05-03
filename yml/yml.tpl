<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="{~date(:Y-m-d H:i,~true)}">
<shop>

	<name>{conf.yml.name}</name>
	<company>{conf.yml.company}</company>
	<url>http://{site}</url>
	<platform>Infrajs</platform>
	<agency>{conf.yml.agency}</agency>
	<email>{infra.conf.admin.support}</email>
	<currencies>
		<currency id="RUR" rate="1"/>
	</currencies>
	<categories>
		{groups::category}	
		
	</categories>
	<offers>
		{poss::pos}	
		
	</offers>
 </shop>
 </yml_catalog>
 {category:}
 	<category id="{id}" parentId="{parentId}">{title}</category>
 {pos:}
 	<offer id="{id}" available="true" bid="21">
		<url>http://best.seller.ru/product_page.asp?pid=12344</url>
		<price>{Цена}</price>
		<currencyId>RUB</currencyId>
		<categoryId>{categoryId}</categoryId >
		<picture>http://best.seller.ru/img/device12345.jpg</picture>
		<picture>http://best.seller.ru/img/device12345.jpg</picture>
		<store>true</store>
		<pickup>true</pickup>
		<delivery>true</delivery>
		<vendor>{Производитель}</vendor>
		<model>{Артикул}</model>
		<description>
		Серия принтеров для людей, которым нужен надежный, простой в использовании цветной принтер для повседневной печати. 
		Формат А4. Технология печати: 4-цветная термальная струйная. Разрешение при печати: 4800х1200 т/д.
		</description>
		<param name="Максимальный формат">А4</param>
		<param name="Технология печати">термическая струйная</param>
		<param name="Тип печати">Цветная</param>
		<param name="Количество страниц в месяц">1000</param>
		<param name="Потребляемая мощность">20</param>
	</offer>