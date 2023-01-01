<?php
/**
 * REST API: CoCart\RestApi\CartValidation
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart\RestApi;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles cart validation.
 *
 * @since 3.0.0 Introduced.
 */
class CartValidation {

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		add_filter( 'cocart_before_get_cart', array( $this, 'check_cart_item_stock' ), 10, 2 );
		add_filter( 'cocart_before_get_cart', array( $this, 'check_cart_coupons' ), 15, 2 );
	} // END __construct()

	/**
	 * Looks through the cart to check each item is in stock. If not, add error notice.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.0.4
	 *
	 * @param array  $cart_contents Cart contents before cart changes.
	 * @param object $cart          Cart object.
	 *
	 * @return array  $cart_contents Cart contents after cart changes.
	 */
	public function check_cart_item_stock( $cart_contents, $cart ) {
		$qty_in_cart              = $cart->get_cart_item_quantities();
		$current_session_order_id = isset( WC()->session->order_awaiting_payment ) ? absint( WC()->session->order_awaiting_payment ) : 0;

		foreach ( $cart_contents as $item_key => $values ) {
			$product = $values['data'];

			$item_has_error = false;

			// Check stock based on stock-status.
			if ( ! $product->is_in_stock() ) {
				/* translators: %s: product name */
				wc_add_notice( sprintf( __( 'Sorry, "%s" is not in stock. Please edit your cart and try again. We apologize for any inconvenience caused.', 'cart-rest-api-for-woocommerce' ), $product->get_name() ), 'error' );

				$item_has_error = true;
			}

			// We only need to check products managing stock, with a limited stock qty.
			if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
				continue;
			}

			// Check stock based on all items in the cart and consider any held stock within pending orders.
			$held_stock     = wc_get_held_stock_quantity( $product, $current_session_order_id );
			$required_stock = $qty_in_cart[ $product->get_stock_managed_by_id() ];

			/**
			 * Allows filter if product have enough stock to get added to the cart.
			 *
			 * @param bool       $has_stock If have enough stock.
			 * @param WC_Product $product   Product instance.
			 * @param array      $values    Cart item values.
			 */
			if ( apply_filters( 'cocart_cart_item_required_stock_is_not_enough', $product->get_stock_quantity() < ( $held_stock + $required_stock ), $product, $values ) ) {
				if ( ! $item_has_error ) {
					/* translators: 1: product name 2: quantity in stock */
					wc_add_notice( sprintf( __( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'cart-rest-api-for-woocommerce' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) ), 'error' );
				}
			}
		}

		$cart_contents = $cart->get_cart(); // Get cart contents now updated.

		return $cart_contents;
	} // END check_cart_item_stock()

	/**
	 * Check cart coupons for errors.
	 *
	 * @access public
	 *
	 * @param array  $cart_contents Cart contents before cart changes.
	 * @param object $cart          Cart object.
	 *
	 * @return array $cart_contents Cart contents after cart changes.
	 */
	public function check_cart_coupons( $cart_contents, $cart ) {
		foreach ( $cart->get_applied_coupons() as $code ) {
			$coupon = new \WC_Coupon( $code );

			if ( ! $coupon->is_valid() ) {
				$coupon->add_coupon_message( 101 );
				$cart->remove_coupon( $code );
			}
		}

		$cart_contents = $cart->get_cart(); // Get cart contents now updated.

		return $cart_contents;
	} // END check_cart_coupons()

} // END class.

return new CartValidation();
