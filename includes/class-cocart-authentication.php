<?php
/**
 * Handles REST API authentication.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart\Authentication
 * @since    2.6.0
 * @version  2.7.0
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
		 * @version 2.7.0
		 */
		public function __construct() {
			if ( CoCart_Helpers::is_rest_api_request() ) {
				// Sends the cart key to the header.
				add_filter( 'rest_authentication_errors', array( $this, 'cocart_key_header' ), 0, 1 );

				// Disable cookie authentication REST check and only if site is secure.
				if ( is_ssl() ) {
					remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
				}
			}

			// Authenticate user.
			add_filter( 'determine_current_user', array( $this, 'authenticate' ), 20 );

			// Allow all cross origin requests.
			add_action( 'rest_api_init', array( $this, 'allow_all_cors' ), 15 );
		}

		/**
		 * Sends the cart key to the header.
		 *
		 * @access public
		 * @since  2.7.0
		 * @param  \WP_Error|mixed $result
		 * @return bool
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
			if ( isset( $_REQUEST['cart_key'] ) ) {
				$cart_key = isset( $_REQUEST['cart_key'] ) ? $_REQUEST['cart_key'] : $cart_key;
			}

			// Send cart key in the header if it's not empty or ZERO.
			if ( ! empty( $cart_key ) && $cart_key !== '0' ) {
				rest_get_server()->send_header( 'X-CoCart-API', $cart_key );
			}

			return true;
		} // END cocart_key_header()

		/**
		 * Authenticate user.
		 *
		 * @access public
		 * @param  int|false $user_id User ID if one has been determined, false otherwise.
		 * @return int|false
		 */
		public function authenticate( $user_id ) {
			// Do not authenticate twice and check if is a request to our endpoint in the WP REST API.
			if ( ! empty( $user_id ) || ! CoCart_Helpers::is_rest_api_request() ) {
				return $user_id;
			}

			$user_id = apply_filters( 'cocart_authenticate', $user_id, is_ssl() );

			return $user_id;
		} // END authenticate()

		/**
		 * Allow all cross origin header requests.
		 * 
		 * Disabled by default. Requires `cocart_allow_all_cors` filter set to true to enable.
		 *
		 * @access  public
		 * @since   2.2.0
		 * @version 2.3.0
		 */
		public function allow_all_cors() {
			// If not enabled via filter then return.
			if ( apply_filters( 'cocart_disable_all_cors', true ) ) {
				return;
			}

			// If the REST API request was not for CoCart then return.
			if ( ! CoCart_Helpers::is_rest_api_request() ) {
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
		 * @version 2.5.1
		 * @param   bool             $served  Whether the request has already been served. Default false.
		 * @param   WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
		 * @param   WP_REST_Request  $request Request used to generate the response.
		 * @param   WP_REST_Server   $server  Server instance.
		 * @return  bool
		 */
		public function cors_headers( $served, $result, $request, $server ) {
			header( 'Access-Control-Allow-Origin: ' . apply_filters( 'cocart_allow_origin', $_SERVER['HTTP_ORIGIN'] ) );
			header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With' );

			return $served;
		} // END cors_headers()

	} // END class.

} // END if class exists.

return new CoCart_Authentication();
