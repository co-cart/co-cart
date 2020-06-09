<?php
/**
 * CoCart - Logout controller
 *
 * Handles the request to logout the user /logout endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Logout v2 controller class.
 *
 * @package CoCart/API
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
	protected $rest_base = 'cart/logout';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Logout user - cocart/v2/cart/logout (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'logout' )
		) );
	} // register_routes()

} // END class
