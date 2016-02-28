<script>
(function($) {
    $(function() {
        var settings = <?php echo json_encode( $this->settings ); ?>;
        $('.pm-enabled').prop('checked', settings.enabled);
        $('.pm-api-key').val(settings.api_key);
        $('.pm-sender-address').val(settings.sender_address);
        $('.pm-force-html').prop('checked', settings.force_html);
        $('.pm-track-opens').prop('checked', settings.track_opens);

        $(document).on('click', '.save-settings', function() {
            var data = {
                'enabled': $('.pm-enabled').is(':checked') ? 1 : 0,
                'api_key': $('.pm-api-key').val(),
                'sender_address': $('.pm-sender-address').val(),
                'force_html': $('.pm-force-html').is(':checked') ? 1 : 0,
                'track_opens': $('.pm-track-opens').is(':checked') ? 1 : 0
            };

            $.post(ajaxurl, {
                'action': 'postmark_save',
                'data': JSON.stringify(data)
            }, function(response) {
                $('.pm-notice').html('<p>' + response + '</p>');
                $('.pm-notice').removeClass('hidden');
            });
        });

        $(document).on('click', '.send-test', function() {
            $.post(ajaxurl, {
                'action': 'postmark_test',
                'email': $('.pm-test-email').val()
            }, function(response) {
                $('.pm-notice').html('<p>' + response + '</p>');
                $('.pm-notice').removeClass('hidden');
            });
        });
    });
})(jQuery);
</script>

<style>
.footnote {
    font-size: 13px;
}
input[type=text] {
    width: 300px;
}
</style>

<div class="wrap">
    <h1>
        <a href="https://postmarkapp.com/" target="_blank"><img src="<?php echo POSTMARK_URL; ?>/images/logo.png" width="130" height="21" alt="" /></a>
    </h1>
    <div class="updated notice pm-notice hidden"></div>

    <table class="form-table">
        <tr>
            <th><label>Enabled?</label></th>
            <td>
                <input type="checkbox" class="pm-enabled" value="1" />
                <span class="footnote">Send emails using Postmark's REST API</span>
            </td>
        </tr>
        <tr>
            <th><label>API Key</label></th>
            <td>
                <input type="text" class="pm-api-key" value="" />
                <div class="footnote">Your API key is available in the <strong>Credentials</strong> screen of your <a href="https://account.postmarkapp.com/servers" target="_blank">Postmark Server</a>.</div>
            </td>
        </tr>
        <tr>
            <th><label>Sender Email Address</label></th>
            <td>
                <input type="text" class="pm-sender-address" value="" />
                <div class="footnote">This email must be a verified <a href="https://account.postmarkapp.com/signatures" target="_blank">Sender Signature</a>. It will appear as the "from" address on all outbound emails.</div>
            </td>
        </tr>
        <tr>
            <th><label>Force HTML</label></th>
            <td>
                <input type="checkbox" class="pm-force-html" value="1" />
                <span class="footnote">Force emails to be sent as HTML</span>
            </td>
        </tr>
        <tr>
            <th><label>Track Opens</label></th>
            <td>
                <input type="checkbox" class="pm-track-opens" value="1" />
                <span class="footnote">Track email opens (forces HTML mode)</span>
            </td>
        </tr>
    </table>

    <div class="submit">
        <input type="submit" class="button-primary save-settings" value="Save Changes" />
    </div>

    <h3>Send Test Email</h3>
    <table class="form-table">
        <tr>
            <th><label>Recipient</label></th>
            <td><input type="text" class="pm-test-email" value="<?php echo get_option('postmark_sender_address'); ?>" class="regular-text"/></td>
        </tr>
    </table>

    <div class="submit">
        <input type="submit" class="button-primary send-test" value="Send Test Email" />
    </div>
</div>
