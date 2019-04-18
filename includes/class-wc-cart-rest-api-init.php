<?php
/**
 * Cart REST API for WooCommerce
 *
 * Handles cart endpoints requests for WC-API.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  Cart REST API for WooCommerce/API
 * @since    1.0.0
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart REST API class.
 */
class WC_Cart_Rest_API {

	/**
	 * Setup class.
	 *
	 * @access public
	 */
	public function __construct() {
		// WC Cart REST API.
		$this->cart_rest_api_init();
	} // END __construct()

	/**
	 * Initialize WC Cart REST API.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	private function cart_rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->include_cart_controller();

		// Register Cart REST API.
		add_action( 'rest_api_init', array( $this, 'register_cart_routes' ), 0 );
	} // cart_rest_api_init()

	/**
	 * Include Cart REST API controller.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	private function include_cart_controller() {
		/**
		 * WooCommerce 3.6+ Compatibility
		 * 
		 * Cart and notice functions are not included during a REST request.
		 */
		if ( version_compare( WC_VERSION, '3.6.0', '>=' ) && WC()->is_rest_api_request() == 'wc/' ) {
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
			if ( null === WC()->customer ) {
				if ( is_user_logged_in() ) {
					WC()->customer = new WC_Customer( get_current_user_id() );
				} else {
					WC()->customer = new WC_Customer( get_current_user_id(), true );
				}
			}

			// Load Cart.
			if ( null === WC()->cart ) {
				WC()->cart = new WC_Cart();
			}
		}

		// REST API v2 controller.
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-cart-controller.php' );
	} // include()

	/**
	 * Register Cart REST API routes.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function register_cart_routes() {
		$controller = new WC_REST_Cart_Controller();
		$controller->register_routes();
	} // END register_cart_route

} // END class

return new WC_Cart_Rest_API();
