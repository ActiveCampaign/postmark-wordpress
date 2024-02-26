<?php
/**
 * Uninstall
 *
 * @package postmark-wordpress
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Delete Settings.
delete_option( 'postmark-settings' );

// Remove Log Table.
postmark_log_remove_table();
