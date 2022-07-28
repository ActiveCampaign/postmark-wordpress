<?php

if ( ! class_exists( 'Postmark_Debug' ) ) {

	class Postmark_Debug {

		/**
		 * Constructor.
		 */
		public function __construct() {
			// https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
			add_filter( 'debug_information', array( $this, 'debug_info' ) );
		}

		/**
		 * WordPress Site Health Debug Info.
		 *
		 * @param  [type] $debug_info Debug Info.
		 */
		public function debug_info( $debug_info ) {

			$settings = json_decode( get_option( 'postmark_settings' ) );

			$postmark = "Postmark_Mail";

			$debug_info['postmark-wordpress'] = array(
				'label'  => __( 'Active Campaign Postmark', 'postmark-wordpress' ),
				'fields' => array(
					'status'         => array(
						'label'   => __( 'Enabled', 'postmark-wordpress' ),
						'value'   => $this->strbool( $settings->enabled ),
						'private' => false,
					),
					'version'        => array(
						'label'   => __( 'POSTMARK_VERSION', 'postmark-wordpress' ),
						'value'   => defined( 'POSTMARK_VERSION' ) ? POSTMARK_VERSION : $postmark::$POSTMARK_VERSION,
						'private' => false,
					),
					'directory'      => array(
						'label'   => __( 'POSTMARK_DIR', 'postmark-wordpress' ),
						'value'   => defined( 'POSTMARK_DIR' ) ? POSTMARK_DIR : $postmark::$POSTMARK_DIR,
						'private' => false,
					),
					'url'            => array(
						'label'   => __( 'POSTMARK_URL', 'postmark-wordpress' ),
						'value'   => defined( 'POSTMARK_URL' ) ? POSTMARK_URL : $postmark::$POSTMARK_URL,
						'private' => false,
					),
					'apikey'         => array(
						'label'   => __( 'API Key', 'postmark-wordpress' ),
						'value'   => $settings->api_key,
						'private' => true,
					),
					'stream'         => array(
						'label'   => __( 'Stream', 'postmark-wordpress' ),
						'value'   => $settings->stream_name,
						'private' => false,
					),
					'sender_address' => array(
						'label'   => __( 'Sender Address', 'postmark-wordpress' ),
						'value'   => $settings->sender_address,
						'private' => false,
					),
					'force_from'     => array(
						'label'   => __( 'Force From', 'postmark-wordpress' ),
						'value'   => $this->strbool( $settings->force_from ),
						'private' => false,
					),
					'force_html'     => array(
						'label'   => __( 'Force HTML', 'postmark-wordpress' ),
						'value'   => $this->strbool( $settings->force_html ),
						'private' => false,
					),
					'track_opens'    => array(
						'label'   => __( 'Track Opens', 'postmark-wordpress' ),
						'value'   => $this->strbool( $settings->track_opens ),
						'private' => false,
					),
					'track_links'    => array(
						'label'   => __( 'Track Links', 'postmark-wordpress' ),
						'value'   => $this->strbool( $settings->track_links ),
						'private' => false,
					),
					'enable_logs'    => array(
						'label'   => __( 'Logs Enabled', 'postmark-wordpress' ),
						'value'   => $this->strbool( $settings->enable_logs ),
						'private' => false,
					),
				),
			);

			return $debug_info;
		}

		/**
		 * String Boolean.
		 *
		 * @param  bool $value  Value.
		 * @return string Yes or No based on boolean provided.
		 */
		private function strbool( $value ) {
			return $value ? 'Yes' : 'No';
		}

	}

	new Postmark_Debug();

}
