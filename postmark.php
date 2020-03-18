<?php
/**
 * Plugin Name: Postmark (Official)
 * Plugin URI: https://postmarkapp.com/
 * Description: Overrides wp_mail to send emails through Postmark
 * Version: 1.12
 * Author: Andrew Yates & Matt Gibbs
 */

/**
 * Postmark Mail.
 */
class Postmark_Mail {

	/**
	 * Settings.
	 *
	 * @var [type]
	 */
	public $settings;

	/**
	 * Last Error.
	 *
	 * @var [type]
	 */
	public static $LAST_ERROR = null;

	/**
	 * Constuctor.
	 */
	public function __construct() {
		if ( ! defined( 'POSTMARK_VERSION' ) ) {
			define( 'POSTMARK_VERSION', '1.12' );
		}

		if ( ! defined( 'POSTMARK_DIR' ) ) {
			define( 'POSTMARK_DIR', dirname( __FILE__ ) );
		}

		if ( ! defined( 'POSTMARK_URL' ) ) {
			define( 'POSTMARK_URL', plugins_url( basename( POSTMARK_DIR ) ) );
		}

		add_filter( 'init', array( $this, 'init' ) );

		$this->settings = $this->load_settings();
	}

	/**
	 * Init.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_postmark_save', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_postmark_test', array( $this, 'send_test_email' ) );
		add_action( 'wp_ajax_postmark_load_more_logs', array( $this, 'postmark_load_more_logs' ) );
		add_action( 'wp_ajax_postmark_test_plugin', array( $this, 'postmark_test_plugin' ) );
	}

	/**
	 * Load Settings.
	 *
	 * @return array Return settings.
	 */
	public function load_settings() {
		$settings = get_option( 'postmark_settings' );

		if ( false === $settings ) {
			$settings = array(
				'enabled'        => get_option( 'postmark_enabled', 0 ),
				'api_key'        => get_option( 'postmark_api_key', '' ),
				'stream_name'    => get_option( 'postmark_stream_name', 'outbound'),
				'sender_address' => get_option( 'postmark_sender_address', '' ),
				'force_html'     => get_option( 'postmark_force_html', 0 ),
				'track_opens'    => get_option( 'postmark_trackopens', 0 ),
				'track_links'    => get_option( 'postmark_tracklinks', 0 ),
				'enable_logs'    => get_option( 'postmark_enable_logs', 1 ),
			);

			update_option( 'postmark_settings', wp_json_encode( $settings ) );

			return $settings;
		}

		if ( is_array( $settings ) && ! isset( $settings['track_links'] ) ) {
			$settings['track_links'] = 0;
			update_option( 'postmark_settings', wp_json_encode( $settings ) );
			return $settings;
		}

		if ( is_array( $settings ) && ! isset( $settings['stream_name'] ) ) {
			$settings['stream_name'] = 'outbound';
			update_option( 'postmark_settings', wp_json_encode( $settings ) );
			return $settings;
		}

		return json_decode( $settings, true );
	}

	/**
	 * Admin Menu.
	 */
	public function admin_menu() {
		add_options_page( 'Postmark', 'Postmark', 'manage_options', 'pm_admin', array( $this, 'settings_html' ) );
	}

	/**
	 * Retrieves additional logs.
	 *
	 * @return array Response.
	 */
	public function postmark_load_more_logs() {

		global $wpdb;

		$new_rows_html = '';

		$table = $wpdb->prefix . 'postmark_log';

		// Checks the wp_nonce.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'postmark_nonce' ) ) {
			wp_die( __( 'We were unable to verify this request, please reload the page and try again.' ) );
		}

		// Retrieves more logs from logs table using offset and prepare() to prevent SQL injections.
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY log_entry_date DESC LIMIT %d OFFSET %d", 10, $_POST['offset'] ) );

		$has_more = true;

		if ( $result->length < 10 ) {
			$has_more = false;
		}

		// Iterates through the retrieved logs and builds HTML rows for each one, to be added to the logs table in the UI.
		foreach ( $result as $row ) {
			$new_rows_html = $new_rows_html . '<tr><td align="center">' . date( 'Y-m-d h:i A', strtotime( $row->log_entry_date ) ) . '</td><td align="center">  ' . $row->fromaddress . '</td><td align="center">  ' . $row->toaddress . '</td><td align="center">  ' . $row->subject . '</td><td align="center">  ' . $row->response . '</td></tr>';
		}

		$response = array(
			'html'     => $new_rows_html,
			// Lets the front end know how if there are any more logs.
			'has_more' => $has_more,
		);

		echo wp_json_encode( $response );

