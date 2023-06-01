<?php
/**
 * Abstract: CoCart\Abstracts\CoCart_Cart_REST_Controller
 *
 * @author  Sébastien Dumont
 * @package CoCart\Abstracts
 * @since   4.0.0 Introduced.
 */

use CoCart\Session\Handler;
use CoCart\RestApi\CartCache;
use \Automattic\WooCommerce\Checkout\Helpers\ReserveStock;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class extends `CoCart_REST_Controller` in order to access the cart instance and validate many cart actions.
 *
 * @extends CoCart_REST_Controller
 */
abstract class CoCart_Cart_REST_Controller extends CoCart_REST_Controller {

	/**
	 * Adds headers to a response object.
	 *
	 * @access protected
	 *
	 * @param \WP_REST_Response $response The response object.
	 *
	 * @return \WP_REST_Response
	 */
	protected function add_response_headers( \WP_REST_Response $response ) {
		$response = parent::add_response_headers( $response );

		if ( ! method_exists( WC()->session, 'get_cart_is_expiring' ) && ! method_exists( WC()->session, 'get_carts_expiration' ) ) {
			return $response;
		}

		$cart_expiring   = WC()->session->get_cart_is_expiring();
		$cart_expiration = WC()->session->get_carts_expiration();

		$response->header( 'CoCart-API-Cart-Expiring', $cart_expiring );
		$response->header( 'CoCart-API-Cart-Expiration', $cart_expiration );

		return $response;
	} // END add_response_headers()

	/**
	 * Ensures the cart totals are calculated before an API response is returned.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 */
	public function calculate_totals() {
		$this->get_cart_instance()->calculate_fees();
		$this->get_cart_instance()->calculate_shipping();
		$this->get_cart_instance()->calculate_totals();
	} // END calculate_totals()

	/**
	 * Cache cart item.
	 *
	 * @see CartCache::set_cached_item()
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $was_added_to_cart Cart item to cache.
	 */
	public function cache_cart_item( $was_added_to_cart ) {
		$item_key = $was_added_to_cart['key'];

		CartCache::set_cached_item( $item_key, $was_added_to_cart );
	} // END cache_cart_item()

	/**
	 * Cleans up the meta data for API.
	 *
	 * @access protected
	 *
	 * @since   3.1.0 Introduced
	 * @version 3.1.2
	 *
	 * @param object $method Method data.
	 * @param string $type   Meta data we are cleaning for.
	 *
	 * @return array Meta data.
	 */
	protected function clean_meta_data( $method, $type = 'shipping' ) {
		$meta_data = $method->get_meta_data();

		switch ( $type ) {
			case 'shipping':
				$meta_data['items'] = isset( $meta_data['Items'] ) ? html_entity_decode( wp_strip_all_tags( $meta_data['Items'] ) ) : '';
				unset( $meta_data['Items'] );

				break;
		}

		return $meta_data;
	} // END clean_meta_data()

	/**
	 * Convert queued error notices into an exception.
	 *
	 * For example, Payment methods may add error notices during validating fields to prevent checkout.
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
			return;
		}

		$error_notices = wc_get_notices( 'error' );

		// Prevent notices from being output later on.
		wc_clear_notices();

		foreach ( $error_notices as $error_notice ) {
			throw new CoCart_Data_Exception( $error_code, wp_strip_all_tags( $error_notice['notice'] ), 400 );
		}
	} // END convert_notices_to_exceptions()

	/**
	 * Gets the cart instance so we only call it once in the API.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return WC_Cart
	 */
	public function get_cart_instance() {
		$cart = WC()->cart;

		if ( ! $cart || ! $cart instanceof \WC_Cart ) {
			throw new CoCart_Data_Exception( 'cocart_cart_error', __( 'Unable to retrieve cart.', 'cart-rest-api-for-woocommerce' ), 404 );
		}

		return $cart;
	} // END get_cart_instance()

	/**
	 * Returns all cart items.
	 *
	 * @access public
	 *
	 * @param callable $callback Optional callback to apply to the array filter.
	 *
	 * @return array $items Returns all cart items.
	 */
	public function get_cart_items( $callback = null ) {
		return $callback ? array_filter( $this->get_cart_instance()->get_cart(), $callback ) : array_filter( $this->get_cart_instance()->get_cart() );
	} // END get_cart_items()

