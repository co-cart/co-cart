<?php
/**
 * Handles REST API authentication.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.6.0 Introduced.
 * @version 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Authentication' ) ) {

	/**
	 * Handles REST API authentication for CoCart.
	 *
	 * Basic and secure authentication is supported.
	 *
	 * Note: When you authenticate CoCart as the customer you are logging
	 * them in with their account.
	 *
	 * If you are using WooCommerce API consumer key and secret,
	 * a secure connection is required.
	 *
	 * @since 2.6.0 Introduced.
	 */
	class CoCart_Authentication {

		/**
		 * Authentication error.
		 *
		 * @access protected
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @var WP_Error
		 */
		protected $error = null;

		/**
		 * Logged in user data.
		 *
		 * @access protected
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @var stdClass
		 */
		protected $user = null;

		/**
		 * Current auth method.
		 *
		 * @access protected
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @var string
		 */
		protected $auth_method = '';

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @since 2.6.0 Introduced.
		 *
		 * @ignore Function ignored when parsed into Code Reference.
		 */
		public function __construct() {
			// Check that we are only authenticating for our API.
			if ( CoCart::is_rest_api_request() ) {
				// Authenticate user.
				add_filter( 'determine_current_user', array( $this, 'authenticate' ), 16 );
				add_filter( 'rest_authentication_errors', array( $this, 'authentication_fallback' ) );

				// Triggers saved cart after login and updates user activity.
				add_filter( 'rest_authentication_errors', array( $this, 'cocart_user_logged_in' ), 10 );

				// Check authentication errors.
				add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ), 15 );

				// Sends the cart key to the header.
				add_filter( 'rest_authentication_errors', array( $this, 'cocart_key_header' ), 20, 1 );

				// Check API permissions.
				add_filter( 'rest_pre_dispatch', array( $this, 'check_api_permissions' ), 10, 3 );

				// Allow all cross origin requests.
				add_action( 'rest_api_init', array( $this, 'allow_all_cors' ), 15 );
			}
		} // END __construct()

		/**
		 * Triggers saved cart after login and updates user activity.
		 *
		 * @access public
		 *
		 * @since 2.9.1 Introduced.
		 *
		 * @param WP_Error|null|bool $error Error from another authentication handler, null if we should handle it, or another value if not.
		 *
		 * @return WP_Error|null|bool
		 */
		public function cocart_user_logged_in( $error ) {
			// Pass through errors from other authentication error checks used before this one.
			if ( ! empty( $error ) ) {
				return $error;
			}

			global $current_user;

			if ( $current_user instanceof WP_User && $current_user->exists() ) {
				wc_update_user_last_active( $current_user->ID );
				update_user_meta( $current_user->ID, '_woocommerce_load_saved_cart_after_login', 1 );
			}
		} // END cocart_user_logged_in()

		/**
		 * Get the authorization header.
		 *
		 * Returns the value from the authorization header.
		 *
		 * On certain systems and configurations, the Authorization header will be
		 * stripped out by the server or PHP. Typically this is then used to
		 * generate `PHP_AUTH_USER`/`PHP_AUTH_PASS` but not passed on. We use
		 * `getallheaders` here to try and grab it out instead.
		 *
		 * @access protected
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @return string $auth_header
		 */
		protected function get_auth_header() {
			$auth_header = ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ) : '';

			if ( function_exists( 'getallheaders' ) ) {
				$headers = getallheaders();
				// Check for the authorization header case-insensitively.
				foreach ( $headers as $key => $value ) {
					if ( 'authorization' === strtolower( $key ) ) {
						$auth_header = $value;
					}
				}
			}

			// Double check for different auth header string if empty (server dependent).
			if ( empty( $auth_header ) ) {
				$auth_header = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) : '';
			}

			/**
			 * Filter allows you to change the authorization header.
			 *
			 * @since 4.1.0 Introduced.
			 *
			 * @param string Authorization header.
			 */
			return apply_filters( 'cocart_auth_header', $auth_header );
		} // END get_auth_header()

		/**
		 * Authenticate user.
		 *
		 * @access public
		 *
		 * @since 2.6.0 Introduced.
		 *
		 * @param int|false $user_id User ID if one has been determined, false otherwise.
		 *
		 * @return int|false
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
			 * Filters the user ID returned and allows for third party to
			 * include another authentication method.
			 *
			 * @since 2.6.0 Introduced.
			 * @since 3.8.1 Passed the authentication class as parameter.
			 *
			 * @param int    $user_id The user ID returned if authentication was successful.
			 * @param bool            Determines if the site is secure.
			 * @param object $this    The Authentication class.
			 */
			$user_id = apply_filters( 'cocart_authenticate', $user_id, is_ssl(), $this );

			return $user_id;
		} // END authenticate()

		/**
		 * Authenticate the user if authentication wasn't performed during the
		 * determine_current_user action.
		 *
		 * Necessary in cases where wp_get_current_user() is called before CoCart is loaded.
		 *
		 * @access public
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param WP_Error|null|bool $error Error from another authentication handler, null if we should handle it, or another value if not.
		 *
		 * @return WP_Error|null|bool
		 */
		public function authentication_fallback( $error ) {
			if ( ! empty( $error ) ) {
				// Another error has already declared a failure.
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
		 * CoCart does not require authentication.
		 *
		 * @access public
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param WP_Error|mixed $error Error from another authentication handler, null if we should handle it, or another value if not.
		 *
		 * @return WP_Error|null|bool
		 */
		public function check_authentication_error( $error ) {
			// Pass through errors from other authentication methods used before this one.
			if ( ! empty( $error ) ) {
				return $error;
			}

			// If any other authentication error is logged then return it.
			if ( is_wp_error( $this->get_error() ) ) {
				return $this->get_error();
			}

			return true;
		} // END check_authentication_error()

		/**
		 * Set authentication error.
		 *
		 * @access protected
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param WP_Error $error Authentication error data.
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
		 *
		 * @return WP_Error|null.
		 */
		protected function get_error() {
			return $this->error;
		} // END get_error()

		/**
		 * Set authentication method.
		 *
		 * @access public
		 *
		 * @since 3.8.1 Introduced.
		 *
		 * @param string $auth_method Authentication method.
		 */
		public function set_method( $auth_method ) {
			$this->auth_method = $auth_method;
		} // END set_method()

		/**
		 * Basic Authentication.
		 *
		 * SSL-encrypted requests are not subject to sniffing or man-in-the-middle
		 * attacks, so the request can be authenticated by simply looking up the user
		 * associated with the given username and password provided that it is valid.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_authenticate/
		 *
		 * @access private
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @uses CoCart_Authentication()->get_auth_header()
		 * @uses CoCart_Authentication()->get_username()
		 * @uses get_user_by()
		 * @uses wp_authenticate()
		 *
		 * @return int|bool
		 */
		private function perform_basic_authentication() {
			$this->auth_method = 'basic_auth';
			$username          = '';
			$password          = '';

			// Look up authorization header and check it's a valid.
			if ( ! empty( $this->get_auth_header() ) && 0 === stripos( $this->get_auth_header(), 'basic ' ) ) {
				$exploded = explode( ':', base64_decode( substr( $this->get_auth_header(), 6 ) ), 2 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

				// If valid return username and password.
				if ( 2 === \count( $exploded ) ) {
					list( $username, $password ) = $exploded;

					$username = $this->get_username( $username );
				}
			} elseif ( ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
				// Check that we're trying to authenticate via simple headers.
				$username = trim( sanitize_user( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$username = $this->get_username( $username );
				$password = trim( sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_PW'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( ! empty( $_REQUEST['username'] ) && ! empty( $_REQUEST['password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// Fallback to check if the username and password was passed via URL.
				$username = trim( sanitize_user( wp_unslash( $_REQUEST['username'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$username = $this->get_username( $username );
				$password = trim( sanitize_text_field( wp_unslash( $_REQUEST['password'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
		 * @link https://developer.wordpress.org/reference/functions/wp_get_environment_type/
		 *
		 * @access protected
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.0.15
		 *
		 * @uses wp_get_environment_type() function introduced in WP 5.5
		 *
		 * @return bool
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
		 * @access public
		 *
		 * @since   2.2.0 Introduced.
		 * @version 3.0.0
		 */
		public function allow_all_cors() {
			/**
			 * Modifies if the "Cross Origin Headers" are allowed.
			 *
			 * Set as false to enable support.
			 *
			 * @since 2.2.0 Introduced.
			 */
			if ( apply_filters( 'cocart_disable_all_cors', true ) ) {
				return;
			}

			// Remove the default cors server headers.
			remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

			// Sets CORS server headers.
			add_filter( 'rest_pre_serve_request', array( $this, 'cors_headers' ), 0, 4 );
		} // END allow_all_cors()

		/**
		 * Is the request a preflight request? Checks the request method.
		 *
		 * @access protected
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @return boolean
		 */
		protected function is_preflight() {
			return isset( $_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'], $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], $_SERVER['HTTP_ORIGIN'] ) && 'OPTIONS' === $_SERVER['REQUEST_METHOD'];
		} // END is_preflight()

		/**
		 * Set Cross Origin headers.
		 *
		 * For security reasons, browsers restrict cross-origin HTTP requests initiated from scripts.
		 * This overrides that by providing access should the request be for CoCart.
		 *
		 * These checks prevent access to the API from non-allowed origins. By default, the WordPress REST API allows
		 * access from any origin. Because some API routes return PII, we need to add our own CORS headers.
		 *
		 * Allowed origins can be changed using the WordPress `allowed_http_origins` or `allowed_http_origin` filters if
		 * access needs to be granted to other domains.
		 *
		 * @link https://developer.wordpress.org/reference/functions/get_http_origin/
		 * @link https://developer.wordpress.org/reference/functions/get_allowed_http_origins/
		 *
		 * @access public
		 *
		 * @since 2.2.0 Introduced.
		 * @since 3.3.0 Added new custom headers without the prefix `X-`
		 * @since 4.0.0 Added a check against a list of allowed HTTP origins.
		 *
		 * @uses get_http_origin()
		 * @uses get_allowed_http_origins()
		 * @uses rest_get_server()
		 * @uses is_allowed_http_origin()
		 *
		 * @param bool             $served  Whether the request has already been served. Default false.
		 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
		 * @param WP_REST_Request  $request The request object.
		 * @param WP_REST_Server   $server  Server instance.
		 *
		 * @return bool
		 */
		public function cors_headers( $served, $result, $request, $server ) {
			if ( strpos( $request->get_route(), 'cocart/' ) !== false ) {
				$origin = get_http_origin();

				// Requests from file:// and data: URLs send "Origin: null".
				if ( 'null' !== $origin ) {
					$origin = esc_url_raw( $origin );
				}

				// Check the origin against a list of allowed HTTP origins.
				if ( $origin && ! in_array( $origin, get_allowed_http_origins(), true ) ) {
					$origin = '';
				}

				/**
				 * Filter allows you to change the allowed HTTP origin result.
				 *
				 * @since 2.5.1 Introduced.
				 *
				 * @param string $origin Origin URL if allowed, empty string if not.
				 */
				$origin = apply_filters( 'cocart_allow_origin', $origin );

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
					'X-CoCart-API', // @todo Deprecate in v5.0
					'CoCart-API-Cart-Key',
					'CoCart-API-Cart-Expiring',
					'CoCart-API-Cart-Expiration',
				);

				$server = rest_get_server();
				$server->send_header( 'Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE' );
				$server->send_header( 'Access-Control-Allow-Credentials', 'true' );
				$server->send_header( 'Vary', 'Origin', false );
				$server->send_header( 'Access-Control-Allow-Headers', implode( ', ', $allow_headers ) );
				$server->send_header( 'Access-Control-Expose-Headers', implode( ', ', $expose_headers ) );
				$server->send_header( 'Access-Control-Max-Age', '600' ); // Cache the result of preflight requests (600 is the upper limit for Chromium).
				$server->send_header( 'X-Robots-Tag', 'noindex' );
				$server->send_header( 'X-Content-Type-Options', 'nosniff' );

				// Allow preflight requests and any allowed origins. Preflight requests
				// are allowed because we'll be unable to validate customer header at that point.
				if ( $this->is_preflight() || ! is_allowed_http_origin( $origin ) ) {
					$server->send_header( 'Access-Control-Allow-Origin', $origin );
				}

				// Exit early during preflight requests. This is so someone cannot access API data by sending an OPTIONS request
				// with preflight headers and a _GET property to override the method.
				if ( $this->is_preflight() ) {
					exit;
				}
			}

			return $served;
		} // END cors_headers()

		/**
		 * Check for permission to access API.
		 *
		 * @throws CoCart_Data_Exception Exception if invalid data is detected.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.1.0
		 *
		 * @param mixed           $result  Response to replace the requested version with.
		 * @param WP_REST_Server  $server  Server instance.
		 * @param WP_REST_Request $request The request object.
		 *
		 * @return mixed
		 */
		public function check_api_permissions( $result, $server, $request ) {
			$method = strtolower( $request->get_method() );
			$path   = $request->get_route();
			$prefix = 'cocart/';

			/**
			 * Filters API permissions check.
			 *
			 * Should you choose to restrict any of CoCart's API routes for any method,
			 * you can set the requested API and method to enforce authentication by
			 * not allowing it permission to be used by the public.
			 *
			 * Methods you can use to filter the permissions check.
			 *
			 * - `get`
			 * - `post`
			 * - `put`
			 * - `patch`
			 * - `delete`
			 * - `options`
			 *
			 * @since 3.0.0 Introduced.
			 */
			$api_not_allowed = apply_filters( 'cocart_api_permission_check_{$method}', array() );

			try {
				// If no user is logged in then just return.
				if ( ! is_user_logged_in() ) {
					switch ( $method ) {
						case 'get':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									throw new CoCart_Data_Exception(
										'cocart_rest_permission_error',
										sprintf(
											/* translators: 1: permission method, 2: api route */
											__( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ),
											'READ',
											$path
										),
										401
									);
								}
							}
							break;
						case 'post':
						case 'put':
						case 'patch':
						case 'delete':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									throw new CoCart_Data_Exception(
										'cocart_rest_permission_error',
										sprintf(
											/* translators: 1: permission method, 2: api route */
											__( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ),
											'WRITE',
											$path
										),
										401
									);
								}
							}
							break;
						case 'options':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									throw new CoCart_Data_Exception(
										'cocart_rest_permission_error',
										sprintf(
											/* translators: 1: permission method, 2: api route */
											__( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ),
											'OPTIONS',
											$path
										),
										401
									);
								}
							}
							break;
						default:
							throw new CoCart_Data_Exception(
								'cocart_rest_permission_error',
								sprintf(
									/* translators: %s: api route */
									__( 'Unknown request method for %s.', 'cart-rest-api-for-woocommerce' ),
									$path
								),
								401
							);
					}
				}

				// Return previous result if nothing has changed.
				return $result;
			} catch ( CoCart_Data_Exception $e ) {
				return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END check_api_permissions()

		/**
		 * Sends the cart key to the header if a cart exists.
		 *
		 * @access public
		 *
		 * @since 2.7.0 Introduced.
		 *
		 * @param WP_Error|null|true $result WP_Error if authentication error, null if authentication
		 *                                    method wasn't used, true if authentication succeeded.
		 *
		 * @return WP_Error|true $result WP_Error if authentication error, true if authentication succeeded.
		 */
		public function cocart_key_header( $result ) {
			if ( ! empty( $result ) ) {
				return $result;
			}

			// Check that the CoCart session handler has loaded.
			if ( ! WC()->session instanceof CoCart_Session_Handler ) {
				return $result;
			}

			// Customer ID used as the cart key by default.
			$cart_key = WC()->session->get_customer_id();

			// Get cart cookie... if any.
			$cookie = WC()->session->get_session_cookie();

			// If a cookie exist, override cart key.
			if ( $cookie ) {
				$cart_key = $cookie[0];
			}

			// Check if we requested to load a specific cart.
			$cart_key = isset( $_REQUEST['cart_key'] ) ? trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ) : $cart_key; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Send cart key in the header if it's not empty or ZERO.
			if ( ! empty( $cart_key ) && '0' !== $cart_key ) {
				rest_get_server()->send_header( 'CoCart-API-Cart-Key', $cart_key );
			}

			return true;
		} // END cocart_key_header()

		/**
		 * Finds a user based on a matching billing phone number.
		 *
		 * @access protected
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @param numeric $phone The billing phone number to check.
		 *
		 * @return string The username returned if found.
		 */
		protected function get_user_by_phone( $phone ) {
			$matching_users = get_users(
				array(
					'meta_key'     => 'billing_phone', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'   => $phone, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_compare' => '=',
				)
			);

			$username = ! empty( $matching_users ) && is_array( $matching_users ) ? $matching_users[0]->user_login : $phone;

			return $username;
		} // END get_user_by_phone()

		/**
		 * Checks if the login provided is valid as a phone number or email address and returns the username.
		 *
		 * @access protected
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @param string $username Either a phone number, email address or username.
		 *
		 * @return string $username Username returned if valid.
		 */
		protected function get_username( $username ) {
			// Check if the username provided is a billing phone number and return the username if true.
			if ( WC_Validation::is_phone( $username ) ) {
				$username = $this->get_user_by_phone( $username ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			// Check if the username provided was an email address and return the username if true.
			if ( is_email( $username ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$user = get_user_by( 'email', $username ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( $user ) {
					$username = $user->user_login; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			return $username;
		} // END get_username()
	} // END class.
} // END if class exists.

return new CoCart_Authentication();
