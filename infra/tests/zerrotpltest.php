<?php

	$ans = array(
		'title'=>'Тест 0 элемента в массиве'
	);

    $tpl="{root:}{0:test}{test:}{title}";
    $data = array(
        array(
            "title" => "good"
        )
    );
	$html = infra_template_parse(array($tpl) ,$data,'root');
	echo $html;
    
    if($html=='good'){
	    return infra_ret($ans,'Теcт пройдены. Получился ожидаемый результат поле распарсивания шаблона.');
    }
	return infra_err($ans,'0 элемент принят за false как будто его нет');
?>