<?php
/**
 * Handles REST API authentication.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart\Authentication
 * @since    2.6.0
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
		 * @access public
		 */
		public function __construct() {
			if ( CoCart_Helpers::is_rest_api_request() ) {
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
