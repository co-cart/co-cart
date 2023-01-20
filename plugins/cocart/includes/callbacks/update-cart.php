<?php
/**
 * Callback: CoCart\RestApi\Callbacks\UpdateCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callback
 * @since   3.1.0 Introduced.
 */

namespace CoCart\RestApi\Callbacks;

use CoCart\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update cart callback.
 *
 * Allows you to update the cart items in bulk.
 *
 * @since 3.1.0 Introduced.
 */
class UpdateCart extends Abstracts\CoCart_Cart_Extension_Callback {

	/**
	 * Callback name.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $name = 'update-cart';

	/**
	 * Callback to update the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.0.0 Added the cart $controller as a parameter.
	 *
	 * @param WP_REST_Request $request    Full details about the request.
	 * @param object          $controller The cart controller.
	 *
	 * @return bool Returns true.
	 */
	public function callback( $request, $controller ) {
		try {
			$items = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			$cart_updated = false;

			if ( ! empty( $items ) ) {
				foreach ( $items as $item_key => $quantity ) {
					$cart_item = $controller->get_cart_item( $item_key, 'update' );

					// If item does not exist then continue to the next item.
					if ( empty( $cart_item ) ) {
						continue;
					}

					$_product = $cart_item['data'];

					$quantity = wc_stock_amount( preg_replace( '/[^0-9\.]/', '', $quantity ) );

					// If quantity is not set or is the same then update the next item.
					if ( '' === $quantity || $quantity === $cart_item['quantity'] ) {
						continue;
					}

					// Update cart validation.
					$passed_validation = apply_filters( 'cocart_update_cart_validation', true, $item_key, $cart_item, $quantity );

					// Is sold individually.
					if ( $_product->is_sold_individually() && $quantity > 1 ) {
						/* Translators: %s Product title. */
						wc_add_notice( sprintf( __( 'You can only have 1 %s in your cart.', 'cart-rest-api-for-woocommerce' ), $_product->get_name() ), 'error' );
						$passed_validation = false;
					}

					if ( $passed_validation ) {
						$controller->get_cart_instance()->set_quantity( $item_key, $quantity, false );
						$cart_updated = true;
					}
				}
			}

			if ( $cart_updated ) {
				/**
				 * Fires after the cart has updated via a callback.
				 *
				 * @since 3.1.0 Introduced.
				 *
				 * @param WP_REST_Request $request    Full details about the request.
				 * @param object          $controller The cart controller.
				 */
				do_action( 'cocart_cart_updated', $request, $controller );

				$controller->calculate_totals();

				wc_add_notice( __( 'Cart updated.', 'cart-rest-api-for-woocommerce' ), 'success' );
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END callback()

} // END class

return new UpdateCart();
