<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// To make account level commands, define POSTMARK_ACCOUNT_TOKEN in wp-config.php.

class PostmarkPluginCLI {

	const POSTMARK_API_BASE_URL = 'https://api.postmarkapp.com/';

	public function __construct() {

	}

	// Helper function for making API calls to Postmark APIs that require using a server token.
	public function server_api_call( $method, $path, $body ) {

		$url = Self::POSTMARK_API_BASE_URL . $path;

		if ( !postmark_cli_check_server_token_is_set() ) {
			WP_CLI::error('You must set your server API token before attempting to use this command.');
			return;
		}

		$postmark_options =  json_decode(get_option( 'postmark_settings' ));

		$headers = array(
			'X-Postmark-Server-Token' => $postmark_options->api_key
		);

		$method = strtoupper( $method );

		switch ( $method ) {

			// GET
			case "GET":

				$headers['Accept'] = 'application/json';

				$options = array(
					'method'  => 'GET',
					'headers' => $headers
				);
				break;

			// POST
			case "POST":
				$headers['Accept']       = 'application/json';
				$headers['Content-Type'] = 'application/json';

				$options = array(

					'method'   => 'POST',

					// Waits for API response.
					'blocking' => true,

					'headers'  => $headers,

					'body'     => $body,
				);
				break;

			// PUT
			case "PUT":
				$headers['Accept']       = 'application/json';
				$headers['Content-Type'] = 'application/json';

				$options = array(
					'method'  => 'PUT',
					// Waits for API response.
					'blocking' => true,
					'headers' => $headers,
					'body'    => $body,
				);

				break;

			// DELETE
			case "DELETE":
				$headers['Accept'] = 'application/json';

				$options = array(
					'method'  => 'DELETE',
					'headers' => $headers,
				);

				break;


		} // end HTTP method switch

		// Returns resulting response from Postmark API.
		$resp = wp_remote_request( esc_url_raw( $url ), $options );

		//Checks for success, returns response.
	  if( !is_wp_error( $resp ) ) {
	    return $resp;
	  } else {
	    return false;
	  }

	}

	// Helper function for making API calls to Postmark APIs that require using a server token.
	public function account_api_call( $method, $path, $body ) {

		$url = Self::POSTMARK_API_BASE_URL . $path;

		if (!postmark_cli_check_account_token_is_set() ) {
			WP_CLI::error( __('You need to set your account api token in your wp-config file before using this command.' ) );
			return;
		}

		$headers = array( 'X-Postmark-Account-Token' => POSTMARK_ACCOUNT_TOKEN );

		$method = strtoupper( $method );

		switch ( $method ) {

			// GET
			case "GET":

				$headers['Accept'] = 'application/json';

				$options = array(
					'method'  => 'GET',
					'headers' => $headers
				);
				break;

			// POST
			case "POST":
				$headers['Accept']       = 'application/json';
				$headers['Content-Type'] = 'application/json';

				$options = array(

					'method'   => 'POST',

					// Waits for API response.
					'blocking' => true,

					'headers'  => $headers,

					'body'     => $body,
				);
				break;

			// PUT
			case "PUT":
				$headers['Accept']       = 'application/json';
				$headers['Content-Type'] = 'application/json';

				$options = array(
					'method'  => 'PUT',
					// Waits for API response.
					'blocking' => true,
					'headers' => $headers,
					'body'    => $body,
				);

				break;

			// DELETE
			case "DELETE":
				$headers['Accept'] = 'application/json';

				$options = array(
					'method'  => 'DELETE',
					'headers' => $headers,
				);

				break;


		} // end HTTP method switch

		// Returns resulting response from Postmark API.
		$resp = wp_remote_request( esc_url_raw( $url ), $options );

		//Checks for success, returns response.
	  if( !is_wp_error( $resp ) ) {
	    return $resp;
	  } else {
	    return false;
	  }

	}

	/**********************************************
	****************** Email API ******************
	**********************************************/

	/**
	 * Sends a test email.
	 *
	 * ## OPTIONS
	 *
	 * <recipientemail>
	 * : Address to send test to.
	 *
	 * [--from=<fromemailaddress>]
	 * : Test email address to send from
	 *
	 * [--subject=<subject>]
	 * : Test email subject
	 *
	 * [--body=<body>]
	 * : Test email body content
	 *
	 * [--trackopens]
	 * : Use open tracking.
	 *
	 * [--tracklinks=<tracklinks>]
	 * : Use link tracking. Options are "None", "HtmlOnly", "TextOnly", "HtmlAndText"
	 *
	 * ## EXAMPLES
	 * $ wp postmark send_test_email recipient@domain.com --from="senderoverride@domain.com" --subject="my custom subject" --body="<b>this is some test html</b>" --opentracking="true"
	 *
	 * Success: Successfully sent a test email to
	 * recipient@domain.com.
	 *
	 */
	public function send_test_email( $args, $assoc_args ) {

		$headers = array();

		// Make sure To email address is present and valid.
		if ( isset( $args[0] ) && is_email( $args[0] ) ) {
			$to = sanitize_email( $args[0] );
		} else {
			WP_CLI::error( 'You need to specify a valid recipient email address.' );
		}

		// Checks for a From address.
		if ( isset( $assoc_args['from'] ) && is_email( $assoc_args['from'] ) ) {

			$from = sanitize_email( $assoc_args['from'] );

			// Sets the From address override.
			array_push( $headers, 'From:' . $from );

		}

		// Checks if a subject was specified and uses it.
		if ( isset( $assoc_args['subject'] ) ) {

			$subject = $assoc_args['subject'];

			// Uses a default subject if not specified.
		} else {

			$subject = sprintf( __( 'Postmark Plugin WP-CLI Test: %1$s' ), get_bloginfo( 'name' ) );

		}

		// Checks if a body was specified and uses it.
		if ( isset( $assoc_args['body'] ) ) {

			$message = $assoc_args['body'];

			// Uses a default body if not specified.
		} else {

			$message = __('This is a test email generated from the Postmark for WordPress plugin using the WP-CLI.');

		}

		// Sets open tracking flag.
		if ( isset( $assoc_args['trackopens'] ) ) {

			$headers['X-PM-Track-Opens'] = true;

		} else {

			$headers['X-PM-Track-Opens'] = false;

		}

		// Sets link tracking flag.
		if ( isset( $assoc_args['tracklinks'] ) ) {

			// Checks for correct link tracking values.
			if( !in_array( strtolower( $assoc_args['tracklinks'] ), array( 'none', 'htmlonly', 'textonly', 'htmlandtext' ) ) ) {
				// Discard incorrect values for track links option.
				WP_CLI::warning( __( 'Incorrect track links value received. Setting to none (no link tracking). Correct options are none, htmlonly, textonly, or htmlandtext' ) );

				$assoc_args['tracklinks'] = 'None';
			}

			$headers['X-PM-Track-Links'] = $assoc_args['tracklinks'];

		}

		// Sends the test email.
		$response = wp_mail( $to, $subject, $message, $headers );

		// If all goes well, display a success message using the CLI.
		if ( false !== $response ) {

			WP_CLI::success(
				sprintf( __( 'Successfully sent a test email to %1$s.' ), $to )
			);
		} else {

			$dump = print_r( Postmark_Mail::$last_error, true );

			WP_CLI::warning( __('Test send failed.') );

			WP_CLI::warning( sprintf( __('Response: %1$s', $dump ) ) );
		}
	}

