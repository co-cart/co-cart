<?php
/**
 * CoCart - Clear Cart controller
 *
 * Handles the request to clear the cart with /clear endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\v1
 * @since    2.1.0
 * @version  2.9.3
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Clear Cart controller class.
 *
 * @package CoCart\API
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
	 * @access  public
	 * @since   2.1.0
	 * @version 2.5.0
	 */
	public function register_routes() {
		// Clear Cart - cocart/v1/clear (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clear_cart' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

	/**
	 * Clear cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.9.3
	 * @return  WP_Error|WP_REST_Response
	 */
	public function clear_cart() {
		do_action( 'cocart_before_cart_emptied' );

		WC()->session->set( 'cart', array() );
		WC()->session->set( 'removed_cart_contents', array() );
		WC()->session->set( 'shipping_methods', array() );
		WC()->session->set( 'coupon_discount_totals', array() );
		WC()->session->set( 'coupon_discount_tax_totals', array() );
		WC()->session->set( 'applied_coupons', array() );
		WC()->session->set(
			'total',
			array(
				'subtotal'            => 0,
				'subtotal_tax'        => 0,
				'shipping_total'      => 0,
				'shipping_tax'        => 0,
				'shipping_taxes'      => array(),
				'discount_total'      => 0,
				'discount_tax'        => 0,
				'cart_contents_total' => 0,
				'cart_contents_tax'   => 0,
				'cart_contents_taxes' => array(),
				'fee_total'           => 0,
				'fee_tax'             => 0,
				'fee_taxes'           => array(),
				'total'               => 0,
				'total_tax'           => 0,
			)
		);
		WC()->session->set( 'cart_fees', array() );

		/**
		 * If the user is authorized and `woocommerce_persistent_cart_enabled` filter is left enabled
		 * then we will delete the persistent cart as well.
		 */
		if ( get_current_user_id() && apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
			delete_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id() );
		}

		do_action( 'cocart_cart_emptied' );

		if ( 0 === count( WC()->cart->get_cart() ) || 0 === count( WC()->session->get( 'cart' ) ) ) {
			do_action( 'cocart_cart_cleared' );

			$message = __( 'Cart is cleared.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'notice' );

			/**
			 * Filters message about the cart being cleared.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_cart_cleared_message', $message );

			return $this->get_response( $message, $this->rest_base );
		} else {
			$message = __( 'Clearing the cart failed!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about the cart failing to clear.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_clear_cart_failed_message', $message );

			return new WP_Error( 'cocart_clear_cart_failed', $message, array( 'status' => 404 ) );
		}
	} // END clear_cart()

} // END class
