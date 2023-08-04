<?php
/**
 * REST API: CoCart_REST_Login_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for logging in users via the REST API (API v2).
 *
 * This REST API controller handles requests to login the user
 * via "cocart/v2/login" endpoint.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_REST_Login_v2_Controller {

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
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Added schema information.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Login user - cocart/v2/login (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'login' ),
					'permission_callback' => array( $this, 'get_permission_callback' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			),
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 *
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
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Added avatar URLS and users email address.
	 * @since 3.8.1 Added users first and last name.
	 *
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
			'first_name'   => $current_user->first_name,
			'last_name'    => $current_user->last_name,
			'display_name' => esc_html( $current_user->display_name ),
			'role'         => implode( ', ', $display_user_roles ),
			'avatar_urls'  => rest_get_avatar_urls( trim( $current_user->user_email ) ),
			'email'        => trim( $current_user->user_email ),
			/**
			 * Filter allows you to add extra information based on the current user.
			 *
			 * @since 3.8.1 Introduced.
			 *
			 * @param array $extra_information The extra information.
			 * @param object $current_user The current user.
			 */
			'extras'       => apply_filters( 'cocart_login_extras', array(), $current_user ),
			'dev_note'     => __( "Don't forget to store the users login information in order to authenticate all other routes with CoCart.", 'cart-rest-api-for-woocommerce' ),
		);

		return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
	} // END login()

	/**
	 * Get the schema for returning the login.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array
	 */
	public function get_public_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_login',
			'type'       => 'object',
			'properties' => array(
				'user_id'      => array(
					'description' => __( 'Unique ID to the user on the site.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'first_name'   => array(
					'description' => __( 'The first name of the user (if any).', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'last_name'    => array(
					'description' => __( 'The last name of the user (if any).', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'display_name' => array(
					'description' => __( 'The display name of the user (if any).', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'role'         => array(
					'description' => __( 'The role type assigned to the user.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'avatar_urls'  => array(
					'description' => __( 'The avatar URLs of the user for each avatar size registered.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(),
					'readonly'    => true,
				),
				'email'        => array(
					'description' => __( 'The email address of the user.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'extras'       => array(
					'description' => __( 'Extra details added via the filter.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(),
					'readonly'    => true,
				),
				'dev_note'     => array(
					'description' => __( 'A message to developers.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);
	} // END get_public_item_schema()

} // END class
