<?php
/**
 * Settings Page.
 *
 * @package postmark
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


?>	
	
<script>
var postmark = postmark || {};
postmark.settings = <?php echo wp_json_encode( $this->settings ); ?>;
</script>

<?php
wp_enqueue_style( 'postmark-admin', POSTMARK_URL . '/assets/css/admin.css' , '', null, 'all' );
wp_enqueue_script( 'postmark-admin', POSTMARK_URL . '/assets/js/admin.js', array( 'jquery' ), null, true );
?>

<?php wp_nonce_field( 'postmark_nonce' ); ?>
<div class="wrap">
	<div class="logo-bar">
		<a href="https://postmarkapp.com/" target="_blank"><img src="<?php echo esc_url( POSTMARK_URL ); ?>/assets/images/logo.png" width="130" height="21" alt="" /></a>
	</div>

	<h1 class="nav-tab-wrapper">
		<a class="nav-tab" rel="general"><?php echo esc_html_e( 'General', 'postmark' ); ?></a>
		<a class="nav-tab" rel="test"><?php echo esc_html_e( 'Send Test Email', 'postmark' ); ?></a>
		<a class="nav-tab" rel="overrides"><?php echo esc_html_e( 'Overrides', 'postmark' ); ?></a>

		<?php if ( isset( $_ENV['POSTMARK_PLUGIN_TESTING'] ) && 'POSTMARK_PLUGIN_TESTING' === $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
			<a class="nav-tab" rel="plugin-testing"><?php echo esc_html_e( 'Plugin Testing', 'postmark' ); ?></a>
		<?php endif; ?>
	</h1>

	<div class="updated notice pm-notice hidden"></div>

	<div class="tab-content tab-general">
		<table class="form-table" style="max-width:740px;">
			<tr>
				<th><label><?php echo esc_html_e( 'Enabled?', 'postmark' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-enabled" value="1" />
					<span class="footnote"><?php echo esc_html_e( 'Send emails using Postmark', 'postmark' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'API Key', 'postmark' ); ?></label></th>
				<td>
					<input type="text" class="pm-api-key" value="" />
					<div class="footnote"><?php echo esc_html_e( 'Your API key is in the', 'postmark' ); ?> <strong><?php echo esc_html_e( 'Credentials', 'postmark' ); ?></strong> <?php echo esc_html_e( 'screen of your ', 'postmark' ); ?><a href="https://account.postmarkapp.com/servers" target="_blank"><?php echo esc_html_e( 'Postmark Server', 'postmark' ); ?></a>.</div>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'Sender Email', 'postmark' ); ?></label></th>
				<td>
					<input type="text" class="pm-sender-address" value="" />
					<div class="footnote"><?php echo esc_html_e( 'This email must be a verified', 'postmark' ); ?> <a href="https://account.postmarkapp.com/signatures" target="_blank"><?php echo esc_html_e( 'Sender Signature', 'postmark' ); ?></a>. <?php echo esc_html_e( 'It will appear as the "from" address on all outbound emails.', 'postmark' ); ?></div>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'Force HTML', 'postmark' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-force-html" value="1" />
					<span class="footnote"><?php echo esc_html_e( 'Force emails to be sent as HTML.', 'postmark' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'Track Opens', 'postmark' ); ?></label></th>
				<td>
					<input type="checkbox" class="pm-track-opens" value="1" />
					<span class="footnote"><?php echo esc_html_e( 'Track email opens (', 'postmark' ); ?><code><?php echo esc_html_e( 'Force HTML', 'postmark' ); ?></code> <?php echo esc_html_e( 'is required).', 'postmark' ); ?></span>
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
				<th><label><?php echo esc_html_e( 'To', 'postmark' ); ?></label></th>
				<td><input type="text" class="pm-test-email" value="" placeholder="recipient@example.com" /></td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'From (optional)', 'postmark' ); ?></label></th>
				<td><input type="text" class="pm-test-email-sender" value="" placeholder="sender@example.com" /></td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="with_tracking_and_html" class="pm-test-with-opens" value="" /><?php echo esc_html_e( 'Send test as HTML, with Open Tracking enabled.', 'postmark' ); ?></td>
			</tr>
		</table>

		<div class="submit">
			<input type="submit" class="button-primary send-test" value="Send Test Email" />
		</div>
	</div>

	<div class="tab-content tab-overrides">
		<h2><?php echo esc_html_e( 'Developer overrides', 'postmark' ); ?></h2>
		<p>Instead of using <code>Force HTML</code>, we recommend setting <code>wp_mail</code> headers when possible.</p>
		<pre>
		$headers = array();

		// Override the default 'From' address
		$headers['From'] = 'john.smith@example.com';

		// Send the message as HTML
		$headers['Content-Type'] = 'text/html';

		// Enable open tracking (requires HTML email enabled)
		$headers['X-PM-Track-Opens'] = true;

		// Send the email
		$response = wp_mail( $to, $subject, $message, $headers );
		</pre>
		To learn more about <code>wp_mail</code>, see the <a href="https://developer.wordpress.org/reference/functions/wp_mail/">WordPress Codex page.</a>
	</div>
	<?php if ( isset( $_ENV['POSTMARK_PLUGIN_TESTING'] ) && 'POSTMARK_PLUGIN_TESTING' === $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
	<div class="tab-content tab-plugin-testing">
		<table class="form-table" style="max-width:740px;">
			<tr>
				<th><label><?php echo esc_html_e( 'Headers', 'postmark' ); ?></label></th>
				<td>
					<textarea name="pm-plugin-test-headers" class="pm-plugin-test-headers" cols=80 placeholder="Reply-To: john@example.com"></textarea>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'Subject', 'postmark' ); ?></label></th>
				<td>
					<input type="text" name="pm-plugin-test-subject" class="pm-plugin-test-subject" placeholder="Dear Emily, I just wanted to say hello..."/>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'Body', 'postmark' ); ?></label></th>
				<td>
					<textarea name="pm-plugin-test-body" class="pm-plugin-test-body" placeholder="Hi there!" cols=80 ></textarea>
				</td>
			</tr>
			<tr>
				<th><label><?php echo esc_html_e( 'To Address', 'postmark' ); ?></label></th>
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
