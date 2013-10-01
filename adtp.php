<?php
/*
Plugin Name: Adtlantida.tv Wordpress Plugin
Plugin URI: http://tareq.wedevs.com/2011/01/new-plugin-wordpress-user-frontend/
Description: Post, Edit, Delete posts and edit profile without coming to backend. Forked from wp-user-frontend plugin by Tareq Hasan
Author: Tareq Hasan and Berio Molina
Version: 1.0
Author URI: http://tareq.weDevs.com and http://berio.alg-a.org

*/
// Redefine user notification function
if ( !function_exists('wp_new_user_notification') ) {
/**
 * Notify the blog admin of a new user, normally via email.
 *
 * @since 2.0
 *
 * @param int $user_id User ID
 * @param string $plaintext_pass Optional. The user's plaintext password
 */
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = get_userdata( $user_id );

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
	$message .= home_url() . "\r\n";

	wp_mail($user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);

}
}


if ( !class_exists( 'WeDevs_Settings_API' ) ) {
    require_once dirname( __FILE__ ) . '/lib/class.settings-api.php';
}

require_once 'adtp-functions.php';
require_once 'admin/settings-options.php';

if ( is_admin() ) {
    require_once 'admin/settings.php';
}

require_once 'adtp-dashboard.php';
require_once 'adtp-add-post.php';
require_once 'adtp-edit-post.php';
require_once 'adtp-editprofile.php';
require_once 'adtp-edit-user.php';
require_once 'adtp-ajax.php';

require_once 'lib/attachment.php';

class ADTP_Main {

    function __construct() {
        register_activation_hook( __FILE__, array($this, 'install') );
        register_deactivation_hook( __FILE__, array($this, 'uninstall') );

        add_action( 'admin_init', array($this, 'block_admin_access') );

        add_action( 'init', array($this, 'load_textdomain') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
     */
    function install() {
        global $wpdb;

        flush_rewrite_rules( false );

    }

    function uninstall() {

    }

    /**
     * Enqueues Styles and Scripts when the shortcodes are used only
     *
     * @uses has_shortcode()
     * @since 0.2
     */
    function enqueue_scripts() {
        $path = plugins_url('', __FILE__ );

        //for multisite upload limit filter
        if ( is_multisite() ) {
            require_once ABSPATH . '/wp-admin/includes/ms.php';
        }

        require_once ABSPATH . '/wp-admin/includes/template.php';

        wp_enqueue_style( 'adtp', $path . '/css/wpuf.css' );

        if ( wpuf_has_shortcode( 'wpuf_addpost' ) || wpuf_has_shortcode( 'wpuf_edit' ) ) {
            wp_enqueue_script( 'plupload-handlers' );
        }
        
        wp_enqueue_script( 'adtp', $path . '/js/wpuf.js', array('jquery') );

        $posting_msg = __('updating', 'adt');
        $feat_img_enabled = ( wpuf_get_option( 'enable_featured_image', 'adtp_frontend_posting' ) == 'yes') ? true : false;
        wp_localize_script( 'adtp', 'adtp', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'postingMsg' => $posting_msg,
            'confirmMsg' => __( 'Are you sure?', 'adtp' ),
            'nonce' => wp_create_nonce( 'wpuf_nonce' ),
            'featEnabled' => $feat_img_enabled,
            'plupload' => array(
                'runtimes' => 'html5,silverlight,flash,html4',
                'browse_button' => 'wpuf-ft-upload-pickfiles',
                'container' => 'wpuf-ft-upload-container',
                'file_data_name' => 'wpuf_featured_img',
                'max_file_size' => wp_max_upload_size() . 'b',
                'url' => admin_url( 'admin-ajax.php' ) . '?action=wpuf_featured_img&nonce=' . wp_create_nonce( 'wpuf_featured_img' ),
                'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
                'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
                'filters' => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                'multipart' => true,
                'urlstream_upload' => true,
            )
        ) );
    }

    /**
     * Block user access to admin panel for specific roles
     *
     * @global string $pagenow
     */
    function block_admin_access() {
        global $pagenow;

        // bail out if we are from WP Cli
        if ( defined( 'WP_CLI' ) ) {
            return;
        }

        $access_level = wpuf_get_option( 'admin_access', 'adtp_others', 'read' );
        $valid_pages = array('admin-ajax.php', 'async-upload.php', 'media-upload.php');

        if ( !current_user_can( $access_level ) && !in_array( $pagenow, $valid_pages ) ) {
            wp_die( __( 'Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'adtp' ) );
        }
    }

    /**
     * Load the translation file for current language.
     *
     * @since version 0.7
     * @author Tareq Hasan
     */
    function load_textdomain() {
        $locale = apply_filters( 'wpuf_locale', get_locale() );
        $mofile = dirname( __FILE__ ) . "/languages/wpuf-$locale.mo";

        if ( file_exists( $mofile ) ) {
            load_textdomain( 'adtp', $mofile );
        }
    }

    /**
     * The main logging function
     *
     * @uses error_log
     * @param string $type type of the error. e.g: debug, error, info
     * @param string $msg
     */
    public static function log( $type = '', $msg = '' ) {
        if ( WP_DEBUG == true ) {
            $msg = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $msg );
            error_log( $msg, 3, dirname( __FILE__ ) . '/log.txt' );
        }
    }

}

$wpuf = new ADTP_Main();
