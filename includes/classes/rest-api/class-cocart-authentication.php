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
		 * Allowed headers.
		 *
		 * @var array
		 */
		const ALLOW_HEADERS = array(
			'Authorization',
			'X-Requested-With',
			'Content-Disposition',
			'Content-MD5',
			'Content-Type',
		);

		/**
		 * Exposed headers.
		 *
		 * @var array
		 */
		const EXPOSE_HEADERS = array(
			'X-WP-Total',
			'X-WP-TotalPages',
			'Link',
			'CoCart-API-Cart-Key',
			'CoCart-API-Cart-Expiring',
			'CoCart-API-Cart-Expiration',
		);

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

				// Check authentication errors.
				add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ), 15 );

				// Check API permissions.
				add_filter( 'rest_pre_dispatch', array( $this, 'check_api_permissions' ), 10, 3 );

				// Send headers.
				add_filter( 'rest_pre_serve_request', array( $this, 'send_headers' ), 1, 4 );

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
		 * @deprecated 4.2.0 No replacement. Not needed anymore.
		 *
		 * @param WP_Error|null|bool $error Error from another authentication handler, null if we should handle it, or another value if not.
		 *
		 * @return WP_Error|null|bool
		 */
		public function cocart_user_logged_in( $error ) {
			cocart_deprecated_function( 'CoCart_Authentication::cocart_user_logged_in', '4.2.0', null );

			// Pass through errors from other authentication error checks used before this one.
			if ( ! empty( $error ) ) {
				return $error;
			}

			global $current_user;

			if ( $current_user instanceof WP_User && $current_user->exists() ) {
				wc_update_user_last_active( $current_user->ID );
				update_user_meta( $current_user->ID, '_woocommerce_load_saved_cart_after_login', 1 );
			}

			return $error;
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
		 * @static
		 *
		 * @since 4.1.0 Introduced.
		 * @since 4.2.0 Changed access from protected to public.
		 *
		 * @return string $auth_header
		 */
		public static function get_auth_header() {
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
		 * @uses CoCart_Authentication()::get_auth_header()
		 * @uses CoCart_Authentication()::get_username()
		 * @uses get_user_by()
		 * @uses wp_check_password()
		 *
		 * @return int|bool
		 */
		private function perform_basic_authentication() {
			$this->auth_method = 'basic_auth';
			$username          = '';
			$password          = '';

			$auth_header = self::get_auth_header();

			// Look up authorization header and check it's a valid.
			if ( ! empty( $auth_header ) && 0 === stripos( $auth_header, 'basic ' ) ) {
				$exploded = explode( ':', base64_decode( substr( $auth_header, 6 ) ), 2 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

				// If valid return username and password.
				if ( 2 === \count( $exploded ) ) {
					list( $username, $password ) = $exploded;

					$username = self::get_username( $username );
				}
			} elseif ( ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
				// Check that we're trying to authenticate via simple headers.
				$username = trim( sanitize_user( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$username = self::get_username( $username );
				$password = trim( sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_PW'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( ! empty( $_REQUEST['username'] ) && ! empty( $_REQUEST['password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// Fallback to check if the username and password was passed via URL.
				$username = trim( sanitize_user( wp_unslash( $_REQUEST['username'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$username = self::get_username( $username );
				$password = trim( sanitize_text_field( wp_unslash( $_REQUEST['password'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			// If no username or password identified then authentication is not required.
			if ( empty( $username ) && empty( $password ) ) {
				return false;
			} elseif ( empty( $username ) || empty( $password ) ) {
				// If either username or password is missing then return error.
				$this->set_error( new WP_Error( 'cocart_authentication_error', __( 'Authentication invalid!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 401 ) ) );
				return false;
			}

			$user = get_user_by( 'login', $username );

			if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
				$this->set_error(
					new WP_Error(
						'cocart_authentication_error',
						sprintf(
							/* translators: %s: User name. */
							__( 'The password you entered for the username "%s" is incorrect.', 'cart-rest-api-for-woocommerce' ),
							$username
						), array( 'status' => 401 )
					)
				);
				return false;
			}

			if ( is_wp_error( $user ) ) {
				$this->set_error( new WP_Error( 'cocart_authentication_error', __( 'Authentication is invalid. Please check the authentication information is correct and try again.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 401 ) ) );
				return false;
			}

			$this->user = $user;

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
		 * Sends headers.
		 *
		 * Returns allowed headers and exposes headers that can be used.
		 * Nocache headers are sent on authenticated requests.
		 *
		 * @access public
		 *
		 * @since 4.2.0 Introduced.
		 *
		 * @uses is_user_logged_in()
		 * @uses wp_get_nocache_headers()
		 *
		 * @param bool             $served  Whether the request has already been served. Default false.
		 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
		 * @param WP_REST_Request  $request The request object.
		 * @param WP_REST_Server   $server  Server instance.
		 *
		 * @return bool
		 */
		public function send_headers( $served, $result, $request, $server ) {
			if ( strpos( $request->get_route(), 'cocart/' ) !== false ) {

				$server->send_header( 'Access-Control-Allow-Headers', implode( ', ', self::ALLOW_HEADERS ) );
				$server->send_header( 'Access-Control-Expose-Headers', implode( ', ', self::EXPOSE_HEADERS ) );

				/**
				 * Send nocache headers on authenticated requests.
				 *
				 * @param bool $rest_send_nocache_headers Whether to send no-cache headers.
				 *
				 * @since 4.2.0 Introduced.
				 */
				$send_no_cache_headers = apply_filters( 'cocart_send_nocache_headers', is_user_logged_in() );
				if ( $send_no_cache_headers ) {
					foreach ( wp_get_nocache_headers() as $no_cache_header_key => $no_cache_header_value ) {
						$server->send_header( $no_cache_header_key, $no_cache_header_value );
					}
				}
			}

			// Exit early during preflight requests. This is so someone cannot access API data by sending an OPTIONS request
			// with preflight headers and a _GET property to override the method.
			if ( $this->is_preflight() ) {
				exit;
			}

			return $served;
		} // END send_headers()

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
		 *
		 * @uses get_http_origin()
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

				/**
				 * Filter allows you to change the allowed HTTP origin result.
				 *
				 * @since 2.5.1 Introduced.
				 *
				 * @param string $origin Origin URL if allowed, empty string if not.
				 */
				$origin = apply_filters( 'cocart_allow_origin', $origin );

				$server->send_header( 'Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE' );
				$server->send_header( 'Access-Control-Allow-Credentials', 'true' );
				$server->send_header( 'Vary', 'Origin', false );
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
			$api_not_allowed = apply_filters( 'cocart_api_permission_check_' . $method, array() );

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
		 * Finds a user based on a matching billing phone number.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.1.0 Introduced.
		 * @since 4.2.0 Changed access from protected to public.
		 *
		 * @param numeric $phone The billing phone number to check.
		 *
		 * @return string The username returned if found.
		 */
		public static function get_user_by_phone( $phone ) {
			$matching_users = get_users(
				array(
					'meta_key'     => 'billing_phone', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'   => $phone, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_compare' => '=',
				)
			);

			$username = ! empty( $matching_users ) && is_array( $matching_users ) ? $matching_users[0]->user_login : '';

			return $username;
		} // END get_user_by_phone()

		/**
		 * Checks if the login provided is valid as a phone number or email address and returns the username.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.1.0 Introduced.
		 * @since 4.2.0 Changed access from protected to public.
		 *
		 * @param string $username Either a phone number, email address or username.
		 *
		 * @return string $username Username returned if valid.
		 */
		public static function get_username( $username ) {
			// Check if the username provided is a billing phone number and return the username if true.
			if ( WC_Validation::is_phone( $username ) ) {
				$username = self::get_user_by_phone( $username ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

		/**
		 * Get current user IP Address.
		 *
		 * X_REAL_IP and CLIENT_IP are custom implementations designed to facilitate obtaining a user's ip through proxies, load balancers etc.
		 *
		 * _FORWARDED_FOR (XFF) request header is a de-facto standard header for identifying the originating IP address of a client connecting to a web server through a proxy server.
		 * Note for X_FORWARDED_FOR, Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2.
		 * Make sure we always only send through the first IP in the list which should always be the client IP.
		 * Documentation at https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
		 *
		 * Forwarded request header contains information that may be added by reverse proxy servers (load balancers, CDNs, and so on).
		 * Documentation at https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
		 * Full RFC at https://datatracker.ietf.org/doc/html/rfc7239
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.2.0 Introduced.
		 *
		 * @param boolean $proxy_support Enables/disables proxy support.
		 *
		 * @return string
		 */
		public static function get_ip_address( bool $proxy_support = false ) { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewParamTypeDeclarations.boolFound
			if ( ! $proxy_support ) {
				return self::validate_ip( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unresolved_ip' ) ) ); // phpcs:ignore PHPCompatibility.Operators.NewOperators.t_coalesceFound
			}

			// Check Cloudflare's connecting IP header.
			if ( array_key_exists( 'HTTP_CF_CONNECTING_IP', $_SERVER ) ) {
				return self::validate_ip( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) );
			}

			if ( array_key_exists( 'HTTP_X_REAL_IP', $_SERVER ) ) {
				return self::validate_ip( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) ) );
			}

			if ( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER ) ) {
				return self::validate_ip( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) );
			}

			if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
				$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
				if ( is_array( $ips ) && ! empty( $ips ) ) {
					return self::validate_ip( trim( $ips[0] ) );
				}
			}

			if ( array_key_exists( 'HTTP_FORWARDED', $_SERVER ) ) {
				// Using regex instead of explode() for a smaller code footprint.
				// Expected format: Forwarded: for=192.0.2.60;proto=http;by=203.0.113.43,for="[2001:db8:cafe::17]:4711"...
				preg_match(
					'/(?<=for\=)[^;,]*/i', // We catch everything on the first "for" entry, and validate later.
					sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) ),
					$matches
				);

				if ( strpos( $matches[0] ?? '', '"[' ) !== false ) { // phpcs:ignore PHPCompatibility.Operators.NewOperators.t_coalesceFound, Detect for ipv6, eg "[ipv6]:port".
					preg_match(
						'/(?<=\[).*(?=\])/i', // We catch only the ipv6 and overwrite $matches.
						$matches[0],
						$matches
					);
				}

				if ( ! empty( $matches ) ) {
					return self::validate_ip( trim( $matches[0] ) );
				}
			}

			return '0.0.0.0';
		} // END get_ip_address()

		/**
		 * Uses filter_var() to validate and return ipv4 and ipv6 addresses.
		 *
		 * Will return 0.0.0.0 if the ip is not valid. This is done to group and still rate limit invalid ips.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 4.2.0 Introduced.
		 *
		 * @param string $ip ipv4 or ipv6 ip string.
		 *
		 * @return string
		 */
		public static function validate_ip( $ip ) {
			$ip = filter_var(
				$ip,
				FILTER_VALIDATE_IP,
				array( FILTER_FLAG_NO_RES_RANGE, FILTER_FLAG_IPV6 )
			);

			return $ip ?: '0.0.0.0';
		} // END validate_ip()
	} // END class.
} // END if class exists.

return new CoCart_Authentication();
