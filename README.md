<a href="https://postmarkapp.com">
    <img src="assets/images/logo.png" alt="Postmark Logo" title="ActiveCampaign Postmark" width="210" height="46" align="right">
</a>

# ActiveCampaign Postmark for WordPress

If you’re still sending email with default SMTP, you’re blind to delivery problems! ActiveCampaign Postmark for WordPress enables sites of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance.

If you don’t already have an ActiveCampaign Postmark account, you can get one in minutes, sign up at https://postmarkapp.com.

## Installation

1. Upload postmark directory to your /wp-content/plugins directory or install from plugin marketplace
2. Activate plugin in WordPress admin
3. In WordPress admin, go to **Settings** then **ActiveCampaign Postmark**. You will then want to insert your ActiveCampaign Postmark details. If you don’t already have an ActiveCampaign Postmark account, get one at https://postmarkapp.com.
4. Verify sending by entering a recipient email address you have access to and pressing the “Send Test Email” button. Enable logging for troubleshooting and to check the send result.
5. Once everything is verified as working, check **Send emails using ActiveCampaign Postmark** and Save, to override `wp_mail` to send using the ActiveCampaign Postmark API instead.

## FAQ

### What is ActiveCampaign Postmark?
ActiveCampaign Postmark is a hosted service that expertly handles all delivery of transactional webapp and web site email. This includes welcome emails, password resets, comment notifications, and more. If you've ever installed WordPress and had issues with PHP's `mail` function not working right, or your WordPress install sends comment notifications or password resets to spam, ActiveCampaign Postmark makes all of these problems vanish in seconds. Without ActiveCampaign Postmark, you may not even know you're having delivery problems. Find out in seconds by installing and configuring this plugin.

### Will this plugin work with my WordPress site?

The ActiveCampaign Postmark for WordPress plugin overrides any usage of the `wp_mail` function. Because of this, if any 3rd party code or plugins send mail directly using the PHP mail function, or any other method, we cannot override it. Please contact the makers of any offending plugins and let them know that they should use `wp_mail` instead of unsupported mailing functions.

### TLS Version Requirements/Compatibility

The ActiveCampaign Postmark API requires TLS v1.1 or v1.2 support. We recommend using TLS v1.2.

You can check your TLS v1.2 compatibility using [this plugin](https://wordpress.org/plugins/tls-1-2-compatibility-test/). After installing the plugin, change the dropdown for 'Select API Endpoint' to _How's My SSL?_ and run the test. If compatibility with TLS v1.2 is not detected, contact your server host or make the necessary upgrades to support TLS v1.1 or v1.2.

TLS 1.2 requires:

- PHP 5.5.19 or higher
- cURL 7.34.0 or higher
- OpenSSL 1.0.1 or higher

### Does this cost me money?

The ActiveCampaign Postmark service (and this plugin) are free to get started, for up to 100 emails a month. You can sign up at https://postmarkapp.com/. When you need to process more than 100 emails a month, ActiveCampaign Postmark offers monthly plans to fit your needs.

### My emails are still not sending, or they are going to spam! HELP!?

First, enable logging in **Settings** and check your send attempts for any errors returned by the ActiveCampaign Postmark API. These errors can let you know why the send attempts are failing. If you aren't seeing log entries for your send attempts, then the plugin or contact form generating the emails is likely not using `wp_mail` and not compatible with this plugin.

If you are still unsure how to proceed, just send an email to [support@postmarkapp.com](mailto:support@postmarkapp.com) or tweet [@postmarkapp](https://twitter.com/postmarkapp) for help. Be sure to include as much detail as possible.

### Why should I trust you with my email sending?

Because we've been in this business for many years. We’ve been running an email marketing service, Newsberry, for five years. Through trial and error we already know what it takes to manage a large volume of email. We handle things like whitelisting, ISP throttling, reverse DNS, feedback loops, content scanning, and delivery monitoring to ensure your emails get to the inbox.

Most importantly, a great product requires great support and even better education. Our team is spread out across six time zones to offer fast support on everything from using ActiveCampaign Postmark to best practices on content and user engagement. A solid infrastructure only goes so far, that’s why improving our customer’s sending practices helps achieve incredible results.

### How do I tag a message?

There are two ways to tag a message.

1. Set an `X-PM-Tag` message header, i.e. `array_push( $headers, 'X-PM-Tag: PostmarkPluginTest' );` where you are calling `wp_mail()`.

2. Add a filter for `postmark_tag` that hooks into a function that returns the tag you desire.

Using the `postmark_tag` filter option will override a tag set via message headers.

### Why aren't my HTML emails being sent?

This plugin detects HTML by checking the headers sent by other WordPress plugins. If a "text/html" content type isn't set then this plugin won't send the HTML to ActiveCampaign Postmark to be sent out only the plain text version of the email.

### Why are password reset links not showing in emails sent with this plugin?

There are a couple ways to resolve this issue.

1. Open the ActiveCampaign Postmark plugin settings and uncheck Force HTML and click Save Changes. If the default WordPress password reset email is sent in Plain Text format, the link will render as expected.

2. Access your WordPress site directory and open the `wp-login.php` file.

Change this line:

    $message .= ‘<‘ . network_site_url(“wp-login.php?action=rp&key=$key&login=” . rawurlencode($user_login), ‘login’) . “>\r\n”;

Remove the brackets, so it becomes:

    $message .= network_site_url(“wp-login.php?action=rp&key=$key&login=” . rawurlencode($user_login), ‘login’) . “\r\n”;

And save the changes to the file.

### How do I set the from name?

The plugin supports using the `wp_mail_from_name` filter for manually setting a name in the From header.

## Additional Resources

[ActiveCampaign Postmark for WordPress FAQ](https://postmarkapp.com/support/article/1138-postmark-for-wordpress-faq)

[Can I use the ActiveCampaign Postmark for WordPress plugin with Gravity Forms?](https://postmarkapp.com/support/article/1129-can-i-use-the-postmark-for-wordpress-plugin-with-gravity-forms)

[How do I send with Ninja Forms and ActiveCampaign Postmark for WordPress?](https://postmarkapp.com/support/article/1047-how-do-i-send-with-ninja-forms-and-postmark-for-wordpress)

[How do I send with Contact Form 7 and ActiveCampaign Postmark for WordPress?](https://postmarkapp.com/support/article/1072-how-do-i-send-with-contact-form-7-and-postmark-for-wordpress)

[Can I use the ActiveCampaign Postmark for WordPress plugin with Divi contact forms?](https://postmarkapp.com/support/article/1128-can-i-use-the-postmark-for-wordpress-plugin-with-divi-contact-forms)

## Changelog
## [1.18.0]
- Add support for tags.

--------

[See the previous changelogs here](https://github.com/ActiveCampaign/postmark-wordpress/blob/master/CHANGELOG.md)

ActiveCampaign, 2022
