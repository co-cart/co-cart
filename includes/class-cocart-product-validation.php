<?php
/**
 * Handles product validation.
 *
 * @since    2.1.0
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart/Classes/Product Validation
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Product_Validation' ) ) {

	class CoCart_Product_Validation {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'cocart_add_to_cart_handler_external', array( $this, 'product_not_allowed_to_add' ), 0, 1 );
			add_filter( 'cocart_add_to_cart_handler_grouped', array( $this, 'product_not_allowed_to_add' ), 0, 1 );
		}

		/**
		 * Error response for product types that are not allowed to be added to the cart.
		 *
		 * @access public
		 * @param  WC_Product $product_data
		 * @return WP_Error
		 */
		public function product_not_allowed_to_add( $product_data ) {
			/* translators: %1$s: product name, %2$s: product type */
			$message = sprintf( __( 'You cannot add "%1$s" to your cart as it is an "%2$s" product.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name(), $product_data->get_type() );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product type that cannot be added to the cart.
			 *
			 * @param string     $message Message.
			 * @param WC_Product $product_data Product data.
			 */
			$message = apply_filters( 'cocart_cannot_add_product_type_to_cart_message', $message, $product_data );

			return new WP_Error( 'cocart_cannot_add_product_type_to_cart', $message, array( 'status' => 500 ) );
		} // END product_not_allowed_to_add()

	} // END class.

} // END if class exists.

return new CoCart_Product_Validation();
