<?php
/**
 * Handles support for JWT Auth plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Third Party\Plugin
 * @since   3.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Plugin_JWT_Auth' ) ) {

	/**
	 * JWT Authentication.
	 */
	class CoCart_Plugin_JWT_Auth {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter(
				'jwt_auth_whitelist',
				function( $endpoints ) {
					return array_merge(
						$endpoints,
						array(
							'/wp-json/cocart/v1/*',
							'/wp-json/cocart/v2/*',
						)
					);
				}
			);
		}

	} // END class.

} // END if class exists.

return new CoCart_Plugin_JWT_Auth();
