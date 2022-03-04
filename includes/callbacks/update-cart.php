<?php
/**
 * CoCart - Update Cart Callback.
 *
 * Allows you to update the cart items in bulk.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callback
 * @since   3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update cart callback class.
 */
class CoCart_Cart_Update_Callback extends CoCart_Cart_Extension_Callback {

	/**
	 * Callback name.
	 *
	 * @access protected
	 * @var    string
	 */
	protected $name = 'update-cart';

	/**
	 * Callback to update the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 */
	public function callback( $request ) {
		try {
			$items = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			$controller = new CoCart_Cart_V2_Controller();

			$cart_updated = false;

			if ( ! empty( $items ) ) {
				foreach ( $items as $item_key => $quantity ) {
					$data = array(
						'item_key' => $item_key,
						'quantity' => wc_stock_amount( preg_replace( '/[^0-9\.]/', '', $quantity ) ),
					);

					$cart_item = $controller->get_cart_item( $item_key, 'update' );

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

return new CoCart_Cart_Update_Callback();
