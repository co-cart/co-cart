<?php
/**
 * Handles REST API authentication.
 *
 * Our built in support for authenticating users with CoCart is basic
 * but secure. When you authenticate CoCart as the customer you are
 * logging them in with their account. If you are using WooCommerce
 * API consumer key and secret you will need a secure connection for
 * authentication to be valid.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.6.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Authentication' ) ) {

	class CoCart_Authentication {

		/**
		 * Authentication error.
		 *
		 * @access protected
		 * @since  3.0.0
		 * @var    WP_Error
		 */
		protected $error = null;

		/**
		 * Logged in user data.
		 *
		 * @access protected
		 * @since  3.0.0
		 * @var    stdClass
		 */
		protected $user = null;

		/**
		 * Current auth method.
		 *
		 * @access protected
		 * @since  3.0.0
		 * @var    string
		 */
		protected $auth_method = '';

		/**
		 * Constructor.
		 *
		 * @access  public
		 * @since   2.6.0
		 * @version 3.1.0
		 */
		public function __construct() {
			// Check that we are only authenticating for our API.
			if ( $this->is_rest_api_request() ) {
				// Authenticate user.
				add_filter( 'determine_current_user', array( $this, 'authenticate' ), 16 );
				add_filter( 'rest_authentication_errors', array( $this, 'authentication_fallback' ) );

				// Triggers saved cart after login and updates user activity.
				add_filter( 'rest_authentication_errors', array( $this, 'cocart_user_logged_in' ), 10 );

				// Check authentication errors.
				add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ), 15 );

				// Disable cookie authentication REST check.
				if ( is_ssl() || $this->is_wp_environment_local() ) {
					remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
				}

				// Check API permissions.
				add_filter( 'rest_pre_dispatch', array( $this, 'check_api_permissions' ), 10, 3 );

				// Allow all cross origin requests.
				add_action( 'rest_api_init', array( $this, 'allow_all_cors' ), 15 );
			}
		}

		/**
		 * Triggers saved cart after login and updates user activity.
		 *
		 * @access  public
		 * @since   2.9.1
		 * @version 3.0.0
		 * @param   WP_Error|null|bool $error Error data.
		 * @return  WP_Error|null|bool
		 */
		public function cocart_user_logged_in( $error ) {
			global $current_user;

			if ( $current_user->ID > 0 ) {
				wc_update_user_last_active( $current_user->ID );
				update_user_meta( $current_user->ID, '_woocommerce_load_saved_cart_after_login', 1 );
			}

			return $error;
		} // END cocart_user_logged_in()

		/**
		 * Returns true if we are making a REST API request for CoCart.
		 *
		 * @access  public
		 * @static
		 * @since   2.1.0
		 * @version 3.0.0
		 * @return  bool
		 */
		public static function is_rest_api_request() {
			if ( empty( $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			$rest_prefix         = trailingslashit( rest_get_url_prefix() );
			$request_uri         = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix . 'cocart/' ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			return apply_filters( 'cocart_is_rest_api_request', $is_rest_api_request );
		} // END is_rest_api_request()

		/**
		 * Authenticate user.
		 *
		 * @access  public
		 * @since   2.6.0
		 * @version 3.0.0
		 * @param   int|false $user_id User ID if one has been determined, false otherwise.
		 * @return  int|false
		 */
		public function authenticate( $user_id ) {
			// Do not authenticate twice.
			if ( ! empty( $user_id ) ) {
				return $user_id;
			}

			if ( is_ssl() || $this->is_wp_environment_local() ) {
				$user_id = $this->perform_basic_authentication();
			}

			/**
			 * Should you need to authenticate as another user instead of the one returned.
			 *
			 * @param int  $user_id The user ID returned if authentication was successful.
			 * @param bool          Determines if the site is secure.
			 */
			$user_id = apply_filters( 'cocart_authenticate', $user_id, is_ssl() );

			return $user_id;
		} // END authenticate()

		/**
		 * Authenticate the user if authentication wasn't performed during the
		 * determine_current_user action.
		 *
		 * Necessary in cases where wp_get_current_user() is called before CoCart is loaded.
		 *
		 * @access public
		 * @since  3.0.0
		 * @param  WP_Error|null|bool $error Error data.
		 * @return WP_Error|null|bool
		 */
		public function authentication_fallback( $error ) {
			if ( ! empty( $error ) ) {
				// Another plugin has already declared a failure.
				return $error;
			}

			if ( empty( $this->error ) && empty( $this->auth_method ) && empty( $this->user ) && 0 === get_current_user_id() ) {
				// Authentication hasn't occurred during `determine_current_user`, so check auth.
				$user_id = $this->authenticate( false );

				if ( ! empty( $user_id ) ) {
					wp_set_current_user( $user_id );
					return true;
				}
			}
			return $error;
		} // END authentication_fallback()

		/**
		 * Check for authentication error.
		 *
		 * @access public
		 * @since  3.0.0
		 * @param  WP_Error|null|bool $error Error data.
		 * @return WP_Error|null|bool
		 */
		public function check_authentication_error( $error ) {
			// Pass through other errors.
			if ( ! empty( $error ) ) {
				return $error;
			}

			return $this->get_error();
		} // END check_authentication_error()

		/**
		 * Set authentication error.
		 *
		 * @access protected
		 * @since  3.0.0
		 * @param  WP_Error $error Authentication error data.
		 */
		protected function set_error( $error ) {
			// Reset user.
			$this->user = null;

			$this->error = $error;
		} // END set_error()

		/**
		 * Get authentication error.
		 *
		 * @access protected
		 * @return WP_Error|null.
		 */
		protected function get_error() {
			return $this->error;
		} // END get_error()

		/**
		 * Basic Authentication.
		 *
		 * SSL-encrypted requests are not subject to sniffing or man-in-the-middle
		 * attacks, so the request can be authenticated by simply looking up the user
		 * associated with the given username and password provided that it is valid.
		 *
		 * @access  private
		 * @since   3.0.0
		 * @version 3.0.7
		 * @return  int|bool
		 */
		private function perform_basic_authentication() {
			$this->auth_method = 'basic_auth';

			// Check that we're trying to authenticate via headers.
			if ( ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
				$username = trim( sanitize_user( $_SERVER['PHP_AUTH_USER'] ) );
				$password = trim( sanitize_text_field( $_SERVER['PHP_AUTH_PW'] ) );

				// Check if the username provided was an email address and get the username if true.
				if ( is_email( $_SERVER['PHP_AUTH_USER'] ) ) {
					$user     = get_user_by( 'email', $_SERVER['PHP_AUTH_USER'] );
					$username = $user->user_login;
				}
			} elseif ( ! empty( $_REQUEST['username'] ) && ! empty( $_REQUEST['password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// Fallback to check if the username and password was passed via URL.
				$username = trim( sanitize_user( $_REQUEST['username'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$password = trim( sanitize_text_field( $_REQUEST['password'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Check if the username provided was an email address and get the username if true.
				if ( is_email( $_REQUEST['username'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$user     = get_user_by( 'email', $_REQUEST['username'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$username = $user->user_login; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			// Only authenticate if a username and password is available to check.
			if ( ! empty( $username ) && ! empty( $password ) ) {
				$this->user = wp_authenticate( $username, $password );
			} else {
				return false;
			}

			if ( is_wp_error( $this->user ) ) {
				$this->set_error( new WP_Error( 'cocart_authentication_error', __( 'Authentication is invalid. Please check the authentication information is correct and try again. Authentication may also only work on a secure connection.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 401 ) ) );

				return false;
			}

			return $this->user->ID;
		} // END perform_basic_authentication()

		/**
		 * Checks the WordPress environment to see if we are running CoCart locally.
		 *
		 * @uses wp_get_environment_type() function introduced in WP 5.5
		 * @link https://developer.wordpress.org/reference/functions/wp_get_environment_type/
		 *
		 * @access  protected
		 * @since   3.0.0
		 * @version 3.0.15
		 * @return  bool
		 */
		protected function is_wp_environment_local() {
			if ( function_exists( 'wp_get_environment_type' ) ) {
				if ( 'local' === wp_get_environment_type() || 'development' === wp_get_environment_type() ) {
					return true;
				}
			}

			return false;
		} // END is_wp_environment_local()

		/**
		 * Allow all cross origin header requests.
		 *
		 * Disabled by default. Requires `cocart_disable_all_cors` filter set to false to enable.
		 *
		 * @access  public
		 * @since   2.2.0
		 * @version 3.0.0
		 */
		public function allow_all_cors() {
			// If not enabled via filter then return.
			if ( apply_filters( 'cocart_disable_all_cors', true ) ) {
				return;
			}

			// Remove the default cors server headers.
			remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

			// Adds new cors server headers.
			add_filter( 'rest_pre_serve_request', array( $this, 'cors_headers' ), 0, 4 );
		} // END allow_all_cors()

		/**
		 * Cross Origin headers.
		 *
		 * For security reasons, browsers restrict cross-origin HTTP requests initiated from scripts.
		 * This overrides that by providing access should the request be for CoCart.
		 *
		 * @access  public
		 * @since   2.2.0 Introduced.
		 * @since   3.3.0 Added new custom headers without the prefix `X-`
		 * @version 3.3.0
		 * @param   bool             $served  Whether the request has already been served. Default false.
		 * @param   WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
		 * @param   WP_REST_Request  $request Request used to generate the response.
		 * @param   WP_REST_Server   $server  Server instance.
		 * @return  bool
		 */
		public function cors_headers( $served, $result, $request, $server ) {
			if ( strpos( $request->get_route(), 'cocart/' ) !== false ) {
				$origin = get_http_origin();

				// Requests from file:// and data: URLs send "Origin: null".
				if ( 'null' !== $origin ) {
					$origin = esc_url_raw( $origin );
				}

				$allow_headers = array(
					'Authorization',
					'X-Requested-With',
					'Content-Disposition',
					'Content-MD5',
					'Content-Type',
				);

				$expose_headers = array(
					'X-WP-Total',
					'X-WP-TotalPages',
					'Link',
					'X-CoCart-API', // @todo Deprecate in v4.0
					'CoCart-API-Cart-Key',
				);

				header( 'Access-Control-Allow-Origin: ' . apply_filters( 'cocart_allow_origin', $origin ) );
				header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
				header( 'Access-Control-Allow-Credentials: true' );
				header( 'Access-Control-Allow-Headers: ' . implode( ', ', $allow_headers ) );
				header( 'Access-Control-Expose-Headers: ' . implode( ', ', $expose_headers ) );
			}

			return $served;
		} // END cors_headers()

		/**
		 * Check for permission to access API.
		 *
		 * @throws CoCart_Data_Exception Exception if invalid data is detected.
		 *
		 * @access  public
		 * @since   3.0.0
		 * @version 3.1.0
		 * @param   mixed           $result  Response to replace the requested version with.
		 * @param   WP_REST_Server  $server  Server instance.
		 * @param   WP_REST_Request $request Request used to generate the response.
		 * @return  mixed
		 */
		public function check_api_permissions( $result, $server, $request ) {
			$method = $request->get_method();
			$path   = $request->get_route();
			$prefix = 'cocart/';

			/**
			 * Should the developer choose to restrict any of CoCart's API routes for any method.
			 * They can set the requested API and method to enforce authentication by not allowing it permission to the public.
			 */
			$api_not_allowed = apply_filters( 'cocart_api_permission_check_' . strtolower( $method ), array() );

			try {
				// If no user is logged in then just return.
				if ( ! is_user_logged_in() ) {
					switch ( $method ) {
						case 'GET':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'READ', $path ), 401 );
								}
							}
							break;
						case 'POST':
						case 'PUT':
						case 'PATCH':
						case 'DELETE':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'WRITE', $path ), 401 );
								}
							}
							break;
						case 'OPTIONS':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'OPTIONS', $path ), 401 );
								}
							}
							break;
						default:
							/* translators: %s: api route */
							throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Unknown request method for %s.', 'cart-rest-api-for-woocommerce' ), $path ), 401 );
					}
				}

				// Return previous result if nothing has changed.
				return $result;
			} catch ( CoCart_Data_Exception $e ) {
				return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END check_api_permissions()

	} // END class.

} // END if class exists.

return new CoCart_Authentication();
