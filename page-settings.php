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
		<a href="https://postmarkapp.com/" target="_blank"><img src="<?php echo esc_url( POSTMARK_URL . '/assets/images/logo.png' ); ?>" width="130" height="21" alt="" /></a>
	</div>

	<h1 class="nav-tab-wrapper">
		<a class="nav-tab" rel="general">General</a>
		<a class="nav-tab" rel="test">Send Test Email</a>
		<a class="nav-tab" rel="overrides">Overrides</a>
		<!-- Only show Logs tab if logging is enabled -->
		<?php if ( isset( $this->settings['enable_logs'] ) && true == $this->settings['enable_logs'] ) : ?>
				 <a class="nav-tab" rel="log" id="pm-log-nav-tab">Logs</a>
		<?php else : ?>
		  <a class="nav-tab hidden" rel="log" id="pm-log-nav-tab">Logs</a>
			<?php endif; ?>

	   <?php if ( isset( $_ENV['POSTMARK_PLUGIN_TESTING'] ) && 'POSTMARK_PLUGIN_TESTING' === $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
				  <a class="nav-tab" rel="plugin-testing">Plugin Testing</a>
		  <?php endif; ?>
	</h1>

	<div class="updated notice pm-notice hidden"></div>

	<div class="tab-content tab-general">
		<table class="form-table">
			<tr>
				<th><label>Enabled?</label></th>
				<td>
					<input type="checkbox" class="pm-enabled" value="1" />
					<span class="footnote">Send emails using Postmark</span>
				</td>
			</tr>
			<tr>
				<th><label>API Key</label></th>
				<td>
					<input type="text" class="pm-api-key" value="" />
					<div class="footnote">Your API key is in the <strong>API Tokens</strong> tab of your <a href="https://account.postmarkapp.com/servers" target="_blank">Postmark Server</a>.</div>
				</td>
			</tr>
			<tr>
				<th><label>Message Stream</label></th>
				<td>
					<input type="text" class="pm-stream-name" value="" placeholder="outbound" />
					<div class="footnote">Optional - Default is 'outbound' if blank.</div>
				</td>
			</tr>
			<tr>
				<th><label>Sender Email</label></th>
				<td>
					<input type="text" class="pm-sender-address" value="" />
					<div class="footnote">This email must be a verified <a href="https://account.postmarkapp.com/signatures" target="_blank">Sender Signature</a>. It will appear as the "from" address on all outbound emails.</div>
				</td>
			</tr>
			<tr>
				<th><label>Force HTML</label></th>
				<td>
					<input type="checkbox" class="pm-force-html" value="1" />
					<span class="footnote">Force emails to be sent as HTML.</span>
				</td>
			</tr>
			<tr>
				<th><label>Track Opens</label></th>
				<td>
					<input type="checkbox" class="pm-track-opens" value="1" />
					<span class="footnote">Track email opens (<code>Force HTML</code> is required).</span>
				</td>
			</tr>
			<tr>
				<th><label>Track Links</label></th>
				<td>
					<input type="checkbox" class="pm-track-links" value="1" />
					<span class="footnote">Track links in emails.</span>
				</td>
			</tr>
			<tr>
				<th><label>Enable Logs</label></th>
				<td>
					<input type="checkbox" class="pm-enable-logs" value="1" />
					<span class="footnote">Log send attempts for historical/troubleshooting purposes (Recommended).</span>
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
				<th><label>To</label></th>
				<td><input type="text" class="pm-test-email" value="" placeholder="recipient@example.com" /></td>
			</tr>
			<tr>
				<th><label>From (optional)</label></th>
				<td><input type="text" class="pm-test-email-sender" value="" placeholder="sender@example.com" /></td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="with_tracking_and_html" class="pm-test-with-opens" value="" />Send test as HTML, with Open and Link Tracking enabled.</td>
			</tr>
		</table>

		<div class="submit">
			<input type="submit" class="button-primary send-test" value="Send Test Email" />
		</div>
	</div>

	<div class="tab-content tab-overrides">
		<h2>Developer overrides</h2>
		<p>Instead of using <code>Force HTML</code>, we recommend setting <code>wp_mail</code> headers when possible.</p>
		<pre>
		$headers = array();

		// Override the default 'From' address
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
				<th><label>Headers</label></th>
				<td>
					<textarea name="pm-plugin-test-headers" class="pm-plugin-test-headers" cols=80 placeholder="Reply-To: john@example.com"></textarea>
				</td>
			</tr>
			<tr>
				<th><label>Subject</label></th>
				<td>
					<input type="text" name="pm-plugin-test-subject" class="pm-plugin-test-subject" placeholder="Dear Emily, I just wanted to say hello..."/>
				</td>
			</tr>
			<tr>
				<th><label>Body</label></th>
				<td>
					<textarea name="pm-plugin-test-body" class="pm-plugin-test-body" placeholder="Hi there!" cols=80 ></textarea>
				</td>
			</tr>
			<tr>
				<th><label>To Address</label></th>
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
