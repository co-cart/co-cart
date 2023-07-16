<?php
/**
 * REST API: CoCart\RestApi\Authentication.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   2.6.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart\RestApi;

use CoCart\Utilities\RateLimits;
use WC_Validation;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 *
 * @see Utilities::RateLimits
 */
class Authentication {

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
	 * @since 4.0.0 Added API rate limits, set logged in cookie to return immediately.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
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

			// Check API rate limit.
			add_filter( 'rest_authentication_errors', array( $this, 'check_rate_limits' ), 20 );

			// Disable cookie authentication REST check.
			if ( is_ssl() || $this->is_wp_environment_local() ) {
				remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
			}

			// Set logged in cookie to return immediately.
			add_action( 'set_logged_in_cookie', array( $this, 'set_logged_in_cookie' ) );

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
	 * @since   2.9.1 Introduced.
	 * @version 3.0.0
	 *
	 * @param WP_Error|null|bool $error Error data.
	 *
	 * @return WP_Error|null|bool
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
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @return bool
	 */
	public static function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$request_uri         = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix . 'cocart/' ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		/**
		 * Filters the REST API requested.
		 *
		 * @since 2.1.0 Introduced.
		 *
		 * @param string $is_rest_api_request REST API uri requested.
		 */
		return apply_filters( 'cocart_is_rest_api_request', $is_rest_api_request );
	} // END is_rest_api_request()

	/**
	 * When the login cookies are set, they are not available until the next page reload. For CoCart specifically
	 * for returning updated carts, we need this to be available immediately.
	 *
	 * This is only to help with the native front and frameworks that have issues with or lack of support for Cookies.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $logged_in_cookie The value for the logged in cookie.
	 */
	public function set_logged_in_cookie( $logged_in_cookie ) {
		if ( ! defined( 'LOGGED_IN_COOKIE' ) ) {
			return;
		}
		$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
	} // END set_logged_in_cookie()

	/**
	 * Authenticate user.
	 *
	 * @access public
	 *
	 * @since   2.6.0 Introduced.
	 * @version 3.0.0
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
	 * @param WP_Error|null|bool $error Error data.
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
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WP_Error|null|bool $error Error data.
	 *
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
	 * @param WP_Error $error Authentication error data.
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
	 * @since 4.0.0 Added ability to authenticate via billing phone number as username.
	 *
	 * @return int|bool
	 */
	private function perform_basic_authentication() {
		$this->auth_method = 'basic_auth';

		// Look up authorization header and check it's a valid.
		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) && 0 === stripos( $_SERVER['HTTP_AUTHORIZATION'], 'basic ' ) ) {
			$exploded = explode( ':', base64_decode( substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ), 6 ) ), 2 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// If valid return username and password.
			if ( 2 == \count( $exploded ) ) {
				list( $username, $password ) = $exploded;

				// Check if the username provided is a billing phone number and return the username if true.
				if ( WC_Validation::is_phone( $username ) ) {
					$username = self::get_user_by_phone( $username ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}

				// Check if the username provided was an email address and return the username if true.
				if ( is_email( $username ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$user     = get_user_by( 'email', $username ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$username = $user->user_login; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}
		}
		// Check that we're trying to authenticate via simple headers.
		elseif ( ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$username = trim( sanitize_user( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$password = trim( sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_PW'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Check if the username provided is a billing phone number and return the username if true.
			if ( WC_Validation::is_phone( $username ) ) {
				$username = self::get_user_by_phone( $username );
			}

			// Check if the username provided was an email address and return the username if true.
			elseif ( is_email( $username ) ) {
				$user     = get_user_by( 'email', $username );
				$username = $user->user_login;
			}
		}
		// Fallback to check if the username and password was passed via URL.
		elseif ( ! empty( $_REQUEST['username'] ) && ! empty( $_REQUEST['password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$username = trim( sanitize_user( wp_unslash( $_REQUEST['username'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$password = trim( sanitize_text_field( wp_unslash( $_REQUEST['password'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Check if the username provided is a billing phone number and return the username if true.
			if ( WC_Validation::is_phone( $username ) ) {
				$username = self::get_user_by_phone( $username );
			}

			// Check if the username provided was an email address and return the username if true.
			elseif ( is_email( $username ) ) {
				$user     = get_user_by( 'email', $username );
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
			$this->set_error( new \WP_Error( 'cocart_authentication_error', __( 'Authentication is invalid. Please check the authentication information is correct and try again. Authentication may also only work on a secure connection.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 401 ) ) );

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
	 * @access protected
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.0.15
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

		// Adds new cors server headers.
		add_filter( 'rest_pre_serve_request', array( $this, 'cors_headers' ), 0, 4 );
	} // END allow_all_cors()

	/**
	 * Cross Origin headers.
	 *
	 * For security reasons, browsers restrict cross-origin HTTP requests initiated from scripts.
	 * This overrides that by providing access should the request be for CoCart.
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
	 * @param bool             $served  Whether the request has already been served. Default false.
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request  $request Request used to generate the response.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @return bool
	 */
	public function cors_headers( $served, $result, $request, $server ) {
		if ( strpos( $request->get_route(), 'cocart/' ) !== false ) {
			$origin     = get_http_origin();
			$origin_arg = $origin;

			// Requests from file:// and data: URLs send "Origin: null".
			if ( 'null' !== $origin ) {
				$origin = esc_url_raw( $origin );
			}

			// Check the origin against a list of allowed HTTP origins.
			if ( $origin && ! in_array( $origin, get_allowed_http_origins(), true ) ) {
				$origin = '';
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
				'CoCart-API-Cart-Key',
				'CoCart-API-Customer',
				'CoCart-API-Cart-Expiring',
				'CoCart-API-Cart-Expiration',
			);

			/**
			 * Change the allowed HTTP origin result.
			 *
			 * @since 2.5.1 Introduced.
			 * @since 4.0.0 Added the `$origin_arg` parameter.
			 *
			 * @param string $origin Origin URL if allowed, empty string if not.
			 * @param string $origin_arg Original origin string passed into is_allowed_http_origin function.
			 */
			$origin = apply_filters( 'cocart_allow_origin', $origin, $origin_arg );

			header( 'Access-Control-Allow-Origin: ' . $origin );
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
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
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
									/* translators: 1: permission method, 2: api route */
									sprintf(
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
									/* translators: 1: permission method, 2: api route */
									sprintf(
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
									/* translators: 1: permission method, 2: api route */
									sprintf(
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
							/* translators: %s: api route */
							sprintf(
								__( 'Unknown request method for %s.', 'cart-rest-api-for-woocommerce'
								),
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
	 * Checks the rate limit has not exceeded before proceeding with the request,
	 * and passes through any errors from other authentication methods used before this one..
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param \WP_Error|mixed $result Error from another authentication handler, null if we should handle it, or another value if not.
	 *
	 * @return \WP_Error|null|bool
	 */
	public function check_rate_limits( $result ) {
		// Disable Rate Limiting for logged-in users with 'manage options' capability.
		if ( current_user_can( 'manage_options' ) ) {
			return $result;
		}

		$rate_limiting_options = RateLimits::get_options();

		if ( $rate_limiting_options->enabled ) {
			$action_id = 'cocart_api_request_';

			if ( is_user_logged_in() ) {
				$action_id .= get_current_user_id();
			} else {
				$ip_address = self::get_ip_address( $rate_limiting_options->proxy_support );

				$action_id .= md5( $ip_address );
			}

			$retry  = RateLimits::is_exceeded_retry_after( $action_id );
			$server = rest_get_server();
			$server->send_header( 'RateLimit-Limit', $rate_limiting_options->limit );

			if ( false !== $retry ) {
				$server->send_header( 'RateLimit-Retry-After', $retry );
				$server->send_header( 'RateLimit-Remaining', 0 );
				$server->send_header( 'RateLimit-Reset', time() + $retry );

				$ip_address = $ip_address ?? self::get_ip_address( $rate_limiting_options->proxy_support );

				/**
				 * Fires after the rate limit exceeded.
				 *
				 * @since 4.0.0 Introduced.
				 *
				 * @param string $ip_address IP address the rate limit exceeded on.
				 */
				do_action( 'cocart_api_rate_limit_exceeded', $ip_address );

				return new \WP_Error(
					'rate_limit_exceeded',
					sprintf(
						__( 'Too many requests. Please wait %d seconds before trying again.', 'cart-rest-api-for-woocommerce' ),
						$retry
					),
					array( 'status' => 400 )
				);
			}

			$rate_limit = RateLimits::update_rate_limit( $action_id );
			$server->send_header( 'RateLimit-Remaining', $rate_limit->remaining );
			$server->send_header( 'RateLimit-Reset', $rate_limit->reset );
		}

		// Pass through errors from other authentication methods used before this one.
		return ! empty( $result ) ? $result : true;
	} // END check_rate_limits()

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
	 * @access protected
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param boolean $proxy_support Enables/disables proxy support.
	 *
	 * @return string
	 */
	protected static function get_ip_address( bool $proxy_support = false ) {
		if ( ! $proxy_support ) {
			return self::validate_ip( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unresolved_ip' ) ) );
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

			if ( strpos( $matches[0] ?? '', '"[' ) !== false ) { // Detect for ipv6, eg "[ipv6]:port".
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
	 * @access protected
	 *
	 * @static
	 *
	 * @param string $ip ipv4 or ipv6 ip string.
	 *
	 * @return string
	 */
	protected static function validate_ip( $ip ) {
		$ip = filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			array( FILTER_FLAG_NO_RES_RANGE, FILTER_FLAG_IPV6 )
		);

		return $ip ?: '0.0.0.0';
	} // END validate_ip()

	/**
	 * Finds a user based on a matching billing phone number.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param numeric $phone The billing phone number to check.
	 *
	 * @return string The username returned if found.
	 */
	protected static function get_user_by_phone( $phone ) {
		$matchingUsers = get_users( array(
			'meta_key'     => 'billing_phone',
			'meta_value'   => $phone,
			'meta_compare' => '=',
		) );

		$username = ! empty( $matchingUsers ) && is_array( $matchingUsers ) ? $matchingUsers[0]->user_login : $phone;

		return $username;
	} // END get_user_by_phone()

} // END class.

return new Authentication();
