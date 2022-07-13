<?php
/**
 * Handles support for Yoast SEO plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Third Party\Plugin
 * @since   3.4.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Plugin_Yoast_SEO' ) ) {

	/**
	 * Yoast SEO.
	 */
	class CoCart_Plugin_Yoast_SEO {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'rest_api_init', function () {
				//unregister_rest_field( 'product', 'yoast_head' );
			}, 11 );
		}

	} // END class.

} // END if class exists.

return new CoCart_Plugin_Yoast_SEO();
