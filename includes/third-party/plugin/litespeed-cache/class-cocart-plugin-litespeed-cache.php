<?php
/**
 * Handles support for LiteSpeed Cache plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Third Party\Plugin
 * @since   4.4.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't do anything if LiteSpeed Cache is not detected.
if ( ! class_exists( 'LiteSpeed_Cache_API' ) ) {
	return;
}

if ( ! class_exists( 'CoCart_Plugin_LiteSpeed_Cache' ) ) {

	LiteSpeed_Cache_API::register( 'CoCart_Plugin_LiteSpeed_Cache' );

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
			add_action( 'rest_api_init', array( $this, 'disable_vary_change' ) );
		}

		/**
		 * Disable vary change for CoCart.
		 *
		 * @access public
		 *
		 * @since 4.4.0 Introduced.
		 */
		public function disable_vary_change() {
			$rest_prefix = trailingslashit( rest_get_url_prefix() );
			$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

			if ( false !== strpos( $request_uri, '/' . $rest_prefix . 'cocart' ) ) {
				LiteSpeed_Cache_API::debug( '3rd CoCart API set no change vary' );
				add_filter( 'litespeed_can_change_vary', '__return_false' );
			}
		}
	} // END class.

} // END if class exists.

return new CoCart_Plugin_LiteSpeed_Cache();
