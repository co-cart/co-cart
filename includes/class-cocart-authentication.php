<?php
/**
 * Handles REST API authentication.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart\Classes
 * @since    2.6.0
 * @version  3.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Authentication' ) ) {

	class CoCart_Authentication {

		/**
		 * Constructor.
		 *
		 * @access  public
		 * @since   2.6.0
		 * @version 3.0.0
		 */
		public function __construct() {
			// Check that we are only authenticating for our API.
			if ( $this->is_rest_api_request() ) {
				// Authenticate user.
				add_filter( 'determine_current_user', array( $this, 'authenticate' ), 20 );

				// Sends the cart key to the header.
				add_filter( 'rest_authentication_errors', array( $this, 'cocart_key_header' ), 0, 1 );

				// Disable cookie authentication REST check and only if site is secure.
				if ( is_ssl() ) {
					remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
				}

				// Check API permissions.
				add_filter( 'rest_pre_dispatch', array( $this, 'check_api_permissions' ), 10, 3 );

			// Allow all cross origin requests.
			add_action( 'rest_api_init', array( $this, 'allow_all_cors' ), 15 );
		}
		}

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
			$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix . 'cocart/' ) );

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

			$user_id = apply_filters( 'cocart_authenticate', $user_id, is_ssl() );

			return $user_id;
		} // END authenticate()

		/**
		 * Sends the cart key to the header.
		 *
		 * @access  public
		 * @since   2.7.0
		 * @version 3.0.0
		 * @param   \WP_Error|mixed $result
		 * @return  bool
		 */
		public function cocart_key_header( $result ) {
			if ( ! empty( $result ) ) {
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
			$cart_key = isset( $_REQUEST['cart_key'] ) ? $_REQUEST['cart_key'] : $cart_key;

			// Send cart key in the header if it's not empty or ZERO.
			if ( ! empty( $cart_key ) && $cart_key !== '0' ) {
				rest_get_server()->send_header( 'X-CoCart-API', $cart_key );
			}

			return true;
		} // END cocart_key_header()

		/**
		 * Allow all cross origin header requests.
		 *
		 * Disabled by default. Requires `cocart_allow_all_cors` filter set to true to enable.
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
		 * @access  public
		 * @since   2.2.0
		 * @version 2.8.3
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

				header( 'Access-Control-Allow-Origin: ' . apply_filters( 'cocart_allow_origin', $origin ) );
				header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE' );
				header( 'Access-Control-Allow-Credentials: true' );
				header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With' );
				header( 'Access-Control-Expose-Headers: X-CoCart-API' );
			}

			return $served;
		} // END cors_headers()

		/**
		 * Check for permission to access API.
		 *
		* @throws CoCart_Data_Exception Exception if invalid data is detected.
		*
		 * @access public
		 * @since  3.0.0
		 * @param  mixed           $result  Response to replace the requested version with.
		 * @param  WP_REST_Server  $server  Server instance.
		 * @param  WP_REST_Request $request Request used to generate the response.
		 * @return mixed
		 */
		public function check_api_permissions( $result, $server, $request ) {
			$method       = $request->get_method();
			$path         = $request->get_route();
			$prefix       = 'cocart/';

			/**
			 * Should the developer choose to restrict any of CoCart's API routes for any method.
			 * They can set the requested API and method to enforce authentication by not allowing it permission to the public.
			 */
			$api_not_allowed = apply_filters( 'cocart_api_permission_check_' . strtolower( $method ), array() );

			try {
				// If the user ID is zero then user is not authenticated.
				if ( 0 === $this->user_id || $this->user_id < 0 ) {
					switch( $method ) {
						case 'GET':
							foreach( $api_not_allowed as $route ) {
								if ( preg_match('!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'READ', $path ), 401 );
								}
							}
							break;
						case 'POST':
						case 'PUT':
						case 'PATCH':
						case 'DELETE':
							foreach( $api_not_allowed as $route ) {
								if ( preg_match('!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'WRITE', $path ), 401 );
								}
							}
							break;
						case 'OPTIONS':
							return true;

						default:
							/* translators: %s: api route */
							throw new CoCart_Data_Exception( 'cocart_rest_permission_error', sprintf( __( 'Unknown request method for %s.', 'cart-rest-api-for-woocommerce' ), $path ), 401 );
					}
				}

				// Return previous result if nothing has changed.
				return $result;
			} catch( CoCart_Data_Exception $e) {
				return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END check_permissions()

	} // END class.

} // END if class exists.

return new CoCart_Authentication();
