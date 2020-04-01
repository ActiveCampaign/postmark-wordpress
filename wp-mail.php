<?php
require_once dirname( __FILE__ ) . '/postmark.php';

/**
 * Determine MIME Content Type.
 *
 * @param  string $filename Filename.
 */
function postmark_determine_mime_content_type( $filename ) {
	if ( function_exists( 'mime_content_type' ) ) {
		return mime_content_type( $filename );
	} elseif ( function_exists( 'finfo_open' ) ) {
		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $filename );
		finfo_close( $finfo );
		return $mime_type;
	} else {
		return 'application/octet-stream';
	}
}

/**
 *  WP Mail.
 *
 * @param  [type] $to          TO.
 * @param  [type] $subject     Subject.
 * @param  [type] $message     Message.
 * @param  string $headers     Headers.
 * @param  array  $attachments Attachments.
 */
function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {

	// Compact the input, apply the filters, and extract them back out.
	extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

	$settings = json_decode( get_option( 'postmark_settings' ), true );

	if ( ! is_array( $attachments ) ) {
		$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
	}

	if ( ! empty( $headers ) && ! is_array( $headers ) ) {
		$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
	}

	/*
	==================================================
		Parse headers
	==================================================
	*/

	$recognized_headers = array();

	$headers_list = array(
		'Content-Type'     => array(),
		'Cc'               => array(),
		'Bcc'              => array(),
		'Reply-To'         => array(),
		'From'             => array(),
		'X-PM-Track-Opens' => array(),
		'X-PM-TrackLinks'  => array(),
	);

	$headers_list_lowercase = array_change_key_case( $headers_list, CASE_LOWER );

	if ( ! empty( $headers ) ) {
		foreach ( $headers as $key => $header ) {
			$key = strtolower( $key );
			if ( array_key_exists( $key, $headers_list_lowercase ) ) {
				$header_key = $key;
				$header_val = $header;
				$segments   = explode( ':', $header );
				if ( 2 === count( $segments ) ) {
					if ( array_key_exists( strtolower( $segments[0] ), $headers_list_lowercase ) ) {
						list( $header_key, $header_val ) = $segments;
						$header_key                      = strtolower( $header_key );
					}
				}
			} else {
				$segments = explode( ':', $header );
				if ( 2 === count( $segments ) ) {
					if ( array_key_exists( strtolower( $segments[0] ), $headers_list_lowercase ) ) {
						list( $header_key, $header_val ) = $segments;
						$header_key                      = strtolower( $header_key );
					}
				}
			}

			// If the key was detected, assign it.
			if ( isset( $header_key ) && isset( $header_val ) ) {
				if ( false === stripos( $header_val, ',' ) ) {
					$headers_list_lowercase[ $header_key ][] = trim( $header_val );
				} else {
					$vals = explode( ',', $header_val );
					foreach ( $vals as $val ) {
						$headers_list_lowercase[ $header_key ][] = trim( $val );
					}
				}

				unset( $header_key );
				unset( $header_val );
			}
		}

		foreach ( $headers_list as $key => $value ) {
			$value = $headers_list_lowercase[ strtolower( $key ) ];
			if ( count( $value ) > 0 ) {
				$recognized_headers[ $key ] = implode( ', ', $value );
			}
		}
	}

	/*
	==================================================
		Content-Type hook
	==================================================
	*/

	$content_type = 'text/plain';
	if ( isset( $recognized_headers['Content-Type'] ) ) {
		if ( false !== strpos( $recognized_headers['Content-Type'], 'text/html' ) ) {
			$content_type = 'text/html';
		}
	}
	$content_type = apply_filters( 'wp_mail_content_type', $content_type );

	/*
	==================================================
		Generate POST payload
	==================================================
	*/

	// Allow overriding the From address when specified in the headers.
	$from = $settings['sender_address'];

	if ( isset( $recognized_headers['From'] ) ) {
		$from = $recognized_headers['From'];
	}

	$body = array(
		'To'       => is_array( $to ) ? implode( ',', $to ) : $to,
		'From'     => $from,
		'Subject'  => $subject,
		'TextBody' => $message,
	);

	if ( ! empty( $recognized_headers['Cc'] ) ) {
		$body['Cc'] = $recognized_headers['Cc'];
	}

	if ( ! empty( $recognized_headers['Bcc'] ) ) {
		$body['Bcc'] = $recognized_headers['Bcc'];
	}

	if ( ! empty( $recognized_headers['Reply-To'] ) ) {
		$body['ReplyTo'] = $recognized_headers['Reply-To'];
	}

	if ( isset( $settings['track_opens'] ) ) {
		$track_opens = (int) $settings['track_opens'];
	}

	if ( isset( $settings['track_links'] ) ) {
		$track_links = (int) $settings['track_links'];
	} else {
		$track_links = 0;
	}

	if ( isset( $recognized_headers['X-PM-Track-Opens'] ) ) {
		if ( $recognized_headers['X-PM-Track-Opens'] ) {
			$track_opens = 1;
		} else {
			$track_opens = 0;
		}
	}

	if ( isset( $recognized_headers['X-PM-TrackLinks'] ) ) {
		if ( 'none' !== $recognized_headers['X-PM-TrackLinks'] ) {
			$body['TrackLinks'] = $recognized_headers['X-PM-TrackLinks'];
		} else {
			$body['TrackLinks'] = 'none';
		}
	}

	if ( 1 === (int) $settings['force_html'] || 'text/html' === $content_type || 1 === $track_opens ) {
		$body['HtmlBody'] = $message;
		// The user really, truly wants this sent as HTML, don't send it as text, too.
		// For historical reasons, we can't "force html" and "track opens" set both html and text bodies,
		// which is incorrect, but in order not to break existing behavior, we only strip out the textbody when
		// the user has gone to the trouble of specifying content type of 'text/html' in their headers.
		if ( 'text/html' === $content_type ) {
			unset( $body['TextBody'] );
		}
	}

	if ( 1 === $track_opens ) {
		$body['TrackOpens'] = 'true';
	}

	if ( 1 === $track_links ) {
		$body['TrackLinks'] = 'HtmlAndText';
	}

	if ( 'outbound' !== $settings['stream_name'] ) {
		$body['MessageStream'] = $settings['stream_name'];
	} else {
		$body['MessageStream'] = 'outbound';
	}

	foreach ( $attachments as $attachment ) {
		if ( is_readable( $attachment ) ) {
			$body['Attachments'][] = array(
				'Name'        => basename( $attachment ),
				'Content'     => base64_encode( file_get_contents( $attachment ) ),
				'ContentType' => postmark_determine_mime_content_type( $attachment ),
			);
		}
	}

	/*
	==================================================
		Send email
	==================================================
	*/

	$args     = array(
		'headers' => array(
			'Accept'                  => 'application/json',
			'Content-Type'            => 'application/json',
			'X-Postmark-Server-Token' => $settings['api_key'],
		),
		'body'    => wp_json_encode( $body ),
	);
	$response = wp_remote_post( 'https://api.postmarkapp.com/email', $args );

	// Logs send attempt, if logging enabled.
	if ( isset( $settings['enable_logs'] ) && 1 === $settings['enable_logs'] ) {
		global $wpdb;
		$table = $wpdb->prefix . 'postmark_log';
		$to    = $body['To'];

		// Only store the To address, not the To name.
		if ( false !== strpos( $body['To'], '<' ) && false !== strpos( $to, '>' ) ) {
			$to = substr( $body['To'], strpos( $body['To'], '<' ), -1 );
		}

		// Only store the From address, not the From name.
		if ( false !== strpos( $from, '<' ) && false !== strpos( $from, '>' ) ) {
			$from = substr( $from, strpos( $from, '<' ), strpos( $from, '>' ) - 1 );
		}

		$log_entry = array(
			'log_entry_date' => current_time( 'mysql' ),
			'fromaddress'    => sanitize_email( $from ),
			'toaddress'      => sanitize_email( $to ),
			'subject'        => sanitize_text_field( $subject ),
		);

		if ( is_array( $response ) ) {
			$log_entry['response'] = sanitize_text_field( $response['body'] );

		} elseif ( is_wp_error( $response ) ) {
			$log_entry['response'] = $response->get_error_message();
		}

		$wpdb->insert( $table, $log_entry );
	}

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		do_action( 'postmark_error', $response, $headers );
		Postmark_Mail::$LAST_ERROR = $response;
		return false;
	}

	do_action( 'postmark_response', $response, $headers );

	return true;
}
