<?php
/**
 * REST API: CoCart_REST_Login_V2_Controller class
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

class_alias( 'CoCart_REST_Login_V2_Controller', 'CoCart_Login_V2_Controller' );

/**
 * Controller for logging in users via the REST API (API v2).
 *
 * This REST API controller handles requests to login the user
 * via "cocart/v2/login" endpoint.
 *
 * @since 3.0.0 Introduced.
 * @since 4.8.0 Added handler to prevent multiple login attempts and added hooks for additional security measures.
 * @extends CoCart_REST_Controller
 */
class CoCart_REST_Login_V2_Controller extends CoCart_REST_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Static flag to prevent multiple request attempts across all instances.
	 *
	 * @access private
	 *
	 * @since 4.8.0 Introduced.
	 *
	 * @var array
	 */
	private static $processed_requests = array();

	/**
	 * Get the path of this rest route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/login';
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
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'login' ),
				'permission_callback' => array( $this, 'get_permission_callback' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()

	/**
	 * Route base.
	 *
	 * @deprecated 5.0.0 Replaced with `get_path()` instead.
	 *
	 * @var string
	 */
	protected $rest_base = 'login';

	/**
	 * Register routes.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Added schema information.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Login user - cocart/v2/login (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_permission_callback( $request = null ) {
		$current_user_id = get_current_user_id();

		if ( strval( $current_user_id ) <= 0 ) {
			return new WP_Error( 'cocart_rest_not_authorized', __( 'Sorry, you are not authorized.', 'cocart-core' ), array( 'status' => rest_authorization_required_code() ) );
		}

		// Create a unique request identifier based on user ID, IP, and timestamp within a small window.
		$client_ip   = CoCart_Authentication::get_ip_address( true );
		$time_window = floor( time() / 5 ); // 5-second window to group duplicate requests.
		$request_id  = md5( $current_user_id . '_' . $client_ip . '_' . $time_window );

		// Check if we've already processed this request.
		if ( isset( self::$processed_requests[ $request_id ] ) ) {
			return self::$processed_requests[ $request_id ]; // Return the previous result.
		}

		$current_user = get_userdata( $current_user_id );

		// Check if user is authenticated via secure token (JWT, etc.) - skip additional auth checks.
		$auth_method          = $this->get_current_auth_method();
		$skip_additional_auth = $this->should_skip_additional_auth( $auth_method );

		/**
		 * Filter to allow additional authentication checks after basic authorization.
		 *
		 * This filter allows plugins (like 2FA) to intervene in the login permission process.
		 * Return WP_Error to deny access, or true to allow continued processing.
		 *
		 * Note: This filter is skipped when user is authenticated via secure tokens (JWT, etc.)
		 *
		 * @since 4.8.0 Introduced.
		 *
		 * @param boolean|WP_Error  $permission           Current permission status (true by default after basic auth).
		 * @param WP_User           $current_user         The current authenticated user.
		 * @param WP_REST_Request   $request              The current REST API request.
		 * @param string            $auth_method          The authentication method used.
		 * @param boolean           $skip_additional_auth Whether to skip additional auth checks.
		 */
		$permission = $skip_additional_auth ? true : apply_filters( 'cocart_login_permission_callback', true, $current_user, $request, $auth_method, $skip_additional_auth );

		// Store the result to prevent duplicate processing.
		self::$processed_requests[ $request_id ] = $permission;

		// Only fire actions if permission was granted.
		if ( true === $permission ) {
			/**
			 * Action fired when login permission is granted.
			 *
			 * @since 4.8.0 Introduced.
			 *
			 * @param WP_User         $current_user The current authenticated user.
			 * @param WP_REST_Request $request      The current REST API request.
			 */
			do_action( 'cocart_login_permission_granted', $current_user, $request );
		}

		// Clean up old processed requests to prevent memory leaks.
		if ( count( self::$processed_requests ) > 100 ) {
			self::$processed_requests = array_slice( self::$processed_requests, -50, 50, true );
		}

		return $permission;
	} // END get_permission_callback()

	/**
	 * Get the current authentication method used.
	 *
	 * @access private
	 *
	 * @since 4.8.0 Introduced.
	 *
	 * @return string The authentication method (jwt, basic_auth, cookie, etc.)
	 */
	private function get_current_auth_method() {
		$auth_header = CoCart_Authentication::get_auth_header();

		// Check for JWT token in Authorization header.
		if ( ! empty( $auth_header ) && false !== strpos( $auth_header, 'Bearer ' ) ) {
			return 'jwt';
		}

		// Check for Basic Auth.
		if ( ! empty( $auth_header ) && false !== strpos( $auth_header, 'Basic ' ) ) {
			return 'basic_auth';
		}

		// Check for API key authentication (if implemented).
		if ( ! empty( $_REQUEST['consumer_key'] ) || ! empty( $_REQUEST['consumer_secret'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return 'api_key';
		}

		return 'unknown';
	} // END get_current_auth_method()

	/**
	 * Determine if additional authentication checks should be skipped.
	 *
	 * @access private
	 *
	 * @since 4.8.0 Introduced.
	 *
	 * @param string $auth_method The authentication method used.
	 *
	 * @return boolean Whether to skip additional authentication checks.
	 */
	private function should_skip_additional_auth( $auth_method ) {
		/**
		 * Authentication methods that should skip additional checks (like 2FA).
		 *
		 * These are considered "already secure" authentication methods where
		 * the user has already proven their identity sufficiently.
		 */
		$secure_auth_methods = array(
			'jwt',     // JWT tokens are already verified and time-limited.
			'api_key', // API keys are for programmatic access.
		);

		/**
		 * Filter which authentication methods should skip additional auth checks.
		 *
		 * @since 4.8.0 Introduced.
		 *
		 * @param array  $secure_auth_methods Authentication methods that skip additional checks.
		 * @param string $auth_method         Current authentication method.
		 */
		$secure_auth_methods = apply_filters( 'cocart_login_secure_auth_methods', $secure_auth_methods, $auth_method );

		return in_array( $auth_method, $secure_auth_methods, true );
	} // END should_skip_additional_auth()

	/**
	 * Login user.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Added avatar URLS and users email address.
	 * @since 3.8.1 Added users first and last name.
	 * @since 5.0.0 Avatars only return if requested.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function login( $request ) {
		$current_user_id = get_current_user_id();
		$current_user    = get_userdata( $current_user_id );

		$user_roles = $current_user->roles;

		$display_user_roles = array();

		foreach ( $user_roles as $role ) {
			$display_user_roles[] = ucfirst( $role );
		}

		$response = array(
			'user_id'      => strval( $current_user_id ),
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
			 * @param array  $extra_information The extra information.
			 * @param object $current_user      The current user.
			 */
			'extras'       => apply_filters( 'cocart_login_extras', array(), $current_user ),
			'dev_note'     => __( "Don't forget to store the users login information in order to authenticate all other routes with CoCart.", 'cocart-core' ),
		);

		// Returns avatars if requested.
		if ( $request->get_param( 'avatars' ) ) {
			$response['avatar_urls'] = rest_get_avatar_urls( trim( $current_user->user_email ) );
		}

		return rest_ensure_response( $response );
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
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_login',
			'type'       => 'object',
			'properties' => array(
				'user_id'      => array(
					'description' => __( 'Unique ID to the user on the site.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'first_name'   => array(
					'description' => __( 'The first name of the user (if any).', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'last_name'    => array(
					'description' => __( 'The last name of the user (if any).', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'display_name' => array(
					'description' => __( 'The display name of the user (if any).', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'role'         => array(
					'description' => __( 'The role type assigned to the user.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'avatar_urls'  => array(
					'description' => __( 'The avatar URLs of the user for each avatar size registered.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(),
					'readonly'    => true,
				),
				'email'        => array(
					'description' => __( 'The email address of the user.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'extras'       => array(
					'description' => __( 'Extra details added via the filter.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(),
					'readonly'    => true,
				),
				'dev_note'     => array(
					'description' => __( 'A message to developers.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->schema;
	} // END get_item_schema()

	/**
	 * Get the query params for login.
	 *
	 * @access public
	 *
	 * @since 4.7.0 Introduced.
	 * @since 4.8.0 Added filter to extend the parameters.
	 * @since 5.0.0 Added avatar query.
	 *
	 * @return array $params The query params.
	 */
	public function get_collection_params() {
		$params = array(
			'username' => array(
				'description'       => __( 'Username, email, or phone number for authentication.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'password' => array(
				'description'       => __( 'Password for authentication.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'avatars'  => array(
				'description'       => __( 'True if you want to return the avatars for the user.', 'cocart-core' ),
				'default'           => false,
				'type'              => 'boolean',
				'required'          => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		/**
		 * Extend the query parameters for the login endpoint.
		 *
		 * Allows you to extend the query parameters without removing any default parameters.
		 *
		 * @since 4.8.0 Introduced.
		 *
		 * @param array $params The current parameters.
		 */
		$params += apply_filters( 'cocart_login_query_parameters', array() );

		return $params;
	} // END get_collection_params()
} // END class
