<?php
/**
 * Utilities: Quantity Limits class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Checkout\Helpers\ReserveStock;

/**
 * Returns quantity limits for products and cart items
 * when using the CoCart REST API.
 *
 * @since 5.0.0 Introduced.
 */
class CoCart_Utilities_Quantity_Limits {

	/**
	 * Get quantity limits (min, max, step/multiple) for a cart item.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array $cart_item A cart item array.
	 *
	 * @return array {
	 *     @type int|float $minimum     Minimum quantity allowed.
	 *     @type int|float $maximum     Maximum quantity allowed.
	 *     @type int|float $multiple_of Quantity must be a multiple of this.
	 *     @type bool      $editable    Whether the quantity can be changed.
	 * }
	 */
	public function get_cart_item_quantity_limits( $cart_item ) {
		$product = $cart_item['data'] ?? false;

		if ( ! $product instanceof \WC_Product ) {
			return array(
				'minimum'     => 1,
				'maximum'     => 9999,
				'multiple_of' => 1,
				'editable'    => true,
			);
		}

		$editable = ! $product->is_sold_individually();

		/**
		 * Filters whether the quantity of a cart item is editable.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param bool        $editable  Whether the quantity is editable.
		 * @param \WC_Product $product   The product object.
		 * @param array|null  $cart_item The cart item, or null.
		 */
		$editable = apply_filters( 'cocart_product_quantity_editable', $editable, $product, $cart_item );

		return array_merge(
			$this->get_add_to_cart_limits( $product, $cart_item ),
			array(
				'editable' => (bool) $editable,
			)
		);
	} // END get_cart_item_quantity_limits()

	/**
	 * Get quantity limits for a product's add-to-cart form.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param \WC_Product $product   Product instance.
	 * @param array|null  $cart_item Optional cart item associated with the product.
	 *
	 * @return array {
	 *     @type int|float $minimum     Minimum quantity allowed.
	 *     @type int|float $maximum     Maximum quantity allowed.
	 *     @type int|float $multiple_of Quantity must be a multiple of this.
	 * }
	 */
	public function get_add_to_cart_limits( \WC_Product $product, $cart_item = null ) {
		$args = wc_get_quantity_input_args( array(), $product );

		$minimum     = max( 1, wc_stock_amount( $args['min_value'] ) );
		$multiple_of = max( 1, wc_stock_amount( $args['step'] ) );
		$maximum     = $this->adjust_product_quantity_limit( $args['max_value'], $product, $cart_item );

		/**
		 * Filters the minimum quantity requirement the product allows to be purchased.
		 *
		 * @since 3.0.17 Introduced.
		 * @since 3.1.0  Added product object as parameter.
		 *
		 * @param int|float   $minimum   Minimum purchase quantity requirement.
		 * @param \WC_Product $product   The product object.
		 * @param array|null  $cart_item The cart item, or null.
		 */
		$minimum = wc_stock_amount( apply_filters( 'cocart_quantity_minimum_requirement', $minimum, $product, $cart_item ) );

		/**
		 * Filters the products maximum quantity allowed to be purchased.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @param int|float   $maximum   Maximum purchase quantity allowed.
		 * @param \WC_Product $product   The product object.
		 * @param array|null  $cart_item The cart item, or null.
		 */
		$maximum = wc_stock_amount( apply_filters( 'cocart_quantity_maximum_allowed', $maximum, $product, $cart_item ) );

		/**
		 * Filters the quantity step (multiple_of) for a product.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param int|float   $multiple_of Quantity step value.
		 * @param \WC_Product $product     The product object.
		 * @param array|null  $cart_item   The cart item, or null.
		 */
		$multiple_of = wc_stock_amount( apply_filters( 'cocart_quantity_multiple_of', $multiple_of, $product, $cart_item ) );

		// Ensure minimum is at least one step.
		$minimum = max( $multiple_of, $this->limit_to_multiple( $minimum, $multiple_of, 'ceil' ) );

		// Ensure maximum is at least the minimum and a valid multiple.
		$maximum = max( $minimum, $this->limit_to_multiple( $maximum, $multiple_of, 'floor' ) );

		return array(
			'minimum'     => $minimum,
			'maximum'     => $maximum,
			'multiple_of' => $multiple_of,
		);
	} // END get_add_to_cart_limits()

	/**
	 * Fix a quantity violation by adjusting it to the nearest valid quantity.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param int|float $quantity  Quantity.
	 * @param array     $cart_item Cart item.
	 *
	 * @return int|float
	 */
	public function normalize_cart_item_quantity( $quantity, array $cart_item ) {
		$product = $cart_item['data'] ?? false;

		if ( ! $product instanceof \WC_Product ) {
			return wc_stock_amount( $quantity );
		}

		$quantity = floatval( $quantity );

		if ( 0 >= $quantity ) {
			return wc_stock_amount( 0 );
		}

		$limits       = $this->get_cart_item_quantity_limits( $cart_item );
		$new_quantity = $this->limit_to_multiple( $quantity, $limits['multiple_of'], 'round' );

		if ( $new_quantity < $limits['minimum'] ) {
			$new_quantity = $limits['minimum'];
		}

		if ( $new_quantity > $limits['maximum'] ) {
			$new_quantity = $limits['maximum'];
		}

		return wc_stock_amount( $new_quantity );
	} // END normalize_cart_item_quantity()