		wp_die();
	}

	/**
	 * Send Test Email.
	 */
	public function send_test_email() {
		// We check the wp_nonce.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'postmark_nonce' ) ) {
			wp_die( __( 'We were unable to verify this request, please reload the page and try again.' ) );
		}

		// We check that the current user is allowed to update settings.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'We were unable to verify this request, please reload the page and try again.' ) );
		}

		// We validate that 'email' is a valid email address.
		if ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) ) {
			$to = sanitize_email( $_POST['email'] );
		} else {
			wp_die( __( 'You need to specify a valid recipient email address.', 'postmark-wordpress' ) );
		}

		// We validate that 'with_tracking_and_html' is a numeric boolean.
		if ( isset( $_POST['with_tracking_and_html'] ) && 1 === $_POST['with_tracking_and_html'] ) {
			$with_tracking_and_html = true;
		} else {
			$with_tracking_and_html = false;
		}

		// We validate that 'override_from_address' is a valid email address.
		if ( isset( $_POST['override_from_address'] ) && is_email( $_POST['override_from_address'] ) ) {
			$override_from = sanitize_email( $_POST['override_from_address'] );
		} else {
			$override_from = false;
		}

		$subject       = 'Postmark Test: ' . get_bloginfo( 'name' );
		$override_from = $_POST['override_from_address'];
		$headers       = array();

		if ( isset( $_POST['with_tracking_and_html'] ) && $_POST['with_tracking_and_html'] ) {
			$message = 'This is an <strong>HTML</strong> test email sent using the Postmark plugin. It has <a href="https://postmarkapp.com/developer/user-guide/tracking-opens">Open Tracking</a> and <a href="https://postmarkapp.com/developer/user-guide/tracking-links">Link Tracking</a> enabled.';
			array_push( $headers, 'X-PM-Track-Opens: true' );
			array_push( $headers, 'X-PM-TrackLinks: HtmlAndText' );
		} else {
			$message = 'This is a test email sent using the Postmark plugin.';
		}

		if ( false !== $override_from && '' !== $override_from ) {
			array_push( $headers, 'From: ' . $override_from );
		}

		$response = wp_mail( $to, $subject, $message, $headers );

		if ( false !== $response ) {
			echo 'Test sent';
		} else {
			$dump = print_r( self::$LAST_ERROR, true );
			echo 'Test failed, the following is the error generated when running the test send:<br/><pre class="diagnostics">' . $dump . '</pre>';
		}

		wp_die();
	}

	/**
	 * Save Settings.
	 */
	public function save_settings() {
		// We check the wp_nonce.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'postmark_nonce' ) ) {
			wp_die( __( 'We were unable to verify this request, please reload the page and try again.' ) );
		}

		// We check that the current user is allowed to update settings.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'We were unable to verify this request, please reload the page and try again.' ) );
		}

		// We check that we have received some data.
		if ( ! isset( $_POST['data'] ) ) {
			wp_die( __( 'We were unable to verify this request, please reload the page and try again.' ) );
		}

		$data = json_decode( stripslashes( $_POST['data'] ), true );

		$settings = array();

		// We check that we were able to decode data.
		if ( ! is_array( $data ) ) {
			wp_die( __( 'Something went wrong!', 'postmark-wordpress' ) );
		}

		// We validate that 'enabled' is a numeric boolean.
		if ( isset( $data['enabled'] ) && 1 === $data['enabled'] ) {
			$settings['enabled'] = 1;
		} else {
			$settings['enabled'] = 0;
		}

		// We validate that 'api_key' contains only allowed caracters [letters, numbers, dash].
		if ( isset( $data['api_key'] ) && ( 1 === preg_match( '/^[A-Za-z0-9\-]*$/', $data['api_key'] || 'POSTMARK_API_TEST' === $data['api_key'] ) ) ) {
			$settings['api_key'] = $data['api_key'];
		} else {
			$settings['api_key'] = '';
		}

		// We validate that 'stream_name' contains only allowed caracters [letters, numbers, dash].
		if ( isset( $data['stream_name'] ) && ( 1 === preg_match( '/^[A-Za-z0-9\-]*$/', $data['stream_name'] ) ) ) {
			$settings['stream_name'] = $data['stream_name'];
		} else {
			$settings['stream_name'] = 'outbound';
		}

		// We validate that 'sender_address' is a valid email address.
		if ( isset( $data['sender_address'] ) && is_email( $data['sender_address'] ) ) {
			$settings['sender_address'] = sanitize_email( $data['sender_address'] );
		} else {
			$settings['sender_address'] = '';
		}

		// We validate that 'force_html' is a numeric boolean.
		if ( isset( $data['force_html'] ) && 1 === $data['force_html'] ) {
			$settings['force_html'] = 1;
		} else {
			$settings['force_html'] = 0;
		}

		// We validate that 'track_opens' is a numeric boolean.
		if ( isset( $data['track_opens'] ) && 1 === $data['track_opens'] ) {
			$settings['track_opens'] = 1;
		} else {
			$settings['track_opens'] = 0;
		}

		// We validate that 'track_links' is a numeric boolean.
		if ( isset( $data['track_links'] ) && 1 === $data['track_links'] ) {
			$settings['track_links'] = 1;
		} else {
			$settings['track_links'] = 0;
		}

		// Validates that 'enable_logs' is a numeric boolean and creates table for storing logs.
		if ( isset( $data['enable_logs'] ) && 1 === $data['enable_logs'] ) {
			$settings['enable_logs'] = 1;
			// check if logs table exists, if not create it.
			pm_log_create_db();
		}
		// Removes logs table if setting for logging is disabled.
		else {
			$settings['enable_logs'] = 0;
			postmark_log_remove_table();
			pm_log_cron_deactivate();
		}

		update_option( 'postmark_settings', wp_json_encode( $settings ) );

		wp_die( 'Settings saved' );
	}

	/**
	 * Settings HTML.
	 */
	function settings_html() {
		include POSTMARK_DIR . '/page-settings.php';
	}
}

