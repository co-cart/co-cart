<?php
/**
 * REST API: CoCart_REST_Logout_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Logout_V2_Controller', 'CoCart_Logout_V2_Controller' );

/**
 * Controller for logging out users via the REST API (API v2).
 *
 * This REST API controller handles requests to logout the user
 * via "cocart/v2/logout" endpoint.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_REST_Logout_V2_Controller extends CoCart_Logout_Controller {

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
	 *
	 * @ignore Function ignored when parsed into Code Reference.
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
	} // END register_routes()
} // END class