	/**
	 * Check that a given quantity is valid according to any limits in place.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param int|float $quantity  Quantity to validate.
	 * @param array     $cart_item Cart item.
	 *
	 * @return \WP_Error|true
	 */
	public function validate_cart_item_quantity( $quantity, $cart_item ) {
		$product = $cart_item['data'] ?? false;

		if ( ! $product instanceof \WC_Product ) {
			return true;
		}

		$limits   = $this->get_cart_item_quantity_limits( $cart_item );
		$quantity = wc_stock_amount( $quantity );

		if ( ! $limits['editable'] && $quantity > $limits['maximum'] ) {
			return new \WP_Error(
				'cocart_readonly_quantity',
				sprintf(
					/* translators: %s: Product name. */
					__( 'The quantity of "%s" cannot be changed.', 'cocart-core' ),
					$product->get_name()
				)
			);
		}

		if ( $quantity < $limits['minimum'] ) {
			return new \WP_Error(
				'cocart_quantity_below_minimum',
				sprintf(
					/* translators: 1: Product name, 2: Minimum quantity. */
					__( 'The minimum quantity of "%1$s" allowed in the cart is %2$s.', 'cocart-core' ),
					$product->get_name(),
					$limits['minimum']
				)
			);
		}

		if ( $quantity > $limits['maximum'] ) {
			return new \WP_Error(
				'cocart_quantity_above_maximum',
				sprintf(
					/* translators: 1: Product name, 2: Maximum quantity. */
					__( 'The maximum quantity of "%1$s" allowed in the cart is %2$s.', 'cocart-core' ),
					$product->get_name(),
					$limits['maximum']
				)
			);
		}

		if ( ! $this->is_multiple_of( $quantity, $limits['multiple_of'] ) ) {
			return new \WP_Error(
				'cocart_quantity_invalid_multiple',
				sprintf(
					/* translators: 1: Product name, 2: Multiple of value. */
					__( 'The quantity of "%1$s" must be a multiple of %2$s.', 'cocart-core' ),
					$product->get_name(),
					$limits['multiple_of']
				)
			);
		}

		return true;
	} // END validate_cart_item_quantity()

	/**
	 * Return a number using the closest multiple of another number.
	 *
	 * Used to enforce step/multiple values.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param int|float $number            Number to round.
	 * @param int|float $multiple_of       The multiple.
	 * @param string    $rounding_function ceil, floor, or round.
	 *
	 * @return int|float
	 */
	protected function limit_to_multiple( $number, $multiple_of, $rounding_function = 'round' ) {
		$number      = floatval( $number );
		$multiple_of = floatval( $multiple_of );

		if ( 0 >= $multiple_of ) {
			return $number;
		}

		if ( $this->is_multiple_of( $number, $multiple_of ) ) {
			return $number;
		}

		$rounding_function = in_array( $rounding_function, array( 'ceil', 'floor', 'round' ), true ) ? $rounding_function : 'round';

		return floatval( $rounding_function( $number / $multiple_of ) * $multiple_of );
	} // END limit_to_multiple()

	/**
	 * Checks if a number is a multiple of another number.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param int|float $number      The number to check.
	 * @param int|float $multiple_of The multiple.
	 *
	 * @return bool
	 */
	protected function is_multiple_of( $number, $multiple_of ) {
		if ( 0 >= $multiple_of ) {
			return false;
		}

		$division_result = $number / $multiple_of;

		// Use tolerance for floating-point comparison to handle precision errors.
		return abs( $division_result - round( $division_result ) ) < 0.0001;
	} // END is_multiple_of()

	/**
	 * Get the limit for the total number of a product allowed in the cart.
	 *
	 * Based on product properties including remaining stock.
	 * Defaults to a maximum of 9999 of any product in the cart at once.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param int|float   $purchase_limit The purchase limit from the product.
	 * @param \WC_Product $product        Product instance.
	 * @param array|null  $cart_item      Optional cart item associated with the product.
	 *
	 * @return int|float
	 */
	protected function adjust_product_quantity_limit( $purchase_limit, \WC_Product $product, $cart_item = null ) {
		$limits = array( $purchase_limit > 0 ? $purchase_limit : 9999 );

		// If managing stock and backorders are not allowed, consider remaining stock.
		if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
			$remaining = $this->get_remaining_stock_for_product( $product );

			if ( ! is_null( $remaining ) ) {
				$limits[] = $remaining;
			}
		}

		$limit = min( array_filter( $limits ) );

		/**
		 * Filters the adjusted quantity limit for a product.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param int|float   $limit     The adjusted quantity limit.
		 * @param \WC_Product $product   The product object.
		 * @param array|null  $cart_item The cart item, or null.
		 */
		return wc_stock_amount( apply_filters( 'cocart_quantity_limit', $limit, $product, $cart_item ) );
	} // END adjust_product_quantity_limit()

	/**
	 * Returns the remaining stock for a product, factoring in reserved stock.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param \WC_Product $product Product instance.
	 *
	 * @return int|float|null
	 */
	public function get_remaining_stock_for_product( \WC_Product $product ) {
		if ( is_null( $product->get_stock_quantity() ) ) {
			return null;
		}

		$reserve_stock  = new ReserveStock();
		$draft_order_id = $this->get_draft_order_id();
		$reserved_stock = $reserve_stock->get_reserved_stock( $product, $draft_order_id );

		return wc_stock_amount( $product->get_stock_quantity() - $reserved_stock );
	} // END get_remaining_stock_for_product()

	/**
	 * Gets the draft order ID from the current session, if any.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return int
	 */
	protected function get_draft_order_id() {
		$session = WC()->session;

		if ( $session && is_callable( array( $session, 'get' ) ) ) {
			return absint( $session->get( 'cocart_draft_order', 0 ) );
		}

		return 0;
	} // END get_draft_order_id()
} // END class
