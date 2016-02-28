<?php
/*
Plugin Name: Postmark (Official)
Plugin URI: https://postmarkapp.com/
Description: Overwrites wp_mail to send emails through Postmark
Version: 1.7
Author: Andrew Yates & Matt Gibbs

Copyright 2011 - 2016 Andrew Yates & Postmark

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

class Postmark_Mail
{
    public $settings;


    function __construct() {
        define( 'POSTMARK_VERSION', '1.7' );
        define( 'POSTMARK_DIR', dirname( __FILE__ ) );
        define( 'POSTMARK_URL', plugins_url( basename( POSTMARK_DIR ) ) );

        add_filter( 'init', array( $this, 'init' ) );

        $this->upgrade();
        $this->settings = json_decode( get_option( 'postmark_settings' ), true );
    }


    function init() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_ajax_postmark_save', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_postmark_test', array( $this, 'send_test_email' ) );
    }


    function upgrade() {
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
        }
    }


    function admin_menu() {
        add_options_page( 'Postmark', 'Postmark', 'manage_options', 'pm_admin', array( $this, 'settings_html' ) );
    }


    function send_test_email() {
        $to = $_POST['email'];
        $subject = 'Postmark Test: ' . get_bloginfo( 'name' );
        $message = 'This is a <strong>test</strong> email sent using the Postmark plugin.';
        $response = wp_mail( $to, $subject, $message );
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
