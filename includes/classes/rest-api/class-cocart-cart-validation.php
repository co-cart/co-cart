<?php
/**
 * Class: CoCart_Cart_Validation
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0 Introduced.
 * @version 4.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles cart validation.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_Cart_Validation {

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		add_filter( 'cocart_before_get_cart', array( $this, 'check_cart_validity' ), 0, 2 );
		add_filter( 'cocart_before_get_cart', array( $this, 'check_cart_item_stock' ), 10, 2 );
		add_filter( 'cocart_before_get_cart', array( $this, 'check_cart_coupons' ), 15, 2 );

		add_filter( 'cocart_after_item_added_to_cart', array( $this, 'add_customer_billing_details' ), 10, 2 );
		add_filter( 'cocart_after_items_added_to_cart', array( $this, 'add_customer_billing_details' ), 10, 2 );
	} // END __construct()

	/**
	 * Looks through the cart to check each item if they are still valid.
	 * If not remove item and add error notice.
	 *
	 * @access public
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @uses wc_get_product()
	 * @uses wc_add_notice()
	 * @uses WC_Cart()->get_cart()
	 *
	 * @hook: cocart_before_get_cart - 0
	 *
	 * @param array  $cart_contents Cart contents before cart changes.
	 * @param object $cart          The cart object.
	 *
	 * @return array $cart_contents Cart contents after cart changes.
	 */
	public function check_cart_validity( $cart_contents, $cart ) {
		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
			}

			$product = $cart_item['data'];

			if ( ! $product || ! $product->exists() || 'trash' === $product->get_status() ) {
				$cart->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.
				wc_add_notice( __( 'An item which is no longer available was removed from your cart.', 'cart-rest-api-for-woocommerce' ), 'error' );
			}

			// If product is no longer purchasable then don't return it and notify customer.
			if ( $product && ! $product->is_purchasable() ) {
				$message = sprintf(
					/* translators: %s: product name */
					__( '%s has been removed from your cart because it can no longer be purchased.', 'cart-rest-api-for-woocommerce' ),
					$product->get_name()
				);

				/**
				 * Filter message about item removed from the cart.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_cart_item_removed_message', $message, $product );

				$cart->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.

				wc_add_notice( $message, 'error' );
			}
		}

		$cart_contents = $cart->get_cart(); // Get cart contents now updated.

		return $cart_contents;
	} // END check_cart_validity()

	/**
	 * Looks through the cart to check each item is in stock. If not, add error notice.
	 *
	 * @access public
	 *
	 * @since 3.0.0  Introduced.
	 * @since 4.0.0 Fetch product data if missing.
	 *
	 * @uses wc_get_product()
	 * @uses wc_add_notice()
	 * @uses wc_get_held_stock_quantity()
	 * @uses wc_format_stock_quantity_for_display()
	 * @uses WC_Cart()->get_cart()
	 *
	 * @hook: cocart_before_get_cart - 10
	 *
	 * @param array  $cart_contents Cart contents before cart changes.
	 * @param object $cart          The cart object.
	 *
	 * @return array $cart_contents Cart contents after cart changes.
	 */
	public function check_cart_item_stock( $cart_contents, $cart ) {
		$qty_in_cart              = $cart->get_cart_item_quantities();
		$current_session_order_id = isset( WC()->session->order_awaiting_payment ) ? absint( WC()->session->order_awaiting_payment ) : 0;

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( empty( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
			}

			$product = $cart_item['data'];

			$item_has_error = false;

			// Check stock based on stock-status.
			if ( ! $product->is_in_stock() ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: product name */
						__( 'Sorry, "%s" is not in stock. Please edit your cart and try again. We apologize for any inconvenience caused.', 'cart-rest-api-for-woocommerce' ),
						$product->get_name()
					),
					'error'
				);

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
			 * @param WC_Product $product   The product object.
			 * @param array      $cart_item Cart item values.
			 */
			if ( apply_filters( 'cocart_cart_item_required_stock_is_not_enough', $product->get_stock_quantity() < ( $held_stock + $required_stock ), $product, $cart_item ) ) {
				if ( ! $item_has_error ) {
					wc_add_notice(
						sprintf(
							/* translators: 1: product name 2: quantity in stock */
							__( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'cart-rest-api-for-woocommerce' ),
							$product->get_name(),
							wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product )
						),
						'error'
					);
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
	 * @uses WC_Coupon()->add_coupon_message()
	 * @uses WC_Cart()->remove_coupon()
	 * @uses WC_Cart()->get_cart()
	 *
	 * @hook: cocart_before_get_cart - 15
	 *
	 * @param array  $cart_contents Cart contents before cart changes.
	 * @param object $cart          The cart object.
	 *
	 * @return array $cart_contents Cart contents after cart changes.
	 */
	public function check_cart_coupons( $cart_contents, $cart ) {
		foreach ( $cart->get_applied_coupons() as $code ) {
			$coupon = new \WC_Coupon( $code );

			if ( ! $coupon->is_valid() ) {
				$coupon->add_coupon_message( \WC_Coupon::E_WC_COUPON_INVALID_REMOVED );
				$cart->remove_coupon( $code );
			}
		}

		$cart_contents = $cart->get_cart(); // Get cart contents now updated.

		return $cart_contents;
	} // END check_cart_coupons()

	/**
	 * Sets customers billing email address and phone number if passed along while adding an item to the cart.
	 *
	 * Originally not hooked in place but provides developers a good example.
	 *
	 * @access public
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @hook: cocart_after_item_added_to_cart
	 * @hook: cocart_after_items_added_to_cart
	 *
	 * @param array           $item_added_to_cart The product added to cart.
	 * @param WP_REST_Request $request            The request object.
	 */
	public function add_customer_billing_details( $item_added_to_cart, $request ) {
		/**
		 * Set customers billing email address.
		 *
		 * @since 3.1.0 Introduced.
		 */
		if ( isset( $request['email'] ) ) {
			$is_email = \WC_Validation::is_email( $request['email'] );

			if ( $is_email ) {
				WC()->customer->set_props(
					array(
						'billing_email' => trim( esc_html( $request['email'] ) ),
					)
				);
			}
		}

		/**
		 * Set customers billing phone number.
		 *
		 * @since 4.1.0 Introduced.
		 */
		if ( isset( $request['phone'] ) ) {
			$is_phone = \WC_Validation::is_phone( $request['phone'] );

			if ( $is_phone ) {
				WC()->customer->set_props(
					array(
						'billing_phone' => trim( esc_html( $request['phone'] ) ),
					)
				);
			}
		}
	} // END add_customer_billing_details()
} // END class.

return new CoCart_Cart_Validation();
