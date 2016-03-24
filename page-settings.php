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

        //if we enable the track opens, we should also enable the force html.
        $('.pm-track-opens').click(function(){
            if ($(this).is(':checked')) {
                $('.pm-force-html').attr('checked', 'checked');
            }
        });

        $('.pm-force-html').click(function(){
            if (!$(this).is(':checked')) {
                $('.pm-track-opens').removeAttr('checked');
            }
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
    <div style="background: #FFDE00; padding: 10px; border-radius: 5px">
        <h1>
            <a href="https://postmarkapp.com/" target="_blank"><img src="<?php echo POSTMARK_URL; ?>/images/logo.png" width="130" height="21" alt="" /></a>
        </h1>
    </div>
    <div class="updated notice pm-notice hidden"></div>

    <table class="form-table" style="max-width:400px;">
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
                <div class="footnote">This email must be a verified <a href="https://account.postmarkapp.com/signatures" target="_blank">Sender Signature</a>. It will appear as the "from" address on all outbound emails.<br/><br/>
                You may override the "From" address set here on individual emails, by including a 'From' header with the address you wish to send from. This address must still be a confirmed Sender Signature associated with your account on Postmark.<br/><br/>
                For information on setting headers using the wp_mail function, <a href="https://developer.wordpress.org/reference/functions/wp_mail/">see the Wordpress Codex page.</a>
                </div>
            </td>
        </tr>
        <tr>
            <th><label>Force HTML</label></th>
            <td>
                <input type="checkbox" class="pm-force-html" value="1" />
                <span class="footnote">Force emails to be sent as HTML.<br/><br/>DEPRECATED: Instead of enabling this feature, add a header to your HTML message with name 'Content-Type' and value 'text/html'. "Forcing HTML" for Wordpress-generated emails (such as password reset emails) will cause them to be sent as HTML, this is often incorrect. Instead, individual messages should include the header above to have them treated as HTML instead of plain text.
                <br/><br/>
                For information on setting headers using the wp_mail function, <a href="https://developer.wordpress.org/reference/functions/wp_mail/">see the Wordpress Codex page.</a></span>
            </td>
        </tr>
        <tr>
            <th><label>Track Opens</label></th>
            <td>
                <input type="checkbox" class="pm-track-opens" value="1" />
                <span class="footnote">Track email opens (which also requires emails to be "forced" to HTML).<br/><br/>DEPRECATED: Instead of enabling this feature, add a header to your HTML message called 'X-PM-Track-Opens' and a value of 'true'. See the explanation of why this is the preferred method in the description of the 'Force HTML' feature.<br/><br/>
                For information on setting headers using the wp_mail function, <a href="https://developer.wordpress.org/reference/functions/wp_mail/">see the Wordpress Codex page.</a></span>
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
            <td><input type="text" class="pm-test-email" value="" /></td>
        </tr>
    </table>

    <div class="submit">
        <input type="submit" class="button-primary send-test" value="Send Test Email" />
    </div>
</div>
