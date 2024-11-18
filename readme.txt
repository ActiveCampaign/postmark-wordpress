=== ActiveCampaign Postmark for Wordpress ===
Contributors: andy7629, alexknowshtml, mgibbs189, jptoto, atheken, prileygraham, dorzki
Tags: postmark, email, smtp, notifications, wp_mail, wildbit
Requires PHP: 7.0
Requires at least: 5.3
Tested up to: 6.7
Stable tag: 1.19.1

The *officially-supported* ActiveCampaign Postmark plugin for Wordpress.

== Description ==

If you're still sending email with default SMTP, you're blind to delivery problems! ActiveCampaign Postmark for Wordpress enables sites of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance.

If you don't already have a Postmark account, you can get one in minutes, sign up at https://postmarkapp.com

Check out our video on how to set up the Postmark for WordPress plugin [here](https://postmarkapp.com/webinars/postmark-wordpress).

== Installation ==

1. Upload postmark directory to your /wp-content/plugins directory
2. Activate plugin in WordPress admin
3. In WordPress admin, go to Settings then Postmark. You will then want to insert your Postmark details. If you don't already have a Postmark account, get one at https://postmarkapp.com
4. Verify sending by entering a recipient email address you have access to and pressing the "Send Test Email" button.
5. Once verified, then check "Enable" to override `wp_mail` and send using the Postmark API instead.

== Frequently Asked Questions ==

= What is Postmark? =

Postmark is a hosted service that expertly handles all delivery of transactional webapp and web site email. This includes welcome emails, password resets, comment notifications, and more. If you've ever installed WordPress and had issues with PHP's `mail()` function not working right, or your WordPress install sends comment notifications or password resets to spam, Postmark makes all of these problems vanish in seconds. Without Postmark, you may not even know you're having delivery problems. Find out in seconds by installing and configuring this plugin.

= Will this plugin work with my WordPress site? =

The Postmark for WordPress plugin overrides any usage of the `wp_mail()` function. Because of this, if any 3rd party code or plugins send mail directly using the PHP mail function, or any other method, we cannot override it. Please contact the makers of any offending plugins and let them know that they should use `wp_mail()` instead of unsupported mailing functions.

= TLS Version Requirements/Compatibility =

The Postmark API requires TLS v1.1 or v1.2 support. We recommend using TLS v1.2.

You can check your TLS v1.2 compatibility using [this plugin](https://wordpress.org/plugins/tls-1-2-compatibility-test/). After installing the plugin, change the dropdown for 'Select API Endpoint' to _How's My SSL?_ and run the test. If compatibility with TLS v1.2 is not detected, contact your server host or make the necessary upgrades to support TLS v1.1 or v1.2.

TLS 1.2 requires:

- PHP 5.5.19 or higher
- cURL 7.34.0 or higher
- OpenSSL 1.0.1 or higher

= Does this cost me money? =

The Postmark service (and this plugin) are free to get started. You can sign up at https://postmarkapp.com/. When you need to process more email, Postmark offers monthly plans to fit your needs.

= My emails are still not sending, or they are going to spam! HELP!? =

No worries, our expert team can help. Just send an email to [support@postmarkapp.com](mailto:support@postmarkapp.com) or tweet [@postmarkapp](https://twitter.com/postmarkapp) for help. Be sure to include as much detail as possible.

= Why should I trust you with my email sending? =

Because we've been in this business for many years. Through trial and error we already know what it takes to manage a large volume of email. We handle things like whitelisting, ISP throttling, reverse DNS, feedback loops, content scanning, and delivery monitoring to ensure your emails get to the inbox.

Most importantly, a great product requires great support and even better education. Our team is spread out across six time zones to offer fast support on everything from using Postmark to best practices on content and user engagement. A solid infrastructure only goes so far, that’s why improving our customer’s sending practices helps achieve incredible results

= How do I tag a message? =

There are two ways to tag a message.

1. Set an X-PM-Tag message header, i.e. array_push( $headers, 'X-PM-Tag: PostmarkPluginTest' ); where you are calling wp_mail().

2. Add a filter for 'postmark_tag' that hooks into a function that returns the tag you desire.

Using the postmark_tag filter will override a tag set via message headers.

= How do I add metadata to a message? =

Add a filter for 'postmark_metadata' that hooks into a function which returns the array of metadata you wish to attach to a message.

= Why aren't my HTML emails being sent? =

This plugin detects HTML by checking the headers sent by other WordPress plugins. If a "text/html" content type isn't set then this plugin won't send the HTML to Postmark to be sent out only the plain text version of the email.

= How do I set environment specific Postmark plugin settings?

You can optionally set the Postmark API Token, message stream, and default sending address by adding the following to your `wp-config.php` file:

```
define( 'POSTMARK_API_KEY', '<api token>' );
define( 'POSTMARK_STREAM_NAME', 'stream-name' );
define( 'POSTMARK_SENDER_ADDRESS', 'from@example.com' );
```

Setting any of these here will override what is set via the plugin's UI.

= Why are password reset links not showing in emails sent with this plugin? =

There are a couple ways to resolve this issue.

1. Open the Postmark plugin settings and uncheck Force HTML and click Save Changes. If the default WordPress password reset email is sent in Plain Text format, the link will render as expected.

2. Access your WordPress site directory and open the `wp-login.php` file.

Change this line:

    `$message .= ‘<‘ . network_site_url(“wp-login.php?action=rp&key=$key&login=” . rawurlencode($user_login), ‘login’) . “>\r\n”;`

Remove the brackets, so it becomes:

    `$message .= network_site_url(“wp-login.php?action=rp&key=$key&login=” . rawurlencode($user_login), ‘login’) . “\r\n”;`

And save the changes to the file.

= How do I set the from name? =

The plugin supports using the `wp_mail_from_name` filter for manually setting a name in the From header.

= I also need a marketing automation tool to connect to my Wordpress site. What do you recommend? =

At [ActiveCampaign](https://www.activecampaign.com/?utm_source=postmark&utm_medium=referral&utm_campaign=postmark_wordpress) you’ll find the email marketing, marketing automation, and CRM tools your marketing and sales teams will love. [ActiveCampaign integrates seamlessly with your Wordpress site](https://wordpress.org/plugins/activecampaign-subscription-forms/) so you can embed forms and collect email addresses on any page, track website visits (and then use that information to trigger automated follow-up emails), or enable live chat to engage with your visitors in real time. [Learn more about ActiveCampaign for Wordpress](https://wordpress.org/plugins/activecampaign-subscription-forms/).

== Additional Resources ==

[Postmark for WordPress FAQ](https://postmarkapp.com/support/article/1138-postmark-for-wordpress-faq)

[Can I use the Postmark for WordPress plugin with Gravity Forms?](https://postmarkapp.com/support/article/1129-can-i-use-the-postmark-for-wordpress-plugin-with-gravity-forms)

[How do I send with Ninja Forms and Postmark for WordPress?](https://postmarkapp.com/support/article/1047-how-do-i-send-with-ninja-forms-and-postmark-for-wordpress)

[How do I send with Contact Form 7 and Postmark for WordPress?](https://postmarkapp.com/support/article/1072-how-do-i-send-with-contact-form-7-and-postmark-for-wordpress)

[Can I use the Postmark for WordPress plugin with Divi contact forms?](https://postmarkapp.com/support/article/1128-can-i-use-the-postmark-for-wordpress-plugin-with-divi-contact-forms)

== Screenshots ==

1. ActiveCampaign Postmark WP Plugin Settings screen.

== Changelog ==
= v1.19.1 =
* Fix warning when logging sends with a null To address

--------

[See the previous changelogs here](https://github.com/ActiveCampaign/postmark-wordpress/blob/master/CHANGELOG.md)
