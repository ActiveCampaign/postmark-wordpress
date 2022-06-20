<script>
	var postmark = postmark || {};
	postmark.settings = <?php echo wp_json_encode( $this->settings ); ?>;
</script>
<?php

// Registers script for JS.
wp_register_script( 'pm-js', plugins_url( 'assets/js/admin.js', __FILE__ ), '', null, true );

// Enqueues script for JS.
wp_enqueue_script( 'pm-js' );

// Registers script for CSS.
wp_register_style( 'pm-styles', plugins_url( 'assets/css/admin.css', __FILE__ ) );

// Enqueues script for CSS.
wp_enqueue_style( 'pm-styles' );

wp_nonce_field( 'postmark_nonce' );

?>
<div class="wrap">
	<div class="logo-bar">
		<a href="https://postmarkapp.com/" target="_blank"><img src="<?php echo esc_url( POSTMARK_URL . '/assets/images/logo.png' ); ?>" width="150" height="40" alt="" /></a>
	</div>

	<h1 class="nav-tab-wrapper">
		<a class="nav-tab" rel="general"><?php _e( 'General', 'postmark-wordpress' ); ?></a>
		<a class="nav-tab" rel="test"><?php _e( 'Send Test Email', 'postmark-wordpress' ); ?></a>
		<a class="nav-tab" rel="overrides"><?php _e( 'Overrides', 'postmark-wordpress' ); ?></a>
		<a class="nav-tab" rel="status"><?php _e( 'Status', 'postmark-wordpress' ); ?></a>
		<!-- Only show Logs tab if logging is enabled -->
		<?php if ( isset( $this->settings['enable_logs'] ) && true == $this->settings['enable_logs'] ) : ?>
			<a class="nav-tab" rel="log" id="pm-log-nav-tab"><?php _e( 'Logs', 'postmark-wordpress' ); ?></a>
		<?php else : ?>
			<a class="nav-tab hidden" rel="log" id="pm-log-nav-tab"><?php _e( 'Logs', 'postmark-wordpress' ); ?></a>
		<?php endif; ?>

		<?php if ( isset( $_ENV['POSTMARK_PLUGIN_TESTING'] ) && 'POSTMARK_PLUGIN_TESTING' === $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
			<a class="nav-tab" rel="plugin-testing"><?php _e( 'Plugin Testing', 'postmark-wordpress' ); ?></a>
		<?php endif; ?>
	</h1>

	<div class="updated notice pm-notice hidden"></div>

	<?php if ( isset( $this->overridden_settings['api_key'] ) ) : ?>
		<div class="notice notice-info"><code>POSTMARK_API_KEY</code> is defined in your wp-config.php and overrides the <code>API Key</code> set here.</div>
	<?php endif; ?>

	<?php if ( isset( $this->overridden_settings['stream_name'] ) ) : ?>
		<div class="notice notice-info"><code>POSTMARK_STREAM_NAME</code> is defined in your wp-config.php and overrides the <code>Message Stream</code> set here.</div>
	<?php endif; ?>

	<?php if ( isset( $this->overridden_settings['sender_address'] ) ) : ?>
		<div class="notice notice-info"><code>POSTMARK_SENDER_ADDRESS</code> is defined in your wp-config.php and overrides the <code>Sender Email</code> set here.</div>
	<?php endif; ?>

	<?php if ( isset( $this->overridden_settings['force_from'] ) ) : ?>
		<div class="notice notice-info"><code>POSTMARK_FORCE_FROM</code> is defined in your wp-config.php and overrides the <code>Force From</code> set here.</div>
	<?php endif; ?>

	<div class="tab-content tab-general">
		<table class="form-table">
			<tr>
				<th><label><?php _e( 'Enabled?', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-enabled" value="1" />
					<span class="footnote"><?php _e( 'Send emails using ActiveCampaign Postmark.', 'postmark-wordpress' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'API Key', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="password" class="pm-api-key postmark-input" value="" placeholder="API Key" />
					<div class="footnote">Your API key is in the <strong>API Tokens</strong> tab of your <a href="https://account.postmarkapp.com/servers" target="_blank">Postmark Server</a>.</div>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Message Stream', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="text" class="pm-stream-name postmark-input" value="" placeholder="outbound" />
					<div class="footnote"><?php _e( 'Optional - Default is \'outbound\' if blank.', 'postmark-wordpress' ); ?></div>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Sender Email', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="text" class="pm-sender-address postmark-input" value="" placeholder="example@domain.com" />
					<div class="footnote">This email must be a verified <a href="https://account.postmarkapp.com/signatures" target="_blank">Sender Signature</a>. It will appear as the "from" address on all outbound emails.</div>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Force Sender Email', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-force-from" value="1" />
					<span class="footnote">Force emails to be sent from the Sender Email specified above. Disallows overriding using the <code>$headers</code> array.</span>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Force HTML', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-force-html" value="1" />
					<span class="footnote"><?php _e( 'Force emails to be sent as HTML.', 'postmark-wordpress' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Track Opens', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-track-opens" value="1" />
					<span class="footnote">Track email opens (<code>Force HTML</code> is required).</span>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Track Links', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-track-links" value="1" />
					<span class="footnote"><?php _e( 'Track links in emails.', 'postmark-wordpress' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Enable Logs', 'postmark-wordpress' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-enable-logs" value="1" />
					<span class="footnote"><?php _e( 'Log send attempts for historical/troubleshooting purposes (Recommended).', 'postmark-wordpress' ); ?></span>
				</td>
			</tr>
		</table>

		<div class="submit">
			<input type="submit" class="button-primary save-settings" value="Save Changes" />
		</div>
	</div>

	<div class="tab-content tab-test">
		<table class="form-table">
			<tr>
				<th><label><?php _e( 'To', 'postmark-wordpress' ); ?></label></th>
				<td><input type="text" class="pm-test-email" value="" placeholder="recipient@example.com" /></td>
			</tr>
			<tr>
				<th><label><?php _e( 'From (optional)', 'postmark-wordpress' ); ?></label></th>
				<td><input type="text" class="pm-test-email-sender" value="" placeholder="sender@example.com" /></td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="with_tracking_and_html" class="pm-test-with-opens" value="" /><?php _e( 'Send test as HTML, with Open and Link Tracking enabled.', 'postmark-wordpress' ); ?></td>
			</tr>
		</table>

		<div class="submit">
			<input type="submit" class="button-primary send-test" value="Send Test Email" />
		</div>
	</div>

	<div class="tab-content tab-overrides">
		<h2><?php _e( 'Developer overrides', 'postmark-wordpress' ); ?></h2>
		<p>Instead of using <code>Force HTML</code>, we recommend setting <code>wp_mail</code> headers when possible.</p>
		<pre>
		$headers = array();

		// Override the default 'From' address if 'Force Sender Email' is not enabled
		$headers['From'] = 'john.smith@example.com';

		// Send the message as HTML
		$headers['Content-Type'] = 'text/html';

		// Enable open tracking (requires HTML email enabled)
		$headers['X-PM-Track-Opens'] = true;

		// Enable or disable link tracking
		// Options are None, HtmlAndText, TextOnly, or HtmlOnly
		$headers['X-PM-Track-Links'] = 'HtmlAndText';

		// Send the email
		$response = wp_mail( $to, $subject, $message, $headers );
		</pre>
		To learn more about <code>wp_mail</code>, see the <a href="https://developer.wordpress.org/reference/functions/wp_mail/">WordPress Codex page.</a>
	</div>


	<div class="tab-content tab-status">
		<?php

		$pm_status = json_decode(
			wp_remote_retrieve_body(
				wp_remote_get(
					'https://status.postmarkapp.com/api/1.0/status/',
					array(
						'headers' => array(
							'Accept'       => 'application/json',
							'Content-Type' => 'application/json',
						),
					)
				)
			)
		);

		?>
		<table class="form-table">
			<tr>
				<th><label><?php _e( 'Status', 'postmark-wordpress' ); ?></label></th>

				<td>
					<?php echo $pm_status->status; ?>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Last Checked', 'postmark-wordpress' ); ?></label></th>
				<td>
				<?php
					$unix_date   = gmdate( 'U', strtotime( $pm_status->lastCheckDate ) );
					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );
					echo wp_date( "{$date_format} {$time_format}", $unix_date );
				?>
				</td>
			</tr>
			<tr>
				<th><label><a href="https://status.postmarkapp.com/" target="_blank"><?php _e( 'Check Postmark Status Site', 'postmark-wordpress' ); ?></a></label></th>
				</td>
			</tr>
		</table>

	</div>

	<!-- Sending logs tab -->
	<!-- Only show Log tab if logging is enabled -->
	<?php if ( isset( $this->settings['enable_logs'] ) && true == $this->settings['enable_logs'] ) : ?>
		<div class="tab-content tab-log">

			<?php
			global $wpdb;

			$table = $wpdb->prefix . 'postmark_log';

			// Checks how many logs are in the logs table.
			$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );

			// Only shows some logs if some logs are stored.
			if ( $count > 0 ) {

				// Pulls sending logs from db to display in UI. prepare() used to prevent SQL injections
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY log_entry_date DESC LIMIT %d", 10 ) );

				// Logs table header HTML.
				echo '<table class="pm-log" id="pm-log-table">
                     <thead>
                       <th>Date</th>
                       <th>From</th>
                       <th>To</th>
                       <th>Subject</th>
                       <th>Postmark API Response</th>
                     </thead><tbody>';

				// Builds HTML for each log to show as a row in the logs table.
				foreach ( $result as $row ) {
					echo '<tr><td align="center">' . date( 'Y-m-d h:i A', strtotime( esc_html( $row->log_entry_date ) ) ) . '</td><td align="center">  ' . esc_html( $row->fromaddress ) . '</td><td align="center">  ' . esc_html( $row->toaddress ) . '</td><td align="center">  ' . esc_html( $row->subject ) . '</td><td align="center">  ' . $row->response . '</td></tr>';
				}

				echo '</tbody></table>';

				// Shows a 'Load More' button if more than 10 logs in logs table.
				if ( $count > 10 ) {
					echo '<div class="submit load-more">
                     <input type="submit" class="button-primary" value="Load More" /></div>';
				}
			} else {
				echo '<h2 align="center">No Logs</h2>';
			}
			?>
		<?php endif; ?>
		</div>

		<?php if ( isset( $_ENV['POSTMARK_PLUGIN_TESTING'] ) && 'POSTMARK_PLUGIN_TESTING' === $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
			<div class="tab-content tab-plugin-testing">
				<table class="form-table" style="max-width:740px;">
					<tr>
						<th><label><?php _e( 'Headers', 'postmark-wordpress' ); ?></label></th>
						<td>
							<textarea name="pm-plugin-test-headers" class="pm-plugin-test-headers" cols=80 placeholder="Reply-To: john@example.com"></textarea>
						</td>
					</tr>
					<tr>
						<th><label><?php _e( 'Subject', 'postmark-wordpress' ); ?></label></th>
						<td>
							<input type="text" name="pm-plugin-test-subject" class="pm-plugin-test-subject" placeholder="Dear Emily, I just wanted to say hello..." />
						</td>
					</tr>
					<tr>
						<th><label><?php _e( 'Body', 'postmark-wordpress' ); ?></label></th>
						<td>
							<textarea name="pm-plugin-test-body" class="pm-plugin-test-body" placeholder="Hi there!" cols=80></textarea>
						</td>
					</tr>
					<tr>
						<th><label><?php _e( 'To Address', 'postmark-wordpress' ); ?></label></th>
						<td>
							<input type="text" name="pm-plugin-test-to-address" class="pm-plugin-test-to-address" value="" placeholder="emily@example.com" />
						</td>
					</tr>
				</table>

				<div class="submit">
					<input type="submit" class="button-primary plugin-send-test" value="Send Test Message" />
				</div>
			</div>
		<?php endif; ?>
</div>
