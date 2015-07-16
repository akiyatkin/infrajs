<?php

    $ans = array(
        'title' => 'Проверяем простые функции шаблонизатора',
    );

    $data = array(
        'Artur' => 'Yanturin',
        'company' => array(
            'UzDaewoo' => array(
                array(
                    'title' => 'Cobalt',
                    'year' => 2033,
                    'mileage' => 1000,
                ),
                array(
                    'title' => 'Malibu',
                    'year' => 2014,
                    'mileage' => 100,
                ),
                array(
                    'title' => 'Nexia',
                    'year' => 2012,
                    'mileage' => 100000,
                ),
            ),
            'Lada' => array(
                array(
                    'title' => 'Vesta',
                    'year' => 2006,
                    'mileage' => 65452,
                ),
                array(
                    'title' => 'Largus',
                    'year' => 2010,
                    'mileage' => 36974,
                ),
                array(
                    'title' => 'Priora',
                    'year' => 2008,
                    'mileage' => 7852,
                ),
            ),
            'Mercedes' => array(
                array(
                    'title' => 'CLA 45 AMG',
                    'year' => 2010,
                    'mileage' => 78674,
                ),
                array(
                    'title' => 'МL 63 AMG ',
                    'year' => 2011,
                    'mileage' => 852126,
                ),
                array(
                    'title' => 'A 45 AMG',
                    'year' => 2011,
                    'mileage' => 654212,
                ),
            ),
        ),
    );

    $html = infra_template_parse('*infra/tests/resources/simpletpl.html', $data, 'text', 'company.UzDaewoo');

if ($html == 'title:MalibuVestayear:2014Vestamileage:100Vesta') {
    $ans['result'] = 1;
} else {
        $ans['result'] = 0;
    }

    return $ans;
