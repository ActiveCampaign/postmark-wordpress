(function ($) {
    $(function() {
        var settings = postmark.settings;
        var logs_offset = 10;
        $('.pm-enabled').prop('checked', settings.enabled);
        $('.pm-api-key').val(settings.api_key);
        $('.pm-stream-name').val(settings.stream_name);
        $('.pm-sender-address').val(settings.sender_address);
        $('.pm-force-html').prop('checked', settings.force_html);
        $('.pm-track-opens').prop('checked', settings.track_opens);
        $('.pm-track-links').prop('checked', settings.track_links);
        $('.pm-enable-logs').prop('checked', settings.enable_logs);

        // save
        $(document).on('click', '.save-settings', function() {
            var data = {
                'enabled': $('.pm-enabled').is(':checked') ? 1 : 0,
                'api_key': $('.pm-api-key').val(),
                'stream_name': $('.pm-stream-name').val() ? $('.pm-stream-name').val() : 'outbound',
                'sender_address': $('.pm-sender-address').val(),
                'force_html': $('.pm-force-html').is(':checked') ? 1 : 0,
                'track_opens': $('.pm-track-opens').is(':checked') ? 1 : 0,
                'track_links': $('.pm-track-links').is(':checked') ? 1 : 0,
                'enable_logs': $('.pm-enable-logs').is(':checked') ? 1 : 0
            };

            $.post(ajaxurl, {
                '_wpnonce' : $('#_wpnonce').val(),
                'action': 'postmark_save',
                'data': JSON.stringify(data)
            }, function(response) {
                $('.pm-notice').html('<p>' + response + '</p>');
                $('.pm-notice').removeClass('hidden');

                // Immediately hides logs tab if logs were disabled.
                if (data.enable_logs == 1) {
                  $('#pm-log-nav-tab').show();
                } else {
                  $('#pm-log-nav-tab').hide();
                }
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

        // Loads more logs when 'Load More' button is clicked.
        $(document).on('click', '.load-more', function() {

          var data = {
            action: 'postmark_load_more_logs',
            offset: logs_offset,
            _wpnonce : $('#_wpnonce').val()
          };

          // Prepares for next 'Load More' by increasing the offset amount for next query.
          logs_offset += 10;

          $.post( ajaxurl, data, function( response ) {

            // Parses response string into JSON.
            response = JSON.parse(response);

            // Adds new logs to logs table.
            $('#pm-log-table tr:last').after(response.html);

            // Tracks the number of total logs present in logs table.
            var has_more = response.has_more;

            // Hides the 'Load More' button if no additional logs to display.
            if (!has_more) {
              $('.load-more').hide();
            }
          });
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