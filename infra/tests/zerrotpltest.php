<?php

    $ans = array(
        'title' => 'Тест 0 элемента в массиве. Известная проблема.',
    );

$tpl = '{root:}{0:test}{test:}{title}';
    $data = array(
        array(
            'title' => 'good',
        ),
    );
    $html = infra_template_parse(array($tpl), $data, 'root');
    echo $html;

$ans['class'] = 'bg-warning';
    if ($html != 'good') {
        return infra_err($ans, '0 элемент принят за false как будто его нет');
    }

return infra_ret($ans, 'Теcт пройдены. Получился ожидаемый результат поле распарсивания шаблона.');
