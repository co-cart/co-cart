<?php
/**
 * CoCart - Login controller
 *
 * Handles the request to login the user /login endpoint.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Login v2 controller class.
 *
 * @package CoCart\API
 */
class CoCart_Login_v2_Controller {

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
	protected $rest_base = 'login';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Login user - cocart/v2/login (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'login' ),
				'permission_callback' => array( $this, 'get_permission_callback' ),
			)
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 * @return WP_Error|boolean
	 */
	public function get_permission_callback() {
		if ( strval( get_current_user_id() ) <= 0 ) {
			return new WP_Error( 'cocart_rest_not_authorized', __( 'Sorry, you are not authorized.', 'cart-rest-api-for-woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_permission_callback()

	/**
	 * Login user.
	 *
	 * @access public
	 * @return WP_REST_Response
	 */
	public function login() {
		$current_user = get_userdata( get_current_user_id() );

		$user_roles = $current_user->roles;

		$display_user_roles = array();

		foreach ( $user_roles as $role ) {
			$display_user_roles[] = ucfirst( $role );
		}

		$response = array(
			'user_id'      => strval( get_current_user_id() ),
			'display_name' => esc_html( $current_user->display_name ),
			'role'         => implode( ', ', $display_user_roles ),
			'dev_note'     => __( "Don't forget to store the users login information in order to authenticate all other routes with CoCart.", 'cart-rest-api-for-woocommerce' ),
		);

		return new WP_REST_Response( $response, 200 );
	} // END login()

} // END class
