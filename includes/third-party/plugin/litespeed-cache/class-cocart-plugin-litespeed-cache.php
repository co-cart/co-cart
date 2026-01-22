<?php
/**
 * Handles support for LiteSpeed Cache plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Third Party\Plugin
 * @since   4.4.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LiteSpeed\Core;
use LiteSpeed\Control;
use LiteSpeed\Debug2;

// Don't do anything if LiteSpeed Cache is not detected.
if ( ! class_exists( '\LiteSpeed\Core' ) ) {
	return;
}

if ( ! class_exists( 'CoCart_Plugin_LiteSpeed_Cache' ) ) {

	/**
	 * LiteSpeed Cache.
	 */
	class CoCart_Plugin_LiteSpeed_Cache {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'prevent_caching' ) );
		}

		/**
		 * Prevent caching for CoCart API requests.
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 */
		public function prevent_caching() {
			$rest_prefix = trailingslashit( rest_get_url_prefix() );
			$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated

			if ( false !== strpos( $request_uri, '/' . $rest_prefix . 'cocart' ) ) {
				Debug2::debug( '3rd CoCart API - No Cache' );

				Control::set_nocache( 'CoCart API request' );
			}
		}
	} // END class.

} // END if class exists.

return new CoCart_Plugin_LiteSpeed_Cache();
