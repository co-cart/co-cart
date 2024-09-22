<?php
/**
 * Utilities: Cart Helpers class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.2.0 Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Checkout\Helpers\ReserveStock;

/**
 * Helper class to handle cart functions for the API.
 *
 * @since 4.2.0 Introduced.
 */
class CoCart_Utilities_Cart_Helpers {

	/**
	 * Returns the cart key.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return string Cart key.
	 */
	public static function get_cart_key() {
		if ( ! method_exists( WC()->session, 'get_customer_id' ) ) {
			return '';
		}

		return (string) WC()->session->get_customer_id();
	} // END get_cart_key()

	/**
	 * Checks if coupons are enabled in WooCommerce.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @return bool
	 */
	public static function are_coupons_enabled() {
		return wc_coupons_enabled();
	} // END are_coupons_enabled()

	/**
	 * Check given coupon exists.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @param string $coupon_code Coupon code.
	 *
	 * @return bool
	 */
	public static function coupon_exists( $coupon_code ) {
		$coupon = new \WC_Coupon( $coupon_code );

		return (bool) $coupon->get_id() || $coupon->get_virtual();
	} // END coupon_exists()

	/**
	 * Checks if shipping is enabled and there is at least one method setup.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @return bool
	 */
	public static function is_shipping_enabled() {
		return wc_shipping_enabled() && 0 !== wc_get_shipping_method_count( true );
	} // END is_shipping_enabled()

	/**
	 * Returns the customers details from checkout fields.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.2.0 Customer object is now required.
	 *
	 * @param string           $fields   The customer fields to return.
	 * @param WC_Customer|null $customer The customer object or nothing.
	 *
	 * @return array Returns the customer details based on the field requested.
	 */
	public static function get_customer_fields( $fields = 'billing', $customer = null ) {
		// If no customer is set then return nothing.
		if ( empty( $customer ) ) {
			return array();
		}

		/**
		 * We get the checkout fields so we return the fields the store uses during checkout.
		 * This is so we ONLY return the customers information for those fields used.
		 * These fields could be changed either via filter, another plugin or
		 * based on the conditions of the customers location or cart contents.
		 */
		$checkout_fields = WC()->checkout->get_checkout_fields( $fields );

		$results = array();

		/**
		 * We go through each field and check that we can return it's data as set by default.
		 * If we can't get the data we rely on getting customer data via a filter for that field.
		 * Any fields that can not return information will be empty.
		 */
		foreach ( $checkout_fields as $key => $value ) {
			$field_name = 'get_' . $key; // Name of the default field function. e.g. "get_billing_first_name".

			$results[ $key ] = method_exists( $customer, $field_name ) ? $customer->$field_name() : apply_filters( "cocart_get_customer_{$key}", '', $customer ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		return $results;
	} // END get_customer_fields()

	/**
	 * Convert queued error notices into an exception.
	 *
	 * Since we're not rendering notices at all, we need to convert them to exceptions.
	 *
	 * This method will find the first error message and thrown an exception instead. Discards notices once complete.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.1 Introduced.
	 *
	 * @param string $error_code Error code for the thrown exceptions.
	 */
	public static function convert_notices_to_exceptions( $error_code = 'unknown_server_error' ) {
		if ( 0 === wc_notice_count( 'error' ) ) {
			wc_clear_notices();
			return;
		}

		$error_notices = wc_get_notices( 'error' );

		// Prevent notices from being output later on.
		wc_clear_notices();

		foreach ( $error_notices as $error_notice ) {
			throw new CoCart_Data_Exception( esc_html( $error_code ), esc_html( wp_strip_all_tags( $error_notice['notice'] ) ), 400 );
		}
	} // END convert_notices_to_exceptions()

	/**
	 * Throws exception when an item cannot be added to the cart.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.4 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 */
	public static function throw_product_not_purchasable( $product ) {
		$message = sprintf(
			/* translators: %s: product name */
			__( "'%s' is not available for purchase.", 'cart-rest-api-for-woocommerce' ),
			$product->get_name()
		);

		/**
		 * Filters message about product unable to be purchased.
		 *
		 * @param string     $message Message.
		 * @param WC_Product $product The product object.
		 */
		$message = apply_filters( 'cocart_product_cannot_be_purchased_message', $message, $product );

		throw new CoCart_Data_Exception( 'cocart_cannot_be_purchased', esc_html( $message ), 400 );
	} // END throw_product_not_purchasable()

	/**
	 * Throws exception if the item key is not provided when either removing, updating or restoring the item.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.17 Introduced.
	 *
	 * @param string $item_key Generated ID based on the product information when added to the cart.
	 * @param string $status   Status of which we are checking the item key.
	 *
	 * @return string $item_key Generated ID based on the product information when added to the cart.
	 */
	public static function throw_missing_item_key( $item_key, $status ) {
		$item_key = (string) $item_key; // Make sure the item key is a string value.

		if ( '0' === $item_key ) {
			$message = __( 'Missing cart item key is required!', 'cart-rest-api-for-woocommerce' );

			/**
			 * Filters message about cart item key required.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string $message Message.
			 * @param string $status  Status of which we are checking the item key.
			 */
			$message = apply_filters( 'cocart_cart_item_key_required_message', $message, $status );

			throw new CoCart_Data_Exception( 'cocart_cart_item_key_required', esc_html( $message ), 404 );
		}

		return $item_key;
	} // END throw_missing_item_key()

	/**
	 * Gets remaining stock for a product.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return int Remaining stock.
	 */
	public static function get_remaining_stock_for_product( $product ) {
		$reserve_stock = new ReserveStock();
		$draft_order   = WC()->session->get( 'cocart_draft_order', 0 );
		$qty_reserved  = $reserve_stock->get_reserved_stock( $product, $draft_order );

		return $product->get_stock_quantity() - $qty_reserved;
	} // END get_remaining_stock_for_product()
} // END class
