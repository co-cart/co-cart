<?php
/**
 * REST API: CoCart_REST_Logout_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
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
 * Note: Originally to help clear the user session cookies which
 * our session handler does not utilize anymore.
 *
 * @since 3.0.0 Introduced.
 * @extends CoCart_REST_Controller
 */
class CoCart_REST_Logout_V2_Controller extends CoCart_REST_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Get the path of this rest route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/logout';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'logout' ),
			'permission_callback' => '__return_true',
		);
	} // END get_args()

	/**
	 * Route base.
	 *
	 * @deprecated 5.0.0 Replaced with `get_path()` instead.
	 *
	 * @var string
	 */
	protected $rest_base = 'logout';

	/**
	 * Register routes.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Logout user - cocart/v2/logout (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

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
