<?php
/**
 * CoCart - Clear Cart controller
 *
 * Handles the request to clear the cart with /clear endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Clear Cart controller class.
 *
 * @package CoCart/API
 */
class CoCart_Clear_Cart_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'clear';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Clear Cart - cocart/v1/clear (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'clear_cart' ),
		) );
	} // register_routes()

	/**
	 * Clear cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 * @return  WP_Error|WP_REST_Response
	 */
	public function clear_cart() {
		WC()->cart->empty_cart( true ); // Clears the cart deletes persistent cart permanently.
		WC()->session->set('cart', array()); // Empty the session cart data.

		if ( WC()->cart->is_empty() ) {
			do_action( 'cocart_cart_cleared' );

			return new WP_REST_Response( __( 'Cart is cleared.', 'cart-rest-api-for-woocommerce' ), 200 );
		} else {
			return new WP_Error( 'cocart_clear_cart_failed', __( 'Clearing the cart failed!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}
	} // END clear_cart()

} // END class
