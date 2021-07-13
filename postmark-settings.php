<?php
/**
 * Override settings
 *
 * @return array $settings Settings overridden by constants
 */

$settings = json_decode( get_option( 'postmark_settings' ), true );
$settings_from_constants = array(
    'api_key'        => defined('POSTMARK_API_KEY') ? POSTMARK_API_KEY : null,
    'stream_name'    => defined('POSTMARK_STREAM_NAME') ? POSTMARK_STREAM_NAME : null,
    'sender_address' => defined('POSTMARK_SENDER_ADDRESS') ? POSTMARK_SENDER_ADDRESS : null,
);
$settings = array_merge( $settings, $settings_from_constants );
return $settings;
