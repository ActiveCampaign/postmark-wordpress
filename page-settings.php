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
        <a class="nav-tab" rel="general"><?php esc_html_e('General', 'postmark-wordpress'); ?></a>
        <a class="nav-tab" rel="test"><?php esc_html_e('Sent Test Email', 'postmark-wordpress'); ?></a>
        <a class="nav-tab" rel="overrides"><?php esc_html_e('Overrides', 'postmark-wordpress'); ?></a>
        <?php if($_ENV['POSTMARK_PLUGIN_TESTING'] == 'POSTMARK_PLUGIN_TESTING'){ ?><a class="nav-tab" rel="plugin-testing"><?php esc_html_e('Plugin Testing', 'postmark-wordpress'); ?></a><? }?>
    </h1>

    <div class="updated notice pm-notice hidden"></div>

    <div class="tab-content tab-general">
        <table class="form-table" style="max-width:740px;">
            <tr>
                <th><label><?php esc_html_e('Enabled?', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="checkbox" class="pm-enabled" value="1" />
                    <span class="footnote"><?php esc_html_e('Send emails using Postmark', 'postmark-wordpress'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('API Key', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="text" class="pm-api-key" value="" />
                    <div class="footnote">
                    	<?php printf( __('Your API key is in the <strong>Credentials</strong> screen of your %s.', 'postmark-wordpress'), '<a href="https://account.postmarkapp.com/servers" target="_blank" rel="noopener noreferrer">' . __( 'Postmark Server', 'postmark-wordpress') . '</a>' ); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Sender Email', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="text" class="pm-sender-address" value="" />
                    <div class="footnote">
                    	<?php printf( __('This email must be a verified %s It will appear as the "from" address on all outbound emails.', 'postmark-wordpress'), '<a href="https://account.postmarkapp.com/signatures" target="_blank" rel="noopener noreferrer">' . __( 'Sender Signature', 'postmark-wordpress') . '</a>' ); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Force HTML', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="checkbox" class="pm-force-html" value="1" />
                    <span class="footnote"><?php esc_html_e('Force emails to be sent as HTML', 'postmark-wordpress'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Track Opens', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="checkbox" class="pm-track-opens" value="1" />
                    <span class="footnote"><?php _e('Track email opens (<code>Force HTML</code> is required).', 'postmark-wordpress'); ?></span>
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
                <th><label><?php esc_html_e('To', 'postmark-wordpress'); ?></label></th>
                <td><input type="text" class="pm-test-email" value="" placeholder="<?php esc_attr_e('recipient@example.com', 'postmark-wordpress'); ?>" /></td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('From (optional)', 'postmark-wordpress'); ?></label></th>
                <td><input type="text" class="pm-test-email-sender" value="" placeholder="<?php esc_attr_e('sender@example.com', 'postmark-wordpress'); ?>" /></td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" name="with_tracking_and_html" class="pm-test-with-opens" value="" /><?php esc_html_e('Send test as HTML, with Open Tracking enabled', 'postmark-wordpress'); ?></td>
            </tr>
        </table>

        <div class="submit">
            <input type="submit" class="button-primary send-test" value="<?php esc_attr_e('Send Test Email', 'postmark-wordpress'); ?>" />
        </div>
    </div>

    <div class="tab-content tab-overrides">
        <h2><?php esc_html_e('Developer overrides', 'postmark-wordpress'); ?></h2>
        <p><?php _e('Instead of using <code>Force HTML</code>, we recommend setting <code>wp_mail</code> headers when possible.', 'postmark-wordpress'); ?></p>
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
        <?php sprintf( __('To learn more about <code>wp_mail</code>, see the %s', 'postmark-wordpress'), '<a href="https://developer.wordpress.org/reference/functions/wp_mail/">WordPress Codex page.</a>' ); ?>
    </div>
    <?php if($_ENV['POSTMARK_PLUGIN_TESTING'] == 'POSTMARK_PLUGIN_TESTING'){ ?>
    <div class="tab-content tab-plugin-testing">
        <table class="form-table" style="max-width:740px;">
            <tr>
                <th><label><?php esc_html_e('Headers', 'postmark-wordpress'); ?></label></th>
                <td>
                    <textarea name="pm-plugin-test-headers" class="pm-plugin-test-headers" cols=80 placeholder="<?php esc_attr_e('Reply-To: john@example.com', 'postmark-wordpress'); ?>"></textarea>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Subject', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="text" name="pm-plugin-test-subject" class="pm-plugin-test-subject" placeholder="<?php esc_attr_e('Dear Emily, I just wanted to say hello...', 'postmark-wordpress'); ?>"/>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Body', 'postmark-wordpress'); ?></label></th>
                <td>
                    <textarea name="pm-plugin-test-body" class="pm-plugin-test-body" placeholder="<?php esc_attr_e('Hi there!', 'postmark-wordpress'); ?>" cols=80 ></textarea>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('To Address', 'postmark-wordpress'); ?></label></th>
                <td>
                    <input type="text" name="pm-plugin-test-to-address" class="pm-plugin-test-to-address" value="" placeholder="<?php esc_attr_e('emily@example.com', 'postmark-wordpress'); ?>" />
                </td>
            </tr>
        </table>

        <div class="submit">
            <input type="submit" class="button-primary plugin-send-test" value="<?php esc_attr_e('Send Test Message', 'postmark-wordpress'); ?>" />
        </div>
    </div>
    <? }?>
</div>
