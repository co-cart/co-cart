<?php
/**
 * Handles product validation.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart/Classes/Product Validation
 * @since    2.1.0
 * @version  2.2.0
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
			// Prevent certain product types from being added to the cart.
			add_filter( 'cocart_add_to_cart_handler_external', array( $this, 'product_not_allowed_to_add' ), 0, 1 );
			add_filter( 'cocart_add_to_cart_handler_grouped', array( $this, 'product_not_allowed_to_add' ), 0, 1 );

			// Prevent password products being added to the cart.
			add_filter( 'cocart_add_to_cart_validation', array( $this, 'protected_product_add_to_cart' ), 10, 2 );

			// Correct product name for missing variation attributes.
			add_filter( 'cocart_product_name', array( $this, 'validate_variation_product_name' ), 0, 3 );
			add_filter( 'cocart_item_added_product_name', array( $this, 'validate_variation_product_name' ), 0, 3 );
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

		/**
		 * Validates the product name for a variable product.
		 *
		 * If variation details are missing then return the product title instead.
		 *
		 * @access  public
		 * @since   2.1.0
		 * @version 2.2.0
		 * @param   string $product_name - Product name before change.
		 * @param   object $_product     - Product data.
		 * @param   array  $cart_item    - Item details of the product in cart.
		 * @return  string $product_name - Product name after change.
		 */
		public function validate_variation_product_name( $product_name, $_product, $cart_item ) {
			if ( $_product->is_type( 'variation' ) ) {
				$product = wc_get_product( $_product->get_parent_id() );
				$default_attributes = $product->get_default_attributes();

				if ( empty( $cart_item['variation'] ) && empty( $default_attributes ) ) {
					return $_product->get_title();
				}
			}

			return $product_name;
		} // END validate_variation_product_name()

		/**
		 * Prevent password protected products being added to the cart.
		 *
		 * @access public
		 * @since  2.1.2
		 * @param  bool $passed     Validation.
		 * @param  int  $product_id Product ID.
		 * @return bool
		 */
		public function protected_product_add_to_cart( $passed, $product_id ) {
			if ( post_password_required( $product_id ) ) {
				$passed = false;

				CoCart_Logger::log( __( 'This product is protected and cannot be purchased.', 'cart-rest-api-for-woocommerce' ), 'error' );
			}
			return $passed;
		} // END protected_product_add_to_cart()

	} // END class.

} // END if class exists.

return new CoCart_Product_Validation();
