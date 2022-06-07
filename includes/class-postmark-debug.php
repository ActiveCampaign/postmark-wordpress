<?php

if( ! class_exists( 'Postmark_Debug' ) ) {

  class Postmark_Debug {

    /**
     * [__construct description]
     */
    public function __construct() {
      // https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
      add_filter( 'debug_information', [ $this, 'debug_info' ] );
    }

    /**
     * [debug_info description]
     * @param  [type] $debug_info               [description]
     * @return [type]             [description]
     */
    public function debug_info( $debug_info ) {

    	$settings = json_decode( get_option( 'postmark_settings' ) );
    	$enabled = $settings->enabled;

        $debug_info['postmark-wordpress'] = array(
            'label'    => __( 'Active Campaign Postmark', 'postmark-wordpress' ),
            'fields'   => array(
                'status' => array(
                    'label'    => __( 'Enabled', 'postmark-wordpress' ),
                    'value'   =>  $this->strbool( $enabled ),
                    'private' => false,
                ),
    						'version' => array(
                    'label'    => __( 'POSTMARK_VERSION', 'postmark-wordpress' ),
                    'value'   => POSTMARK_VERSION,
                    'private' => false,
                ),
    						'directory' => array(
                    'label'    => __( 'POSTMARK_DIR', 'postmark-wordpress' ),
                    'value'   => POSTMARK_DIR,
                    'private' => false,
                ),
    						'url' => array(
                    'label'    => __( 'POSTMARK_URL', 'postmark-wordpress' ),
                    'value'   => POSTMARK_URL,
                    'private' => false,
                ),
    						'apikey' => array(
                    'label'    => __( 'API Key', 'postmark-wordpress' ),
                    'value'   => $settings->api_key,
                    'private' => true,
                ),
    						'stream' => array(
                    'label'    => __( 'Stream', 'postmark-wordpress' ),
                    'value'   => $settings->stream_name,
                    'private' => false,
                ),
    						'sender_address' => array(
                    'label'    => __( 'Sender Address', 'postmark-wordpress' ),
                    'value'   => $settings->sender_address,
                    'private' => false,
                ),
    						'force_from' => array(
                    'label'    => __( 'Force From', 'postmark-wordpress' ),
                    'value'   => $this->strbool( $settings->force_from ),
                    'private' => false,
                ),
    						'force_html' => array(
                    'label'    => __( 'Force HTML', 'postmark-wordpress' ),
                    'value'   => $this->strbool( $settings->force_html ),
                    'private' => false,
                ),
    						'track_opens' => array(
                    'label'    => __( 'Track Opens', 'postmark-wordpress' ),
                    'value'   => $this->strbool( $settings->track_opens ),
                    'private' => false,
                ),
    						'track_links' => array(
                    'label'    => __( 'Track Links', 'postmark-wordpress' ),
                    'value'   => $this->strbool( $settings->track_links ),
                    'private' => false,
                ),
    						'enable_logs' => array(
                    'label'    => __( 'Logs Enabled', 'postmark-wordpress' ),
                    'value'   => $this->strbool( $settings->enable_logs ),
                    'private' => false,
                ),
            ),
        );

        return $debug_info;
    }

    private function strbool( bool $value ) {
      return $value ? 'Yes' : 'No';
    }

  }

  new Postmark_Debug;

}




function myplugin_add_caching_test( $tests ) {
    $tests['direct']['caching_plugin'] = array(
        'label' => __( 'My Caching Test' ),
        'test'  => 'myplugin_caching_test',
    );
    return $tests;
}
add_filter( 'site_status_tests', 'myplugin_add_caching_test' );

function myplugin_caching_test() {
    $result = array(
        'label'       => __( 'Postmark is enabled' ),
        'status'      => 'good',
        'badge'       => array(
            'label' => __( 'Email' ),
            'color' => 'orange',
        ),
        'description' => sprintf(
            '<p>%s</p>',
            __( 'Caching can help load your site more quickly for visitors.' )
        ),
        'actions'     => '',
        'test'        => 'caching_plugin',
    );


        $result['status'] = 'recommended';
        $result['label'] = __( 'Postmark is not enabled' );
        $result['description'] = sprintf(
            '<p>%s</p>',
            __( 'Caching is not currently enabled on your site. Caching can help load your site more quickly for visitors.' )
        );
        $result['actions'] .= sprintf(
            '<p><a href="%s">%s</a></p>',
            esc_url( admin_url( 'admin.php?page=cachingplugin&action=enable-caching' ) ),
            __( 'Enable Caching' )
        );


    return $result;
}
