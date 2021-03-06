<?php
/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan & Berio Molina
 * @forked from wp-user-frontend plugin by Tareq Hasan
 */
class ADTP_Settings {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API();

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    /**
     * Register the admin menu
     *
     * @since 1.0
     */
    function admin_menu() {
        add_menu_page( __( 'Adtlantida', 'adtp' ), __( 'Adtlantida', 'adtp' ), 'activate_plugins', 'adtp-admin-opt', array($this, 'plugin_page'), null );
    }

    /**
     * WPUF Settings sections
     *
     * @since 1.0
     * @return array
     */
    function get_settings_sections() {
        return adtp_settings_sections();
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        return adtp_settings_fields();
    }

    function plugin_page() {
        ?>
        <div class="wrap">
            <?php
            settings_errors();

            screen_icon( 'adtp' );
            $this->settings_api->show_navigation();
            $this->settings_api->show_forms();
            ?>

        </div>
        <?php
    }
}

$adtp_settings = new ADTP_Settings();