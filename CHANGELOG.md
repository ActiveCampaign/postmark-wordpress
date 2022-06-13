# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.16.0]
- Added a Status tab to the plugin's Settings page for quickly checking [Postmark's Status](https://status.postmarkapp.com/).

## [1.15.7]

-  ActiveCampaign branding update.

## [1.15.6]

-  Do wp_mail_succeeded action after successful sends (introduced in WordPress v5.9).

## [1.15.5]

-  Honour pre_wp_mail filters.

## [1.15.4]

-  Fix notice when overriding force_from setting.

## [1.15.3]

-  Ensure Postmark plugin is loaded before attempting to load settings from it after upgrading.

## [1.15.2]

-  Use correct admin.js file version.

## [1.15.1]

-  Bugfix for using Force From setting.

## [1.15.0]

-  Adds new Force From setting to allow preventing override of From address using headers, if desired.

## [1.14.0]

-  Adds ability to override settings for environment specific Postmark plugin settings.

## [1.13.7]

-  Fix limit of 500 sending logs deleted per day.

## [1.13.6]

-  Even better handling of apostrophes in email address From names.

## [1.13.5]

-  Handle apostrophes in email address From names. These are sometimes used in site titles, which can be the default From address name with other plugins.

## [1.13.4]

-  Handle special characters in site titles for test emails.

## [1.13.3]

-  Additional bugfix for using wp_mail_from_name filter.

## [1.13.2]

-  Fixes error when upgrading by ensuring $postmark is set before trying to load settings.

## [1.13.1]

-  Fixes error from using incorrect filter name and mailparse_rfc822_parse_addresses function.

## [1.13.0]

-  Adds support for using the wp_mail_from_name filter to specify a from_name when sending.

## [1.12.5]

-  Fixes 'POSTMARK_DIR is undefined' warning when upgrading other plugins via the CLI.

## [1.12.4] - 2020-05-11

-  Fixes potential collation mismatch errors from date comparisons during old sending logs deletion.

## [1.12.3]

-  Uses count() for check of logs query result count.

## [1.12.2]

-  Corrects SQL for deletion of log entries older than 7 days.

## [1.12.1]

-  Checks if stream_name is set in settings before determining which stream to use.

## [1.12]

-  Adds support for message streams.

## [1.11.6]

-  Updates server API token location hint in plugin settings.

## [1.11.5]

-  Allows using POSTMARK_API_TEST in the plugin settings for generating test send requests that aren't actually delivered.

## [1.11.4]

-  Fixes handling of situation where call to Postmark API results in WP_Error instead of array for response, such as during incidents of the API being offline and not returning a response.

## [1.11.3]

-  Fixes log page display of From/To addresses including the From/To names. Only email addresses will now appear in logs page, to avoid confusion, while also preserving the sanitation of email addresses before inserting into db.

## [1.11.2] - 2019-02-05

-  Fixes no index error with track links check in wp_mail.

## [1.11.1]

-  Fixes call of non-global load_settings function during upgrade.

## [1.11.0] - 2018-12-13

-  Adds link tracking support.
-  Fixes send test with HTML/open tracking option not being honored in sent test email.

## [1.10.6]

-  Fixes undefined index error.
-  Adds Upgrade Notice

## [1.10.5]

-  Corrects logs deletion cron job unscheduling issue.

## [1.10.4]

-  Removes index on logs table.

## [1.10.3]

-  Corrects version mismatch in constructor.

## [1.10.1]

-  Adds a new logging feature that can be enabled to store logs for send attempts. Logs include Date, From address, To address, Subject, and Postmark API response. Logs are displayed in a Logs tab in the plugin setting once enabled.
-  Switch loading of JS/CSS to use enqueue()

## [1.9.6]

-  Resolves issue when saving settings in UI.
-  Falls attachment Content-Type back to 'application/octet-stream' when other methods fail.

## [1.9.5]

-  Update javascript to fix settings update issue.

## [1.9.4]

-  Added `postmark_error` and `postmark_response` actions to the plugin, to intercept API results after calling wp_mail. You can register callbacks for these using `add_action` (more info here: https://developer.wordpress.org/reference/functions/add_action/)

## [1.9.3]

-  Interface cleanup
-  Minor code restructuring

## [1.9.2]

-  Make the errors available in the PHP variable `Postmark_Mail::$LAST_ERROR` if `wp_mail()` returns false, examine this variable to find out what happened.
-  When doing a test send, if an error occurs, it will be printed in the settings page header.

## [1.9.1]

-  Fix case where 'From' header is specified as a literal string, instead of in an associative array.

## [1.9] - 2016-04-01

-  Allow the 'From' header to override the default sender.
-  Don't send TextBody when the user has specified the 'Content-Type' header of 'text/html'
-  Allow individual messages to opt-in to Track-Opens by including a header of 'X-PM-Track-Opens' and a value of `true`

## [1.8]

-  Modernization of codebase.

## [1.7]

-  Support headers for cc, bcc, and reply-to

## [1.6]

-  Added open tracking support.

## [1.5]

-  Fix issue with new WordPress HTTP API Integration.

## [1.4]

-  New option to force emails to be sent as HTML. Previously just detected Content-Type.
-  Now uses the WordPress HTTP API.

## [1.3]

-  Resolved error with handing arrays of recipients

## [1.2]

-  Arrays of recipients are now properly handled
-  HTML emails and Text Emails are now handled by checking the headers of the emails sent, and sends via Postmark appropriately.
-  Optional "Powered by Postmark" footer of sent emails. "Postmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com"
-  Add license to README and PHP file

## [1.0.0]

-  First Public release.
