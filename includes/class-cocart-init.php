<?php
/**
 * CoCart REST API
 *
 * Handles cart endpoints requests for WC-API and CoCart.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    1.0.0
 * @version  2.1.2
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

		// Include REST API Controllers. - ONLY works if hooked to `wp_loaded` !!!
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
	 * @version 2.1.2
	 */
	private function maybe_load_cart() {
		if ( version_compare( WC_VERSION, '3.6.0', '>=' ) && WC()->is_rest_api_request() ) {
			require_once( WC_ABSPATH . 'includes/wc-cart-functions.php' );
			require_once( WC_ABSPATH . 'includes/wc-notice-functions.php' );

			// Disable cookie authentication REST check and only if site is secure.
			if ( is_ssl() ) {
				remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
			}

			// Initialize session.
			$this->initialize_session();

			// Initialize cart.
			$this->initialize_cart();

			// Identify if user has switched.
			if ( $this->has_user_switched() ) {
				$this->user_switched();
			}
		}
	} // END maybe_load_cart()

	/**
	 * If the current customer ID in session does not match,
	 * then the user has switched.
	 *
	 * @access protected
	 * @since  2.1.0
	 * @return bool
	 */
	protected function has_user_switched() {
		if ( ! WC()->session instanceof CoCart_Session_Handler ) {
			return;
		}

		// Get cart cookie... if any.
		$cookie = WC()->session->get_cart_cookie();

		// Current user ID. If user is NOT logged in then the customer is a guest.
		$current_user_id = strval( get_current_user_id() );

		// Does a cookie exist?
		if ( $cookie ) {
			$customer_id = $cookie[0];

			// If the user is logged in and does not match ID in cookie then user has switched.
			if ( $current_user_id !== $customer_id ) {
				CoCart_Logger::log( sprintf( __( 'User has changed! Was %s before and is now %s', 'cart-rest-api-for-woocommerce' ), $customer_id, $current_user_id ), 'info' );

				return true;
			}
		}

		return false;
	} // END has_user_switched()

	/**
	 * Allows something to happen if a user has switched.
	 *
	 * @access public
	 * @since  2.1.0
	 */
	public function user_switched() {
		do_action( 'cocart_user_switched' );
	} // END user_switched()

	/**
	 * Initialize CoCart session.
	 *
	 * @access public
	 * @since  2.1.0
	 * @return object WC()->session
	 */
	public function initialize_session() {
		// CoCart session handler class.
		$session_class = 'CoCart_Session_Handler';

		if ( is_null( WC()->session ) || ! WC()->session instanceof $session_class ) {
			// Prefix session class with global namespace if not already namespaced.
			if ( false === strpos( $session_class, '\\' ) ) {
				$session_class = '\\' . $session_class;
			}

			// Initialize new session.
			WC()->session = new $session_class();
			WC()->session->init();
		}
	} // END initialize_session()

	/**
	 * Initialize CoCart cart.
	 *
	 * @access public
	 * @since  2.1.0
	 */
	public function initialize_cart() {
		if ( is_null( WC()->customer ) || ! WC()->customer instanceof WC_Customer ) {
			$customer_id = strval( get_current_user_id() );

			WC()->customer = new WC_Customer( $customer_id, true );

			// Customer should be saved during shutdown.
			add_action( 'shutdown', array( WC()->customer, 'save' ), 10 );
		}

		if ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
			WC()->cart = new WC_Cart();
		}
	} // END initialize_cart()

	/**
	 * Include CoCart REST API controllers.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 */
	public function rest_api_includes() {
		$this->maybe_load_cart();

		// Legacy - WC Cart REST API v2 controller.
		include_once( dirname( __FILE__ ) . '/api/legacy/wc-v2/class-wc-rest-cart-controller.php' );

		// CoCart REST API controllers.
		include_once( dirname( __FILE__ ) . '/api/class-cocart-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-add-item-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-clear-cart-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-calculate-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-count-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-item-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-logout-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-cocart-totals-controller.php' );
	} // rest_api_includes()

	/**
	 * Register CoCart REST API routes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 */
	public function register_cart_routes() {
		$controllers = array(
			// WC Cart REST API v2 controller.
			'WC_REST_Cart_Controller',

			// CoCart REST API v1 controller.
			'CoCart_API_Controller',
			'CoCart_Add_Item_Controller',
			'CoCart_Clear_Cart_Controller',
			'CoCart_Calculate_Controller',
			'CoCart_Count_Items_Controller',
			'CoCart_Item_Controller',
			'CoCart_Logout_Controller',
			'CoCart_Totals_Controller'
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	} // END register_cart_routes()

} // END class

return new CoCart_Rest_API();
