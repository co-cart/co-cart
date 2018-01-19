<?php
/**
 * WooCommerce Cart REST API
 *
 * Handles cart endpoints requests for WC-API.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  WooCommerce Cart REST API/API
 * @since    1.0.0
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
	} // END setup

	/**
	 * Init WC Cart REST API.
	 *
	 * @access private
	 * @since  1.0.0
	 */
	private function cart_rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->include();

		// Init Cart REST API route.
		add_action( 'rest_api_init', array( $this, 'register_cart_routes' ), 0 );
	} // cart_rest_api_init()

	/**
	 * Include Cart REST API controller.
	 *
	 * @access private
	 * @since  1.0.0
	 */
	private function include() {
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
