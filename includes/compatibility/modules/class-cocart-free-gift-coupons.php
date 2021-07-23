<?php
/**
 * Handles support for Free Gift Coupons extension.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Compatibility\Modules
 * @since   3.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Free_Gift_Coupons' ) ) {
	return;
}

if ( ! class_exists( 'CoCart_FGC_Compatibility' ) ) {

	/**
	 * Free Gift Coupons Support.
	 */
	class CoCart_FGC_Compatibility {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			// Validate quantity on update cart in case sneaky folks mess with the markup.
			add_filter( 'cocart_update_cart_validation', array( $this, 'update_cart_validation' ), 10, 4 );

			// Display as Free! in cart and in orders.
			add_filter( 'cocart_cart_item_price', array( 'WC_Free_Gift_Coupons', 'cart_item_price' ), 10, 2 );
			add_filter( 'cocart_cart_item_subtotal', array( 'WC_Free_Gift_Coupons', 'cart_item_price' ), 10, 2 );
		}

		/**
		 * Update cart validation.
		 *
		 * Malicious users can change the quantity input in the source markup.
		 *
		 * @throws CoCart_Data_Exception Exception if invalid data is detected.
		 *
		 * @access public
		 * @static
		 * @param  bool   $passed_validation Whether or not this product is valid.
		 * @param  string $cart_item_key     The unique key in the cart array.
		 * @param  array  $values            The cart item data values.
		 * @param  int    $quantity          The cart quantity.
		 * @return bool
		 */
		public static function update_cart_validation( $passed_validation, $cart_item_key, $values, $quantity ) {
			try {
				if ( ! empty( $values['free_gift'] ) ) {
					// Has an initial FGC quantity.
					if ( ! empty( $values['fgc_quantity'] ) && $quantity !== $values['fgc_quantity'] ) {
						/* translators: %s Product title. */
						$error_message = sprintf( __( 'You are not allowed to modify the quantity of your %s gift.', 'cart-rest-api-for-woocommerce' ), $values['data']->get_name() );

						throw new CoCart_Data_Exception( 'cocart_fgc_update_quantity', $error_message, 404 );
					}
				}

				return $passed_validation;
			} catch ( CoCart_Data_Exception $e ) {
				return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END update_cart_validation()

	} // END class.

} // END if class exists.

return new CoCart_FGC_Compatibility();
