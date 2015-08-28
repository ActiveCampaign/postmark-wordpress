<?php
/*
Plugin Name: Postmark Approved WordPress Plugin
Plugin URI: http://www.andydev.co.uk
Description: Overwrites wp_mail to send emails through Postmark.
Author: Andrew Yates
Version: 1.7
Author URI: http://www.andydev.co.uk
Created: 2011-07-05
Modified: 2012-09-10

Copyright 2011 - 2012  Andrew Yates & Postmarkapp.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Define
define('POSTMARK_ENDPOINT', 'http://api.postmarkapp.com/email');

// Admin Functionality
add_action('admin_menu', 'pm_admin_menu'); // Add Postmark to Settings

function pm_admin_menu() {
	add_options_page('Postmark', 'Postmark', 'manage_options', 'pm_admin', 'pm_admin_options');
}

function pm_admin_action_links($links, $file) {
    static $pm_plugin;
    if (!$pm_plugin) {
        $pm_plugin = plugin_basename(__FILE__);
    }
    if ($file == $pm_plugin) {
        $settings_link = '<a href="options-general.php?page=pm_admin">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

add_filter('plugin_action_links', 'pm_admin_action_links', 10, 2);


function pm_admin_options() {
	if (isset($_POST['submit']) && $_POST['submit'] == "Save") {
		$pm_enabled = $_POST['pm_enabled'];
		if($pm_enabled):
			$pm_enabled = 1;
		else:
			$pm_enabled = 0;
		endif;

		$api_key = $_POST['pm_api_key'];
		$sender_email = $_POST['pm_sender_address'];

		$pm_forcehtml = $_POST['pm_forcehtml'];
		if($pm_forcehtml):
			$pm_forcehtml = 1;
		else:
			$pm_forcehtml = 0;
		endif;

		$pm_poweredby = $_POST['pm_poweredby'];
		if($pm_poweredby):
			$pm_poweredby = 1;
		else:
			$pm_poweredby = 0;
		endif;

		$pm_trackopens = $_POST['pm_trackopens'];
		if($pm_trackopens){
			$pm_trackopens = 1;
			$pm_forcehtml = 1;
		}
		else
		{
			$pm_trackopens = 0;
		}


		update_option('postmark_enabled', $pm_enabled);
		update_option('postmark_api_key', $api_key);
		update_option('postmark_sender_address', $sender_email);
		update_option('postmark_force_html', $pm_forcehtml);
		update_option('postmark_poweredby', $pm_poweredby);
		update_option('postmark_trackopens', $pm_trackopens);

		$msg_updated = "Postmark settings have been saved.";
	}
	?>

	<script type="text/javascript" >
	jQuery(document).ready(function($) {

		$("#test-form").submit(function(e){
			e.preventDefault();
			var $this = $(this);
			var send_to = $('#pm_test_address').val();

			$("#test-form .button-primary").val("Sending…");
			$.post(ajaxurl, {email: send_to, action:$this.attr("action")}, function(data){
				$("#test-form .button-primary").val(data);
			});
		});

	});
	</script>

	<div class="wrap">

		<?php if (isset($msg_updated)): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
		<?php if (isset($msg_error)): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>

		<div id="icon-tools" class="icon32"></div>
		<h2><img src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) ?>images/PM-Logo.jpg" /></h2>
    <h3>What is Postmark?</h3>
		<p>This Postmark Approved plugin enables WordPress blogs of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance. </p>
		<p>If you don't already have a free Postmark account, <a href="https://postmarkapp.com/sign_up">you can get one in minutes</a>. Every account comes with 1000 free sends.</p>

		<br />

		<h3>Your Postmark Settings</h3>
		<form method="post" action="options-general.php?page=pm_admin">
			<table class="form-table">
			<tbody>
				<tr>
					<th><label for="pm_enabled">Send using Postmark</label></th>
					<td><input name="pm_enabled" id="" type="checkbox" value="1"<?php if(get_option('postmark_enabled') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Sends emails sent using wp_mail via Postmark.</span></td>
				</tr>
				<tr>
					<th><label for="pm_api_key">Postmark API Key</label></th>
					<td><input name="pm_api_key" id="" type="text" value="<?php echo get_option('postmark_api_key'); ?>" class="regular-text"/> <br/><span style="font-size:11px;">Your API key is available in the <strong>credentials</strong> screen of your Postmark server. <a href="https://postmarkapp.com/servers/">Create a new server in Postmark</a>.</span></td>
				</tr>
				<tr>
					<th><label for="pm_sender_address">Sender Email Address</label></th>
					<td><input name="pm_sender_address" id="" type="text" value="<?php echo get_option('postmark_sender_address'); ?>" class="regular-text"/> <br/><span style="font-size:11px;">This email needs to be one of your <strong>verified sender signatures</strong>. <br/>It will appear as the "from" email on all outbound messages. <a href="https://postmarkapp.com/signatures">Set one up in Postmark</a>.</span></td>
				</tr>
				<tr>
					<th><label for="pm_forcehtml">Force HTML</label></th>
					<td><input name="pm_forcehtml" id="" type="checkbox" value="1"<?php if(get_option('postmark_force_html') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Force all emails to be sent as HTML.</span></td>
				</tr>
				<tr>
					<th><label for="pm_trackopens">Track Opens</label></th>
					<td><input name="pm_trackopens" id="" type="checkbox" value="1"<?php if(get_option('postmark_trackopens') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Use Postmark's Open Tracking feature to capture open events. (Forces Html option to be turned on)</span></td>
				</tr>
				<tr>
					<th><label for="pm_poweredby">Support Postmark</label></th>
					<td><input name="pm_poweredby" id="" type="checkbox" value="1"<?php if(get_option('postmark_poweredby') == 1): echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Adds a credit to Postmark at the bottom of emails.</span></td>
				</tr>
			</tbody>
			</table>
			<div class="submit">
				<input type="submit" name="submit" value="Save" class="button-primary" />
			</div>
		</form>

		<br />

		<h3>Test Postmark Sending</h3>
		<form method="post" id="test-form" action="pm_admin_test">
			<table class="form-table">
			<tbody>
				<tr>
					<th><label for="pm_test_address">Send a Test Email To</label></th>
					<td> <input name="pm_test_address" id="pm_test_address" type="text" value="<?php echo get_option('postmark_sender_address'); ?>" class="regular-text"/></td>
				</tr>
			</tbody>
			</table>
			<div class="submit">
				<input type="submit" name="submit" value="Send Test Email" class="button-primary" />
			</div>
		</form>

		<p style="margin-top:40px; padding-top:10px; border-top:1px solid #ddd;">This plugin is brought to you by <a href="http://www.postmarkapp.com">Postmark</a> &amp; <a href="http://www.andydev.co.uk/">Andrew Yates</a>.</p>

	</div>

<?php
}

add_action('wp_ajax_pm_admin_test', 'pm_admin_test_ajax');
function pm_admin_test_ajax() {
	$response = pm_send_test();

	echo $response;

	die();
}

// End Admin Functionality

// Override wp_mail() if postmark enabled
if(get_option('postmark_enabled') == 1){
	if (!function_exists("wp_mail")){
		function wp_mail( $to, $subject, $message, $headers = '', $attachments = array()) {

			// Compact the input, apply the filters, and extract them back out
			extract(apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments')));

			$recognized_headers = pm_parse_headers($headers);

			//Adding the filter thats executed in the wp_mail function so that plugins using those filters will not clash
			//Did not used the filters wp_mail_from_name, wp_mail_from(both are defined in the postmarkapp settings) and wp_mail_charset(postmarkapp does not accept charset)
			$recognized_headers['Content-Type'] = apply_filters( 'wp_mail_content_type', $recognized_headers['Content-Type'] );

			if (isset($recognized_headers['Content-Type']) && stripos($recognized_headers['Content-Type'], 'text/html') !== false) {
					$current_email_type = 'HTML';
			} else {
					$current_email_type = 'PLAINTEXT';
			}

			// Define Headers
			$postmark_headers = array(
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
		        'X-Postmark-Server-Token' => get_option('postmark_api_key')
			);

			// If "Support Postmark" is on
			if(get_option('postmark_poweredby') == 1){
				// Check Content Type
				if(!strpos($headers, "text/html")){
					$message .= "\n\nPostmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com";
				}
			}

			// Send Email
			if (is_array($to)) {
					$recipients = implode(",", $to);
			} else {
					$recipients = $to;
			}

			// Construct Message
			$email = array();
			$email['To'] = $recipients;
			$email['From'] = get_option('postmark_sender_address');
			$email['Subject'] = $subject;
			$email['TextBody'] = $message;

			if (isset($recognized_headers['Cc']) && !empty($recognized_headers['Cc'])) {
					$email['Cc'] = $recognized_headers['Cc'];
			}

			if (isset($recognized_headers['Bcc']) && !empty($recognized_headers['Bcc'])) {
					$email['Bcc'] = $recognized_headers['Bcc'];
			}

			if (isset($recognized_headers['Reply-To']) && !empty($recognized_headers['Reply-To'])) {
					$email['ReplyTo'] = $recognized_headers['Reply-To'];
			}

			if ($current_email_type == 'HTML') {
					$email['HtmlBody'] = $message;
			} else if (get_option('postmark_force_html') == 1 || get_option('postmark_trackopens') == 1) {
					$email['HtmlBody'] = pm_convert_plaintext_to_html($message);
			}

			if (get_option('postmark_trackopens') == 1) {
					$email['TrackOpens'] = "true";
			}

			$response = pm_send_mail($postmark_headers, $email);

			if (is_wp_error($response)) {
					return false;
			}
			return true;
		}
	}
}

function pm_convert_plaintext_to_html($message) {
    $message = nl2br(htmlspecialchars($message));
    return $message;
}

function pm_parse_headers($headers) {
    if (!is_array($headers)) {
        if (stripos($headers, "\r\n") !== false) {
            $headers = explode("\r\n", $headers);
        } else {
            $headers = explode("\n", $headers);
        }
    }
    $recognized_headers = array();
    $headers_list = array(
        'Content-Type' => array(),
        'Bcc' => array(),
        'Cc' => array(),
        'Reply-To' => array()
    );
    $headers_list_lowercase = array_change_key_case($headers_list, CASE_LOWER);
    if (!empty($headers)) {
	    foreach ($headers as $key => $header) {
                    $key = strtolower($key);
		    if (array_key_exists($key, $headers_list_lowercase)) {
			    $header_key = $key;
                            $header_val = $header;
                            $segments = explode(':', $header);
                            if (count($segments) === 2) {
				    if (array_key_exists(strtolower($segments[0]), $headers_list_lowercase)) {
					    list($header_key, $header_val) = $segments;
                                            $header_key = strtolower($header_key);
				    }
			    }
		    }
		    else {
			    $segments = explode(':', $header);
			    if (count($segments) === 2) {
				    if (array_key_exists(strtolower($segments[0]), $headers_list_lowercase)) {
					    list($header_key, $header_val) = $segments;
                                            $header_key = strtolower($header_key);
				    }
			    }
		    }
		    if (isset($header_key) && isset($header_val)) {
			    if (stripos($header_val, ',') === false) {
				    $headers_list_lowercase[$header_key][] = trim($header_val);
			    }
			    else {
				    $vals = explode(',', $header_val);
				    foreach ($vals as $val) {
					    $headers_list_lowercase[$header_key][] = trim($val);
				    }
			    }
			    unset($header_key);
			    unset($header_val);
		    }
	    }

	    foreach ($headers_list as $key => $value) {
                    $value = $headers_list_lowercase[strtolower($key)];
		    if (count($value) > 0) {
			    $recognized_headers[$key] = implode(', ', $value);
		    }
	    }
    }
    return $recognized_headers;
}

function pm_send_test(){
	$email_address = $_POST['email'];

	// Define Headers
	$postmark_headers = array(
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
        'X-Postmark-Server-Token' => get_option('postmark_api_key')
	);

	$message = 'This is a test email sent via Postmark from '.get_bloginfo('name').'.';
	$html_message = 'This is a test email sent via <strong>Postmark</strong> from '.get_bloginfo('name').'.';

	if(get_option('postmark_poweredby') == 1){
		$message .= "\n\nPostmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com";
		$html_message .= '<br /><br />Postmark solves your WordPress email problems. Send transactional email confidently using <a href="http://postmarkapp.com">Postmark</a>.';
	}

	$email = array();
	$email['To'] = $email_address;
	$email['From'] = get_option('postmark_sender_address');
    $email['Subject'] = get_bloginfo('name').' Postmark Test';
    $email['TextBody'] = $message;

    if(get_option('postmark_force_html') == 1){
    	$email['HtmlBody'] = $html_message;
	}

	if(get_option('postmark_trackopens') == 1){
		$email['TrackOpens'] = "true";
	}

    $response = pm_send_mail($postmark_headers, $email);

    if ($response === false){
    	return "Test Failed with Error ".curl_error($curl);
    } else {
    	return "Test Sent";
   	}

    die();
}


function pm_send_mail($headers, $email){
	$args = array(
		'headers' => $headers,
		'body' => json_encode($email)
	);
	$response = wp_remote_post(POSTMARK_ENDPOINT, $args);

	if($response['response']['code'] == 200) {
		return true;
	} else {
		return false;
	}
}

?>
