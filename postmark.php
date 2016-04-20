<?php
/*
Plugin Name: Postmark (Official)
Plugin URI: https://postmarkapp.com/
Description: Overwrites wp_mail to send emails through Postmark
Version: 1.9.1
Author: Andrew Yates & Matt Gibbs
*/

class Postmark_Mail
{
    public $settings;


    function __construct() {
        define( 'POSTMARK_VERSION', '1.9.1' );
        define( 'POSTMARK_DIR', dirname( __FILE__ ) );
        define( 'POSTMARK_URL', plugins_url( basename( POSTMARK_DIR ) ) );

        add_filter( 'init', array( $this, 'init' ) );

        $this->settings = $this->load_settings();
    }


    function init() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_ajax_postmark_save', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_postmark_test', array( $this, 'send_test_email' ) );
    }


    function load_settings() {
        $settings = get_option( 'postmark_settings' );

        if ( false === $settings ) {
            $settings = array(
                'enabled'           => get_option( 'postmark_enabled', 0 ),
                'api_key'           => get_option( 'postmark_api_key', '' ),
                'sender_address'    => get_option( 'postmark_sender_address', '' ),
                'force_html'        => get_option( 'postmark_force_html', 0 ),
                'track_opens'       => get_option( 'postmark_trackopens', 0 )
            );

            update_option( 'postmark_settings', json_encode( $settings ) );

            return $settings;
        }

        return json_decode( $settings, true );
    }


    function admin_menu() {
        add_options_page( 'Postmark', 'Postmark', 'manage_options', 'pm_admin', array( $this, 'settings_html' ) );
    }


    function send_test_email() {
        $to = $_POST['email'];
        $with_tracking_and_html = $_POST['with_tracking_and_html'];
        $subject = 'Postmark Test: ' . get_bloginfo( 'name' );
        $override_from = $_POST['override_from_address'];
        $headers = array();

        if( $with_tracking_and_html ){
            $message = 'This is an <strong>HTML test</strong> email sent using the Postmark plugin. It has Open Tracking enabled.';
            array_push($headers, 'X-PM-Track-Opens: true');
        }else{ 
            $message = 'This is a test email sent using the Postmark plugin.';
        }

        
        if( isset( $override_from ) && $override_from != '' ) {
            array_push($headers, 'From: ' . $override_from);
        }

        $response = wp_mail( $to, $subject, $message, $headers );
        echo ( false !== $response ) ? 'Test sent' : 'Test failed';
        wp_die();
    }

    function save_settings() {
        $settings = stripslashes( $_POST['data'] );
        $json_test = json_decode( $settings, true );

        // Check for valid JSON
        if ( isset( $json_test['enabled'] ) ) {
            update_option( 'postmark_settings', $settings );
            echo 'Settings saved';
        }
        else {
            echo 'Error: invalid JSON';
        }
        wp_die();
    }


    function settings_html() {
        include( POSTMARK_DIR . '/page-settings.php' );
    }
}


if ( ! function_exists( 'wp_mail' ) ) {
    $postmark = new Postmark_Mail();

    if ( 1 == $postmark->settings['enabled'] ) {
        include( POSTMARK_DIR . '/wp-mail.php' );
    }
}
