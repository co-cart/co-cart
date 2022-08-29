<?php
/**
 * REST API: Cart v3 controller.
 *
 * Handles requests to the /cart endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Cart\v3
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v3 - Cart controller class.
 *
 * @since 4.0.0 Introduced.
 */
class CoCart_REST_Cart_v3_Controller {

	/**
	 * Get endpoint.
	 *
	 * @var string
	 */
	public function get_endpoint() {
		return 'cart';
	}

	/**
	 * Get path.
	 *
	 * @var string
	 */
	public function get_path() {
		return '';
	}

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_args() {
		return array(
			array(
				// 'methods'             => WP_REST_Server::READABLE,
				// 'callback'            => null,
				// 'permission_callback' => '__return_true',
			),
			// 'schema' => array( $this, 'get_public_object_schema' ),
		);
	} // get_args()

} // END class
