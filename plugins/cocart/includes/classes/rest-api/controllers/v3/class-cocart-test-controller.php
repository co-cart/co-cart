<?php
/**
 * CoCart REST API Store controller.
 *
 * Returns store details and all public routes.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v3
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v3 - Test controller class.
 *
 * @package CoCart REST API/API
 */
class CoCart_Test_v3_Controller {

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
		return '/test';
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
