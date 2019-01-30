<?php
add_action('wp_ajax_nopriv_floor_add', 'floor_add');
add_action('wp_ajax_floor_add', 'floor_add');

function floor_add()
{
    function contact_send($info)
    {
        $title = 'Новый заказ - ' . $info['name'];
        $headers[] = 'From: polclean.ru <mail@polclean.ru>';
        $headers[] = 'Content-type: text/html; charset=utf-8';

        $floorcalc_options = get_option('floorcalc_option_name');
        $email = $floorcalc_options['manager_email_0'];
        if (!empty($email)) {

            $message = '<html><body>';
            $message .= '<table rules="all" style="border-color: #666;" cellpadding="10" border="1">';
            $message .= "<tr style='background: #eee;'><td><strong>Имя:</strong> </td><td>" . $info['name'] . "</td></tr>";
            $message .= "<tr><td><strong>Телефон:</strong> </td><td>" . $info['phone'] . "</td></tr>";
            $message .= "<tr style='background: #eee;'><td><strong>Объект клининга:</strong> </td><td>" . $info['objectCleaning'] . "</td></tr>";
            if ($info['numberOfRooms'] !== '') {
                $message .= "<tr><td><strong>Количество комнат:</strong> </td><td>" . $info['numberOfRooms'] . "</td></tr>";
            }
            if ($info['cleaningArea'] !== 0) {
                $message .= "<tr><td><strong>Площадь уборки:</strong> </td><td>" . $info['cleaningArea'] . "</td></tr>";
            }
            $message .= "<tr style='background: #eee;'><td><strong>Периодичность уборки:</strong> </td><td>" . $info['periodicity'] . "</td></tr>";
            $message .= "<tr><td><strong>Тип уборки:</strong> </td><td>" . $info['cleaningType'] . "</td></tr>";
            if ($info['additionalServicesCount'] !== 0) {
                $msg = '';
                for ($i = 0; $i <= $info['additionalServicesCount'] - 1; $i++) {
                    $msg .= $info["additionalServices$i"] . '<br>';
                }
                $message .= "<tr style='background: #eee;'><td><strong>Дополнительные услуги:</strong> </td><td>" . $msg . "</td></tr>";
            }
            $message .= "<tr><td><strong>Сумма:</strong> </td><td>" . $info['sum'] . " руб.</td></tr>";
            $message .= "</table>";
            $message .= "</body></html>";

            if (!wp_mail($email, $title, $message, $headers)) {
                array_push($errorArr, 'Ошибка при отправки письма!');
            }
        } else {
            array_push($errorArr, 'Ошибка при отправки письма! Email отправки не указан!');
        }
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
    $info['sum'] = (isset($_POST['sum']) ? intval($_POST['sum']) : 0);

    $errorArr = [];

    contact_send($info);

    if (count($errorArr) > 0) {
        wp_send_json_error($errorArr);
    } else {
        wp_send_json_success('Заявка успешно зарегистрирована!');
//        wp_send_json_success($info);
    }
    wp_die();
}