/**
 * Creates a table for storing logs of send attempts.
 */
function pm_log_create_db() {

	global $wpdb;

	$table_name = $wpdb->prefix . 'postmark_log';

	// Creates a logs table if it doesn't exist already.
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
     id INT(9) NOT NULL AUTO_INCREMENT,
     log_entry_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
     fromaddress text,
     toaddress text,
     subject text,
     response text,
     PRIMARY KEY  id (id)
   ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		// Activates cron job for automatically purging old (7+ days) Postmark logs.
		pm_log_cron_activation();

	}
}

/**
 * Schedules cron job for deleting old Postmark logs.
 */
function pm_log_cron_activation() {
	if ( ! wp_next_scheduled( 'pm_log_cron_job' ) ) {
		wp_schedule_event( time(), 'daily', 'pm_log_cron_job' );
	}
}

// Attaches pm_clear_old_logs function to cron job hook.
add_action( 'pm_log_cron_job', 'pm_clear_old_logs' );

/**
 * Deletes up to 500 Postmark logs at a time that are older than 7 days.
 */
function pm_clear_old_logs() {

	global $wpdb;

	$table_name = $wpdb->prefix . 'postmark_log';

	// Checks if there are any logs older than seven days to delete.
	$rows_to_delete_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name
          WHERE %s < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL %d DAY)) LIMIT 500
         ",
			'log_entry_date',
			7
		)
	);

	// Deletes logs that are more than 7 days old, limited to 500 log deletions at a time to prevent locking up db.
	if ( $rows_to_delete_count > 0 ) {

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table_name
            WHERE %s < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL %d DAY)) LIMIT %d
           ",
				'log_entry_date',
				7,
				$rows_to_delete_count
			)
		);

		// Check again for more logs to delete.
		pm_clear_old_logs();
	}
}

/**
 * Unschedules Postmark logs cleanup cron job.
 */
function pm_log_cron_deactivate() {

	// Checks when the next cron job was scheduled for.
	$timestamp = wp_next_scheduled( 'pm_log_cron_job' );

	// Unschedules upcoming cron job.
	wp_unschedule_event( $timestamp, 'pm_log_cron_job' );
}

// Removes cron job for deleting old logs, if plugin is disabled.
register_deactivation_hook( __FILE__, 'pm_log_cron_deactivate' );

// Creates logs table on activation, if it doesn't exist.
register_activation_hook( __FILE__, 'pm_log_create_db' );

/**
 * Drops logs table (called when plugin is uninstalled).
 */
function postmark_log_remove_table() {
	 global $wpdb;
	 $table_name = $wpdb->prefix . 'postmark_log';
	 $sql        = "DROP TABLE IF EXISTS $table_name";
	 $wpdb->query( $sql );
}

// Removes logs table on uninstall.
register_uninstall_hook( __FILE__, 'postmark_log_remove_table' );

if ( ! function_exists( 'wp_mail' ) ) {
	$postmark = new Postmark_Mail();

	if ( is_array( $postmark->settings ) && ( 1 == $postmark->settings['enabled'] ) ) {
		include POSTMARK_DIR . '/wp-mail.php';
	}
}

/**
 * Handle upgrades (build the logs db) for upgrades from versions prior to 1.10.1.
 *
 * @param  [type] $upgrader_object Upgrader Object.
 * @param  [type] $options         Options.
 */
function upgrade_completed( $upgrader_object, $options ) {

	$pm_plugin = POSTMARK_DIR . '/postmark.php';

	if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {

		foreach ( $options['plugins'] as $plugin ) {
			if ( $plugin == $pm_plugin ) {
				 pm_log_create_db();
				 $postmark->load_settings();
			}
		}
	}
}

add_action( 'upgrader_process_complete', 'upgrade_completed', 10, 2 );
