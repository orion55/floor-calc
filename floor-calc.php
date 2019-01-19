<?php

/*
Plugin Name: Floor Calc
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Плагин калькулятора по расчету стоимости услуг клининговой компании
Version: 1.0
Author: Grebenev Oleg
Author URI: http://portfolio.infoblog72.ru/
License: GPL2
*/

defined('ABSPATH') or die('Nope, not accessing this');

require plugin_dir_path(__FILE__) . 'inc/admin_options.php';

require plugin_dir_path(__FILE__) . 'shortcode/class-floor-shortcode.php';

function run_floor_shortcode()
{
    $shortcode = new Floor_Shortcode();
    $shortcode->init();
}

run_floor_shortcode();
