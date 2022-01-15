<?php
/**
 * CoCart - Logout controller
 *
 * Handles the request to logout the user /logout endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Logout controller class.
 *
 * @package CoCart\API
 */
class CoCart_Logout_v2_Controller extends CoCart_Logout_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'logout';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Logout user - cocart/v2/logout (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'logout' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

} // END class
