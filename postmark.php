<?php
/*
Plugin Name: Postmark (Official)
Plugin URI: https://postmarkapp.com/
Description: Overwrites wp_mail to send emails through Postmark
Version: 1.9.5
Author: Andrew Yates & Matt Gibbs
*/

class Postmark_Mail
{
    public $settings;
    public static $LAST_ERROR = null;

    function __construct() {
        define( 'POSTMARK_VERSION', '1.9.4' );
        define( 'POSTMARK_DIR', dirname( __FILE__ ) );
        define( 'POSTMARK_URL', plugins_url( basename( POSTMARK_DIR ) ) );

        add_filter( 'init', array( $this, 'init' ) );

        $this->settings = $this->load_settings();
    }


    function init() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_ajax_postmark_save', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_postmark_test', array( $this, 'send_test_email' ) );
        add_action( 'wp_ajax_postmark_test_plugin', array( $this, 'postmark_test_plugin' ) );
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
	    // We check the wp_nonce.
	    if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'postmark_nonce' ) ) {
		    wp_die(__('We were unable to verify this request, please reload the page and try again.'));
	    }
	    
	    // We check that the current user is allowed to update settings.
	    if ( ! current_user_can('manage_options') ) {
		    wp_die(__('We were unable to verify this request, please reload the page and try again.'));
	    }
	    
        // We validate that 'email' is a valid email address
        if ( isset($_POST['email']) && is_email($_POST['email']) ) {
	        $to = sanitize_email($_POST['email']);
        }
        else {
	        wp_die(__('You need to specify a valid recipient email address.', 'postmark-wordpress'));
        }
        
        // We validate that 'with_tracking_and_html' is a numeric boolean
        if ( isset($_POST['with_tracking_and_html']) && 1 === $_POST['with_tracking_and_html'] ) {
	        $with_tracking_and_html = true;
        }
        else {
	        $with_tracking_and_html = false;
        }
        
        // We validate that 'override_from_address' is a valid email address
        if ( isset($_POST['override_from_address']) && is_email($_POST['override_from_address']) ) {
	        $override_from = sanitize_email($_POST['override_from_address']);
        }
        else {
	        $override_from = false;
        }
        
        $subject = 'Postmark Test: ' . get_bloginfo( 'name' );
        $override_from = $_POST['override_from_address'];
        $headers = array();

        if ( $with_tracking_and_html ) {
            $message = 'This is an <strong>HTML test</strong> email sent using the Postmark plugin. It has Open Tracking enabled.';
            array_push( $headers, 'X-PM-Track-Opens: true' );
        }
        else{ 
            $message = 'This is a test email sent using the Postmark plugin.';
        }

        if( false !== $override_from && '' !== $override_from ) {
            array_push($headers, 'From: ' . $override_from);
        }

        $response = wp_mail( $to, $subject, $message, $headers );

        if ( false !== $response ) {
            echo 'Test sent';
        }
        else {
            $dump = print_r( Postmark_Mail::$LAST_ERROR, true );
            echo 'Test failed, the following is the error generated when running the test send:<br/><pre class="diagnostics">' . $dump . '</pre>';
        }
		
        wp_die();
    }

    function save_settings() {
	    // We check the wp_nonce.
	    if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'postmark_nonce' ) ) {
		    wp_die(__('We were unable to verify this request, please reload the page and try again.'));
	    }
	    	    
	    // We check that the current user is allowed to update settings.
	    if ( ! current_user_can('manage_options') ) {
		    wp_die(__('We were unable to verify this request, please reload the page and try again.'));
	    }
	    
	    // We check that we have received some data.
	    if ( ! isset($_POST['data']) ) {
		    wp_die(__('We were unable to verify this request, please reload the page and try again.'));
    }

        $data = json_decode( stripslashes( $_POST['data'] ), true);

        $settings = array();
        
        // We check that we were able to decode data.
        $subject = $_POST['subject'];
        if ( ! is_array($data) ) {
	        wp_die(__('Something went wrong!', 'postmark-wordpress'));
        }

        // We validate that 'enabled' is a numeric boolean
        if ( isset($data['enabled']) && 1 === $data['enabled'] ) {
	        $settings['enabled'] = 1;
        }
        else {
	        $settings['enabled'] = 0;
        }

        // We validate that 'api_key' contains only allowed caracters [letters, numbers, dash]
        if ( isset($data['api_key']) && 1 === preg_match('/^[A-Za-z0-9\-]*$/', $data['api_key']) ) {
	        $settings['api_key'] = $data['api_key'];
        }
        else {
	        $settings['api_key'] = '';
        }
        
        // We validate that 'sender_address' is a valid email address
        if ( isset($data['sender_address']) && is_email($data['sender_address']) ) {
	        $settings['sender_address'] = sanitize_email($data['sender_address']);
        }
        else {
	        $settings['sender_address'] = '';
    }

        // We validate that 'force_html' is a numeric boolean
        if ( isset($data['force_html']) && 1 === $data['force_html'] ) {
	        $settings['force_html'] = 1;
        }
        else {
	        $settings['force_html'] = 0;
        }

        // We validate that 'track_opens' is a numeric boolean
        if ( isset($data['track_opens']) && 1 === $data['track_opens'] ) {
	        $settings['track_opens'] = 1;
        }
        else {
	        $settings['track_opens'] = 0;
        }

        update_option( 'postmark_settings', json_encode($settings) );

        wp_die('Settings saved');
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
