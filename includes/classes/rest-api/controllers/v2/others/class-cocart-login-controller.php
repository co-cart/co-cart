<?php
/**
 * REST API: CoCart_REST_Login_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0 Introduced.
 * @version 4.x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Login_V2_Controller', 'CoCart_Login_V2_Controller' );

/**
 * Controller for logging in users via the REST API (API v2).
 *
 * This REST API controller handles requests to login the user
 * via "cocart/v2/login" endpoint.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_REST_Login_V2_Controller {

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
	 * @since 4.x.x Added arguments.
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
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	} // END register_routes()

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
	 * @since 4.x.x Avatars only return if requested.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function login( $request ) {
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
			'avatar_urls'  => array(),
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

		// Returns avatars if requested.
		if ( $request->get_param( 'avatars' ) ) {
			$response['avatar_urls'] = rest_get_avatar_urls( trim( $current_user->user_email ) );
		}

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

	/**
	 * Get the query params for login.
	 *
	 * @access public
	 *
	 * @since 4.x.x Introduced.
	 *
	 * @return array $params The query params.
	 */
	public function get_collection_params() {
		$params = array(
			'avatars' => array(
				'description'       => __( 'True if you want to return the avatars for the user.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()
} // END class
