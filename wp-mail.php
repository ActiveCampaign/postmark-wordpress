<?php

function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {

    // Compact the input, apply the filters, and extract them back out
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
        'Content-Type'  => array(),
        'Cc'            => array(),
        'Bcc'           => array(),
        'Reply-To'      => array()
    );

    $headers_list_lowercase = array_change_key_case( $headers_list, CASE_LOWER );

    if ( ! empty( $headers ) ) {
        foreach ( $headers as $key => $header ) {
            $key = strtolower( $key );
            if ( array_key_exists( $key, $headers_list_lowercase ) ) {
                $header_key = $key;
                $header_val = $header;
                $segments = explode( ':', $header );
                if ( 2 === count( $segments ) ) {
                    if ( array_key_exists( strtolower( $segments[0] ), $headers_list_lowercase ) ) {
                        list( $header_key, $header_val ) = $segments;
                        $header_key = strtolower( $header_key );
                    }
                }
            }
            else {
                $segments = explode( ':', $header );
                if ( 2 === count( $segments ) ) {
                    if ( array_key_exists( strtolower( $segments[0] ), $headers_list_lowercase ) ) {
                        list( $header_key, $header_val ) = $segments;
                        $header_key = strtolower( $header_key );
                    }
                }
            }

            if ( isset( $header_key ) && isset( $header_val ) ) {
                if ( false === stripos( $header_val, ',' ) ) {
                    $headers_list_lowercase[ $header_key ][] = trim( $header_val );
                }
                else {
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
    if ( isset( $recognized_headers[ 'Content-Type'] ) ) {
        if ( false !== strpos( $recognized_headers[ 'Content-Type'], 'text/html' ) ) {
            $content_type = 'text/html';
        }
    }
    $content_type = apply_filters( 'wp_mail_content_type', $content_type );

    /*
    ==================================================
        Generate POST payload
    ==================================================
    */

    $body = array(
        'To'        => is_array( $to ) ? implode( ',', $to ) : $to,
        'From'      => $settings['sender_address'],
        'Subject'   => $subject,
        'TextBody'  => $message,
    );

    if ( ! empty( $recognized_headers['Cc'] ) ) {
        $body['Cc'] = $recognized_headers['Cc'];
    }

    if ( ! empty($recognized_headers['Bcc'] ) ) {
        $body['Bcc'] = $recognized_headers['Bcc'];
    }

    if ( ! empty($recognized_headers['Reply-To'] ) ) {
        $body['ReplyTo'] = $recognized_headers['Reply-To'];
    }

    if ( 1 == (int) $settings['force_html'] || 'text/html' == $content_type ) {
        $body['HtmlBody'] = $message;
    }
    elseif ( 1 == (int) $settings['force_html'] || 1 == (int) $settings['track_opens'] ) {
        $body['HtmlBody'] = $message;
    }

    if ( 1 == (int) $settings['track_opens'] ) {
        $body['TrackOpens'] = 'true';
    }

    foreach ( $attachments as $attachment ) {
        $body['Attachments'][] = array(
            'Name'          => basename( $attachment ),
            'Content'       => base64_encode( file_get_contents( $attachment ) ),
            'ContentType'   => mime_content_type( $attachment ),
        );
    }

    /*
    ==================================================
        Send email
    ==================================================
    */

    $args = array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Postmark-Server-Token' => $settings['api_key']
        ),
        'body' => json_encode( $body )
    );
    $response = wp_remote_post( 'https://api.postmarkapp.com/email', $args );

    if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
        return false;
    }

    return true;
}
