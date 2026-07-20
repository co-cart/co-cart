<?php
/**
 * CoCart - Update Cart Callback.
 *
 * Allows you to update the cart items in bulk.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callback
 * @since   3.1.0 Introduced.
 * @version 4.1.0
 */

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
class CoCart_Cart_Update_Callback extends CoCart_Cart_Extension_Callback {

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
	 * @since 4.0.0 Added the cart controller as a parameter.
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param object          $controller The cart controller.
	 *
	 * @return bool Returns true.
	 */
	public function callback( $request, $controller ) {
		try {
			if ( $controller->is_completely_empty() ) {
				throw new CoCart_Data_Exception( 'cocart_cart_empty', __( 'Cart is empty. Please add items to cart first.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$items = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			$cart_updated = false;

			if ( ! empty( $items ) ) {
				foreach ( $items as $item_key => $quantity ) {
					$cart_item = $controller->get_cart_item( $item_key, 'update' );

					// If item does not exist then continue to the next item.
					if ( empty( $cart_item ) ) {
						continue;
					}

					$product = $cart_item['data'];

					$quantity = wc_stock_amount( preg_replace( '/[^0-9\.]/', '', $quantity ) );

					// If quantity is not set or is the same then update the next item.
					if ( '' === $quantity || $quantity === $cart_item['quantity'] ) {
						continue;
					}

					/**
					 * Filter allows you to determine if the updated item in cart passed validation.
					 *
					 * @since 2.1.0 Introduced.
					 *
					 * @param bool   $cart_valid True by default.
					 * @param string $item_key   Item key of the item updated.
					 * @param array  $cart_item  Cart item after updated.
					 * @param int    $quantity   New quantity amount.
					 */
					$passed_validation = apply_filters( 'cocart_update_cart_validation', true, $item_key, $cart_item, $quantity );

					// Is sold individually.
					if ( $product->is_sold_individually() && $quantity > 1 ) {
						$message = sprintf(
							/* translators: %s Product name. */
							__( 'You can only have 1 "%s" in your cart.', 'cart-rest-api-for-woocommerce' ),
							$product->get_name()
						);
						wc_add_notice( $message, 'error' );
						$passed_validation = false;
					}

					// Check there is enough stock for the requested quantity before it is set on the cart item.
					if ( $passed_validation && $quantity > 0 && ! $product->has_enough_stock( $quantity ) ) {
						$message = sprintf(
							/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
							__( 'You cannot add %1$s of "%2$s" to the cart — there is not enough stock (only %3$s remaining).', 'cart-rest-api-for-woocommerce' ),
							$quantity,
							$product->get_name(),
							wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product )
						);
						wc_add_notice( $message, 'error' );
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
				 * @deprecated 4.1.0 Replaced with `cocart_update_cart_before_totals` hook.
				 *
				 * @see cocart_update_cart_before_totals
				 */
				cocart_do_deprecated_action( 'cocart_cart_updated', '4.1.0', 'cocart_update_cart_before_totals', '', array( $request, $controller ) );

				$this->recalculate_totals( $request, $controller );

				// Only returns success notice if there are no error notices.
				if ( 0 === wc_notice_count( 'error' ) ) {
					wc_add_notice( __( 'Cart updated.', 'cart-rest-api-for-woocommerce' ), 'success' );
				}
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END callback()
} // END class

return new CoCart_Cart_Update_Callback();
