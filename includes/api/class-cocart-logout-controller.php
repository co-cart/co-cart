<?php
/**
 * CoCart - Logout controller
 *
 * Handles the request to logout the user /logout endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Logout controller class.
 *
 * @package CoCart/API
 */
class CoCart_Logout_Controller extends CoCart_API_Controller {

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
		// Logout user - cocart/v1/logout (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'logout' )
		) );
	} // register_routes()

	/**
	 * Logout user.
	 *
	 * @access public
	 * @param  array $data
	 * @return WP_REST_Response
	 */
	public function logout( $data = array() ) {
		wp_logout();

		return new WP_REST_Response( true, 200 );
	} // END logout()

} // END class
