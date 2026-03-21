<?php
/**
 * Handles support for JWT Auth plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Third Party\Plugin
 * @since   3.0.0
 * @license GPL-3.0
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
			$rest_prefix = trailingslashit( rest_get_url_prefix() );
			$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated

			add_filter(
				'jwt_auth_whitelist',
				function ( $endpoints ) {
					return array_merge(
						$endpoints,
						array(
							'/' . $rest_prefix . '/cocart/v1/*',
							'/' . $rest_prefix . CoCart::get_api_namespace() . '/v2/*',
						)
					);
				}
			);
		}
	} // END class.

} // END if class exists.

return new CoCart_Plugin_JWT_Auth();