	/**
	 * Returns the cart key.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return string Cart key.
	 */
	public function get_cart_key( $request ) {
		try {
			if ( ! method_exists( WC()->session, 'get_cart_key' ) ) {
				return '';
			}

			return WC()->session->get_cart_key();
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_key()

	/**
	 * Get cart totals.
	 *
	 * Returns the cart subtotal, fees, discounted total, shipping total
	 * and total of the cart.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param WC_Cart         $cart    Cart class instance.
	 * @param array           $fields  An array of requested fields for the cart response to return.
	 *
	 * @return array Cart totals.
	 */
	public function get_cart_totals( $request = array(), $cart, $fields ) {
		$totals = array(
			'subtotal'       => $cart->get_subtotal(),
			'subtotal_tax'   => $cart->get_subtotal_tax(),
			'fee_total'      => $cart->get_fee_total(),
			'fee_tax'        => $cart->get_fee_tax(),
			'discount_total' => $cart->get_discount_total(),
			'discount_tax'   => $cart->get_discount_tax(),
			'shipping_total' => $cart->get_shipping_total(),
			'shipping_tax'   => $cart->get_shipping_tax(),
			'total'          => $cart->get_total( 'edit' ),
			'total_tax'      => $cart->get_total_tax(),
		);

		if ( ! in_array( 'fees', $fields ) ) {
			unset( $totals['fee_total'] );
			unset( $totals['fee_tax'] );
		}

		if ( ! in_array( 'shipping', $fields ) ) {
			unset( $totals['shipping_total'] );
			unset( $totals['shipping_tax'] );
		}

		/**
		 * Filters the cart totals.
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @param array           $totals  Cart totals.
		 * @param WP_REST_Request $request Full details about the request.
		 * @param WC_Cart         $cart    Cart class instance.
		 * @param array           $fields  An array of requested fields for the cart response to return.
		 */
		return apply_filters( 'cocart_cart_totals', $totals, $request, $cart, $fields );
	} // END get_cart_totals()

	/**
	 * Get cart fees.
	 *
	 * @access public
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Cart fees.
	 */
	public function get_fees( $cart ) {
		$cart_fees = $cart->get_fees();

		$fees = array();

		if ( ! empty( $cart_fees ) ) {
			foreach ( $cart_fees as $key => $fee ) {
				$fees[ $key ] = array(
					'name' => esc_html( $fee->name ),
					'fee'  => (float) cocart_prepare_money_response( $this->fee_html( $cart, $fee ) ),
				);
			}
		}

		return $fees;
	} // END get_fees()

	/**
	 * Returns an array of fields based on the configuration requested.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	public function get_fields_configuration( $request ) {
		$config = trim( $request['config']['fields'] );

		switch ( $config ) {
			case 'digital':
				$fields = array( 'currency', 'customer', 'items', 'coupons', 'needs_payment', 'taxes', 'totals', 'notices' );
				break;
			case 'digital_fees':
				$fields = array( 'currency', 'customer', 'items', 'coupons', 'needs_payment', 'fees', 'taxes', 'totals', 'notices' );
				break;
			case 'shipping':
				$fields = array( 'currency', 'customer', 'items', 'items_weight', 'coupons', 'needs_payment', 'needs_shipping', 'shipping', 'taxes', 'totals', 'notices' );
				break;
			case 'shipping_fees':
				$fields = array( 'currency', 'customer', 'items', 'items_weight', 'coupons', 'needs_payment', 'needs_shipping', 'shipping', 'fees', 'taxes', 'totals', 'notices' );
				break;
			case 'removed_items':
				$fields = array( 'currency', 'removed_items', 'notices' );
				break;
			case 'cross_sells':
				$fields = array( 'currency', 'cross_sells', 'notices' );
				break;
			default:
				$fields = $this->get_fields_for_response( $request );
				break;
		}

		return $fields;
	} // END get_fields_configuration()

	/**
	 * Returns the item thumbnail from the requested product in cart.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WC_Product $product      Product object.
	 * @param array      $cart_item    The item in the cart containing the default cart item data.
	 * @param string     $item_key     The item key generated based on the details of the item.
	 * @param boolean    $removed_item Determines if the item in the cart is removed.
	 *
	 * @return string
	 */
	public function get_item_thumbnail( $product, $cart_item, $item_key, $removed_item ) {
		$thumbnail_id = ! empty( $product->get_image_id() ) ? $product->get_image_id() : get_option( 'woocommerce_placeholder_image', 0 );

		/**
		 * Filter allows you to change the thumbnail ID of the item in cart.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param int     $thumbnail_id Thumbnail ID of the image.
		 * @param array   $cart_item    The item in the cart containing the default cart item data.
		 * @param string  $item_key     The item key generated based on the details of the item.
		 * @param boolean $removed_item Determines if the item in the cart is removed.
		 */
		$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $thumbnail_id, $cart_item, $item_key, $removed_item );

		$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail', $removed_item ) );

		$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';

		/**
		 * Filters the source of the product thumbnail of the item in cart.
		 *
		 * @since   2.1.0 Introduced.
		 * @version 3.0.0
		 *
		 * @param string $thumbnail_src URL of the product thumbnail.
		 */
		$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src, $cart_item, $item_key, $removed_item );

		// Return main featured image.
		return esc_url( $thumbnail_src );
	} // END get_item_thumbnail()

