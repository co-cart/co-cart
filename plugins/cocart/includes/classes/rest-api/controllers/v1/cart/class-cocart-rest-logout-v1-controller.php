<?php
/**
 * REST API: CoCart_Logout_Controller class
 *
 * @author     Sébastien Dumont
 * @package    CoCart\RESTAPI\v1
 * @since      2.1.0 Introduced.
 * @version    2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logs out the user from the WordPress instance. (API v1)
 *
 * Handles the request to logout the user via the /logout endpoint.
 *
 * @since 2.1.0 Introduced.
 *
 * @see CoCart_API_Controller
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
	 *
	 * @since 2.1.0 Introduced.
	 * @since 2.5.0 Added permission callback set to return true due to a change to the REST API in WordPress v5.5
	 */
	public function register_routes() {
		// Logout user - cocart/v1/logout (POST)
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

	/**
	 * Logout user.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function logout() {
		wp_logout();

		return new WP_REST_Response( true, 200 );
	} // END logout()

} // END class
