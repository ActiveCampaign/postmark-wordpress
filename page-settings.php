<script>
var postmark = postmark || {};
postmark.settings = <?php echo json_encode( $this->settings ); ?>;
</script>
<script src="<?php echo POSTMARK_URL; ?>/assets/js/admin.js"></script>
<link href="<?php echo POSTMARK_URL; ?>/assets/css/admin.css" rel="stylesheet">
<?php wp_nonce_field( 'postmark_nonce' ); ?>
<div class="wrap">
    <div class="logo-bar">
        <a href="https://postmarkapp.com/" target="_blank"><img src="<?php echo POSTMARK_URL; ?>/assets/images/logo.png" width="130" height="21" alt="" /></a>
    </div>

    <h1 class="nav-tab-wrapper">
        <a class="nav-tab" rel="general">General</a>
        <a class="nav-tab" rel="test">Send Test Email</a>
        <a class="nav-tab" rel="overrides">Overrides</a>

       <?php if ( isset($_ENV['POSTMARK_PLUGIN_TESTING']) && 'POSTMARK_PLUGIN_TESTING' == $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
			<a class="nav-tab" rel="plugin-testing">Plugin Testing</a>
		<?php endif; ?>
    </h1>

    <div class="updated notice pm-notice hidden"></div>

    <div class="tab-content tab-general">
        <table class="form-table" style="max-width:740px;">
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
                    <div class="footnote">Your API key is in the <strong>Credentials</strong> screen of your <a href="https://account.postmarkapp.com/servers" target="_blank">Postmark Server</a>.</div>
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
                <td colspan="2"><input type="checkbox" name="with_tracking_and_html" class="pm-test-with-opens" value="" />Send test as HTML, with Open Tracking enabled.</td>
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

        // Send the email
        $response = wp_mail( $to, $subject, $message, $headers );
        </pre>
        To learn more about <code>wp_mail</code>, see the <a href="https://developer.wordpress.org/reference/functions/wp_mail/">WordPress Codex page.</a>
    </div>
   <?php if ( isset($_ENV['POSTMARK_PLUGIN_TESTING']) &&'POSTMARK_PLUGIN_TESTING' == $_ENV['POSTMARK_PLUGIN_TESTING'] ) : ?>
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