	/**
	 * Gets the quantity of a product across line items.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return int Quantity of the product.
	 */
	protected function get_product_quantity_in_cart( $product ) {
		$cart               = $this->get_cart_instance();
		$product_quantities = $cart->get_cart_item_quantities();
		$product_id         = $product->get_stock_managed_by_id();

		return isset( $product_quantities[ $product_id ] ) ? $product_quantities[ $product_id ] : 0;
	} // END get_product_quantity_in_cart()

	/**
	 * Gets remaining stock for a product.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return int Remaining stock.
	 */
	protected function get_remaining_stock_for_product( $product ) {
		$reserve_stock = new ReserveStock();
		$draft_order   = WC()->session->get( 'cocart_draft_order', 0 );
		$qty_reserved  = $reserve_stock->get_reserved_stock( $product, $draft_order );

		return $product->get_stock_quantity() - $qty_reserved;
	} // END get_remaining_stock_for_product()

	/**
	 * Get number of removed items in the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return int Number of removed items in the cart.
	 */
	public function get_removed_cart_contents_count() {
		return array_sum( wp_list_pluck( $this->get_cart_instance()->get_removed_cart_contents(), 'quantity' ) );
	} // END get_removed_cart_contents_count()

	/**
	 * Get product attributes from the variable product (which may be the parent if the product object is a variation).
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   2.1.2 Introduced.
	 * @version 3.0.0
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return array $attributes Product attributes.
	 */
	protected function get_variable_product_attributes( $product ) {
		try {
			if ( $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( ! $product || 'trash' === $product->get_status() ) {
				$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_cart_invalid_parent_product', $message, 404 );
			}

			return $product->get_attributes();
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_variable_product_attributes()

	/**
	 * Tries to match variation attributes passed to a variation ID and return the ID.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   2.1.2 Introduced.
	 * @version 3.0.0
	 *
	 * @param array      $variation Submitted attributes.
	 * @param WC_Product $product   Product object.
	 *
	 * @return int $variation_id Matching variation ID.
	 */
	protected function get_variation_id_from_variation_data( $variation, $product ) {
		try {
			$data_store   = \WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $variation );

			if ( empty( $variation_id ) ) {
				$message = __( 'No matching variation found.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_no_variation_found', $message, 404 );
			}

			return $variation_id;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_variation_id_from_variation_data()

	/**
	 * Checks if the product in the cart has enough stock
	 * before updating the quantity.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   1.0.6 Introduced.
	 * @version 3.0.0
	 *
	 * @param array     $current_data Cart item details.
	 * @param int|float $quantity     The quantity to check stock.
	 *
	 * @return bool
	 */
	protected function has_enough_stock( $current_data = array(), $quantity = 1 ) {
		try {
			$product_id      = ! isset( $current_data['product_id'] ) ? 0 : absint( $current_data['product_id'] );
			$variation_id    = ! isset( $current_data['variation_id'] ) ? 0 : absint( $current_data['variation_id'] );
			$current_product = wc_get_product( $variation_id ? $variation_id : $product_id );

			if ( ! $current_product->has_enough_stock( $quantity ) ) {
				/* translators: 1: Quantity Requested, 2: Product Name 3: Quantity in Stock */
				$message = sprintf( __( 'You cannot add a quantity of (%1$s) for "%2$s" to the cart because there is not enough stock. - only (%3$s remaining)!', 'cart-rest-api-for-woocommerce' ), $quantity, $current_product->get_name(), wc_format_stock_quantity_for_display( $current_product->get_stock_quantity(), $current_product ) );

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 404 );
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END has_enough_stock()

	/**
	 * Returns true if the cart is completely empty.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return bool True if the cart is completely empty.
	 */
	public function is_completely_empty() {
		if ( $this->get_cart_instance()->get_cart_contents_count() <= 0 && $this->get_removed_cart_contents_count() <= 0 ) {
			return true;
		}

		return false;
	} // END is_completely_empty()

	/**
	 * Return notices in cart if any.
	 *
	 * @access protected
	 *
	 * @return array $notices.
	 */
	protected function maybe_return_notices() {
		$notice_count = 0;
		$all_notices  = WC()->session->get( 'wc_notices', array() );

		foreach ( $all_notices as $notices ) {
			$notice_count += count( $notices );
		}

		$notices = $notice_count > 0 ? $this->print_notices() : array();

		return $notices;
	} // END maybe_return_notices()

	/**
	 * Removes all internal elements of an item that is not needed.
	 *
	 * @access protected
	 *
	 * @param array $cart_item Before cart item data is modified.
	 *
	 * @return array $cart_item Modified cart item data returned.
	 */
	protected function prepare_item( $cart_item ) {
		unset( $cart_item['key'] );
		unset( $cart_item['product_id'] );
		unset( $cart_item['variation_id'] );
		unset( $cart_item['variation'] );
		unset( $cart_item['quantity'] );
		unset( $cart_item['data'] );
		unset( $cart_item['data_hash'] );
		unset( $cart_item['line_tax_data'] );
		unset( $cart_item['line_subtotal'] );
		unset( $cart_item['line_subtotal_tax'] );
		unset( $cart_item['line_total'] );
		unset( $cart_item['line_tax'] );

		return $cart_item;
	} // END prepare_item()

	/**
	 * Returns messages and errors which are stored in the session, then clears them.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function print_notices() {
		$all_notices  = WC()->session->get( 'wc_notices', array() );
		$notice_types = apply_filters( 'cocart_notice_types', array( 'error', 'success', 'notice', 'info' ) );
		$notices      = array();

		foreach ( $notice_types as $notice_type ) {
			if ( wc_notice_count( $notice_type ) > 0 ) {
				foreach ( $all_notices[ $notice_type ] as $key => $notice ) {
					$notices[ $notice_type ][ $key ] = wc_kses_notice( $notice['notice'] );
				}
			}
		}

		wc_clear_notices();

		return $notices;
	} // END print_notices()

	/**
	 * Validates item and check for errors before added to cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WC_Product $product  Product object.
	 * @param int|float  $quantity Quantity of product to validate availability.
	 */
	public function validate_add_to_cart( $product, $quantity ) {
		try {
			// Product is purchasable check.
			if ( ! $product->is_purchasable() ) {
				$this->throw_product_not_purchasable( $product );
			}

			// Stock check - only check if we're managing stock and backorders are not allowed.
			if ( ! $product->is_in_stock() ) {
				/* translators: %s: Product name */
				$message = sprintf( __( 'You cannot add "%s" to the cart because the product is out of stock.', 'cart-rest-api-for-woocommerce' ), $product->get_name() );

				/**
				 * Filters message about product is out of stock.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product Product object.
				 */
				$message = apply_filters( 'cocart_product_is_out_of_stock_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_out_of_stock', $message, 404 );
			}

			if ( ! $product->has_enough_stock( $quantity ) ) {
				$stock_quantity = wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product );

				if ( $stock_quantity > 0 ) {
					/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
					$message = sprintf( __( 'You cannot add the amount of %1$s for "%2$s" to the cart because there is not enough stock, only (%3$s) remaining.', 'cart-rest-api-for-woocommerce' ), $quantity, $product->get_name(), $stock_quantity );
				} else {
					/* translators: 1: Product Name */
					$message = sprintf( __( 'You cannot add %1$s to the cart as it is no longer in stock.', 'cart-rest-api-for-woocommerce' ), $product->get_name() );
				}

				/**
				 * Filters message about product not having enough stock.
				 *
				 * @since 3.1.0 Introduced.
				 *
				 * @param string     $message        Message.
				 * @param WC_Product $product        Product object.
				 * @param int        $stock_quantity Quantity remaining.
				 */
				$message = apply_filters( 'cocart_product_not_enough_stock_message', $message, $product, $stock_quantity );

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 404 );
			}

			// Stock check - this time accounting for whats already in-cart and look up what's reserved.
			if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
				$qty_remaining = $this->get_remaining_stock_for_product( $product );
				$qty_in_cart   = $this->get_product_quantity_in_cart( $product );

				if ( $qty_remaining < $qty_in_cart + $quantity ) {
					$message = sprintf(
						/* translators: 1: product name, 2: Quantity in Stock, 3: Quantity in Cart */
						__( 'You cannot add that amount of "%1$s" to the cart &mdash; we have (%2$s) in stock remaining. You already have (%3$s) in your cart.', 'cart-rest-api-for-woocommerce' ),
						$product->get_name(),
						wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ),
						wc_format_stock_quantity_for_display( $qty_in_cart, $product )
					);

					throw new CoCart_Data_Exception( 'cocart_not_enough_stock_remaining', $message, 404 );
				}
			}
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_add_to_cart()

	/**
	 * Validates item quantity and checks if sold individually.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WC_Product $product      Product object.
	 * @param int|float  $quantity     The quantity to validate.
	 * @param int        $product_id   The product ID.
	 * @param int        $variation_id The variation ID.
	 * @param array      $item_data    The cart item data.
	 * @param string     $cart_id      Generated ID based on item in cart.
	 * @param string     $item_key     The item key of the cart item.
	 *
	 * @return float $quantity The quantity returned.
	 */
	public function validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $cart_id, $item_key ) {
		try {
			// Force quantity to 1 if sold individually and check for existing item in cart.
			if ( $product->is_sold_individually() ) {
				/**
				 * Quantity for sold individual products can be filtered.
				 *
				 * @since 2.0.13 Introduced.
				 */
				$quantity = apply_filters( 'cocart_add_to_cart_sold_individually_quantity', 1 );

				$cart_contents = $this->get_cart();

				$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $item_key && $cart_contents[ $item_key ]['quantity'] > 0, $product_id, $variation_id, $item_data, $cart_id );

				if ( $found_in_cart ) {
					/* translators: %s: Product Name */
					$message = sprintf( __( "You cannot add another '%s' to your cart.", 'cart-rest-api-for-woocommerce' ), $product->get_name() );

					/**
					 * Filters message about product not being allowed to add another.
					 *
					 * @since 3.0.0 Introduced.
					 *
					 * @param string     $message Message.
					 * @param WC_Product $product Product object.
					 */
					$message = apply_filters( 'cocart_product_can_not_add_another_message', $message, $product );

					throw new CoCart_Data_Exception( 'cocart_product_sold_individually', $message, 403 );
				}
			}

			return $quantity;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_item_quantity()

	/**
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @since   3.0.0 Deprecated $variation_id parameter is no longer used.
	 * @version 3.1.0
	 *
	 * @param int             $product_id   Contains the ID of the product.
	 * @param int|float       $quantity     Contains the quantity of the item.
	 * @param null            $variation_id Used to pass the variation id of the product to add to the cart.
	 * @param array           $variation    Contains the selected attributes.
	 * @param array           $item_data    Extra cart item data we want to pass into the item.
	 * @param string          $product_type The product type.
	 * @param WP_REST_Request $request      Full details about the request.
	 *
	 * @return array Item data.
	 */
	protected function validate_product( $product_id = null, $quantity = 1, $variation_id = null, $variation = array(), $item_data = array(), $product_type = '', $request = array() ) {
		try {
			// Get product and validate product for the cart.
			$product = wc_get_product( $product_id );
			$product = $this->validate_product_for_cart( $product );

			// Look up the product type if not passed.
			if ( empty( $product_type ) ) {
				$product_type = $product->get_type();
			}

			$variation_id = 0;

			// Set correct product ID's if product type is a variation.
			if ( $product->is_type( 'variation' ) ) {
				$product_id   = $product->get_parent_id();
				$variation_id = $product->get_id();
			}

			// If we have a parent product and no variation ID, find the variation ID.
			if ( $product->is_type( 'variable' ) && 0 === $variation_id ) {
				$variation_id = $this->get_variation_id_from_variation_data( $variation, $product );
			}

			// Throw exception if no variation is found.
			if ( is_wp_error( $variation_id ) ) {
				return $variation_id;
			}

			// Validate variable/variation product.
			if ( 'variable' === $product_type || 'variation' === $product_type ) {
				$variation = $this->validate_variable_product( $variation_id, $variation, $product );
			}

			/**
			 * If variables are not valid then return error response.
			 *
			 * @param $variation
			 */
			if ( is_wp_error( $variation ) ) {
				return $variation;
			}

			/**
			 * Filters add to cart validation.
			 *
			 * @since 1.0.0 Introduced.
			 *
			 * @param bool   true          Default is true to allow the product to pass validation.
			 * @param int    $product_id   Contains the ID of the product.
			 * @param int    $quantity     Contains the quantity of the item.
			 * @param int    $variation_id Used to pass the variation id of the product to add to the cart.
			 * @param array  $variation    Contains the selected attributes.
			 * @param object $item_data    Extra cart item data we want to pass into the item.
			 * @param string $product_type The product type.
			 */
			$passed_validation = apply_filters( 'cocart_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation, $item_data, $product_type, $request );

			/**
			 * If validation returned an error return error response.
			 *
			 * @param $passed_validation
			 */
			if ( is_wp_error( $passed_validation ) ) {
				return $passed_validation;
			}

			// If validation returned false.
			if ( ! $passed_validation ) {
				$message = __( 'Product did not pass validation!', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about product failing validation.
				 *
				 * @since 1.0.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product Product object.
				 */
				$message = apply_filters( 'cocart_product_failed_validation_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_failed_validation', $message, 400 );
			}

			/**
			 * Filter allows other plugins to add their own cart item data.
			 *
			 * @since 1.0.0 Introduced.
			 *
			 * @param array           $item_data    Extra cart item data we want to pass into the item.
			 * @param int             $product_id   Contains the ID of the product.
			 * @param null            $variation_id Used to pass the variation id of the product to add to the cart.
			 * @param int|float       $quantity     Contains the quantity of the item.
			 * @param string          $product_type The product type.
			 * @param WP_REST_Request $request      Full details about the request.
			 */
			$item_data = (array) apply_filters( 'cocart_add_cart_item_data', $item_data, $product_id, $variation_id, $quantity, $product_type, $request );

			// Generate an ID based on product ID, variation ID, variation data, and other cart item data.
			$cart_id = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $item_data );

			// Find the cart item key in the existing cart.
			$item_key = $this->find_product_in_cart( $cart_id );

			/**
			 * Filters the quantity for specified products.
			 *
			 * @since 1.0.0 Introduced.
			 *
			 * @param int|float $quantity     The original quantity of the item.
			 * @param int       $product_id   The product ID.
			 * @param int       $variation_id The variation ID.
			 * @param array     $variation    The variation data.
			 * @param array     $item_data    The cart item data.
			 */
			$quantity = apply_filters( 'cocart_add_to_cart_quantity', $quantity, $product_id, $variation_id, $variation, $item_data );

			// Validates the item quantity.
			$quantity = $this->validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $cart_id, $item_key );

			/**
			 * If product validation returned an error return error response.
			 *
			 * @param $quantity
			 */
			if ( is_wp_error( $quantity ) ) {
				return $quantity;
			}

			// Validates the item before adding to cart.
			$is_valid = $this->validate_add_to_cart( $product, $quantity );

			/**
			 * If product validation returned an error return error response.
			 *
			 * @param $is_valid
			 */
			if ( is_wp_error( $is_valid ) ) {
				return $is_valid;
			}

			// Returns all valid data.
			return array(
				'product_id'   => $product_id,
				'quantity'     => $quantity,
				'variation_id' => $variation_id,
				'variation'    => $variation,
				'item_data'    => $item_data,
				'item_key'     => $item_key,
				'product_data' => $product,
				'request'      => $request,
			);
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product()

	/**
	 * Validates a product object for the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return WC_Product $product Returns a product object if purchasable.
	 */
	public function validate_product_for_cart( $product ) {
		try {
			// Check if the product exists before continuing.
			if ( ! $product || 'trash' === $product->get_status() ) {
				$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about product that cannot be added to cart.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product Product object.
				 */
				$message = apply_filters( 'cocart_product_cannot_be_added_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_invalid_product', $message, 400 );
			}

			return $product;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product_for_cart()

	/**
	 * Validate the product ID or SKU ID.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @version 3.0.11
	 *
	 * @param int|string $product_id The product ID to validate.
	 *
	 * @return int $product_id The validated product ID.
	 */
	protected function validate_product_id( $product_id ) {
		try {
			// If the product ID was used by a SKU ID, then look up the product ID and return it.
			if ( ! is_numeric( $product_id ) ) {
				$product_id_by_sku = wc_get_product_id_by_sku( $product_id );

				if ( ! empty( $product_id_by_sku ) && $product_id_by_sku > 0 ) {
					$product_id = $product_id_by_sku;
				} else {
					$message = __( 'Product does not exist! Check that you have submitted a product ID or SKU ID correctly for a product that exists.', 'cart-rest-api-for-woocommerce' );

					throw new CoCart_Data_Exception( 'cocart_unknown_product_id', $message, 404 );
				}
			}

			if ( empty( $product_id ) ) {
				$message = __( 'Product ID number is required!', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_product_id_required', $message, 404 );
			}

			if ( ! is_numeric( $product_id ) ) {
				$message = __( 'Product ID must be numeric!', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_product_id_not_numeric', $message, 405 );
			}

			// Force product ID to be integer.
			$product_id = (int) $product_id;

			return $product_id;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product_id()

	/**
	 * Validate the product quantity.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since 1.0.0 Introduced.
	 * @since 3.1.0 Added product object as parameter and validation for maximum quantity allowed to add to cart.
	 *
	 * @param int|float  $quantity The quantity to validate.
	 * @param WC_Product $product  Product object.
	 */
	protected function validate_quantity( $quantity, WC_Product $product = null ) {
		try {
			if ( ! is_numeric( $quantity ) ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_not_numeric', __( 'Quantity must be integer or a float value!', 'cart-rest-api-for-woocommerce' ), 405 );
			}

			/**
			 * Filter allows control over the minimum quantity a customer must add to purchase said item.
			 *
			 * @since 3.0.17 Introduced.
			 * @since 3.1.0 Added product object as parameter.
			 *
			 * @param int|float  Minimum quantity to validate with.
			 * @param WC_Product Product object.
			 */
			$minimum_quantity = apply_filters( 'cocart_quantity_minimum_requirement', $product->get_min_purchase_quantity(), $product );

			if ( 0 === $quantity || $quantity < $minimum_quantity ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_invalid_amount', sprintf(
					/* translators: %s: Minimum quantity. */
					__( 'Quantity must be a minimum of %s.', 'cart-rest-api-for-woocommerce' ),
					$minimum_quantity
				), 405 );
			}

			$maximum_quantity = ( ( $product->get_max_purchase_quantity() < 0 ) ) ? '' : $product->get_max_purchase_quantity(); // We replace -1 with a blank if stock management is not used.

			/**
			 * Filter allows control over the maximum quantity a customer
			 * is able to add said item to the cart.
			 *
			 * @since 3.1.0 Introduced.
			 *
			 * @param int|float  Maximum quantity to validate with.
			 * @param WC_Product Product object.
			 */
			$maximum_quantity = apply_filters( 'cocart_quantity_maximum_allowed', $maximum_quantity, $product );

			if ( ! empty( $maximum_quantity ) && $quantity > $maximum_quantity ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_invalid_amount', sprintf(
					/* translators: %s: Maximum quantity. */
					__( 'Quantity must be %s or lower.', 'cart-rest-api-for-woocommerce' ),
					$maximum_quantity
				), 405 );
			}

			return wc_stock_amount( $quantity );
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_quantity()

	/**
	 * Validate variable product.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.6
	 *
	 * @param int        $variation_id ID of the variation.
	 * @param array      $variation    Attribute values.
	 * @param WC_Product $product      Product object.
	 *
	 * @return array $variation_id ID of the variation and attribute values.
	 */
	protected function validate_variable_product( int $variation_id, array $variation, WC_Product $product ) {
		try {
			// Flatten data and format posted values.
			$variable_product_attributes = $this->get_variable_product_attributes( $product );

			// Now we have a variation ID, get the valid set of attributes for this variation. They will have an attribute_ prefix since they are from meta.
			$expected_attributes = wc_get_product_variation_attributes( $variation_id );
			$missing_attributes  = array();

			foreach ( $variable_product_attributes as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				$prefixed_attribute_name = 'attribute_' . sanitize_title( $attribute['name'] );
				$expected_value          = isset( $expected_attributes[ $prefixed_attribute_name ] ) ? $expected_attributes[ $prefixed_attribute_name ] : '';
				$attribute_label         = wc_attribute_label( $attribute['name'] );

				if ( isset( $variation[ wc_variation_attribute_name( $attribute['name'] ) ] ) ) {
					$given_value = $variation[ wc_variation_attribute_name( $attribute['name'] ) ];

					if ( $expected_value === $given_value ) {
						continue;
					}

					// If valid values are empty, this is an 'any' variation so get all possible values.
					if ( '' === $expected_value && in_array( $given_value, $attribute->get_slugs(), true ) ) {
						continue;
					}

					/* translators: %1$s: Attribute name, %2$s: Allowed values. */
					$message = sprintf( __( 'Invalid value posted for %1$s. Allowed values: %2$s', 'cart-rest-api-for-woocommerce' ), $attribute_label, implode( ', ', $attribute->get_slugs() ) );

					/**
					 * Filters message about invalid variation data.
					 *
					 * @param string $message         Message.
					 * @param string $attribute_label Attribute Label.
					 * @param array  $attribute       Allowed values.
					 */
					$message = apply_filters( 'cocart_invalid_variation_data_message', $message, $attribute_label, $attribute->get_slugs() );

					throw new CoCart_Data_Exception( 'cocart_invalid_variation_data', $message, 400 );
				}

				// If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
				if ( '' === $expected_value ) {
					$missing_attributes[] = $attribute_label;
				}
			}

			if ( ! empty( $missing_attributes ) ) {
				/* translators: %s: Attribute name. */
				$message = __( 'Missing variation data for variable product.', 'cart-rest-api-for-woocommerce' ) . ' ' . sprintf( _n( '%s is a required field.', '%s are required fields.', count( $missing_attributes ), 'cart-rest-api-for-woocommerce' ), wc_format_list_of_items( $missing_attributes ) );

				/**
				 * Filters message about missing variation data.
				 *
				 * @param string $message            Message.
				 * @param string $missing_attributes Number of missing attributes.
				 * @param array  $missing_attributes List of missing attributes.
				 */
				$message = apply_filters( 'cocart_missing_variation_data_message', $message, count( $missing_attributes ), wc_format_list_of_items( $missing_attributes ) );

				throw new CoCart_Data_Exception( 'cocart_missing_variation_data', $message, 400 );
			}

			return $variation;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_variable_product()

	/**
	 * Throws exception if the item key is not provided when either removing, updating or restoring the item.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access protected
	 *
	 * @since 3.0.17 Introduced.
	 *
	 * @param string $item_key Item key of the item in the cart.
	 * @param string $status   Status of which we are checking the item key.
	 *
	 * @return string $item_key Item key of the item in the cart.
	 */
	protected function throw_missing_item_key( $item_key, $status ) {
		$item_key = (string) $item_key; // Make sure the item key is a string value.

		if ( '0' === $item_key ) {
			$message = __( 'Missing cart item key is required!', 'cart-rest-api-for-woocommerce' );

			/**
			 * Filters message about cart item key required.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_cart_item_key_required_message', $message, $status );

			throw new CoCart_Data_Exception( 'cocart_cart_item_key_required', $message, 404 );
		}

		return $item_key;
	} // END throw_missing_item_key()

	/**
	 * Throws exception when an item cannot be added to the cart.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access protected
	 *
	 * @since 3.0.4 Introduced.
	 *
	 * @param WC_Product $product Product object.
	 */
	protected function throw_product_not_purchasable( $product ) {
		$message = sprintf(
			/* translators: %s: product name */
			__( "'%s' is not available for purchase.", 'cart-rest-api-for-woocommerce' ),
			$product->get_name()
		);

		/**
		 * Filters message about product unable to be purchased.
		 *
		 * @param string     $message Message.
		 * @param WC_Product $product Product object.
		 */
		$message = apply_filters( 'cocart_product_cannot_be_purchased_message', $message, $product );

		throw new CoCart_Data_Exception( 'cocart_cannot_be_purchased', $message, 400 );
	} // END throw_product_not_purchasable()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.0.0 Added `config` parameters.
	 *
	 * @return array $params The query params.
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description'       => __( 'Unique identifier for the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'config'   => array(
				'description' => __( 'Configure the cart response for each sub-parameter.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'required'    => false,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'fields' => array(
							'description'       => __( 'Specify the type of cart response before the data is fetched.', 'cart-rest-api-for-woocommerce' ),
							'type'              => 'string',
							'required'          => false,
							'enum'              => array(
								'digital',
								'digital_fees',
								'shipping',
								'shipping_fees',
								'removed_items',
								'cross_sells',
							),
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'prices' => array(
							'description'       => __( 'Return the price values in the format you prefer.', 'cart-rest-api-for-woocommerce' ),
							'type'              => 'string',
							'required'          => false,
							'enum'              => array( 'preformatted' ),
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				),
			),
			'fields'   => array(
				'description'       => __( 'Specify each parent field you want to request separated by (,) in the cart response before the data is fetched.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'thumb'    => array(
				'description' => __( 'True if you want to return the URL of the featured product image for each item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'default'     => true,
				'type'        => 'boolean',
				'required'    => false,
			),
			'default'  => array(
				'description' => __( 'Return the default cart data if set to true.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
				'required'    => false,
			),
		);

		/**
		 * Extend the query parameters for the cart.
		 *
		 * This filter allows you to extend the query parameters without removing any default parameters.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$params += apply_filters( 'cocart_cart_query_parameters', array() );

		return $params;
	} // END get_collection_params()

} // END class
