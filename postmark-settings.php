<?php
/**
 * Override settings
 *
 * @return array $settings Settings overridden by constants
 */

$settings = json_decode( get_option( 'postmark_settings' ), true );
$settings_from_constants = array(
    'api_key'        => defined( 'POSTMARK_API_KEY' ) ? POSTMARK_API_KEY : null,
    'stream_name'    => defined( 'POSTMARK_STREAM_NAME' ) ? POSTMARK_STREAM_NAME : null,
    'sender_address' => defined( 'POSTMARK_SENDER_ADDRESS' ) ? POSTMARK_SENDER_ADDRESS : null,
);
$settings_from_constants = array_filter( $settings_from_constants );
$settings = array_merge( $settings, $settings_from_constants );
return array(
    'settings' => $settings,
    'settings_from_constants' => $settings_from_constants,
);