	/**
	 * Sends a batch of emails.
	 *
	 * ## OPTIONS
	 * <jsonfilename>
	 * : File containing JSON for each message to send.
	 *
	 * [--csv]
	 * : Output batch results to a csv.
	 *
	 * ## EXAMPLES
	 * $ wp postmark send_batch mybatchdata.json
	 *
	 */
	public function send_batch( $args, $assoc_args ) {

		$path = 'email/batch';

		// Get file contents to use as JSON body.
		$file = file_get_contents( $args[0], true );

		// Sends batch of emails using the messages in file.
		$response = $this->server_api_call( 'post', $path, $file );

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$fields = array( __( 'Recipient' ), __( 'Result' ), __( 'Message ID' ) );

			$data = array();

			foreach ( $body as $send_result ) {

				array_push(
					$data,
					array(
						__( 'Recipient' )  => $send_result['To'],
						__( 'Result' )     => $send_result['MessageID'] ? 'Sent' : "Failed - {$send_result['Message']}",
						__( 'Message ID' ) => $send_result['MessageID'] ? $send_result['MessageID'] : '',
					)
				);

			}

			// Outputs stats in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			if ( isset( $assoc_args['csv'] ) ) {

				postmark_cli_make_csv( array( __( "Recipient" ), __( "Result" ), __( "Message ID" ) ), $body, 'batch_send' );

			}
		} else {

			postmark_cli_handle_api_error( $response );

		}

	} // End Email API

	/**********************************************
	***************** Bounces API *****************
	**********************************************/

	/**
	 * Retrieves delivery stats.
	 *
	 *
	 * ## OPTIONS
	 * [--csv]
	 * : Output results to a csv file.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_delivery_stats
	 */
	public function get_delivery_stats( $args, $assoc_args ) {

		$path = 'deliverystats';

		// Retrieves delivery stats
		$response = $this->server_api_call( 'get', $path, null );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$fields = array( __( 'Type' ), __( 'Name' ), __( 'Count' ) );

			$data = array();

			foreach ( $body['Bounces'] as $bounce ) {

				array_push(
					$data,
					array(
						__( 'Type' )  => $bounce['Type'],
						__( 'Name' )  => $bounce['Name'],
						__( 'Count' ) => $bounce['Count'],
					)
				);
			}

			// Outputs in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			if ( isset( $assoc_args['csv'] ) ) {

				postmark_cli_make_csv( array( __( "Type" ), __( "Name" ), __( "Count" ) ), $body, 'delivery_stats' );

			}

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );

		}

	}

	/**
	 * Retrieves bounces.
	 *
	 * [--count=<count>]
	 * : Number of bounces to return per request. Max 500.
	 *
	 * [--offset=<offset>]
	 * : Number of bounces to skip.
	 *
	 * [--type=<type>]
	 * : Filter by type of bounce.
	 *
	 * [--inactive=<inactive>]
	 * : Filter by emails that were deactivated by Postmark due
	 * to the bounce. Set to true or false. If this isn’t
	 * specified it will return both active and inactive.
	 *
	 * [--emailfilter=<emailaddress>]
	 * : Filter by email address.
	 *
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--messageid=<messageid>]
	 * : Filter by messageID.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter messages starting from the date specified
	 * (inclusive). e.g. 2014-02-01
	 *
	 * [--todate=<todate>]
	 * : Filter messages up to the date specified (inclusive).
	 * e.g. 2014-02-01.
	 *
	 * [--csv]
	 * : Generate a CSV of bounces.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_bounces
	 *
	 */
	public function get_bounces( $args, $assoc_args ) {

		$path = 'bounces';

		if ( !isset( $assoc_args['count'] ) ) {
			$assoc_args['count'] = 500;
		}

		if ( !isset( $assoc_args['offset'] ) ) {
			$assoc_args['offset'] = 0;
		}

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieve bounces
		$response = $this->server_api_call( 'get', $path, null );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$fields = array( __( 'Email' ), __( 'Type' ), __( 'ID' ), __( 'Bounced At' ) );

			$data = array();

			foreach ( $body['Bounces'] as $bounce ) {

				array_push(
					$data,
					array(
						__( 'Email' )      => $bounce['Email'],
						__( 'Type' )       => $bounce['Type'],
						__( 'ID' )         => $bounce['ID'],
						__( 'Bounced At' ) => $bounce['BouncedAt'],
					)
				);
			}

			// Outputs in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			if ( isset( $assoc_args['csv'] ) ) {

				postmark_cli_make_csv( array( __("Email"), __("Type"), __("ID"), __("BouncedAt") ), $body, 'bounces' );

			}

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );

		}

	}

	/**
	 * Retrieves a single bounce.
	 *
	 * ## OPTIONS
	 * <bounceid>
	 * : ID of bounce.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_bounce 12345678
	 *
	 */
	public function get_bounce( $args ) {

		$path = 'bounces/' . $args['0'];

		// Retrieve the bounce
		$response = $this->server_api_call( 'get', $path, null );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$fields = array( __( 'ID' ), __( 'Recipient' ), __( 'Subject' ), __( 'Inactive' ) );

			$data = array();

			array_push(
				$data,
				array(
					__( 'ID' )        => $body['ID'],
					__( 'Recipient' ) => $body['Email'],
					__( 'Subject' )   => $body['Subject'],
					__( 'Inactive' )  => $body['Inactive'] ? 'True' : 'False',
				)
			);

			// Outputs in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );
		}

	}

	/**
	 * Retrieves raw source of bounce. If no dump is available
	 * this will return an empty string.
	 *
	 * ## OPTIONS
	 * <bounceid>
	 * : ID of bounce.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_bounce_dump 1234567
	 *
	 */
	public function get_bounce_dump( $args ) {

		$path = 'bounces/' . $args['0'] . '/dump';

		// Retrieves the bounce dump.
		$response = $this->server_api_call( 'get', $path, null );

		var_dump($response);

		postmark_cli_handle_response( $response );

	}

	/**
	 * Reactivates a bounced address.
	 *
	 * ## OPTIONS
	 * <bounceid>
	 * : ID of bounce.
	 *
	 * ## EXAMPLES
	 * $ wp postmark activate_bounce 1234567
	 *
	 */
	public function activate_bounce( $args ) {

		$path = 'bounces/' . $args['0'] . '/activate';

		// Activates the bounce - needs to include an empty body.
		$response = $this->server_api_call( 'put', $path, ' ' );

		$response_body = json_decode( $response['body'] );

		// Successful API call (200 response).
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			WP_CLI::success( __( "Bounce ID {$args[0]} reactivated ({$response_body->Bounce->Email})." ) );

			// Non-200 response from Postmark API.
		} elseif ( !( 200 === wp_remote_retrieve_response_code( $response )  ) ) {

			postmark_cli_handle_api_error( $response );

		} else {

			WP_CLI::error( 'Error occurred with command. API call unsuccessful.' );
		}
	}

	/**
	 * Gets an array of tags that have generated bounces for a given server.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_bounced_tags
	 *
	 */
	public function get_bounced_tags() {

		$path = '/bounces/tags';

		// Retrieves the bounced tags.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	} // End Bounces API

	/**********************************************
	***************** Templates API *****************
	**********************************************/

	/**
	 * Sends an email using a template.
	 *
	 * ## OPTIONS
	 * <jsonfilename>
	 *
	 * ## EXAMPLES
	 * $ wp postmark send_with_template template_send.json
	 */
	public function send_with_template( $args ) {

		$path = 'email/withTemplate';

		// Get file contents to use as JSON body.
		$file = file_get_contents( $args[0], true );

		// Sends email using the template.
		$response = $this->server_api_call( 'post', $path, $file );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Push all templates with changes to another server. If the template already exists on the destination server, the template will be updated. If the template does not exist on the destination server, it will be created and assigned the alias of the template on the source server.
	 *
	 * ## OPTIONS
	 * <sourceserverid>
	 * : Server ID of the source server containing the templates that will be
	 * pushed.
	 *
	 * <desintationserverid>
	 * : Server ID of the destination server receiving the pushed templates.
	 *
	 * [--performchanges]
	 * : Specifies whether to push templates to destination server or not. This
	 * parameter can be set to false to allow you to do a "dry-run" of the push
	 * operation so that you can see which templates would be created or updated
	 * from this operation. Must be included to execute template push when using
	 * the CLI.
	 *
	 * ## EXAMPLES
	 * $ wp postmark push_templates 123456 654321 --performchanges
	 *
	 */
	public function push_templates( $args, $assoc_args ) {

		$path = 'templates/push';

		$body = array(
			'SourceServerID'      => $args[0],
			'DestinationServerID' => $args[1],
		);

		if ( isset( $assoc_args['performchanges'] ) ) {

			$body['PerformChanges'] = 'true';

		} else {

			$body['PerformChanges'] = 'false';

		}

		// Pushes the templates.
		$response = $this->account_api_call( 'put', $path, wp_json_encode( $body ) );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$data = array();

			if ( isset( $assoc_args['performchanges'] ) ) {

				$fields = array( __( 'Action' ), __( 'TemplateId' ), __( 'Alias' ), __( 'Name' ) );

				foreach ( $body['Templates'] as $template ) {

					array_push(
						$data,
						array(
							__( 'Action' )     => $template['Action'],
							__( 'TemplateId' ) => $template['TemplateId'],
							__( 'Alias' )      => $template['Alias'],
							__( 'Name' )       => $template['Name'],
						)
					);

				}
			} else {

				$fields = array( __( 'Action' ), __( 'Alias' ), __( 'Name' ) );

				foreach ( $body['Templates'] as $template ) {

					array_push(
						$data,
						array(
							__( 'Action' ) => $template['Action'],
							__( 'Alias' )  => $template['Alias'],
							__( 'Name' )   => $template['Name'],
						)
					);

				}
			}

			if ( ! isset( $assoc_args['performchanges'] ) ) {

				WP_CLI::log( __( 'Potential changes from templates push:' ) );
				// Outputs in a table.
				WP_CLI\Utils\format_items( 'table', $data, $fields );
				WP_CLI::log( __( "Run the command again with" ) . " --performchanges " .
				__( "to execute the templates push." ) );

			} else {

				WP_CLI::success( __( 'Pushed templates:' ) );
				// Outputs in a table.
				WP_CLI\Utils\format_items( 'table', $data, $fields );

			}

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );

		}

	}

	/**
	 * Retrieves a single template.
	 *
	 * ## OPTIONS
	 * <templateidoralias>
	 * : ID or alias of template.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_template welcome-template
	 */
	public function get_template( $args ) {

		$path = "templates/{$args[0]}";

		// Retrieves template details.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Creates a new template.
	 *
	 * ## OPTIONS
	 * <name>
	 * : Name of new template.
	 *
	 * <subject>
	 * : The content to use for the Subject when this template is used to send
	 * email. See our template language documentation for more information on the
	 * syntax for this field.
	 *
	 * [--htmlbody=<htmlbody>]
	 * : File location for the content to use for the HtmlBody when this template
	 * is used to send email. Required if TextBody is not specified. See our
	 * template language documentation for more information on the syntax for this
	 * field.
	 *
	 * [--textbody=<textbody>]
	 * : File location for the content to use for the TextBody when this template
	 * is used to send email. Required if HtmlBody is not specified. See our
	 * template language documentation for more information on the syntax for this
	 * field.
	 *
	 * [--alias=<alias>]
	 * : An optional string you can provide to identify this Template. Allowed
	 * characters are numbers, ASCII letters, and ‘.’, ‘-’, ‘_’ characters, and
	 * the string has to start with a letter.
	 *
	 * ## EXAMPLES
	 * $ wp postmark create_template welcome "Welcome to Product Name, {{ name }}!" --htmlbody=htmlfilename.html
	 *
	 */
	public function create_template( $args, $assoc_args ) {

		$path = 'templates';

		$new_template = array(
			'Name'    => $args[0],
			'Subject' => $args[1],
		);

		if ( ! isset( $assoc_args['htmlbody'] ) && ! isset( $assoc_args['textbody'] ) ) {
			WP_CLI::error( 'Must specify either an HTML or Text body when creating a template.' );
		}

		if ( isset( $assoc_args['htmlbody'] ) ) {
			$new_template['HtmlBody'] = file_get_contents( $assoc_args['htmlbody'], true );
		}

		if ( isset( $assoc_args['textbody'] ) ) {
			$new_template['TextBody'] = file_get_contents( $assoc_args['textbody'], true );
		}

		if ( isset( $assoc_args['alias'] ) ) {
			$new_template['Alias'] = $assoc_args['alias'];
		}

		// Creates new template.
		$response = $this->server_api_call( 'post', $path, wp_json_encode( $new_template ) );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Edits a template.
	 *
	 * ## OPTIONS
	 * <templateidoralias>
	 * : Template ID or alias
	 *
	 * <name>
	 * : Name of template.
	 *
	 * <subject>
	 * : The content to use for the Subject when this template is used to send
	 * email. See our template language documentation for more information on the
	 * syntax for this field.
	 *
	 * [--htmlbody=<htmlbody>]
	 * : File location for the content to use for the HtmlBody when this template
	 * is used to send email. Required if TextBody is not specified. See our
	 * template language documentation for more information on the syntax for this
	 * field.
	 *
	 * [--textbody=<textbody>]
	 * : File location for the content to use for the TextBody when this template
	 * is used to send email. Required if HtmlBody is not specified. See our
	 * template language documentation for more information on the syntax for this
	 * field.
	 *
	 * [--alias=<alias>]
	 * : An optional string you can provide to identify this Template. Allowed
	 * characters are numbers, ASCII letters, and ‘.’, ‘-’, ‘_’ characters, and
	 * the string has to start with a letter.
	 *
	 *  ## EXAMPLES
	 *  $ wp postmark edit_template welcome welcome-email "Welcome to my product!" --htmlbody=htmlfilename.html
	 */
	public function edit_template( $args, $assoc_args ) {

		$path = "templates/{$args[0]}";

		$template_edits = array(
			'Name'    => $args[1],
			'Subject' => $args[2],
		);

		if ( isset( $assoc_args['htmlbody'] ) ) {
			$template_edits['HtmlBody'] = file_get_contents( $assoc_args['htmlbody'], true );
		}

		if ( isset( $assoc_args['textbody'] ) ) {
			$template_edits['TextBody'] = file_get_contents( $assoc_args['textbody'], true );
		}

		if ( isset( $assoc_args['alias'] ) ) {
			$template_edits['Alias'] = $assoc_args['alias'];
		}

		// Edits template.
		$response = $this->server_api_call( 'put', $path, wp_json_encode( $template_edits ) );

		postmark_cli_handle_response( $response );

	}

	# Registers a custom WP-CLI command for retrieving all templates.
	#
	# Example usage:
	#
	#
	#
	# Success: {
	#  "TotalCount": 2,
	#  "Templates": [
	#    {
	#      "Active": true,
	#      "TemplateId": 1234,
	#      "Name": "Account Activation Email",
	#      "Alias": null
	#    },
	#    {
	#      "Active": true,
	#      "TemplateId": 5678,
	#      "Name": "Password Recovery Email",
	#      "Alias": "password-recovery"
	#    }]
	# }

	/**
	 * Retrieves all templates.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : The number of templates to return.
	 *
	 * [--offset=<offset>]
	 * : The number of templates to "skip" before returning results.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_templates
	 */
	public function get_templates( $args, $assoc_args ) {

		$path = 'templates';

		if ( !isset( $assoc_args['count'] ) ) {
			$assoc_args['count'] = 500;
		}

		if ( !isset( $assoc_args['offset'] ) ) {
			$assoc_args['offset'] = 0;
		}

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves templates.
		$response = $this->server_api_call( 'get', $path, null );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$data = array();

			$fields = array( __( 'TemplateId' ), __( 'Alias' ), __( 'Name' ) );

			foreach ( $body['Templates'] as $template ) {

				array_push(
					$data,
					array(
						__( 'TemplateId' ) => $template['TemplateId'],
						__( 'Alias' )      => $template['Alias'],
						__( 'Name' )       => $template['Name'],
					)
				);

			}

			WP_CLI\Utils\format_items( 'table', $data, $fields );

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );

		}

	}

	/**
	 * Deletes a template.
	 *
	 * ## OPTIONS
	 * <templateidoralias>
	 * : ID or alias of template.
	 *
	 * ## EXAMPLES
	 * $ wp postmark delete_template <templateidoralias>
	 */
	public function delete_template( $args ) {

		$path = "templates/{$args[0]}";

		// Deletes template.
		$response = $this->server_api_call( 'delete', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Validates template.
	 *
	 * ## OPTIONS
	 * <subject>
	 * : The content to use for the Subject when this template is used to send
	 * email. See our template language documentation for more information on the
	 * syntax for this field.
	 *
	 * [--htmlbody=<htmlbody>]
	 * : File location for the content to use for the HtmlBody when this template
	 * is used to send email. Required if TextBody is not specified. See our
	 * template language documentation for more information on the syntax for this
	 * field.
	 *
	 * [--textbody=<textbody>]
	 * : File location for the content to use for the TextBody when this template
	 * is used to send email. Required if HtmlBody is not specified. See our
	 * template language documentation for more information on the syntax for this
	 * field.
	 *
	 * [--testrendermodel=<testrendermodel>]
	 * : The template model to be used when rendering test content. Use
	 * filepath for template model json if using this optional argument.
	 *
	 * [--inlinecssforhtmltestrender=<inlinecssforhtmltestrender>]
	 * : When HtmlBody is specified, the test render will have style blocks
	 * inlined as style attributes on matching html elements. You may disable the
	 * css inlining behavior by passing false for this parameter.
	 *
	 * ## EXAMPLES
	 * $ wp postmark validate_template <subject> --htmlbody=htmlfilename.html
	 *
	 */
	public function validate_template( $args, $assoc_args ) {

		$path = 'templates/validate';

		$template = array(
			'Subject' => $args[0],
		);

		if ( ! isset( $assoc_args['htmlbody'] ) && ! isset( $assoc_args['textbody'] ) ) {
			WP_CLI::error( 'Must specify either an HTML or Text body when creating a template.' );
		}

		if ( isset( $assoc_args['htmlbody'] ) ) {
			$template['HtmlBody'] = file_get_contents( $assoc_args['htmlbody'], true );
		}

		if ( isset( $assoc_args['textbody'] ) ) {
			$template['TextBody'] = file_get_contents( $assoc_args['textbody'], true );
		}

		if ( isset( $assoc_args['testrendermodel'] ) ) {
			$template['TestRenderModel'] = file_get_contents( $assoc_args['testrendermodel'], true );
		}

		if ( isset( $assoc_args['inlinecssforhtmltestrender'] ) ) {
			$template['InlineCssForHtmlTestRender'] = $assoc_args['inlinecssforhtmltestrender'];
		}

		// Creates new template.
		$response = $this->server_api_call( 'post', $path, wp_json_encode( $template ) );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Sends a batch of emails using templates.
	 *
	 * ## OPTIONS
	 * <jsonfilename>
	 * : File containing JSON for each templated message to send.
	 *
	 * [--csv]
	 * : Output batch results to a csv.
	 *
	 * ## EXAMPLES
	 * $ wp postmark send_template_batch mybatchdata.json
	 */
	public function send_batch_with_template( $args, $assoc_args ) {

		$path = 'email/batchWithTemplates';

		// Get file contents to use as JSON body.
		$file = file_get_contents( $args[0], true );

		// Sends batch of emails using the template(s).
		$response = $this->server_api_call( 'post', $path, $file );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$fields = array( __( 'Recipient' ), __( 'Result' ), __( 'Message ID' ) );

			$data = array();

			foreach ( $body as $send_result ) {

				array_push(
					$data,
					array(
						__( 'Recipient' )  => $send_result['To'],
						__( 'Result' )     => $send_result['MessageID'] ? 'Sent' : "Failed - {$send_result['Message']}",
						__( 'Message ID' ) => $send_result['MessageID'] ? $send_result['MessageID'] : '',
					)
				);

			}

			// Outputs stats in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			if ( isset( $assoc_args['csv'] ) ) {

				postmark_cli_make_csv( array( __( "Recipient" ), __( "Result" ), __( "Message ID" )  ), $body, 'templated_batch_send' );

			}

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );

		}

	} // End Templates API

	/**********************************************
	**************** Statistics API ***************
	**********************************************/

	/**
	 * Gets a brief overview of statistics for all of your outbound email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * [--csv]
	 * : Output stats to a csv file.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_outbound_overview
	 *
	 * @alias outbound_overview
	 */
	public function get_outbound_overview( $args, $assoc_args ) {

		$path = 'stats/outbound';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves outbound overview.
		$response = $this->server_api_call( 'get', $path, null );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$fields = array( 'Statistic', 'Value' );

			$data = array();

			foreach ( $body as $key => $value ) {

				// Show a percentage on rates and round to two decimal places.
				if ( 'BounceRate' === $key || 'SpamComplaintsRate' === $key ) {

					array_push(
						$data,
						array(
							'Statistic' => $key,
							'Value'     => round( $value, 2 ) . '%',
						)
					);

				} else {

					array_push(
						$data,
						array(
							'Statistic' => $key,
							'Value'     => $value,
						)
					);

				}
			}

			// Outputs stats in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			if ( isset( $assoc_args['csv'] ) ) {

				postmark_cli_make_csv( array( __( "Statistic" ), __( "Value" ) ), $body, 'outbound_overview' );

			}

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );
		}

	}

	/**
	 * Gets a total count of emails you’ve sent out.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_sent_counts
	 */
	public function get_sent_counts( $args, $assoc_args ) {

		$path = 'stats/outbound/sends';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves sent counts.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets total counts of emails you’ve sent out that have been
	 * returned as bounced.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_bounce_counts
	 */
	public function get_bounce_counts( $args, $assoc_args ) {

		$path = 'stats/outbound/bounces';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves bounce counts.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets a total count of recipients who have marked your email
	 * as spam.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_spam_complaints_counts
	 */
	public function get_spam_complaints_counts( $args, $assoc_args ) {

		$path = 'stats/outbound/spam';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves spam complaint counts.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets a total count of emails you’ve sent with open tracking
	 * or link tracking enabled.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_tracked_email_counts
	 */
	public function get_tracked_email_counts( $args, $assoc_args ) {

		$path = 'stats/outbound/tracked';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves tracked email counts.
		$response = $this->server_api_call( 'get', $path, null);

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets total counts of recipients who opened your emails.
	 * This is only recorded when open tracking is enabled for
	 * that email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_email_open_counts
	 */
	public function get_email_open_counts( $args, $assoc_args ) {

		$path = 'stats/outbound/opens';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves open counts.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets an overview of the platforms used to open your emails.
	 * This is only recorded when open tracking is enabled for that
	 * email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-
	 * 01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_email_platform_usage
	 */
	public function get_email_platform_usage( $args, $assoc_args ) {

		$path = 'stats/outbound/opens/platforms';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves email platform usage.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets an overview of the email clients used to open your emails. This is only recorded when open tracking is enabled for that email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-
	 * 01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_email_client_usage
	 */
	public function get_email_client_usage( $args, $assoc_args ) {

		$path = 'stats/outbound/opens/emailclients';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves email client usage.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets total counts of unique links that were clicked.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_click_counts
	 * @alias click_counts
	 */
	public function get_click_counts( $args, $assoc_args ) {

		$path = 'stats/outbound/clicks';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves click counts.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets an overview of the browsers used to open links in your emails. This is
	 * only recorded when Link Tracking is enabled for that email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_browser_usage
	 * @alias browser_usage
	 */
	public function get_browser_usage( $args, $assoc_args ) {

		$path = 'stats/outbound/clicks/browserfamilies';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves click browser usage.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets an overview of the browser platforms used to open your emails. This is only recorded when Link Tracking is enabled for that email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_browser_platform_usage
	 */
	public function get_browser_platform_usage( $args, $assoc_args ) {

		$path = 'stats/outbound/clicks/platforms';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves click browser platform usage.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets an overview of which part of the email links were clicked from (HTML or Text). This is only recorded when Link Tracking is enabled for that email.
	 *
	 * ## OPTIONS
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter stats starting from the date specified (inclusive). e.g. 2014-01-01.
	 *
	 * [--todate=<todate>]
	 * : Filter stats up to the date specified (inclusive). e.g. 2014-02-01.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_click_locations
	 * @alias click_locations
	 */
	public function get_click_locations( $args, $assoc_args ) {

		$path = 'stats/outbound/clicks/location';

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves click browser location stats.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	} // End Stats API

	/**********************************************
	************ Sender signatures API ************
	**********************************************/

	/**
	 * Gets a list of sender signatures containing brief details associated with
	 * your account.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of signatures to return per request. Max 500.
	 *
	 * [--offset=<offset>]
	 * : Number of signatures to skip.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_signatures
	 */
	public function get_signatures( $args, $assoc_args ) {

		$path = 'senders';

		// Checks for a count parameter and uses it if set.
		if ( isset( $assoc_args['count'] ) && is_int( $assoc_args['count'] ) && $assoc_args['count'] < 501 && $assoc_args['count'] > 0 ) {
			$path .= "?count={$assoc_args['count']}";

			// Uses 500 for default count if count not specified.
		} else {
			$path .= '?count=500';
		}

		// Checks for an offset parameter and uses it if set.
		if ( isset( $assoc_args['offset'] ) && is_int( $assoc_args['offset'] ) ) {
			$path .= "&offset={$assoc_args['offset']}";

			// Uses 0 for default offset if offset not specified.
		} else {
			$path .= '&offset=0';
		}

		// Retrieves signatures list
		$response = $this->account_api_call( 'get', $path, null );

		$body = json_decode( $response['body'], true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$data = array();

			$fields = array( __( 'ID' ), __( 'Email Address' ), __( 'Name' ), __( 'Reply-To Address' ), __( 'Confirmed' ) );

			foreach ( $body['SenderSignatures'] as $signature ) {

				array_push(
					$data,
					array(
						__( 'ID' )               => $signature['ID'],
						__( 'Email Address' )    => $signature['EmailAddress'],
						__( 'Name' )             => $signature['Name'],
						__( 'Reply-To Address' ) => $signature['ReplyToEmailAddress'],
						__( 'Confirmed' )        => $signature['Confirmed'] ? 'Yes' : 'No',
					)
				);

			}

			WP_CLI\Utils\format_items( 'table', $data, $fields );

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error ( $response );

		}

	}

	/**
	 * Gets all the details for a specific sender signature.
	 *
	 * ## OPTIONS
	 * <signatureid>
	 * : Sender signature ID.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_signature
	 */
	public function get_signature( $args ) {

		$path = "senders/{$args[0]}";

		// Retrieves signatures list
		$response = $this->account_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Creates a sender signature.
	 *
	 * ## OPTIONS
	 * <fromemail>
	 * : From email associated with sender signature.
	 *
	 * <name>
	 * : From name associated with sender signature.
	 *
	 * [--replytoemail=<replytoemail>]
	 * : Override for reply-to address.
	 *
	 * [--returnpathdomain=<returnpathdomain>]
	 * : A custom value for the Return-Path domain. It is an optional field, but
	 * it must be a subdomain of your From Email domain and must have a CNAME
	 * record that points to pm.mtasv.net.
	 *
	 * ## EXAMPLES
	 * $ wp postmark create_signature <fromemail> <name>
	 */
	public function create_signature( $args, $assoc_args ) {

		$path = 'senders';

		// Checks for ReplyToEmail.
		if ( isset( $assoc_args['replytoemail'] ) ) {

			$replytoemail = $assoc_args['replytoemail'];

		} else {
			// Creates signature without setting a replytoemail.
			$replytoemail = '';
		}

		// Checks for ReturnPathDomain.
		if ( isset( $assoc_args['returnpathdomain'] ) ) {

			$rpdomain = $assoc_args['returnpathdomain'];

		} else {
			// Creates signature without setting a custom return-path domain.
			$rpdomain = '';
		}

		$body = wp_json_encode(
			array(
				'FromEmail' => $args[0],
				'Name'      => $args[1],
			)
		);

		// Calls Signatures API to create new signature.
		$response = $this->account_api_call( 'post', $path, $body );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Edits an existing sender signature.
	 *
	 * ## OPTIONS
	 * <signatureid>
	 * : ID of the signature to edit.
	 *
	 * <name>
	 * : From name associated with sender signature.
	 *
	 * [--replytoemail=<ReplyToEmail>]
	 * : Override for reply-to address.
	 *
	 *  [--returnpathdomain=<returnpathdomain>]
	 * : A custom value for the Return-Path domain. It is an optional field, but
	 * it must be a subdomain of your From Email domain and must have a CNAME
	 * record that points to pm.mtasv.net. For more information about this field,
	 * please read our support page.
	 *
	 * ## EXAMPLES
	 * $ wp postmark edit_signature <signatureid> <name>
	 */
	public function edit_signature( $args, $assoc_args ) {

		$path = "senders/{$args[0]}";

		$body = array(
			'Name' => "{$args[1]}",
		);

		if ( isset( $assoc_args['replytoemail'] ) ) {
			$body['ReplyToEmail'] = $assoc_args['replytoemail'];
		}

		if ( isset( $assoc_args['returnpathdomain'] ) ) {
			$body['ReturnPathDomain'] = $assoc_args['returnpathdomain'];
		}

		$response = $this->account_api_call( 'put', $path, wp_json_encode( $body ) );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Deletes an existing sender signature.
	 *
	 * ## OPTIONS
	 * <signatureid>
	 * : ID of the signature to delete.
	 *
	 * ## EXAMPLES
	 * $ wp postmark delete_signature 1234567
	 */
	public function delete_signature( $args ) {

		$path = "senders/{$args[0]}";

		// Deletes signature.
		$response = $this->account_api_call( 'delete', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Resends a confirmation email for a sender signature.
	 *
	 * ## OPTIONS
	 * <signatureid>
	 * : ID of sender signature.
	 *
	 * ## EXAMPLES
	 * $ wp postmark resend_confirmation 1234567
	 */
	public function resend_confirmation( $args ) {

		$path = "https://api.postmarkapp.com/senders/{$args[0]}/resend";

		$body = '';

		// Calls Signatures API to resend confirmation email.
		$response = $this->account_api_call( 'post', $path, $body );

		postmark_cli_handle_response( $response );

	} // End Signatures API

	/**********************************************
	***************** Domains API *****************
	**********************************************/

	/**
	 * Retrieves list of domains.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of domains to return per request. Max 500.
	 *
	 * [--offset=<offset>]
	 * : Number of domains to skip.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_domains
	 */
	public function get_domains( $args, $assoc_args ) {

		$path = 'https://api.postmarkapp.com/domains';

		// Checks for a count parameter and uses it if set.
		if ( isset( $assoc_args['count'] ) ) {
			$path .= "?count={$assoc_args['count']}";

		// Uses 500 for default count if count not specified.
		} elseif ( ! isset( $assoc_args['count'] ) ) {
			$path .= '?count=500';
		}

		// Checks for an offset parameter and uses it if set.
		if ( isset( $assoc_args['offset'] ) ) {
			$path .= "&offset={$assoc_args['offset']}";

			// Uses 0 for default offset if offset not specified.
		} elseif ( ! isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=0';
		}

		// Retrieves domains.
		$response = $this->account_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Retrieves details for a domain.
	 *
	 * ## OPTIONS
	 * <domainid>
	 * : ID of domain to retrieve details for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_domain 1234567
	 */
	public function get_domain( $args ) {

		$path = "domains/{$args[0]}" ;

		// Retrieves domain information.
		$response = $this->account_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Creates a domain.
	 *
	 * ## OPTIONS
	 * <name>
	 * : Name of the new domain - e.g. example.com.
	 *
	 * [--returnpathdomain=<returnpathdomain>]
	 * : A custom value for the Return-Path domain. It is an optional field, but it
	 * must be a subdomain of your From Email domain and must have a CNAME record
	 * that points to pm.mtasv.net.
	 *
	 * ## EXAMPLES
	 * $ wp postmark create_domain example.com
	 */
	public function create_domain( $args, $assoc_args ) {

		$path = 'domains';

		// Checks for ReturnPathDomain.
		if ( isset( $assoc_args['returnpathdomain'] ) ) {

			$rpdomain = $assoc_args['returnpathdomain'];

		} else {
			// Creates domain without setting a custom return-path domain.
			$rpdomain = '';
		}

		$body = wp_json_encode(
			array(
				'Name'             => $args[0],
				'ReturnPathDomain' => $rpdomain,
			)
		);

		// Calls Domains API to create new domain.
		$response = $this->account_api_call( 'post', $path, $body );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Edits an existing domain.
	 *
	 * ## OPTIONS
	 * <domainid>
	 * : ID of the domain to edit.
	 *
	 * <returnpathdomain>
	 * : A custom value for the Return-Path domain. It is an optional field, but
	 * it must be a subdomain of your From Email domain and must
	 * have a CNAME record that points to pm.mtasv.net.
	 *
	 * ## EXAMPLES
	 * $ wp postmark edit_domain 1234567 pm-bounces.domain.com
	 */
	public function edit_domain( $args ) {

		$path = "domains/{$args[0]}";

		$body = wp_json_encode(
			array( 'ReturnPathDomain' => "{$args[1]}" )
		);

		$response = $this->account_api_call( 'put', $path, $body );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Deletes an existing domain.
	 *
	 * ## OPTIONS
	 * <domainid>
	 * : ID of the domain to edit.
	 *
	 * ## EXAMPLES
	 * $ wp postmark delete_domain 1234567
	 */
	public function delete_domain( $args ) {

		$path = "domains/{$args[0]}";

		$response = $this->account_api_call( 'delete', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Verifies DKIM for an existing domain.
	 *
	 * ## OPTIONS
	 * <domainid>
	 * : ID of the domain to verify DKIM for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark verify_dkim 1234567
	 */
	public function verify_dkim( $args ) {

		$path = "domains/{$args[0]}/verifyDkim";

		$response = $this->account_api_call( 'put', $path, ' ' );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Verifies a custom return-path for an existing domain.
	 *
	 * ## OPTIONS
	 * <domainid>
	 * : ID of the domain to verify return-path for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark verify_return_path <domainid>
	 */
	public function verify_return_path( $args ) {

		$path = "domains/{$args[0]}/verifyReturnPath";

		$response = $this->account_api_call( 'put', $path, ' ' );

		postmark_cli_handle_response( $response );
	}

	/**
	 * Creates a new DKIM key to replace your current key. Until the new DNS entries are confirmed, the pending values will be in DKIMPendingHost and DKIMPendingTextValue fields. After the new DKIM value is verified in DNS, the pending values will migrate to DKIMTextValue and DKIMPendingTextValue and Postmark will begin to sign emails with the new DKIM key.
	 *
	 * ## OPTIONS
	 * <domainid>
	 * : ID of the domain to rotate DKIM for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark rotate_dkim 1234567
	 */
	public function rotate_dkim( $args ) {

		$path = "domains/{$args[0]}/rotatedkim";

		// Rotates DKIM key.
		$response = $this->account_api_call( 'post', $path, ' ' );

		postmark_cli_handle_response( $response );

	} // End Domains API

	/**********************************************
	***************** Messages API ****************
	**********************************************/

	# Registers a custom WP-CLI command for searching
	# outbound messages.
	#
	# Example usage:
	#
	#
	#
	# Success: {
	#  "TotalCount": 194,
	#  "Messages": [
	#    {
	#      "Tag": "Invitation",
	#      "MessageID": "0ac29aee-e1cd-480d-b08d-4f48548ff48d",
	#      "To": [
	#        {
	#          "Email": "john.doe@yahoo.com",
	#          "Name": null
	#        }
	#      ],
	#      "Cc": [],
	#      "Bcc": [],
	#      "Recipients": [
	#        "john.doe@yahoo.com"
	#      ],
	#      "ReceivedAt": "2014-02-20T07:25:02.8782715-05:00",
	#      "From": "\"Joe\" <joe@domain.com>",
	#      "Subject": "staging",
	#      "Attachments": [],
	#      "Status": "Sent",
	#      "TrackOpens" : true,
	#      "TrackLinks" : "HtmlAndText"
	#    }
	#  ]
	# }

	/**
	 * Searches outbound messages.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of servers to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of servers to skip.
	 *
	 * [--recipient=<recipient>]
	 * : by the user who was receiving the email.
	 *
	 * [--fromemail=<fromemail>]
	 * : Filter by the sender email address.
	 *
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--status=<status>]
	 * : Filter by status (queued or sent / processed). Note that sent and
	 * processed will return the same results and can be used interchangeably.
	 *
	 * [--todate=<todate>]
	 * : Filter messages up to the date specified (inclusive). e.g. 2014-02-01
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter messages starting from the date specified (inclusive).
	 * e.g. 2014-02-01
	 *
	 * [--subject=<subject>]
	 * : Filter by email subject.
	 *
	 * [--metadatakey=<metadatakey>]
	 * : Set metadata key to search on, e.g. color.
	 *
	 * [--metadatavalue=<metadatavalue>]
	 * : Set metadata value to search on, e.g. blue.
	 *
	 * [--csv]
	 * : Have search results sent to a csv file.
	 *
	 * ## EXAMPLES
	 * $ wp postmark outbound_message_search
	 */
	public function outbound_message_search( $args, $assoc_args ) {

		$path = 'messages/outbound';

		// Sets count & offset values for convenience.
		if ( !isset( $assoc_args['count'] ) ) {
			$assoc_args['count'] = 500;
		}

		if ( !isset( $assoc_args['offset'] ) ) {
			$assoc_args['offset'] = 0;
		}

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Special handling of metadata values.
		if ( isset( $assoc_args['metadatakey'] ) && isset( $assoc_args['metadatavalue'] ) ) {
			$path .= "&metadata_{$assoc_args['metadatakey']}={$assoc_args['metadatavalue']}";
		}

		// Retrieves results of outbound message search.
		$response = $this->server_api_call( 'get', $path, null );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $body['TotalCount'] > 0 ) {

				WP_CLI::log( WP_CLI::colorize( __( "%G{$body['TotalCount']} hits from search.%n" ) ) );

				$count = 1;

				$fields = array( __( 'Date') , __( 'Message ID' ), __( 'Subject' ) );

				$data = array();

				foreach ( $body['Messages'] as $message ) {
					array_push(
						$data,
						array(
							__( 'Date' )       => $message['ReceivedAt'],
							__ ('Message ID' ) => $message['MessageID'],
							__ ('Subject' )    => $message['Subject'],
						)
					);
					$count++;
				}

				WP_CLI\Utils\format_items( 'table', $data, $fields );

				// No results from search
			} else {

				WP_CLI::log( WP_CLI::colorize( __( '%RNo hits from search.%n' ) ) );

			}

			// Non 200 API response
		} else {
			postmark_cli_handle_api_error( $response );
		}

	}

	/**
	 * Gets details of an outbound message.
	 *
	 * ## OPTIONS
	 * <messageid>
	 * : Message ID of the message to retrieve details for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_outbound_message 4c8e5cf2-cbba-44ee-a125-a14787d371e0
	 */
	public function get_outbound_message_details( $args ) {

		$path = "messages/outbound/{$args[0]}/details";

		// Retrieves outbound message details.
		$response = $this->server_api_call( 'get', $path, null );

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Successful call
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

			WP_CLI::log( WP_CLI::colorize( "%9Subject:%n {$body['Subject']}" ) );

			WP_CLI::log(
				WP_CLI::colorize( "%9Status:%n {$body['Status']}" )
			);

			if ( ! '' === $body['Tag'] ) {
				WP_CLI::log(
					WP_CLI::colorize( "%9Tag:%n {$body['Tag']}" )
				);
			}

			WP_CLI::log(
				WP_CLI::colorize( "%9Date:%n {$body['ReceivedAt']}" )
			);

			WP_CLI::log(
				WP_CLI::colorize( '%9Track Opens:%n ' . ( $body['TrackOpens'] ? 'True' : 'False' ) )
			);

			WP_CLI::log(
				WP_CLI::colorize( "%9Track Links:%n {$body['TrackLinks']}" )
			);

			if ( ! [] === $body['Attachments'] ) {
				WP_CLI::log(
					WP_CLI::colorize( '📎 %9Attachments:%n' . implode( ' ', $body['Attachments'] ) )
				);
			}

			$fields = array( __( 'Recipient' ), __( 'Result' ), __( 'Details' ) );

			$data = array();

			foreach ( $body['MessageEvents'] as $event ) {

				switch ( $event['Type'] ) {

					// Delivered event
					case 'Delivered':
						array_push(
							$data,
							array(
								__( 'Recipient' ) => $event['Recipient'],
								__( 'Result' )    => $event['Type'],
								__( 'Details' )   => $event['Details']['DeliveryMessage'],
							)
						);
						break;

					// Bounced event
					case 'Bounced':
						array_push(
							$data,
							array(
								__( 'Recipient' ) => $event['Recipient'],
								__( 'Result' )    => $event['Type'],
								__( 'Details' )   => "Bounce ID {$event['Details']['BounceID']} " . "{$event['Details']['Summary']}",
							)
						);
				}
			}

			// Outputs result of send for each recipient in a table.
			WP_CLI\Utils\format_items( 'table', $data, $fields );

			// Non 200 API response
		} else {

			postmark_cli_handle_api_error( $response );
		}

	}

	# Registers a custom WP-CLI command for getting a dump of an outbound message.
	#
	# Example usage:
	#
	#
	#
	# Success: {
	#  "Body": "From: \"John Doe\" <john.doe@yahoo.com> \r\nTo: \"john.doe@yahoo.com\" <john.doe@yahoo.com>\r\nReply-To: joe@domain.com\r\nDate: Fri, 14 Feb 2014 11:12:56 -0500\r\nSubject: Parts Order #5454\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\nX-Mailer: aspNetEmail ver 4.0.0.22\r\nX-Job: 44013_34141\r\nX-virtual-MTA: shared1\r\nX-Complaints-To: abuse@postmarkapp.com\r\nX-PM-RCPT: |bTB8NDQwMTN8MzQxNDF8anBAd2lsZGJpdC5jb20=|\r\nX-PM-Tag: product-orders\r\nX-PM-Message-Id: 07311c54-0687-4ab9-b034-b54b5bad88ba\r\nMessage-ID: <SC-ORD-MAIL4390fbe08b95f4257984dcaed896b4730@SC-ORD-MAIL4>\r\n\r\nThank you for your order=2E=2E=2E\r\n"
	# }

	/**
	 * Gets a dump of an outbound message.
	 *
	 * ## OPTIONS
	 * <messageid>
	 * : Message ID of the message to retrieve details for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_outbound_message_dump  4c8e5cf2-cbba-44ee-a125-a14787d371e0
	 */
	public function get_outbound_message_dump( $args ) {

		$path = "messages/outbound/{$args[0]}/dump";

		// Retrieves outbound message dump.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Searches inbound messages.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of servers to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of servers to skip.
	 *
	 * [--recipient=<recipient>]
	 * : by the user who was receiving the email.
	 *
	 * [--fromemail=<fromemail>]
	 * : Filter by the sender email address.
	 *
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--subject=<subject>]
	 * : Filter by email subject.
	 *
	 * [--mailboxhash=<mailboxhash>]
	 * : Filter by mailboxhash.
	 *
	 * [--status=<status>]
	 * : Filter by status (blocked, processed / sent, queued, failed, scheduled)
	 *
	 * [--todate=<todate>]
	 * : Filter messages up to the date specified (inclusive). e.g. 2014-02-01
	 *
	 * [--fromdate=<fromdate>]
	 * : Filter messages starting from the date specified (inclusive).
	 * e.g. 2014-02-01
	 *
	 * ## EXAMPLES
	 * $ wp postmark inbound_message_search
	 */
	public function inbound_message_search( $args, $assoc_args ) {

		$path = 'messages/inbound';

		if ( isset( $assoc_args['count'] ) ) {
			$path .= '?count=' . $assoc_args['count'];
		} else {
			$path .= '?count=500';
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=' . $assoc_args['offset'];
		} else {
			$path .= '&offset=0';
		}

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves results of inbound message search.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets details of an inbound message.
	 *
	 * <messageid>
	 * : Message ID of the message to retrieve details for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_inbound_message f41ce794-1193-43b5-a70b-7456348fd5fd
	 */
	public function get_inbound_message_details( $args ) {

		$path = "messages/inbound/{$args[0]}/details";

		// Retrieves inbound message details.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Bypasses rules for a blocked inbound message.
	 *
	 * ## OPTIONS
	 * <messageid>
	 * : Message ID of the message to bypass rules for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark bypass_inbound_message f41ce794-1193-43b5-a70b-7456348fd5fd
	 */
	public function bypass_inbound_message( $args ) {

		$path = "messages/inbound/{$args[0]}/bypass";

		// Performs the bypass.
		$response = $this->server_api_call( 'put', $path, ' ' );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Retries a failed inbound message.
	 *
	 * ## OPTIONS
	 * <messageid>
	 * : Message ID of the message to bypass rules for.
	 *
	 * ## EXAMPLES
	 * $ wp postmark retry_inbound_message f41ce794-1193-43b5-a70b-7456348fd5fd
	 *
	 */
	public function retry_inbound_message( $args ) {

		$path = "messages/inbound/{$args[0]}/retry";

		// Performs the bypass.
		$response = $this->server_api_call( 'put', $path, ' ' );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Retrives open events.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of opens to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of opens to skip.
	 *
	 * [--recipient=<recipient>]
	 * : by the user who was receiving the email.
	 *
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--client_name=<client_name>]
	 * : Filter by client name, i.e. Outlook, Gmail.
	 *
	 * [--client_company=<client_company>]
	 * : Filter by company, i.e. Microsoft, Apple, Google
	 *
	 * [--client_family=<client_family>]
	 * : Filter by client family, i.e. OS X, Chrome.
	 *
	 * [--os_name=<os_name>]
	 * : Filter by full OS name and specific version, i.e. OS X 10.9 Mavericks, Windows 7
	 *
	 * [--os_family=<os_family>]
	 * : Filter by kind of OS used without specific version, i.e. OS X, Windows.
	 *
	 * [--os_company=<os_company>]
	 * : Filter by company which produced the OS, i.e. Apple Computer, Inc., Microsoft Corporation
	 *
	 * [--platform=<platform>]
	 * : Filter by platform, i.e. webmail, desktop, mobile
	 *
	 * [--country=<country>]
	 * : Filter by country messages were opened in, i.e. Denmark, Russia
	 *
	 * [--region=<region>]
	 * : Filter by full name of region messages were opened in, i.e. Moscow, New York
	 *
	 * [--city=<city>]
	 * : Filter by full name of city messages were opened in, i.e. Minneapolis, Philadelphia
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_opens
	 */
	public function get_opens( $args, $assoc_args ) {

		$path = 'messages/outbound/opens';

		if ( isset( $assoc_args['count'] ) ) {
			$path .= '?count=' . $assoc_args['count'];
		} else {
			$path .= '?count=500';
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=' . $assoc_args['offset'];
		} else {
			$path .= '&offset=0';
		}

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves opens.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets opens for an outbound message.
	 *
	 * ## OPTIONS
	 * <messageid>
	 * : Message ID of the message to retrieve opens for.
	 *
	 * [--count=<count>]
	 * : Number of opens to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of opens to skip.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_message_opens 841cec01-5ed8-4a77-aef5-de52f10b3905
	 */
	public function get_message_opens( $args, $assoc_args ) {

		$path = "messages/outbound/opens/{$args[0]}";

		if ( isset( $assoc_args['count'] ) ) {
			$path .= '?count=' . $assoc_args['count'];
		} else {
			$path .= '?count=500';
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=' . $assoc_args['offset'];
		} else {
			$path .= '&offset=0';
		}

		// Retrieves message opens.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Retrives click events.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of opens to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of opens to skip.
	 *
	 * [--recipient=<recipient>]
	 * : by the user who was receiving the email.
	 *
	 * [--tag=<tag>]
	 * : Filter by tag.
	 *
	 * [--client_name=<client_name>]
	 * : Filter by client name, i.e. Outlook, Gmail.
	 *
	 * [--client_company=<client_company>]
	 * : Filter by company, i.e. Microsoft, Apple, Google
	 *
	 * [--client_family=<client_family>]
	 * : Filter by client family, i.e. OS X, Chrome.
	 *
	 * [--os_name=<os_name>]
	 * : Filter by full OS name and specific version, i.e. OS X 10.9 Mavericks, Windows 7
	 *
	 * [--os_family=<os_family>]
	 * : Filter by kind of OS used without specific version, i.e. OS X, Windows.
	 *
	 * [--os_company=<os_company>]
	 * : Filter by company which produced the OS, i.e. Apple Computer, Inc., Microsoft Corporation
	 *
	 * [--platform=<platform>]
	 * : Filter by platform, i.e. webmail, desktop, mobile
	 *
	 * [--country=<country>]
	 * : Filter by country messages were opened in, i.e. Denmark, Russia
	 *
	 * [--region=<region>]
	 * : Filter by full name of region messages were opened in, i.e. Moscow, New York
	 *
	 * [--city=<city>]
	 * : Filter by full name of city messages were opened in, i.e. Minneapolis, Philadelphia
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_clicks
	 *
	 */
	public function get_clicks( $args, $assoc_args ) {

		$path = 'messages/outbound/clicks';

		if ( isset( $assoc_args['count'] ) ) {
			$path .= '?count=' . $assoc_args['count'];
		} else {
			$path .= '?count=500';
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=' . $assoc_args['offset'];
		} else {
			$path .= '&offset=0';
		}

		$path = postmark_cli_add_query_params( $assoc_args, $path );

		// Retrieves clicks.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Gets clicks for an outbound message.
	 *
	 * ## OPTIONS
	 * <messageid>
	 * : Message ID of the message to retrieve clicks for.
	 *
	 * [--count=<count>]
	 * : Number of clicks to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of clicks to skip.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_message_clicks 841cec01-5ed8-4a77-aef5-de52f10b3905
	 */
	public function get_message_clicks( $args, $assoc_args ) {

		$path = "messages/outbound/clicks/{$args[0]}";

		if ( isset( $assoc_args['count'] ) ) {
			$path .= '?count=' . $assoc_args['count'];
		} else {
			$path .= '?count=500';
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=' . $assoc_args['offset'];
		} else {
			$path .= '&offset=0';
		}

		// Retrieves a message's clicks.
		$response = $this->server_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	} // End Messages API

	/**********************************************
	***************** Servers API *****************
	**********************************************/

	/**
	 * Retrieves a single server's details.
	 *
	 * ## OPTIONS
	 * <serverid>
	 * : Server ID of the server to retrieve.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_server 4523253
	 */
	public function get_server( $args, $assoc_args ) {

		$path = "servers/{$args[0]}";

		// Retrieves server details.
		$response = $this->account_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Creates a new server.
	 *
	 * ## OPTIONS
	 * <name>
	 * : Name of server.
	 *
	 * [--servercolor=<servercolor>]
	 * : Color of the server in the rack screen. Purple Blue Turqoise Green Red
	 * Yellow Grey Orange
	 *
	 * [--smtpapiactivated]
	 * : Specifies whether or not SMTP is enabled on this server.
	 *
	 * [--rawemailenabled]
	 * : When enabled, the raw email content will be included with inbound webhook
	 * payloads under the RawEmail key.
	 *
	 * [--deliveryhookurl=<deliveryhookurl>]
	 * : URL to POST to every time email is delivered.
	 *
	 * [--inboundhookurl=<inboundhookurl>]
	 * : URL to POST to every time an inbound event occurs.
	 *
	 * [--bouncehookurl=<bouncehookurl>]
	 * : URL to POST to every time a bounce event occurs.
	 *
	 * [--includebouncecontentinhook]
	 * : Include bounce content in webhook.
	 *
	 * [--openhookurl=<openhookurl>]
	 * : URL to POST to every time an open event occurs.
	 *
	 * [--postfirstopenonly]
	 * : If set to true, only the first open by a particular recipient will initiate
	 * the open webhook. Any subsequent opens of the same email by the same
	 * recipient will not initiate the webhook.
	 *
	 * [--trackopens]
	 * : Indicates if all emails being sent through this server have open tracking
	 * enabled.
	 *
	 * [--tracklinks=<tracklinks>]
	 * : Indicates if all emails being sent through this server should have link
	 * tracking enabled for links in their HTML or Text bodies. Possible options:
	 * None HtmlAndText HtmlOnly TextOnly
	 *
	 * [--clickhookurl=<clickhookurl>]
	 * : URL to POST to when a unique click event occurs.
	 *
	 * [--inbounddomain=<inbounddomain>]
	 * : Inbound domain for MX setup.
	 *
	 * [--inboundspamthreshold=<inboundspamthreshold>]
	 * : The maximum spam score for an inbound message before it's blocked.
	 *
	 * [--enablesmtpapierrorhooks]
	 * : Specifies whether or not SMTP API Errors will be included with bounce
	 * webhooks.
	 *
	 * ## EXAMPLES
	 * $ wp postmark create_server "My Newest Server"
	 */
	public function create_server( $args, $assoc_args ) {

		$path = 'servers';

		$new_server = array( 'Name' => "{$args[0]}" );

		if ( isset( $assoc_args['smtpapiactivated'] ) ) {
			$new_server['SmtpApiActivated'] = 'true';
		}

		if ( isset( $assoc_args['rawemailenabled'] ) ) {
			$new_server['RawEmailEnabled'] = 'true';
		}

		if ( isset( $assoc_args['deliveryhookurl'] ) ) {
			$new_server['DeliveryHookUrl'] = $assoc_args['deliveryhookurl'];
		}

		if ( isset( $assoc_args['inboundhookurl'] ) ) {
			$new_server['InboundHookUrl'] = $assoc_args['inboundhookurl'];
		}

		if ( isset( $assoc_args['bouncehookurl'] ) ) {
			$new_server['BounceHookUrl'] = $assoc_args['bouncehookurl'];
		}

		if ( isset( $assoc_args['includebouncecontentinhook'] ) ) {
			$new_server['IncludeBounceContentInHook'] = 'true';
		}

		if ( isset( $assoc_args['openhookurl'] ) ) {
			$new_server['OpenHookUrl'] = $assoc_args['openhookurl'];
		}

		if ( isset( $assoc_args['postfirstopenonly'] ) ) {
			$new_server['PostFirstOpenOnly'] = 'true';
		}

		if ( isset( $assoc_args['trackopens'] ) ) {
			$new_server['TrackOpens'] = 'true';
		}

		if ( isset( $assoc_args['clickhookurl'] ) ) {
			$new_server['ClickHookUrl'] = $assoc_args['clickhookurl'];
		}

		if ( isset( $assoc_args['inbounddomain'] ) ) {
			$new_server['InboundDomain'] = $assoc_args['inbounddomain'];
		}

		if ( isset( $assoc_args['inboundspamthreshold'] ) && is_int( $assoc_args['inboundspamthreshold'] ) ) {
			$new_server['InboundSpamThreshold'] = $assoc_args['inboundspamthreshold'];
		}

		if ( isset( $assoc_args['enablesmtpapierrorhooks'] ) ) {
			$new_server['EnableSmtpApiErrorHooks'] = 'true';
		}

		// Uses server color if present.
		if ( isset( $assoc_args['servercolor'] ) ) {

			switch ( $assoc_args['servercolor'] ) {
				case 'purple':
					$new_server['Color'] = 'purple';
					break;

				case 'blue':
					$new_server['Color'] = 'blue';
					break;

				case 'turqoise':
					$new_server['Color'] = 'turqoise';
					break;

				case 'green':
					$new_server['Color'] = 'green';
					break;

				case 'red':
					$new_server['Color'] = 'red';
					break;

				case 'yellow':
					$new_server['Color'] = 'yellow';
					break;

				case 'grey':
					$new_server['Color'] = 'grey';
					break;

				case 'orange':
					$new_server['Color'] = 'orange';
					break;

				default:
					WP_CLI::Warning( 'Possible values for colors are purple, blue, turquoise, green, red, yellow, grey, or orange.' );
					break;
			}
		}

		// Uses track links value if present.
		if ( isset( $assoc_args['tracklinks'] ) ) {
			switch ( $assoc_args['tracklinks'] ) {
				case 'None':
					$new_server['TrackLinks'] = 'None';
					break;
				case 'HtmlAndText':
					$new_server['TrackLinks'] = 'HtmlAndText';
					break;
				case 'HtmlOnly':
					$new_server['TrackLinks'] = 'HtmlOnly';
					break;
				case 'TextOnly':
					$new_server['TrackLinks'] = 'TextOnly';
					break;
				default:
					WP_CLI::Warning( 'Possible values for track links option are None, HtmlAndText, HtmlOnly, or TextOnly. Setting to None.' );
					break;
			}
		}

		// Creates new server.
		$response = $this->account_api_call( 'post', $path, wp_json_encode( $new_server ) );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Edits an existing server.
	 *
	 * ## OPTIONS
	 * <serverid>
	 * : ID of server to edit.
	 *
	 * * [--name=<name>]
	 * : Name of the server.
	 *
	 * [--servercolor=<servercolor>]
	 * : Color of the server in the rack screen. Purple Blue Turqoise Green Red
	 * Yellow Grey Orange
	 *
	 * [--smtpapiactivated=<smtpapiactivated>]
	 * : Specifies whether or not SMTP is enabled on this server.
	 *
	 * [--rawemailenabled=<rawemailenabled>]
	 * : When enabled, the raw email content will be included with inbound webhook
	 * payloads under the RawEmail key.
	 *
	 * [--deliveryhookurl=<deliveryhookurl>]
	 * : URL to POST to every time email is delivered.
	 *
	 * [--inboundhookurl=<inboundhookurl>]
	 * : URL to POST to every time an inbound event occurs.
	 *
	 * [--bouncehookurl=<bouncehookurl>]
	 * : URL to POST to every time a bounce event occurs.
	 *
	 * [--includebouncecontentinhook=<includebouncecontentinhook>]
	 * : Include bounce content in webhook.
	 *
	 * [--openhookurl=<openhookurl>]
	 * : URL to POST to every time an open event occurs.
	 *
	 * [--postfirstopenonly=<postfirstopenonly>]
	 * : If set to true, only the first open by a particular recipient will initiate
	 * the open webhook. Any subsequent opens of the same email by the same
	 * recipient will not initiate the webhook.
	 *
	 * [--trackopens=<trackopens>]
	 * : Indicates if all emails being sent through this server have open tracking
	 * enabled.
	 *
	 * [--tracklinks=<tracklinks>]
	 * : Indicates if all emails being sent through this server should have link
	 * tracking enabled for links in their HTML or Text bodies. Possible options:
	 * None HtmlAndText HtmlOnly TextOnly
	 *
	 * [--clickhookurl=<clickhookurl>]
	 * : URL to POST to when a unique click event occurs.
	 *
	 * [--inbounddomain=<inbounddomain>]
	 * : Inbound domain for MX setup.
	 *
	 * [--inboundspamthreshold=<inboundspamthreshold>]
	 * : The maximum spam score for an inbound message before it's blocked.
	 *
	 * [--enablesmtpapierrorhooks=<enablesmtpapierrorhooks>]
	 * : Specifies whether or not SMTP API Errors will be included with bounce
	 * webhooks.
	 *
	 * ## EXAMPLES
	 * $ wp postmark edit_server 4523253
	 */
	public function edit_server( $args, $assoc_args ) {

		$path = "servers/{$args[0]}";

		$server_edits = array();

		if ( isset( $assoc_args['name'] ) ) {
			$server_edits['Name'] = $assoc_args['name'];
		}

		if ( isset( $assoc_args['smtpapiactivated'] ) ) {
			$server_edits['SmtpApiActivated'] = $assoc_args['smtpapiactivated'];
		}

		if ( isset( $assoc_args['rawemailenabled'] ) ) {
			$server_edits['RawEmailEnabled'] = $assoc_args['rawemailenabled'];
		}

		if ( isset( $assoc_args['deliveryhookurl'] ) ) {
			$server_edits['DeliveryHookUrl'] = $assoc_args['deliveryhookurl'];
		}

		if ( isset( $assoc_args['inboundhookurl'] ) ) {
			$server_edits['InboundHookUrl'] = $assoc_args['inboundhookurl'];
		}

		if ( isset( $assoc_args['bouncehookurl'] ) ) {
			$server_edits['BounceHookUrl'] = $assoc_args['bouncehookurl'];
		}

		if ( isset( $assoc_args['includebouncecontentinhook'] ) ) {
			$server_edits['IncludeBounceContentInHook'] = $assoc_args['includebouncecontentinhook'];
		}

		if ( isset( $assoc_args['openhookurl'] ) ) {
			$server_edits['OpenHookUrl'] = $assoc_args['openhookurl'];
		}

		if ( isset( $assoc_args['postfirstopenonly'] ) ) {
			$server_edits['PostFirstOpenOnly'] = $assoc_args['postfirstopenonly'];
		}

		if ( isset( $assoc_args['trackopens'] ) ) {
			$server_edits['TrackOpens'] = $assoc_args['trackopens'];
		}

		if ( isset( $assoc_args['clickhookurl'] ) ) {
			$server_edits['ClickHookUrl'] = $assoc_args['clickhookurl'];
		}

		if ( isset( $assoc_args['inbounddomain'] ) ) {
			$server_edits['InboundDomain'] = $assoc_args['inbounddomain'];
		}

		if ( isset( $assoc_args['inboundspamthreshold'] ) && is_int( $assoc_args['inboundspamthreshold'] ) ) {
			$server_edits['InboundSpamThreshold'] = $assoc_args['inboundspamthreshold'];
		}

		if ( isset( $assoc_args['enablesmtpapierrorhooks'] ) ) {
			$server_edits['EnableSmtpApiErrorHooks'] = $assoc_args['enablesmtpapierrorhooks'];
		}

		// Uses server color if present.
		if ( isset( $assoc_args['servercolor'] ) ) {

			switch ( $assoc_args['servercolor'] ) {
				case 'purple':
					$server_edits['Color'] = 'purple';
					break;

				case 'blue':
					$server_edits['Color'] = 'blue';
					break;

				case 'turqoise':
					$server_edits['Color'] = 'turqoise';
					break;

				case 'green':
					$server_edits['Color'] = 'green';
					break;

				case 'red':
					$server_edits['Color'] = 'red';
					break;

				case 'yellow':
					$server_edits['Color'] = 'yellow';
					break;

				case 'grey':
					$server_edits['Color'] = 'grey';
					break;

				case 'orange':
					$server_edits['Color'] = 'orange';
					break;

				default:
					WP_CLI::Warning( 'Possible values for colors are purple, blue, turquoise, green, red, yellow, grey, or orange.' );
					break;
			}
		}

		// Uses track links value if present.
		if ( isset( $assoc_args['tracklinks'] ) ) {
			switch ( $assoc_args['tracklinks'] ) {

				case 'None':
					$server_edits['TrackLinks'] = 'None';
					break;

				case 'HtmlAndText':
					$server_edits['TrackLinks'] = 'HtmlAndText';
					break;

				case 'HtmlOnly':
					$server_edits['TrackLinks'] = 'HtmlOnly';
					break;

				case 'TextOnly':
					$server_edits['TrackLinks'] = 'TextOnly';
					break;

				default:
					WP_CLI::Warning( 'Possible values for track links option are None, HtmlAndText, HtmlOnly, or TextOnly. Setting to None.' );
					break;
			}
		}

		// Edits the server.
		$response = $this->account_api_call( 'put', $path, wp_json_encode( $server_edits ) );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Retrieves a list of all servers.
	 *
	 * ## OPTIONS
	 * [--count=<count>]
	 * : Number of servers to retrieve.
	 *
	 * [--offset=<offset>]
	 * : Number of servers to skip.
	 *
	 * [--name=<name>]
	 * : Filter by a specific server name. Note that this is a string search, so
	 * MyServer will match MyServer, MyServer Production, and MyServer Test.
	 *
	 * ## EXAMPLES
	 * $ wp postmark get_servers
	 */
	public function get_servers( $args, $assoc_args ) {

		$path = 'servers';

		if ( isset( $assoc_args['count'] ) ) {
			$path .= '?count=' . $assoc_args['count'];
		} else {
			$path .= '?count=500';
		}

		if ( isset( $assoc_args['offset'] ) ) {
			$path .= '&offset=' . $assoc_args['offset'];
		} else {
			$path .= '&offset=0';
		}

		if ( isset( $assoc_args['name'] ) ) {
			$path .= '&name=' . $assoc_args['name'];
		}

		// Retrieves server details.
		$response = $this->account_api_call( 'get', $path, null );

		postmark_cli_handle_response( $response );

	}

	/**
	 * Deletes an existing server.
	 *
	 * ## OPTIONS
	 * <serverid>
	 * : ID of server to delete.
	 *
	 * ## EXAMPLES
	 * $ wp postmark delete_server 4523253
	 */
	public function delete_server( $args, $assoc_args ) {

		$path = "servers/{$args[0]}";

		// Deletes the server.
		$response = $this->account_api_call( 'delete', $path, null );

		postmark_cli_handle_response( $response );

	} // End Servers API

} // End PostmarkPluginCLI

// Helper function for displaying Postmark API response in CLI.
function postmark_cli_handle_response( $response ) {

	$body = json_decode( wp_remote_retrieve_body( $response ) );

	// Successful API call (200 response).
	if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

		WP_CLI::success( wp_json_encode( $body, JSON_PRETTY_PRINT ) );

	// Non-200 response from Postmark API.
	} elseif ( false === ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {

		postmark_cli_handle_api_error( $response );

	} else {

		WP_CLI::error( 'Error occurred with command. API call failed.' );
	}
}

// Displays Postmark API error response codes + messages.
function postmark_cli_handle_api_error( $response ) {
	WP_CLI::warning( 'Error occurred. Check API response from Postmark for more details.' );

	$error_message = [];

	$error_body =  json_decode( wp_remote_retrieve_body( $response ), true );

	array_push( $error_message, 'Postmark API Error Code: ' . $error_body["ErrorCode"] );

	array_push( $error_message, 'Postmark Error Message: ' . $error_body["Message"] );

	WP_CLI::error_multi_line( $error_message );
}

// Generates a csv file.
function postmark_cli_make_csv( $csv_headers, $body, $type ) {

	$current_time = str_replace( ':', ' ', ( date( 'F j, Y, g:i a' ) ) );

	$dir = dirname( __FILE__ );

	// Makes a file in the current directory for the csv.
	$file = fopen( "{$dir}/{$type}_{$current_time}.csv", 'w' );

	// Outputs data to a CSV file.

	$csv_data = array();

	array_push($csv_data, $csv_headers);

	switch ( $type ) {

		case 'delivery_stats':

			foreach ( $body as $key => $value ) {

				if ( "InactiveMails" == $key ) {

					array_push( $csv_data, array($key, '', $value) );

				} else {

					foreach ( $value as $v ) {
						array_push( $csv_data, array($v['Type'], $v['Name'], $v['Count'] ) );
					}
				}
			}

			break;

		case 'templated_batch_send':

			foreach ( $body as $send_result ) {

				$recipients = $send_result['To'];

				$status = $send_result['MessageID'] ? 'Sent' : 'Failed - ' . preg_replace( "/\r|\n/", ' ', $send_result['Message'] );

				$message_id = $send_result['MessageID'] ? $send_result['MessageID'] : '';

				array_push( $csv_data, array( $recipients, $status, $message_id ) );
			}

			break;

		case 'batch_send':
			foreach ( $body as $send_result ) {

				$recipients = $send_result['To'];

				$status = $send_result['MessageID'] ? 'Sent' : 'Failed - ' . preg_replace( "/\r|\n/", ' ', $send_result['Message'] );

				$message_id = $send_result['MessageID'] ? $send_result['MessageID'] : '';

				array_push( $csv_data, array( $recipients, $status, $message_id) );

			}
			break;

		case 'bounces':

			foreach ( $body['Bounces'] as $bounce ) {

				array_push( $csv_data, array( $bounce['Email'], $bounce['Type'], $bounce['ID'], $bounce['BouncedAt'] ) );

			}
			break;
	}

	foreach ($csv_data as $fields) {
    fputcsv($file, $fields);
	}

	fclose( $file );
}

// Checks if server token is set in Postmark plugin settings.
function postmark_cli_check_server_token_is_set() {

	$postmark_settings = json_decode( get_option( 'postmark_settings' ), true );

	if ( ! isset( $postmark_settings['api_key'] ) ) {

		WP_CLI::error( 'You need to set your Server API Token in the Postmark plugin settings.' );

		return false;

	} else {

		return true;

	}

}

function postmark_cli_check_account_token_is_set() {

	if ( null === POSTMARK_ACCOUNT_TOKEN ) {

		WP_CLI::error( 'You need to set your Account API Token in your wp-config.php file.' );

		return false;

	} else {

		return true;

	}

}

// Adds query params to API call endpoint from associated arguments.
function postmark_cli_add_query_params( $assoc_args, $path ) {
	// Adds query params, if present.
	foreach( $assoc_args as $qp => $value ) {
		$path = add_query_arg( $qp, $value, $path );
	}

	return $path;
}

// Makes sure Postmark plugin is activated before adding
// Postmark wp cli commands.
if ( class_exists( 'Postmark_Mail' ) && class_exists( 'WP_CLI' ) ) {

	WP_CLI::add_command( 'postmark', 'PostmarkPluginCLI' );

}
