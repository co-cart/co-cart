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

	// ** Get Data Functions **//

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

			$results[ $key ] = method_exists( $customer, $field_name ) ? $customer->$field_name() : apply_filters( 'cocart_get_customer_' . $key, '', $customer );
		}

		return $results;
	} // END get_customer_fields()

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

	/**
	 * Get product attributes from the variable product (which may be the parent if the product object is a variation).
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.2 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array $attributes Product attributes.
	 */
	public static function get_variable_product_attributes( $product ) {
		try {
			if ( $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( ! $product || ! $product->exists() || 'trash' === $product->get_status() ) {
				$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_cart_invalid_parent_product', $message, 404 );
			}

			return $product->get_attributes();
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_variable_product_attributes()

	/**
	 * Returns shipping details.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.4.0 Added cart class instance and recurring cart as new parameters.
	 *
	 * @see cocart_format_money()
	 * @see CoCart_Utilities_Cart_Helpers::is_shipping_enabled()
	 *
	 * @param WC_Cart $cart           Cart class instance.
	 * @param bool    $recurring_cart True or false if cart is recurring.
	 *
	 * @return array Shipping details.
	 */
	public static function get_shipping_details( $cart, $recurring_cart = false ) {
		if ( ! self::is_shipping_enabled() ) {
			return array();
		}

		// See if we need to calculate anything.
		if ( ! $cart->needs_shipping() ) {
			return array();
		}

		// Get shipping rates and packages.
		$get_packages          = WC()->shipping->get_packages();
		$get_shipping_packages = $cart->get_shipping_packages();

		// Return early if invalid object supplied by the filter or no packages.
		if ( ! is_array( $get_packages ) || empty( $get_packages ) ) {
			return array();
		}

		$has_calculated_shipping = $cart->show_shipping();

		// Return nothing if the cart has no subscriptions that require shipping.
		if ( $recurring_cart ) {
			if ( ! $has_calculated_shipping && ! WC_Subscriptions_Cart::cart_contains_subscriptions_needing_shipping() ) {
				return array();
			}
		}

		$details = array(
			'total_packages'          => count( (array) $get_shipping_packages[0]['contents'] ),
			'show_package_details'    => $recurring_cart ? true : count( (array) $get_shipping_packages[0]['contents'] ) > 1,
			'has_calculated_shipping' => $has_calculated_shipping,
			'packages'                => array(),
		);

		$packages      = array();
		$package_key   = 1;
		$chosen_method = ''; // Leave blank until a method has been selected.

		foreach ( $get_packages as $package_id => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $package_id ] ) ? WC()->session->chosen_shipping_methods[ $package_id ] : '';
			$product_names = array();

			if ( count( (array) $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' x' . $values['quantity'];
				}

				/**
				 * Filter allows you to change the package details.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param array $product_names Product names.
				 * @param array $package       Package details.
				 */
				$product_names = apply_filters( 'cocart_shipping_package_details_array', $product_names, $package );
			}

			if ( 0 === $package_id ) {
				$package_key = 'default'; // Identifies the default package.
			}

			// Check that there are rates available for the package.
			if ( count( (array) $package['rates'] ) > 0 ) {
				$shipping_name = ( ( $package_id + 1 ) > 1 ) ? sprintf(
					/* translators: %d: shipping package ID */
					_x( 'Shipping #%d', 'shipping packages', 'cart-rest-api-for-woocommerce' ),
					( $package_id + 1 )
				) : _x( 'Shipping', 'shipping packages', 'cart-rest-api-for-woocommerce' );

				$packages[ $package_key ] = array(
					/**
					 * Filters the package name for the shipping method.
					 *
					 * @since 3.0.0 Introduced.
					 * @since 4.4.0 Added cart class instance as new parameter.
					 *
					 * @param string  $shipping_name Package name.
					 * @param int     $package_id    Package ID.
					 * @param array   $package       Package contents.
					 * @param WC_Cart $cart          Cart class instance.
					 */
					'package_name'          => apply_filters( 'cocart_shipping_package_name', $shipping_name, $package_id, $package, $cart ),
					'rates'                 => array(),
					'package_details'       => implode( ', ', $product_names ),
					'index'                 => $package_id, // Shipping package ID.
					'chosen_method'         => $chosen_method,
					'formatted_destination' => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				);

				$rates = array();

				// Return each rate.
				foreach ( $package['rates'] as $key => $method ) {
					$meta_data = self::clean_meta_data( $method, 'shipping' );

					$rates[ $key ] = array(
						'key'           => $key,
						'method_id'     => $method->get_method_id(),
						'instance_id'   => $method->instance_id,
						'label'         => $method->get_label(),
						'cost'          => cocart_format_money( $method->cost ),
						'html'          => html_entity_decode( wp_strip_all_tags( wc_cart_totals_shipping_method_label( $method ) ) ),
						'taxes'         => '',
						'chosen_method' => ( $chosen_method === $key ),
						'meta_data'     => $meta_data,
					);

					foreach ( $method->taxes as $shipping_cost => $tax_cost ) {
						$rates[ $key ]['taxes'] = cocart_format_money( $tax_cost );
					}
				}

				$packages[ $package_key ]['rates'] = $rates;
			}

			++$package_key; // Update package key for next inline if any.
		}

		/**
		 * Filter allows you to alter the shipping packages returned.
		 *
		 * @since 4.1.0 Introduced.
		 * @since 4.4.0 Added $recurring_cart as parameter.
		 *
		 * @param array   $packages       Available shipping packages.
		 * @param array   $chosen_method  Chosen shipping method.
		 * @param WC_Cart $cart           Cart class instance.
		 * @param bool    $recurring_cart True or false if cart is recurring.
		 */
		$packages = apply_filters( 'cocart_available_shipping_packages', $packages, $chosen_method, $cart, $recurring_cart );

		$details['packages'] = $has_calculated_shipping ? $packages : array();

		return $details;
	} // END get_shipping_details()

	/**
	 * Cleans up the meta data for API.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced
	 *
	 * @param object $method Method data.
	 * @param string $type   Meta data we are cleaning for.
	 *
	 * @return array Meta data.
	 */
	protected static function clean_meta_data( $method, $type = 'shipping' ) {
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
	 * Get cart fees.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @see cocart_format_money()
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Cart fees.
	 */
	public static function get_fees( $cart ) {
		$cart_fees = $cart->get_fees();

		$fees = array();

		if ( ! empty( $cart_fees ) ) {
			foreach ( $cart_fees as $key => $fee ) {
				$fees[ $key ] = array(
					'name' => esc_html( $fee->name ),
					'fee'  => cocart_format_money( self::fee_html( $cart, $fee ) ),
				);
			}
		}

		return $fees;
	} // END get_fees()

	/**
	 * Get the fee value.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WC_Cart $cart Cart class instance.
	 * @param object  $fee  Fee data.
	 *
	 * @return string Returns the fee value.
	 */
	public static function fee_html( $cart, $fee ) {
		$cart_totals_fee_html = $cart->display_prices_including_tax() ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );

		return apply_filters( 'cocart_cart_totals_fee_html', $cart_totals_fee_html, $fee );
	} // END fee_html()

	/**
	 * Get coupon in HTML.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.4.0 Added Cart class instance as the first parameter.
	 *
	 * @see cocart_format_money()
	 *
	 * @param WC_Cart          $cart      Cart class instance.
	 * @param string|WC_Coupon $coupon    Coupon data or code.
	 * @param boolean          $formatted Formats the saving amount.
	 *
	 * @return string Returns coupon amount.
	 */
	public static function coupon_html( $cart, $coupon, $formatted = true ) {
		if ( is_string( $coupon ) ) {
			$coupon = new \WC_Coupon( $coupon );
		}

		$amount = $cart->get_coupon_discount_amount( $coupon->get_code(), $cart->display_cart_ex_tax );

		if ( $formatted ) {
			$savings = html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
		} else {
			$savings = cocart_format_money( $amount );
		}

		$discount_amount_html = '-' . $savings;

		if ( $coupon->get_free_shipping() && empty( $amount ) ) {
			$discount_amount_html = __( 'Free shipping coupon', 'cart-rest-api-for-woocommerce' );
		}

		$discount_amount_html = apply_filters( 'cocart_coupon_discount_amount_html', $discount_amount_html, $coupon );

		return $discount_amount_html;
	} // END coupon_html()

	/**
	 * Get applied coupons to the cart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Applied coupons.
	 */
	public static function get_applied_coupons( $cart ) {
		// Returns each coupon applied and coupon total applied if store has coupons enabled.
		$coupons = self::are_coupons_enabled() ? $cart->get_applied_coupons() : array();

		$applied_coupons = array();

		if ( ! empty( $coupons ) ) {
			foreach ( $coupons as $code ) {
				$coupon = new \WC_Coupon( $code );

				$applied_coupons[] = array(
					'coupon'        => wc_format_coupon_code( wp_unslash( $code ) ),
					'label'         => esc_attr( wc_cart_totals_coupon_label( $code, false ) ),
					'discount_type' => $coupon->get_discount_type(),
					'saving'        => self::coupon_html( $cart, $code, false ),
					'saving_html'   => self::coupon_html( $cart, $code ),
				);
			}
		}

		return $applied_coupons;
	} // END get_applied_coupons()

	/**
	 * Get taxes from the cart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @see cocart_format_money()
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Returns taxes if any.
	 */
	public static function get_taxes( $cart ) {
		// Return calculated tax based on store settings and customer details.
		if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				$estimated_text = sprintf(
					/* translators: %s location. */
					' ' . esc_html__( '(estimated for %s)', 'cart-rest-api-for-woocommerce' ),
					WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ]
				);
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				return self::get_tax_lines( $cart );
			} else {
				return array(
					'label' => esc_html( WC()->countries->tax_or_vat() ) . $estimated_text,
					'total' => apply_filters( 'cocart_cart_totals_taxes_total', cocart_format_money( $cart->get_taxes_total() ) ),
				);
			}
		}

		return array();
	} // END get_taxes()

	/**
	 * Get tax lines from the cart and format to match schema.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @see cocart_format_money()
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Tax lines.
	 */
	public static function get_tax_lines( $cart ) {
		$cart_tax_totals = $cart->get_tax_totals();
		$tax_lines       = array();

		foreach ( $cart_tax_totals as $code => $tax ) {
			$tax_lines[ $code ] = array(
				'name'  => $tax->label,
				'price' => cocart_format_money( $tax->amount ),
			);
		}

		return $tax_lines;
	} // END get_tax_lines()

	/**
	 * Return notices in cart if any.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return array $notices.
	 */
	public static function maybe_return_notices() {
		$notice_count = 0;
		$all_notices  = WC()->session->get( 'wc_notices', array() );

		foreach ( $all_notices as $notices ) {
			$notice_count += count( $notices );
		}

		$notices = $notice_count > 0 ? self::print_notices( $all_notices ) : array();

		return $notices;
	} // END maybe_return_notices()

	/**
	 * Returns messages and errors which are stored in the session, then clears them.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @uses cocart_get_notice_types()
	 *
	 * @param array $all_notices Return notices already fetched.
	 *
	 * @return array
	 */
	public static function print_notices( $all_notices = array() ) {
		$all_notices  = empty( $all_notices ) ? WC()->session->get( 'wc_notices', array() ) : $all_notices;
		$notice_types = cocart_get_notice_types();
		$notices      = array();

		foreach ( $notice_types as $notice_type ) {
			if ( wc_notice_count( $notice_type ) > 0 ) {
				foreach ( $all_notices[ $notice_type ] as $key => $notice ) {
					$notices[ $notice_type ][ $key ] = html_entity_decode( wc_kses_notice( $notice['notice'] ) );
				}
			}
		}

		wc_clear_notices();

		return $notices;
	} // END print_notices()

	// ** Validation Functions **//

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
	 * Validates a product object for the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return WC_Product $product Returns a product object if purchasable.
	 */
	public static function validate_product_for_cart( $product ) {
		try {
			// Check if the product exists before continuing.
			if ( ! $product || ! $product->exists() || 'trash' === $product->get_status() ) {
				$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about product that cannot be added to cart.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_product_cannot_be_added_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_invalid_product', $message, 400 );
			}

			return $product;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product_for_cart()

	/**
	 * Validate the product ID or SKU ID.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
	 *
	 * @param int|string $product_id The product ID to validate.
	 *
	 * @return int $product_id The validated product ID.
	 */
	public static function validate_product_id( $product_id ) {
		try {
			// Need a product ID of some sort first!
			if ( empty( $product_id ) ) {
				$message = __( 'Product ID number is required!', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_product_id_required', $message, 404 );
			}

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

			// Product ID did not identify as numeric.
			if ( ! is_numeric( $product_id ) ) {
				$message = __( 'Product ID must be numeric!', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_product_id_not_numeric', $message, 405 );
			}

			// Force product ID to be integer.
			$product_id = (int) $product_id;

			return $product_id;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product_id()

	/**
	 * Validate the product quantity.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
	 * @since 3.1.0 Added product object as parameter and validation for maximum quantity allowed to add to cart.
	 *
	 * @param int|float  $quantity The quantity to validate.
	 * @param WC_Product $product  The product object.
	 *
	 * @return int|float|\WP_Error
	 */
	public static function validate_quantity( $quantity, WC_Product $product = null ) {
		try {
			if ( ! is_numeric( $quantity ) ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_not_numeric', __( 'Quantity must be integer or a float value!', 'cart-rest-api-for-woocommerce' ), 405 );
			}

			/**
			 * Filter allows control over the minimum quantity a customer must add to purchase said item.
			 *
			 * @since 3.0.17 Introduced.
			 * @since 3.1.0  Added product object as parameter.
			 *
			 * @param int|float  Minimum quantity to validate with.
			 * @param WC_Product The product object.
			 */
			$minimum_quantity = apply_filters( 'cocart_quantity_minimum_requirement', $product->get_min_purchase_quantity(), $product );

			if ( 0 === $quantity || $quantity < $minimum_quantity ) {
				throw new CoCart_Data_Exception(
					'cocart_quantity_invalid_amount',
					sprintf(
						/* translators: %s: Minimum quantity. */
						__( 'Quantity must be a minimum of %s.', 'cart-rest-api-for-woocommerce' ),
						$minimum_quantity
					),
					405
				);
			}

			$maximum_quantity = ( ( $product->get_max_purchase_quantity() < 0 ) ) ? '' : $product->get_max_purchase_quantity(); // We replace -1 with a blank if stock management is not used.
			/**
			 * Filter allows control over the maximum quantity a customer
			 * is able to add said item to the cart.
			 *
			 * @since 3.1.0 Introduced.
			 *
			 * @param int|float  Maximum quantity to validate with.
			 * @param WC_Product The product object.
			 */
			$maximum_quantity = apply_filters( 'cocart_quantity_maximum_allowed', $maximum_quantity, $product );

			if ( ! empty( $maximum_quantity ) && $quantity > $maximum_quantity ) {
				throw new CoCart_Data_Exception(
					'cocart_quantity_invalid_amount',
					sprintf(
						/* translators: %s: Maximum quantity. */
						__( 'Quantity must be %s or lower.', 'cart-rest-api-for-woocommerce' ),
						$maximum_quantity
					),
					405
				);
			}

			return wc_stock_amount( $quantity );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_quantity()

	/**
	 * Validate variable product.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.6
	 *
	 * @param int        $variation_id The variation ID.
	 * @param array      $variation    The variation attributes.
	 * @param WC_Product $product      The product object.
	 *
	 * @return array $variation_id ID of the variation and attribute values.
	 */
	public static function validate_variable_product( int $variation_id, array $variation, WC_Product $product ) {
		try {
			// Flatten data and format posted values.
			$variable_product_attributes = self::get_variable_product_attributes( $product );

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

					$message = sprintf(
						/* translators: %1$s: Attribute name, %2$s: Allowed values. */
						__( 'Invalid value posted for %1$s. Allowed values: %2$s', 'cart-rest-api-for-woocommerce' ),
						$attribute_label,
						implode( ', ', $attribute->get_slugs() )
					);

					/**
					 * Filters message about invalid variation data.
					 *
					 * @since 2.1.0 Introduced.
					 *
					 * @param string $message         Message.
					 * @param string $attribute_label Attribute Label.
					 * @param array  $attribute       Allowed values.
					 */
					$message = apply_filters( 'cocart_invalid_variation_data_message', $message, $attribute_label, $attribute->get_slugs() );

					throw new CoCart_Data_Exception( 'cocart_invalid_variation_data', $message, 400 );
				}

				// Fills variation array with unspecified attributes that have default values. This ensures the variation always has full data.
				if ( '' !== $expected_value && ! isset( $variation[ wc_variation_attribute_name( $attribute['name'] ) ] ) ) {
					$variation[ wc_variation_attribute_name( $attribute['name'] ) ] = $expected_value;
				}

				// If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
				if ( '' === $expected_value ) {
					$missing_attributes[] = $attribute_label;
				}
			}

			if ( ! empty( $missing_attributes ) ) {
				$message = __( 'Missing variation data for variable product.', 'cart-rest-api-for-woocommerce' ) . ' ' . sprintf(
					/* translators: %s: Attribute name. */
					_n( '%s is a required field.', '%s are required fields.', count( $missing_attributes ), 'cart-rest-api-for-woocommerce' ),
					wc_format_list_of_items( $missing_attributes )
				);

				/**
				 * Filters message about missing variation data.
				 *
				 * @since 2.1.0 Introduced.
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_variable_product()

	/**
	 * Checks if the product in the cart has enough stock
	 * before updating the quantity.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   1.0.6 Introduced.
	 * @version 3.0.0
	 *
	 * @param array     $current_data Cart item details.
	 * @param int|float $quantity     The quantity to check stock.
	 *
	 * @return bool
	 */
	public static function has_enough_stock( $current_data = array(), $quantity = 1 ) {
		try {
			$product_id      = ! isset( $current_data['product_id'] ) ? 0 : absint( $current_data['product_id'] );
			$variation_id    = ! isset( $current_data['variation_id'] ) ? 0 : absint( $current_data['variation_id'] );
			$current_product = wc_get_product( $variation_id ? $variation_id : $product_id );

			if ( ! $current_product->has_enough_stock( $quantity ) ) {
				$stock_quantity = $current_product->get_stock_quantity();

				$message = sprintf(
					/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
					__( 'You cannot add that amount of (%1$s) for "%2$s" to the cart because there is not enough stock, only (%3$s remaining).', 'cart-rest-api-for-woocommerce' ),
					$quantity,
					$product->get_name(),
					wc_format_stock_quantity_for_display( $stock_quantity, $product )
				);

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 404 );
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END has_enough_stock()

	// ** Convert Functions **//

	/**
	 * Removes all internal elements of an item that is not needed.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param array $cart_item Before cart item data is modified.
	 *
	 * @return array $cart_item Modified cart item data returned.
	 */
	public static function prepare_item( $cart_item ) {
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

	// ** Throw an Exception Functions **//

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
} // END class
