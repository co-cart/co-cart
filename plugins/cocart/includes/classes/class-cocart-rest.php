<?php
/**
 * Class: CoCart\REST.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   4.0.0 Introduced.
 */

namespace CoCart;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize REST API Decoupled.
 *
 * Use CoCart under your own prefix other than WP-JSON.
 *
 * @since 4.0.0 Introduced.
 */
class REST {

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'rest_api_init' ) );
		add_action( 'parse_request', array( $this, 'rest_api_loaded' ) );
		add_filter( 'cocart_is_rest_api_request', array( $this, 'is_cocart_request' ) );
	} // END __construct()

	/**
	 * Registers rewrite rules for CoCart REST API.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @see CoCare\REST\rest_api_register_rewrites()
	 *
	 * @global WP $wp Current WordPress environment instance.
	 */
	public function rest_api_init() {
		/**
		 * Filter checks if we are to register the CoCart route?
		 *
		 * Set as false to allow routes to register.
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'cocart_disable_register_route', true ) ) {
			return false;
		}

		self::rest_api_register_rewrites();

		global $wp;
		$wp->add_query_var( 'rest_cocart' );
	} // END rest_api_init()

	/**
	 * Adds REST rewrite rules for CoCart.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @see CoCare\REST\rest_get_url_prefix()
	 *
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 */
	public function rest_api_register_rewrites() {
		global $wp_rewrite;

		add_rewrite_rule( '^' . self::rest_get_url_prefix() . '/?$', 'index.php?rest_cocart=/', 'top' );
		add_rewrite_rule( '^' . self::rest_get_url_prefix() . '/(.*)?', 'index.php?rest_cocart=/$matches[1]', 'top' );
	} // END rest_api_register_rewrites

	/**
	 * Retrieves the CoCart URL prefix for API.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return string Prefix.
	 */
	protected function rest_get_url_prefix() {
		/**
		 * Filters the REST URL prefix.
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @param string $prefix URL prefix. Default 'cocart-api'.
		 */
		return apply_filters( 'cocart_rest_get_url_prefix', 'cocart-api' );
	} // END rest_get_url_prefix()

	/**
	 * Loads the REST API for CoCart.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @global WP $wp Current WordPress environment instance.
	 */
	public function rest_api_loaded() {
		if ( empty( $GLOBALS['wp']->query_vars['rest_cocart'] ) ) {
			return;
		}

		// Define REST Request.
		define( 'REST_REQUEST', true );

		// Initialize the server.
		$server = rest_get_server();

		// Fire off the request.
		$route = untrailingslashit( $GLOBALS['wp']->query_vars['rest_cocart'] );
		if ( empty( $route ) ) {
			$route = '/';
		}

		// If not a CoCart endpoint, load store endpoint instead.
		if ( ( false === strpos( $route, 'cocart/' ) ) ) {
			$route = '/cocart/v2/store';
		}

		$server->serve_request( $route );

		// We're done.
		die();
	} // END rest_api_loaded()

	/**
	 * Filters the REST API requested.
	 *
	 * Checks if we are requesting via CoCart store name API or regular WP REST API.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $rest_requested REST API uri requested.
	 *
	 * @return bool
	 */
	public function is_cocart_request( $rest_requested ) {
		$rest_prefix       = trailingslashit( self::rest_get_url_prefix() );
		$request_uri       = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$is_cocart_request = ( false !== strpos( $request_uri, $rest_prefix . 'cocart/' ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( $is_cocart_request ) {
			return $is_cocart_request;
		}

		return $rest_requested;
	} // END is_cocart_request()

} // END class

return new REST();
