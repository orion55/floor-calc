<?php
/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class FloorCalc
{
    private $floorcalc_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'floorcalc_add_plugin_page'));
        add_action('admin_init', array($this, 'floorcalc_page_init'));
    }

    public function floorcalc_add_plugin_page()
    {
        add_options_page(
            'FloorCalc', // page_title
            'Floor Calc', // menu_title
            'manage_options', // capability
            'floorcalc', // menu_slug
            array($this, 'floorcalc_create_admin_page') // function
        );
    }

    public function floorcalc_create_admin_page()
    {
        $this->floorcalc_options = get_option('floorcalc_option_name'); ?>

        <div class="wrap">
            <h2>Floor Calc</h2>
            <p>Калькулятор по расчету стоимости услуг клининговой компании</p>
            <p><b>[floor-calc]</b> - шорткод для вставки калькулятора</p>
            <p><b><?php echo plugin_dir_path(dirname(__FILE__)) . 'shortcode/json/price.json' ?></b> - файл с ценами</p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('floorcalc_option_group');
                do_settings_sections('floorcalc-admin');
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function floorcalc_page_init()
    {
        register_setting(
            'floorcalc_option_group', // option_group
            'floorcalc_option_name', // option_name
            array($this, 'floorcalc_sanitize') // sanitize_callback
        );

        add_settings_section(
            'floorcalc_setting_section', // id
            'Настройки', // title
            array($this, 'floorcalc_section_info'), // callback
            'floorcalc-admin' // page
        );

        add_settings_field(
            'manager_email_0', // id
            'Email', // title
            array($this, 'manager_email_0_callback'), // callback
            'floorcalc-admin', // page
            'floorcalc_setting_section' // section
        );
    }

    public function floorcalc_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['manager_email_0'])) {
            $sanitary_values['manager_email_0'] = sanitize_text_field($input['manager_email_0']);
        }

        return $sanitary_values;
    }

    public function floorcalc_section_info()
    {

    }

    public function manager_email_0_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="floorcalc_option_name[manager_email_0]" id="manager_email_0" value="%s">',
            isset($this->floorcalc_options['manager_email_0']) ? esc_attr($this->floorcalc_options['manager_email_0']) : ''
        );
    }

}

if (is_admin())
    $floorcalc = new FloorCalc();

/*
 * Retrieve this value with:
 * $floorcalc_options = get_option( 'floorcalc_option_name' ); // Array of All Options
 * $manager_email_0 = $floorcalc_options['manager_email_0']; // manager_email
 */
