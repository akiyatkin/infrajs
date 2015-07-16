<?php
    $ans['title'] = 'Cyrilic support';

    $src = infra_theme('*infra/tests/resources/Тест русского.языка');
    if (!$src) {
        return infra_err($ans, 'Cyrillic unreadable. Check config infra.fscharset:UTF-8');
    }

    return infra_ret($ans, 'Cyrillic alright');
