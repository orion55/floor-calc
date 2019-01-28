<?php
add_action('wp_ajax_nopriv_floor_add', 'floor_add');
add_action('wp_ajax_floor_add', 'floor_add');

function floor_add()
{
    function contact_send($info)
    {

    }

    if (empty($_POST['nonce'])) {
        wp_die('Nonce bad');
    }

    $check_ajax_referer = check_ajax_referer('myajax-nonce123', 'nonce', false);

    if (!$check_ajax_referer) {
        wp_send_json_error('Эх! Сработала защита');
    }

    $info = [];

    $errorArr = [];

    contact_send($info);

    if (count($errorArr) > 0) {
        wp_send_json_error($errorArr);
    } else {
        wp_send_json_success('Заявка успешно зарегистрирована!');
//        wp_send_json_success(wp_json_encode($_POST));
    }
    wp_die();
}

