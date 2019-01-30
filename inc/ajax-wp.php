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
    $info['name'] = (isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '');
    $info['phone'] = (isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '');
    $info['objectCleaning'] = (isset($_POST['objectCleaning']) ? sanitize_text_field($_POST['objectCleaning']) : '');
    $info['numberOfRooms'] = (isset($_POST['numberOfRooms']) ? sanitize_text_field($_POST['numberOfRooms']) : '');
    $info['cleaningArea'] = (isset($_POST['cleaningArea']) ? intval($_POST['cleaningArea']) : 0);
    $info['periodicity'] = (isset($_POST['periodicity']) ? sanitize_text_field($_POST['periodicity']) : '');
    $info['cleaningType'] = (isset($_POST['cleaningType']) ? sanitize_text_field($_POST['cleaningType']) : '');
    $info['additionalServicesCount'] = (isset($_POST['additionalServicesCount']) ? intval($_POST['additionalServicesCount']) : 0);
    if ($info['additionalServicesCount'] !== 0) {
        for ($i = 0; $i <= $info['additionalServicesCount'] - 1; $i++) {
            $info["additionalServices$i"] = (isset($_POST["additionalServices$i"]) ? sanitize_text_field($_POST["additionalServices$i"]) : '');
        }
    }

    $errorArr = [];

    contact_send($info);

    if (count($errorArr) > 0) {
        wp_send_json_error($errorArr);
    } else {
//        wp_send_json_success('Заявка успешно зарегистрирована!');
        wp_send_json_success($info);
    }
    wp_die();
}

