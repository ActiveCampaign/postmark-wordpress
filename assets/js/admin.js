(function ($) {
    $(function() {
        var settings = postmark.settings;
        $('.pm-enabled').prop('checked', settings.enabled);
        $('.pm-api-key').val(settings.api_key);
        $('.pm-sender-address').val(settings.sender_address);
        $('.pm-force-html').prop('checked', settings.force_html);
        $('.pm-track-opens').prop('checked', settings.track_opens);

        // save
        $(document).on('click', '.save-settings', function() {
            var data = {
                'enabled': $('.pm-enabled').is(':checked') ? 1 : 0,
                'api_key': $('.pm-api-key').val(),
                'sender_address': $('.pm-sender-address').val(),
                'force_html': $('.pm-force-html').is(':checked') ? 1 : 0,
                'track_opens': $('.pm-track-opens').is(':checked') ? 1 : 0
            };

            $.post(ajaxurl, {
                '_wpnonce' : $('#_wpnonce').val(),
                'action': 'postmark_save',
                'data': JSON.stringify(data)
            }, function(response) {
                $('.pm-notice').html('<p>' + response + '</p>');
                $('.pm-notice').removeClass('hidden');
            });
        });

        // send test email
        $(document).on('click', '.send-test', function() {
            $.post(ajaxurl, {
                '_wpnonce' : $('#_wpnonce').val(),
                'action': 'postmark_test',
                'email': $('.pm-test-email').val(),
                'with_tracking_and_html': $('.pm-test-with-opens').is(':checked') ? 1 : 0,
                'override_from_address' : $('.pm-test-email-sender').val()
            }, function(response) {
                $('.pm-notice').html('<p>' + response + '</p>');
                $('.pm-notice').removeClass('hidden');
            });
        });

        // tab handler
        $(document).on('click', '.nav-tab', function() {
            var which = $(this).attr('rel');
            $('.nav-tab').removeClass('nav-tab-active');
            $('.tab-content').removeClass('active');
            $(this).addClass('nav-tab-active');
            $('.tab-' + which).addClass('active');
        });

        // force html if track opens is enabled
        $(document).on('click', '.pm-track-opens', function() {
            if ($(this).is(':checked')) {
                $('.pm-force-html').attr('checked', 'checked');
            }
        });

        // uncheck track opens if force html is disabled
        $(document).on('click', '.pm-force-html', function() {
            if (! $(this).is(':checked')) {
                $('.pm-track-opens').removeAttr('checked');
            }
        });

        // trigger active tab
        $('.nav-tab:first').click();
    });
})(jQuery);