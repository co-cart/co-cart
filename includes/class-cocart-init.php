<?php
/**
 * CoCart REST API
 *
 * Handles cart endpoints requests for WC-API.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    1.0.0
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API class.
 */
class CoCart_Rest_API {

	/**
	 * Setup class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.0.0
	 */
	public function __construct() {
		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register API endpoint.
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// Handle cocart endpoint requests.
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );

		// CoCart REST API.
		$this->cocart_rest_api_init();
	} // END __construct()

	/**
	 * Add new query vars.
	 *
	 * @access public
	 * @since  2.0.0
	 * @param  array $vars Query vars.
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'cocart';

		return $vars;
	} // END add_query_vars()

	/**
	 * Add rewrite endpoint.
	 *
	 * @access public
	 * @static
	 * @since  2.0.0
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( 'cocart', EP_ALL );
	} // END add_endpoint()

	/**
	 * API request - Trigger any API requests.
	 *
	 * @access public
	 * @since  2.0.0
	 * @global $wp
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['cocart'] ) ) {
			$wp->query_vars['cocart'] = sanitize_key( wp_unslash( $_GET['cocart'] ) );
		}

		// CoCart endpoint requests.
		if ( ! empty( $wp->query_vars['cocart'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			wc_nocache_headers();

			// Clean the API request.
			$api_request = strtolower( wc_clean( $wp->query_vars['cocart'] ) );

			// Trigger generic action before request hook.
			do_action( 'cocart_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'cocart_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'cocart_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	} // END handle_api_requests()

	/**
	 * Init CoCart REST API.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @version 2.0.0
	 */
	private function cocart_rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		// If WooCommerce does not exists then do nothing!
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Include REST API Controllers.
		add_action( 'wp_loaded', array( $this, 'rest_api_includes' ), 5 );

		// Register CoCart REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_cart_routes' ), 10 );
	} // cart_rest_api_init()

	/**
	 * Loads the cart, session and notices should it be required.
	 * 
	 * Note: Only needed should the site be running WooCommerce 3.6 
	 * or higher as they are not included during a REST request.
	 *
	 * @access  private
	 * @since   2.0.0
	 * @version 2.0.3
	 */
	private function maybe_load_cart() {
		if ( version_compare( WC_VERSION, '3.6.0', '>=' ) && WC()->is_rest_api_request() ) {
			require_once( WC_ABSPATH . 'includes/wc-cart-functions.php' );
			require_once( WC_ABSPATH . 'includes/wc-notice-functions.php' );

			if ( null === WC()->session ) {
				$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

				// Prefix session class with global namespace if not already namespaced
				if ( false === strpos( $session_class, '\\' ) ) {
					$session_class = '\\' . $session_class;
				}

				WC()->session = new $session_class();
				WC()->session->init();
			}

			/**
			 * For logged in customers, pull data from their account rather than the 
			 * session which may contain incomplete data.
			 */
			if ( is_null( WC()->customer ) ) {
				if ( is_user_logged_in() ) {
					WC()->customer = new WC_Customer( get_current_user_id() );
				} else {
					WC()->customer = new WC_Customer( get_current_user_id(), true );
				}

				// Customer should be saved during shutdown.
				add_action( 'shutdown', array( WC()->customer, 'save' ), 10 );
			}

			// Load Cart.
			if ( null === WC()->cart ) {
				WC()->cart = new WC_Cart();
			}
		}
	} // END maybe_load_cart()

	/**
	 * Include CoCart REST API controllers.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.0.0
	 */
	public function rest_api_includes() {
		$this->maybe_load_cart();

		// WC Cart REST API v2 controller.
		include_once( dirname( __FILE__ ) . '/api/wc-v2/class-wc-rest-cart-controller.php' );

		// CoCart REST API controller.
		include_once( dirname( __FILE__ ) . '/api/class-cocart-controller.php' );
	} // rest_api_includes()

	/**
	 * Register CoCart REST API routes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.0.0
	 */
	public function register_cart_routes() {
		$controllers = array(
			// WC Cart REST API v2 controller.
			'WC_REST_Cart_Controller',

			// CoCart REST API v1 controller.
			'CoCart_API_Controller'
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	} // END register_cart_routes()

} // END class

return new CoCart_Rest_API();
