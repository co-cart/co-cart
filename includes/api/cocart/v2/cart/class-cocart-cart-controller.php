<?php
/**
 * CoCart REST API controller
 *
 * Handles requests to the /cart endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @version 3.6.2
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Checkout\Helpers\ReserveStock;

/**
 * CoCart REST API v2 - Cart controller class.
 *
 * @package CoCart REST API/API
 * @extends CoCart_API_Controller
 */
class CoCart_Cart_V2_Controller extends CoCart_API_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart';

	/**
	 * Register the routes for cart.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart - cocart/v2/cart (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_cart_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Gets the cart instance so we only call it once in the API.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @since  3.0.0
	 * @return WC_Cart
	 */
	public function get_cart_instance() {
		$cart = WC()->cart;

		if ( ! $cart || ! $cart instanceof \WC_Cart ) {
			throw new CoCart_Data_Exception( 'cocart_cart_error', __( 'Unable to retrieve cart.', 'cart-rest-api-for-woocommerce' ), 500 );
		}

		return $cart;
	} // END get_cart_instance()

	/**
	 * Return a cart item from the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   string $item_id   The item we are looking up in the cart.
	 * @param   string $condition Default is 'add', other conditions are: container, update, remove, restore.
	 * @return  array  $item      Returns details of the item in the cart if it exists.
	 */
	public function get_cart_item( $item_id, $condition = 'add' ) {
		$item = isset( $this->get_cart_instance()->cart_contents[ $item_id ] ) ? $this->get_cart_instance()->cart_contents[ $item_id ] : array();

		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // EMD get_cart_item()

	/**
	 * Returns all cart items.
	 *
	 * @access public
	 * @param  callable $callback Optional callback to apply to the array filter.
	 * @return array
	 */
	public function get_cart_items( $callback = null ) {
		return $callback ? array_filter( $this->get_cart_instance()->get_cart(), $callback ) : array_filter( $this->get_cart_instance()->get_cart() );
	} // END get_cart_items()

	/**
	 * Returns true if the cart is completely empty.
	 *
	 * @access public
	 * @since  3.1.0
	 * @return bool
	 */
	public function is_completely_empty() {
		if ( $this->get_cart_instance()->get_cart_contents_count() <= 0 && $this->get_removed_cart_contents_count() <= 0 ) {
			return true;
		}

		return false;
	} // END is_completely_empty()

	/**
	 * Get number of removed items in the cart.
	 *
	 * @access public
	 * @since  3.1.0
	 * @return int
	 */
	public function get_removed_cart_contents_count() {
		return array_sum( wp_list_pluck( $this->get_cart_instance()->get_removed_cart_contents(), 'quantity' ) );
	} // END get_removed_cart_contents_count()

	/**
	 * Get cart.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @param   WP_REST_Request $request       Full details about the request.
	 * @param   string          $cart_item_key Originally the cart item key.
	 * @return  WP_REST_Response
	 */
	public function get_cart( $request = array(), $cart_item_key = null ) {
		$show_raw      = ! empty( $request['raw'] ) ? $request['raw'] : false; // Internal parameter request.
		$cart_contents = ! $this->is_completely_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

		/**
		 * Runs before getting cart. Useful for add-ons or 3rd party plugins.
		 *
		 * @since 3.0.0
		 * @param array           $cart_contents Cart contents.
		 * @param WC_Cart         Cart object.
		 * @param WP_REST_Request $request       Full details about the request.
		 */
		$cart_contents = apply_filters( 'cocart_before_get_cart', $cart_contents, $this->get_cart_instance(), $request );

		// Return cart contents raw if requested.
		if ( $show_raw ) {
			return $cart_contents;
		}

		/**
		 * Deprecated action hook `cocart_get_cart`.
		 *
		 * @reason Better filtering for cart contents later on.
		 */
		cocart_deprecated_hook( 'cocart_get_cart', '3.0.0', null, null );

		// Ensures the cart totals are calculated before an API response is returned.
		$this->calculate_totals();

		$cart_contents = $this->return_cart_contents( $request, $cart_contents );

		return CoCart_Response::get_response( $cart_contents, $this->namespace, $this->rest_base );
	} // END get_cart()

	/**
	 * Return cart contents.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 3.1.0
	 * @param   WP_REST_Request $request       Full details about the request.
	 * @param   array           $cart_contents Cart content.
	 * @param   deprecated      $cart_item_key Originally the cart item key.
	 * @param   deprecated      $from_session  Identifies if the cart is called from a session.
	 * @return  array           $cart
	 */
	public function return_cart_contents( $request = array(), $cart_contents = array(), $cart_item_key = null, $from_session = false ) {
		/**
		 * Return the default cart data if set to true.
		 *
		 * @since 3.0.0
		 */
		if ( ! empty( $request['default'] ) && $request['default'] ) {
			return $cart_contents;
		}

		// Get cart template.
		$cart_template = $this->get_cart_template( $request );

		// If the cart is empty then return nothing.
		if ( empty( $cart_contents ) ) {
			/**
			 * Filter response for empty cart.
			 *
			 * @since   2.0.8
			 * @version 3.0.0
			 */
			cocart_deprecated_filter( 'cocart_return_empty_cart', array(), '3.0.0', 'cocart_empty_cart', __( 'But only if you are using API v2.', 'cart-rest-api-for-woocommerce' ) );

			return apply_filters( 'cocart_empty_cart', $cart_template );
		}

		// Other Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		// Defines an empty cart template.
		$cart = array();

		if ( array_key_exists( 'coupons', $cart_template ) ) {
			// Returns each coupon applied and coupon total applied if store has coupons enabled.
			$coupons = wc_coupons_enabled() ? $this->get_cart_instance()->get_applied_coupons() : array();

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					$cart['coupons'][] = array(
						'coupon'      => wc_format_coupon_code( wp_unslash( $coupon ) ),
						'label'       => esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ),
						'saving'      => $this->coupon_html( $coupon, false ),
						'saving_html' => $this->coupon_html( $coupon ),
					);
				}
			}
		}

		if ( array_key_exists( 'taxes', $cart_template ) ) {
			// Return calculated tax based on store settings and customer details.
			if ( wc_tax_enabled() && ! $this->get_cart_instance()->display_prices_including_tax() ) {
				$taxable_address = WC()->customer->get_taxable_address();
				$estimated_text  = '';

				if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
					/* translators: %s location. */
					$estimated_text = sprintf( ' ' . esc_html__( '(estimated for %s)', 'cart-rest-api-for-woocommerce' ), WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
				}

				if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
					$cart['taxes'] = $this->get_tax_lines( $this->get_cart_instance() );
				} else {
					$cart['taxes'] = array(
						'label' => esc_html( WC()->countries->tax_or_vat() ) . $estimated_text,
						'total' => apply_filters( 'cocart_cart_totals_taxes_total', cocart_prepare_money_response( $this->get_cart_instance()->get_taxes_total(), wc_get_price_decimals() ) ),
					);
				}
			}
		}

		// Returns items.
		if ( array_key_exists( 'items', $cart_template ) ) {
			$cart['items'] = $this->get_items( $cart_contents, $show_thumb );
		}

		// Parse cart data to template.
		$cart = wp_parse_args( $cart, $cart_template );

		return apply_filters( 'cocart_cart', $cart, $from_session );
	} // END return_cart_contents()

	/**
	 * Validate the product ID or SKU ID.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since   1.0.0
	 * @version 3.0.11
	 * @param   int|string $product_id The product ID to validate.
	 * @return  int $product_id
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

					throw new CoCart_Data_Exception( 'cocart_unknown_product_id', $message, 500 );
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product_id()

	/**
	 * Validate the product quantity.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since   1.0.0
	 * @since   3.1.0     Added product object as parameter and validation for maximum quantity allowed to add to cart.
	 * @version 3.1.0
	 * @param   int|float  $quantity The quantity to validate.
	 * @param   WC_Product $product Product object.
	 */
	protected function validate_quantity( $quantity, WC_Product $product = null ) {
		try {
			if ( ! is_numeric( $quantity ) ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_not_numeric', __( 'Quantity must be integer or a float value!', 'cart-rest-api-for-woocommerce' ), 405 );
			}

			/**
			 * This filter allows control over the minimum quantity a customer must add to purchase said item.
			 *
			 * @since 3.0.17
			 * @since 3.1.0      Added product object as parameter.
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

			/**
			 * This filter allows control over the maximum quantity a customer is able to add said item to the cart.
			 *
			 * @since 3.1.0
			 * @param int|float  Maximum quantity to validate with.
			 * @param WC_Product Product object.
			 */
			$maximum_quantity = ( ( $product->get_max_purchase_quantity() < 0 ) ) ? '' : $product->get_max_purchase_quantity(); // We replace -1 with a blank if stock management is not used.
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_quantity()

	/**
	 * Validate variable product.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since   2.1.0
	 * @version 3.0.6
	 * @param   int        $variation_id ID of the variation.
	 * @param   array      $variation    Attribute values.
	 * @param   WC_Product $product      Product data.
	 * @return  array
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_variable_product()

	/**
	 * Try to match variation data to a variation ID and return the ID.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since   2.1.2
	 * @version 3.0.0
	 * @param   array      $variation    Submitted attributes.
	 * @param   WC_Product $product      Product being added to the cart.
	 * @return  int        $variation_id Matching variation ID.
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_variation_id_from_variation_data()

	/**
	 * Get product attributes from the variable product (which may be the parent if the product object is a variation).
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since   2.1.2
	 * @version 3.0.0
	 * @param   WC_Product $product Product being added to the cart.
	 * @return  array
	 */
	protected function get_variable_product_attributes( $product ) {
		try {
			if ( $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( ! $product || 'trash' === $product->get_status() ) {
				$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_cart_invalid_parent_product', $message, 403 );
			}

			return $product->get_attributes();
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_variable_product_attributes()

	/**
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since      1.0.0           Introduced.
	 * @deprecated 3.0.0           $variation_id param is no longer used.
	 * @version    3.1.0
	 * @param      int             $product_id   Contains the ID of the product.
	 * @param      int|float       $quantity     Contains the quantity of the item.
	 * @param      null            $variation_id Used to pass the variation id of the product to add to the cart.
	 * @param      array           $variation    Contains the selected attributes.
	 * @param      array           $item_data    Extra cart item data we want to pass into the item.
	 * @param      string          $product_type The product type.
	 * @param      WP_REST_Request $request      Full details about the request.
	 * @return     array
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
				 * @param string     $message Message.
				 * @param WC_Product $product Product data.
				 */
				$message = apply_filters( 'cocart_product_failed_validation_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_failed_validation', $message, 500 );
			}

			// Add cart item data - may be added by other plugins.
			$item_data = (array) apply_filters( 'cocart_add_cart_item_data', $item_data, $product_id, $variation_id, $quantity, $product_type, $request );

			// Generate an ID based on product ID, variation ID, variation data, and other cart item data.
			$cart_id = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $item_data );

			// Find the cart item key in the existing cart.
			$item_key = $this->find_product_in_cart( $cart_id );

			/**
			 * Filters the quantity for specified products.
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_product()

	/**
	 * Checks if the product in the cart has enough stock
	 * before updating the quantity.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  protected
	 * @since   1.0.6
	 * @version 3.0.0
	 * @param   array     $current_data Cart item details.
	 * @param   int|float $quantity     The quantity to check stock.
	 * @return  bool
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END has_enough_stock()

	/**
	 * Prepares a list of store currency data to return in responses.
	 *
	 * @access     public
	 * @since      3.0.0
	 * @deprecated 3.1.0 Use cocart_get_store_currency()
	 * @see              cocart_get_store_currency()
	 *
	 * @return  array
	 */
	public function get_store_currency() {
		_deprecated_function( __FUNCTION__, '3.1', 'cocart_get_store_currency' );

		return cocart_get_store_currency();
	} // END get_store_currency()

	/**
	 * Returns the cart key.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return string
	 */
	public function get_cart_key( $request ) {
		if ( ! class_exists( 'CoCart_Session_Handler' ) || ! WC()->session instanceof CoCart_Session_Handler ) {
			return;
		}

		// Current user ID.
		$current_user_id = strval( get_current_user_id() );

		if ( $current_user_id > 0 ) {
			return $current_user_id;
		}

		// Customer ID used as the cart key by default.
		$cart_key = WC()->session->get_customer_id();

		// Get cart cookie... if any.
		$cookie = WC()->session->get_session_cookie();

		// Does a cookie exist?
		if ( $cookie ) {
			$cart_key = $cookie[0];
		}

		// Check if we requested to load a specific cart.
		$cart_key = ! empty( $request['cart_key'] ) ? $request['cart_key'] : $cart_key;

		return $cart_key;
	} // END get_cart_key()

	/**
	 * Get tax lines from the cart and format to match schema.
	 *
	 * @access protected
	 * @param  WC_Cart $cart Cart class instance.
	 * @return array
	 */
	protected function get_tax_lines( $cart ) {
		$cart_tax_totals = $cart->get_tax_totals();
		$tax_lines       = array();

		foreach ( $cart_tax_totals as $code => $tax ) {
			$tax_lines[ $code ] = array(
				'name'  => $tax->label,
				'price' => cocart_prepare_money_response( $tax->amount, wc_get_price_decimals() ),
			);
		}

		return $tax_lines;
	} // END get_tax_lines()

	/**
	 * Convert monetary values from WooCommerce to string based integers, using
	 * the smallest unit of a currency.
	 *
	 * @access     public
	 * @since      3.0.0
	 * @deprecated 3.1.0 Use cocart_prepare_money_response()
	 * @see              cocart_prepare_money_response()
	 *
	 * @param   string|float $amount        Monetary amount with decimals.
	 * @param   int          $decimals      Number of decimals the amount is formatted with.
	 * @param   int          $rounding_mode Defaults to the PHP_ROUND_HALF_UP constant.
	 * @return  string       The new amount.
	 */
	public function prepare_money_response( $amount, $decimals = 2, $rounding_mode = PHP_ROUND_HALF_UP ) {
		_deprecated_function( __FUNCTION__, '3.1', 'cocart_prepare_money_response' );

		return cocart_prepare_money_response( $amount, $decimals, $rounding_mode );
	} // END prepare_money_response()

	/**
	 * Format variation data, for example convert slugs such as attribute_pa_size to Size.
	 *
	 * @access protected
	 * @param  array      $variation_data Array of data from the cart.
	 * @param  WC_Product $product        Product data.
	 * @return array
	 */
	protected function format_variation_data( $variation_data, $product ) {
		$return = array();

		if ( empty( $variation_data ) ) {
			return $return;
		}

		foreach ( $variation_data as $key => $value ) {
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $key ) ) );

			if ( taxonomy_exists( $taxonomy ) ) {
				// If this is a term slug, get the term's nice name.
				$term = get_term_by( 'slug', $value, $taxonomy );
				if ( ! is_wp_error( $term ) && $term && $term->name ) {
					$value = $term->name;
				}
				$label = wc_attribute_label( $taxonomy );
			} else {
				// If this is a custom option slug, get the options name.
				$value = apply_filters( 'cocart_variation_option_name', $value, $product );
				$label = wc_attribute_label( str_replace( 'attribute_', '', $key ), $product );
			}

			$return[ $label ] = $value;
		}

		return $return;
	} // END format_variation_data()

	/**
	 * Get cart fees.
	 *
	 * @access public
	 * @param  WC_Cart $cart Cart class instance.
	 * @return array
	 */
	public function get_fees( $cart ) {
		$cart_fees = $cart->get_fees();

		$fees = array();

		if ( ! empty( $cart_fees ) ) {
			foreach ( $cart_fees as $key => $fee ) {
				$fees[ $key ] = array(
					'name' => esc_html( $fee->name ),
					'fee'  => cocart_prepare_money_response( $this->fee_html( $cart, $fee ), wc_get_price_decimals() ),
				);
			}
		}

		return $fees;
	} // END get_fees()

	/**
	 * Get coupon in HTML.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.0.4
	 * @param   string|WC_Coupon $coupon    Coupon data or code.
	 * @param   boolean          $formatted Formats the saving amount.
	 * @return  string                      The coupon in HTML.
	 */
	public function coupon_html( $coupon, $formatted = true ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}

		$amount = $this->get_cart_instance()->get_coupon_discount_amount( $coupon->get_code(), $this->get_cart_instance()->display_cart_ex_tax );

		if ( $formatted ) {
			$savings = html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
		} else {
			$savings = cocart_prepare_money_response( $amount, wc_get_price_decimals() );
		}

		$discount_amount_html = '-' . $savings;

		if ( $coupon->get_free_shipping() && empty( $amount ) ) {
			$discount_amount_html = __( 'Free shipping coupon', 'cart-rest-api-for-woocommerce' );
		}

		$discount_amount_html = apply_filters( 'cocart_coupon_discount_amount_html', $discount_amount_html, $coupon );

		return $discount_amount_html;
	} // END coupon_html()

	/**
	 * Get the fee value.
	 *
	 * @access public
	 * @param  object $cart Cart instance.
	 * @param  object $fee  Fee data.
	 * @return string       Returns the fee value.
	 */
	public function fee_html( $cart, $fee ) {
		$cart_totals_fee_html = $cart->display_prices_including_tax() ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );

		return apply_filters( 'cocart_cart_totals_fee_html', $cart_totals_fee_html, $fee );
	} // END fee_html()

	/**
	 * Validates a product object for the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WC_Product $product Passes the product object if valid.
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
				 * @param WC_Product $product Product data.
				 */
				$message = apply_filters( 'cocart_product_cannot_be_added_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_invalid_product', $message, 400 );
			}

			return $product;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_product_for_cart()

	/**
	 * Validates item quantity and checks if sold individually.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @param   WC_Product $product      Product object associated with the cart item.
	 * @param   int|float  $quantity     The quantity to validate.
	 * @param   int        $product_id   The product ID.
	 * @param   int        $variation_id The variation ID.
	 * @param   array      $item_data    The cart item data.
	 * @param   string     $cart_id      Generated ID based on item in cart.
	 * @param   string     $item_key     The item key of the cart item.
	 * @return  float      $quantity     The quantity returned.
	 */
	public function validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $cart_id, $item_key ) {
		try {
			// Force quantity to 1 if sold individually and check for existing item in cart.
			if ( $product->is_sold_individually() ) {
				/**
				 * Quantity for sold individual products can be filtered.
				 *
				 * @since 2.0.13
				 */
				$quantity = apply_filters( 'cocart_add_to_cart_sold_individually_quantity', 1 );

				$cart_contents = $this->get_cart();

				$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $item_key && $cart_contents[ $item_key ]['quantity'] > 0, $product_id, $variation_id, $item_data, $cart_id );

				if ( $found_in_cart ) {
					/* translators: %s: Product Name */
					$message = sprintf( __( 'You cannot add another "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product->get_name() );

					/**
					 * Filters message about product not being allowed to add another.
					 *
					 * @param string     $message Message.
					 * @param WC_Product $product Product data.
					 */
					$message = apply_filters( 'cocart_product_can_not_add_another_message', $message, $product );

					throw new CoCart_Data_Exception( 'cocart_product_sold_individually', $message, 403 );
				}
			}

			return $quantity;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_item_quantity()

	/**
	 * Validates item and check for errors before added to cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.1.0
	 * @param   WC_Product $product  Product object associated with the cart item.
	 * @param   int|float  $quantity Quantity of product to validate availability.
	 */
	public function validate_add_to_cart( $product, $quantity ) {
		try {
			// Product is purchasable check.
			if ( ! $product->is_purchasable() ) {
				$this->throw_product_not_purchasable( $product );
			}

			// Stock check - only check if we're managing stock and backorders are not allowed.
			if ( ! $product->is_in_stock() ) {
				/* translators: 1: Product name */
				$message = sprintf( __( 'You cannot add "%1$s" to the cart because the product is out of stock.', 'cart-rest-api-for-woocommerce' ), $product->get_name() );

				/**
				 * Filters message about product is out of stock.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product Product data.
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
				 * @since 3.1.0
				 * @param string     $message        Message.
				 * @param WC_Product $product        Product data.
				 * @param int        $stock_quantity Quantity remaining.
				 */
				$message = apply_filters( 'cocart_product_not_enough_stock_message', $message, $product, $stock_quantity );

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 403 );
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

					throw new CoCart_Data_Exception( 'cocart_not_enough_stock_remaining', $message, 403 );
				}
			}

			cocart_deprecated_hook( 'cocart_ok_to_add_response', '3.0.0', null, 'This filter is no longer used in the API.' );
			cocart_deprecated_hook( 'cocart_ok_to_add', '3.0.0', null, 'This filter is no longer used in the API.' );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_add_to_cart()

	/**
	 * Filters additional requested data.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return $request
	 */
	public function filter_request_data( $request ) {
		return apply_filters( 'cocart_filter_request_data', $request );
	} // END filter_request_data()

	/**
	 * Get the main product slug even if the product type is a variation.
	 *
	 * @access public
	 * @param  WC_Product $object The product object.
	 * @return string
	 */
	public function get_product_slug( $object ) {
		$product_type = $object->get_type();

		if ( 'variation' === $product_type ) {
			$product = wc_get_product( $object->get_parent_id() );

			$product_slug = $product->get_slug();
		} else {
			$product_slug = $object->get_slug();
		}

		return $product_slug;
	} // END get_product_slug()

	/**
	 * Get a single item from the cart and present the data required.
	 *
	 * @access  public
	 *
	 * @since   3.0.0
	 * @version 3.6.2
	 *
	 * @param   WC_Product $_product     The product data of the item in the cart.
	 * @param   array      $cart_item    The item in the cart containing the default cart item data.
	 * @param   string     $item_key     The item key generated based on the details of the item.
	 * @param   boolean    $show_thumb   Determines if requested to return the item featured thumbnail.
	 * @param   boolean    $removed_item Determines if the item in the cart is removed.
	 * @return  array      $item         Full details of the item in the cart and it's purchase limits.
	 */
	public function get_item( $_product, $cart_item = array(), $item_key = '', $show_thumb = true, $removed_item = false ) {
		$item = array(
			'item_key'       => $item_key,
			'id'             => $_product->get_id(),
			'name'           => apply_filters( 'cocart_cart_item_name', $_product->get_name(), $_product, $cart_item, $item_key ),
			'title'          => apply_filters( 'cocart_cart_item_title', $_product->get_title(), $_product, $cart_item, $item_key ),
			'price'          => apply_filters( 'cocart_cart_item_price', cocart_prepare_money_response( $this->get_cart_instance()->get_product_price( $_product ), wc_get_price_decimals() ), $cart_item, $item_key ),
			'quantity'       => array(
				'value'        => apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item ),
				'min_purchase' => $_product->get_min_purchase_quantity(),
				'max_purchase' => $_product->get_max_purchase_quantity(),
			),
			'totals'         => array(
				'subtotal'     => apply_filters( 'cocart_cart_item_subtotal', cocart_prepare_money_response( $cart_item['line_subtotal'], wc_get_price_decimals() ), $cart_item, $item_key ),
				'subtotal_tax' => apply_filters( 'cocart_cart_item_subtotal_tax', $cart_item['line_subtotal_tax'], $cart_item, $item_key ),
				'total'        => apply_filters( 'cocart_cart_item_total', $cart_item['line_total'], $cart_item, $item_key ),
				'tax'          => apply_filters( 'cocart_cart_item_tax', $cart_item['line_tax'], $cart_item, $item_key ),
			),
			'slug'           => $this->get_product_slug( $_product ),
			'meta'           => array(
				'product_type' => $_product->get_type(),
				'sku'          => $_product->get_sku(),
				'dimensions'   => array(),
				'weight'       => wc_get_weight( (float) $_product->get_weight() * (int) $cart_item['quantity'], get_option( 'woocommerce_weight_unit' ) ),
			),
			'backorders'     => '',
			'cart_item_data' => array(),
			'featured_image' => '',
		);

		// Item dimensions.
		$dimensions = $_product->get_dimensions( false );
		if ( ! empty( $dimensions ) ) {
			$item['meta']['dimensions'] = array(
				'length' => $dimensions['length'],
				'width'  => $dimensions['width'],
				'height' => $dimensions['height'],
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			);
		}

		// Variation data.
		if ( ! isset( $cart_item['variation'] ) ) {
			$cart_item['variation'] = array();
		}
		$item['meta']['variation'] = $this->format_variation_data( $cart_item['variation'], $_product );

		// Backorder notification.
		if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
			$item['backorders'] = wp_kses_post( apply_filters( 'cocart_cart_item_backorder_notification', esc_html__( 'Available on backorder', 'cart-rest-api-for-woocommerce' ), $_product->get_id() ) );
		}

		// Prepares the remaining cart item data.
		$cart_item = $this->prepare_item( $cart_item );

		// Collect all cart item data if any thing is left.
		if ( ! empty( $cart_item ) ) {
			$item['cart_item_data'] = apply_filters( 'cocart_cart_item_data', $cart_item, $item_key, $cart_item );
		}

		// If thumbnail is requested then add it to each item in cart.
		if ( $show_thumb ) {
			$thumbnail_id = ! empty( $_product->get_image_id() ) ? $_product->get_image_id() : get_option( 'woocommerce_placeholder_image', 0 );

			$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $thumbnail_id, $cart_item, $item_key, $removed_item );

			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail', $removed_item ) );

			$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';

			/**
			 * Filters the source of the product thumbnail.
			 *
			 * @since   2.1.0
			 * @version 3.0.0
			 * @param   string $thumbnail_src URL of the product thumbnail.
			 */
			$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src, $cart_item, $item_key, $removed_item );

			// Add main featured image.
			$item['featured_image'] = esc_url( $thumbnail_src );
		}

		return $item;
	} // END get_item()

	/**
	 * Gets the cart items.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.0.17
	 * @param   array   $cart_contents The cart contents passed.
	 * @param   boolean $show_thumb    Determines if requested to return the item featured thumbnail.
	 * @return  array   $items         Returns all items in the cart.
	 */
	public function get_items( $cart_contents = array(), $show_thumb = true ) {
		$items = array();

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data']          = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
				$items[ $item_key ]['data'] = $cart_item['data']; // Internal use only!
			}

			$_product = apply_filters( 'cocart_item_product', $cart_item['data'], $cart_item, $item_key );

			if ( ! $_product || ! $_product->exists() || 'trash' === $_product->get_status() ) {
				$this->get_cart_instance()->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.
				wc_add_notice( __( 'An item which is no longer available was removed from your cart.', 'cart-rest-api-for-woocommerce' ), 'error' );
			}

			// If product is no longer purchasable then don't return it and notify customer.
			if ( ! $_product->is_purchasable() ) {
				/* translators: %s: product name */
				$message = sprintf( __( '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.', 'cart-rest-api-for-woocommerce' ), $_product->get_name() );

				/**
				 * Filter message about item removed from the cart.
				 *
				 * @since 2.1.0
				 * @param string     $message  Message.
				 * @param WC_Product $_product Product data.
				 */
				$message = apply_filters( 'cocart_cart_item_removed_message', $message, $_product );

				$this->get_cart_instance()->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.

				wc_add_notice( $message, 'error' );
			} else {
				$items[ $item_key ] = $this->get_item( $_product, $cart_item, $item_key, $show_thumb );

				// This filter allows additional data to be returned for a specific item in cart.
				$items = apply_filters( 'cocart_cart_items', $items, $item_key, $cart_item, $_product );
			}
		}

		return $items;
	} // END get_items()

	/**
	 * Gets the cart removed items.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.7.8
	 * @param   array   $removed_items The removed cart contents passed.
	 * @param   boolean $show_thumb    Determines if requested to return the item featured thumbnail.
	 * @return  array   $items         Returns all removed items in the cart.
	 */
	public function get_removed_items( $removed_items = array(), $show_thumb = true ) {
		$items = array();

		foreach ( $removed_items as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
			}

			$_product = $cart_item['data'];

			// If the product no longer exists then remove it from the cart completely.
			if ( ! $_product || ! $_product->exists() || 'trash' === $_product->get_status() ) {
				unset( $this->get_cart_instance()->removed_cart_contents[ $item_key ] );
				unset( $removed_items[ $item_key ] );
				continue;
			}

			$items[ $item_key ] = $this->get_item( $_product, $cart_item, $item_key, $show_thumb, true );

			// Move the quantity value to it's parent.
			$items[ $item_key ]['quantity'] = $items[ $item_key ]['quantity']['value'];
		}

		return $items;
	} // END get_removed_items()

	/**
	 * Removes all internal elements of an item that is not needed.
	 *
	 * @access protected
	 * @param  array $cart_item Before cart item data is modified.
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
	 * Returns cross sells based on the items in the cart.
	 *
	 * @access  public
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0 Prices now return with formatted decimals.
	 * @return  array
	 */
	public function get_cross_sells() {
		// Get visible cross sells then sort them at random.
		$get_cross_sells = array_filter( array_map( 'wc_get_product', $this->get_cart_instance()->get_cross_sells() ), 'wc_products_array_filter_visible' );

		// Handle orderby and limit results.
		$orderby         = apply_filters( 'cocart_cross_sells_orderby', 'rand' );
		$order           = apply_filters( 'cocart_cross_sells_order', 'desc' );
		$get_cross_sells = wc_products_array_orderby( $get_cross_sells, $orderby, $order );
		$limit           = apply_filters( 'cocart_cross_sells_total', 3 );
		$get_cross_sells = $limit > 0 ? array_slice( $get_cross_sells, 0, $limit ) : $get_cross_sells;

		$cross_sells = array();

		foreach ( $get_cross_sells as $cross_sell ) {
			$id = $cross_sell->get_id();

			$thumbnail_id  = apply_filters( 'cocart_cross_sell_item_thumbnail', $cross_sell->get_image_id() );
			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_cross_sell_item_thumbnail_size', 'woocommerce_thumbnail' ) );
			$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src[0] );

			$cross_sells[] = array(
				'id'             => $id,
				'name'           => $cross_sell->get_name(),
				'title'          => $cross_sell->get_title(),
				'slug'           => $this->get_product_slug( $cross_sell ),
				'price'          => cocart_prepare_money_response( $cross_sell->get_price(), wc_get_price_decimals() ),
				'regular_price'  => cocart_prepare_money_response( $cross_sell->get_regular_price(), wc_get_price_decimals() ),
				'sale_price'     => cocart_prepare_money_response( $cross_sell->get_sale_price(), wc_get_price_decimals() ),
				'image'          => esc_url( $thumbnail_src ),
				'average_rating' => $cross_sell->get_average_rating() > 0 ? sprintf(
					/* translators: %s: average rating */
					esc_html__( 'Rated %s out of 5', 'cart-rest-api-for-woocommerce' ),
					esc_html( $cross_sell->get_average_rating() )
				) : '',
				'on_sale'        => $cross_sell->is_on_sale() ? true : false,
				'type'           => $cross_sell->get_type(),
			);
		}

		$cross_sells = apply_filters( 'cocart_cross_sells', $cross_sells );

		return $cross_sells;
	} // END get_cross_sells()

	/**
	 * Returns shipping details.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @return  array.
	 */
	public function get_shipping_details() {
		if ( ! wc_shipping_enabled() || 0 === wc_get_shipping_method_count( true ) ) {
			return array();
		}

		// Get shipping packages.
		$packages = WC()->shipping->get_packages();

		$details = array(
			'total_packages'          => count( (array) $packages ),
			'show_package_details'    => count( (array) $packages ) > 1,
			'has_calculated_shipping' => WC()->customer->has_calculated_shipping(),
			'packages'                => array(),
		);

		$package_key = 1;

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$product_names = array();

			if ( count( (array) $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' x' . $values['quantity'];
				}

				$product_names = apply_filters( 'cocart_shipping_package_details_array', $product_names, $package );
			}

			if ( 0 === $i ) {
				$package_key = 'default'; // Identifies the default package.
			}

			// Check that there are rates available for the package.
			if ( count( (array) $package['rates'] ) > 0 ) {
				$details['packages'][ $package_key ] = array(
					/* translators: %d: shipping package number */
					'package_name'          => apply_filters( 'cocart_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping #%d', 'shipping packages', 'cart-rest-api-for-woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'cart-rest-api-for-woocommerce' ), $i, $package ),
					'rates'                 => array(),
					'package_details'       => implode( ', ', $product_names ),
					'index'                 => $i, // Shipping package number.
					'chosen_method'         => $chosen_method,
					'formatted_destination' => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				);

				$rates = array();

				// Return each rate.
				foreach ( $package['rates'] as $key => $method ) {
					$meta_data = $this->clean_meta_data( $method, 'shipping' );

					$rates[ $key ] = array(
						'key'           => $key,
						'method_id'     => $method->get_method_id(),
						'instance_id'   => $method->instance_id,
						'label'         => $method->get_label(),
						'cost'          => cocart_prepare_money_response( $method->cost, wc_get_price_decimals() ),
						'html'          => html_entity_decode( wp_strip_all_tags( wc_cart_totals_shipping_method_label( $method ) ) ),
						'taxes'         => '',
						'chosen_method' => ( $chosen_method === $key ),
						'meta_data'     => $meta_data,
					);

					foreach ( $method->taxes as $shipping_cost => $tax_cost ) {
						$rates[ $key ]['taxes'] = cocart_prepare_money_response( $tax_cost, wc_get_price_decimals() );
					}
				}

				$details['packages'][ $package_key ]['rates'] = $rates;
			}

			$package_key++; // Update package key for next inline if any.
		}

		return $details;
	} // END get_shipping_details()

	/**
	 * Cleans up the meta data for API.
	 *
	 * @access  protected
	 * @since   3.1.0 Introduced
	 * @version 3.1.2
	 * @param   object $method Method data.
	 * @param   string $type   Meta data we are cleaning for.
	 * @return  array
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
	 * Return notices in cart if any.
	 *
	 * @access protected
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
	 * Returns messages and errors which are stored in the session, then clears them.
	 *
	 * @access protected
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
	 * Adds item to cart internally rather than WC.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @param   int        $product_id     Contains the id of the product to add to the cart.
	 * @param   int        $quantity       Contains the quantity of the item to add.
	 * @param   int        $variation_id   ID of the variation being added to the cart.
	 * @param   array      $variation      Attribute values.
	 * @param   array      $cart_item_data Extra cart item data we want to pass into the item.
	 * @param   WC_Product $product_data   Product data.
	 * @return  string|boolean $item_key
	 */
	public function add_cart_item( int $product_id, int $quantity, $variation_id, array $variation, array $cart_item_data, WC_Product $product_data ) {
		try {
			// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

			// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
			$this->get_cart_instance()->cart_contents[ $item_key ] = apply_filters(
				'cocart_add_cart_item',
				array_merge(
					$cart_item_data,
					array(
						'key'          => $item_key,
						'product_id'   => $product_id,
						'variation_id' => $variation_id,
						'variation'    => $variation,
						'quantity'     => $quantity,
						'data'         => $product_data,
						'data_hash'    => wc_get_cart_item_data_hash( $product_data ),
					)
				),
				$item_key
			);

			$this->get_cart_instance()->cart_contents = apply_filters( 'cocart_cart_contents_changed', $this->get_cart_instance()->cart_contents );

			do_action( 'cocart_add_to_cart', $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			return $item_key;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_cart_item()

	/**
	 * Cache cart item.
	 *
	 * @access public
	 * @since  3.1.0
	 * @param  array $was_added_to_cart Cart item to cache.
	 */
	public function cache_cart_item( $was_added_to_cart ) {
		$item_key = $was_added_to_cart['key'];

		CoCart_Cart_Cache::set_cached_item( $item_key, $was_added_to_cart );
	} // END cache_cart_item()

	/**
	 * Returns the customers details from fields.
	 *
	 * @access  protected
	 * @since   3.0.0
	 * @version 3.1.0
	 * @param   string      $fields The customer fields to return.
	 * @param   WC_Customer $customer The customer object or ID.
	 * @return  array  Returns the customer details based on the field requested.
	 */
	protected function get_customer_fields( $fields = 'billing', $customer = '' ) {
		// If no customer is set then get customer from cart.
		if ( empty( $customer ) ) {
			$customer = $this->get_cart_instance()->get_customer();
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

			$results[ $key ] = method_exists( $customer, $field_name ) ? $customer->$field_name() : apply_filters( 'cocart_get_customer_' . $key, '' );
		}

		return $results;
	} // END get_customer_fields()

	/**
	 * Convert queued error notices into an exception.
	 *
	 * For example, Payment methods may add error notices during validating fields to prevent checkout.
	 *
	 * This method will find the first error message and thrown an exception instead. Discards notices once complete.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access public
	 * @static
	 * @since  3.0.1
	 * @param  string $error_code Error code for the thrown exceptions.
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
	 * Get cart template.
	 *
	 * Used as a base even if the cart is empty along with
	 * customer information should the user be logged in.
	 *
	 * @access  protected
	 * @since   3.0.3
	 * @version 3.1.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  array Returns the default cart response.
	 */
	protected function get_cart_template( $request = array() ) {
		$fields = ! empty( $request['fields'] ) ? $request['fields'] : '';

		if ( ! empty( $fields ) ) {
			return self::get_cart_template_limited( $request );
		}

		// Other Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		return array(
			'cart_hash'      => $this->get_cart_instance()->get_cart_hash(),
			'cart_key'       => $this->get_cart_key( $request ),
			'currency'       => cocart_get_store_currency(),
			'customer'       => array(
				'billing_address'  => $this->get_customer_fields( 'billing' ),
				'shipping_address' => $this->get_customer_fields( 'shipping' ),
			),
			'items'          => array(),
			'item_count'     => $this->get_cart_instance()->get_cart_contents_count(),
			'items_weight'   => wc_get_weight( (float) $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) ),
			'coupons'        => array(),
			'needs_payment'  => $this->get_cart_instance()->needs_payment(),
			'needs_shipping' => $this->get_cart_instance()->needs_shipping(),
			'shipping'       => $this->get_shipping_details(),
			'fees'           => $this->get_fees( $this->get_cart_instance() ),
			'taxes'          => array(),
			'totals'         => array(
				'subtotal'       => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal(), wc_get_price_decimals() ),
				'subtotal_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal_tax(), wc_get_price_decimals() ),
				'fee_total'      => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_total(), wc_get_price_decimals() ),
				'fee_tax'        => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_tax(), wc_get_price_decimals() ),
				'discount_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_total(), wc_get_price_decimals() ),
				'discount_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_tax(), wc_get_price_decimals() ),
				'shipping_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_total(), wc_get_price_decimals() ),
				'shipping_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_tax(), wc_get_price_decimals() ),
				'total'          => cocart_prepare_money_response( $this->get_cart_instance()->get_total(), wc_get_price_decimals() ),
				'total_tax'      => cocart_prepare_money_response( $this->get_cart_instance()->get_total_tax(), wc_get_price_decimals() ),
			),
			'removed_items'  => $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $show_thumb ),
			'cross_sells'    => $this->get_cross_sells(),
			'notices'        => $this->maybe_return_notices(),
		);
	} // END get_cart_template()

	/**
	 * Get cart template - Limited.
	 *
	 * Same as original cart template only it returns the fields requested.
	 *
	 * @access protected
	 * @since  3.1.0
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @return array           $template Returns requested cart response.
	 */
	protected function get_cart_template_limited( $request = array() ) {
		$fields     = ! empty( $request['fields'] ) ? explode( ',', $request['fields'] ) : '';
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		$template = array();

		foreach ( $fields as $field ) {
			$field        = explode( ':', $field );
			$parent_field = $field[0];
			$child_field  = ! empty( $field[1] ) ? $field[1] : '';

			switch ( $parent_field ) {
				case 'cart_hash':
					$template['cart_hash'] = $this->get_cart_instance()->get_cart_hash();
					break;
				case 'cart_key':
					$template['cart_key'] = $this->get_cart_key( $request );
					break;
				case 'currency':
					$template['currency'] = cocart_get_store_currency();
					break;
				case 'customer':
					if ( ! empty( $child_field ) ) {
						if ( 'billing_address' === $child_field ) {
							$template['customer']['billing_address'] = $this->get_customer_fields( 'billing' );
						}
						if ( 'shipping_address' === $child_field ) {
							$template['customer']['shipping_address'] = $this->get_customer_fields( 'shipping' );
						}
					} else {
						$template['customer'] = array(
							'billing_address'  => $this->get_customer_fields( 'billing' ),
							'shipping_address' => $this->get_customer_fields( 'shipping' ),
						);
					}
					break;
				case 'items':
					$template['items'] = array();
					break;
				case 'item_count':
					$template['item_count'] = $this->get_cart_instance()->get_cart_contents_count();
					break;
				case 'items_weight':
					$template['items_weight'] = wc_get_weight( (float) $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) );
					break;
				case 'coupons':
					$template['coupons'] = array();
					break;
				case 'needs_payment':
					$template['needs_payment'] = $this->get_cart_instance()->needs_payment();
					break;
				case 'needs_shipping':
					$template['needs_shipping'] = $this->get_cart_instance()->needs_shipping();
					break;
				case 'shipping':
					$template['shipping'] = $this->get_shipping_details();
					break;
				case 'fees':
					$template['fees'] = $this->get_fees( $this->get_cart_instance() );
					break;
				case 'taxes':
					$template['taxes'] = array();
					break;
				case 'totals':
					if ( ! empty( $child_field ) ) {
						$child_field = explode( '-', $child_field );

						foreach ( $child_field as $total ) {
							if ( 'subtotal' === $total ) {
								$template['totals']['subtotal'] = cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal(), wc_get_price_decimals() );
							}
							if ( 'subtotal_tax' === $total ) {
								$template['totals']['subtotal_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal_tax(), wc_get_price_decimals() );
							}
							if ( 'fee_total' === $total ) {
								$template['totals']['fee_total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_fee_total(), wc_get_price_decimals() );
							}
							if ( 'fee_tax' === $total ) {
								$template['totals']['fee_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_fee_tax(), wc_get_price_decimals() );
							}
							if ( 'discount_total' === $total ) {
								$template['totals']['discount_total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_discount_total(), wc_get_price_decimals() );
							}
							if ( 'discount_tax' === $total ) {
								$template['totals']['discount_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_discount_tax(), wc_get_price_decimals() );
							}
							if ( 'shipping_total' === $total ) {
								$template['totals']['shipping_total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_total(), wc_get_price_decimals() );
							}
							if ( 'shipping_tax' === $total ) {
								$template['totals']['shipping_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_tax(), wc_get_price_decimals() );
							}
							if ( 'total' === $total ) {
								$template['totals']['total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_total(), wc_get_price_decimals() );
							}
							if ( 'total_tax' === $total ) {
								$template['totals']['total_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_total_tax(), wc_get_price_decimals() );
							}
						}
					} else {
						$template['totals'] = array(
							'subtotal'       => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal(), wc_get_price_decimals() ),
							'subtotal_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal_tax(), wc_get_price_decimals() ),
							'fee_total'      => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_total(), wc_get_price_decimals() ),
							'fee_tax'        => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_tax(), wc_get_price_decimals() ),
							'discount_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_total(), wc_get_price_decimals() ),
							'discount_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_tax(), wc_get_price_decimals() ),
							'shipping_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_total(), wc_get_price_decimals() ),
							'shipping_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_tax(), wc_get_price_decimals() ),
							'total'          => cocart_prepare_money_response( $this->get_cart_instance()->get_total(), wc_get_price_decimals() ),
							'total_tax'      => cocart_prepare_money_response( $this->get_cart_instance()->get_total_tax(), wc_get_price_decimals() ),
						);
					}
					break;
				case 'removed_items':
					$template['removed_items'] = $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $show_thumb );
					break;
				case 'cross_sells':
					$template['cross_sells'] = $this->get_cross_sells();
					break;
				case 'notices':
					$template['notices'] = $this->maybe_return_notices();
					break;
			}
		}

		return $template;
	} // END get_cart_template_limited()

	/**
	 * Throws exception when an item cannot be added to the cart.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access protected
	 * @since  3.0.4
	 * @param  WC_Product $product Product object associated with the cart item.
	 */
	protected function throw_product_not_purchasable( $product ) {
		$message = sprintf(
			/* translators: %s: product name */
			__( '"%s" is not available for purchase.', 'cart-rest-api-for-woocommerce' ),
			$product->get_name()
		);

		/**
		 * Filters message about product unable to be purchased.
		 *
		 * @param string     $message Message.
		 * @param WC_Product $product Product data.
		 */
		$message = apply_filters( 'cocart_product_cannot_be_purchased_message', $message, $product );

		throw new CoCart_Data_Exception( 'cocart_cannot_be_purchased', $message, 403 );
	} // END throw_product_not_purchasable()

	/**
	 * Gets the quantity of a product across line items.
	 *
	 * @access protected
	 * @since  3.1.0
	 * @param  WC_Product $product Product object.
	 * @return int
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
	 * @since  3.1.0
	 * @param  WC_Product $product Product object.
	 * @return int
	 */
	protected function get_remaining_stock_for_product( $product ) {
		$reserve_stock = new ReserveStock();
		$draft_order   = WC()->session->get( 'cocart_draft_order', 0 );
		$qty_reserved  = $reserve_stock->get_reserved_stock( $product, $draft_order );

		return $product->get_stock_quantity() - $qty_reserved;
	} // END get_remaining_stock_for_product()

	/**
	 * Throws exception if the item key is not provided when either removing, updating or restoring the item.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access protected
	 * @since  3.0.17
	 * @param  string $item_key Item key of the item in the cart.
	 * @param  string $status   Status of which we are checking the item key.
	 * @return string $item_key Item key of the item in the cart.
	 */
	protected function throw_missing_item_key( $item_key, $status ) {
		$item_key = (string) $item_key; // Make sure the item key is a string value.

		if ( '0' === $item_key ) {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			/**
			 * Filters message about cart item key required.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_cart_item_key_required_message', $message, $status );

			throw new CoCart_Data_Exception( 'cocart_cart_item_key_required', $message, 404 );
		}

		return $item_key;
	} // END throw_missing_item_key()

	/**
	 * Ensures the cart totals are calculated before an API response is returned.
	 *
	 * @access public
	 * @since  3.1.0
	 */
	public function calculate_totals() {
		$this->get_cart_instance()->calculate_fees();
		$this->get_cart_instance()->calculate_shipping();
		$this->get_cart_instance()->calculate_totals();
	} // END calculate_totals()

	/**
	 * Get the schema for returning the cart.
	 *
	 * @access public
	 * @since  3.1.0 Introduced.
	 * @return array
	 */
	public function get_public_cart_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Cart', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'cart_hash'      => array(
					'description' => __( 'The cart hash.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_key'       => array(
					'description' => __( 'The cart key.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'currency'       => array(
					'description' => __( 'The store currency information.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'currency_code'               => array(
							'description' => __( 'The currency code (in ISO format) for returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_symbol'             => array(
							'description' => __( 'The currency symbol for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_minor_unit'         => array(
							'description' => __( 'The currency minor unit (number of digits after the decimal separator) for returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_decimal_separator'  => array(
							'description' => __( 'The decimal separator for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_thousand_separator' => array(
							'description' => __( 'The thousand separator for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_prefix'             => array(
							'description' => __( 'The price prefix for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_suffix'             => array(
							'description' => __( 'The price prefix for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'customer'       => array(
					'description' => __( 'The customer information.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'billing_address'  => array(
							'description' => __( 'Customers billing address.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'billing_first_name' => array(
									'description' => __( 'Customers billing first name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_last_name'  => array(
									'description' => __( 'Customers billing last name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_company'    => array(
									'description' => __( 'Customers billing company name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_country'    => array(
									'description' => __( 'Customers billing country.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_address_1'  => array(
									'description' => __( 'Customers billing address line one.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_address_2'  => array(
									'description' => __( 'Customers billing address line two.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_city'       => array(
									'description' => __( 'Customers billing address city.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_state'      => array(
									'description' => __( 'Customers billing state.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_postcode'   => array(
									'description' => __( 'Customers billing postcode or zip code.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_phone'      => array(
									'description' => __( 'Customers billing phone.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_email'      => array(
									'description' => __( 'Customers billing email address.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
						'shipping_address' => array(
							'description' => __( 'Customers shipping address.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'shipping_first_name' => array(
									'description' => __( 'Customers shipping first name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_last_name'  => array(
									'description' => __( 'Customers shipping last name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_company'    => array(
									'description' => __( 'Customers shipping company name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_country'    => array(
									'description' => __( 'Customers shipping country.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_address_1'  => array(
									'description' => __( 'Customers shipping address line one.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_address_2'  => array(
									'description' => __( 'Customers shipping address line two.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_city'       => array(
									'description' => __( 'Customers shipping address city.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_state'      => array(
									'description' => __( 'Customers shipping state.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_postcode'   => array(
									'description' => __( 'Customers shipping postcode or zip code.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
				'items'          => array(
					'description' => __( 'The list of items in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'item_key'       => array(
								'description' => __( 'Unique ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'id'             => array(
								'description' => __( 'Product ID or Variation ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'           => array(
								'description' => __( 'The name of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'title'          => array(
								'description' => __( 'The title of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'price'          => array(
								'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'quantity'       => array(
								'description' => __( 'The quantity of the item in the cart and minimum and maximum purchase capability.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'value'        => array(
										'description' => __( 'The quantity of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'min_purchase' => array(
										'description' => __( 'The minimum purchase amount required.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'max_purchase' => array(
										'description' => __( 'The maximum purchase amount allowed. If -1 the item has an unlimited purchase amount.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
							'totals'         => array(
								'description' => __( 'The totals of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'subtotal'     => array(
										'description' => __( 'The subtotal of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'subtotal_tax' => array(
										'description' => __( 'The subtotal tax of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total'        => array(
										'description' => __( 'The total of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total_tax'    => array(
										'description' => __( 'The total tax of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
							'slug'           => array(
								'description' => __( 'The product slug of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'meta'           => array(
								'description' => __( 'The meta data of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'product_type' => array(
										'description' => __( 'The product type of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'sku'          => array(
										'description' => __( 'The SKU of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'dimensions'   => array(
										'description' => __( 'The dimensions of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(
											'length' => array(
												'description' => __( 'The length of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'width'  => array(
												'description' => __( 'The width of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'height' => array(
												'description' => __( 'The height of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'unit'   => array(
												'description' => __( 'The unit measurement of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
										),
									),
									'weight'       => array(
										'description' => __( 'The weight of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'variation'    => array(
										'description' => __( 'The variation attributes of the item in the cart (if item is a variation of a variable product).', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(),
									),
								),
							),
							'backorders'     => array(
								'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'cart_item_data' => array(
								'description' => __( 'Custom item data applied to the item in the cart (if any).', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'properties'  => array(),
							),
							'featured_image' => array(
								'description' => __( 'The featured image of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'item_count'     => array(
					'description' => __( 'The number of items in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'items_weight'   => array(
					'description' => __( 'The total weight of all items in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'coupons'        => array(
					'description' => __( 'Coupons added to the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'coupon'      => array(
								'description' => __( 'The coupon code.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'label'       => array(
								'description' => __( 'The coupon label.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'saving'      => array(
								'description' => __( 'The amount discounted from the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'saving_html' => array(
								'description' => __( 'The amount discounted from the cart (HTML formatted).', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'needs_payment'  => array(
					'description' => __( 'True if the cart needs payment. False for carts with only free products and no shipping costs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'needs_shipping' => array(
					'description' => __( 'True if the cart needs shipping and requires the showing of shipping costs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'shipping'       => array(
					'description' => __( 'The shipping packages available to the customer.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'total_packages'          => array(
							'description' => __( 'The number of shipping packages available calculated on the shipping address.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'show_package_details'    => array(
							'description' => __( 'True if the cart meets the criteria for showing items in the cart assigned to package.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'has_calculated_shipping' => array(
							'description' => __( 'True if the cart meets the criteria for showing shipping costs, and rates have been calculated and included in the totals.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'packages'                => array(
							'description' => __( 'The packages returned after calculating shipping.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'default' => array(
									'description' => __( 'The default package.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'readonly'    => true,
									'properties'  => array(
										'package_name'    => array(
											'description' => __( 'The package name.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'rates'           => array(
											'description' => __( 'The packages returned after calculating shipping.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'object',
											'context'     => array( 'view' ),
											'readonly'    => true,
											'properties'  => array(
												'[a-z0-9]' => array(
													'key'  => array(
														'description' => __( 'The rate key.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'method_id' => array(
														'description' => __( 'The method ID.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'instance_id' => array(
														'description' => __( 'The instance ID.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'label' => array(
														'description' => __( 'The rate label.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'cost' => array(
														'description' => __( 'The rate cost.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'html' => array(
														'description' => __( 'The rate label and cost formatted.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'taxes' => array(
														'description' => __( 'The rate tax cost.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'chosen_method' => array(
														'description' => __( 'The chosen method.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'boolean',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'meta_data' => array(
														'description' => __( 'The rate meta data.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'object',
														'context'     => array( 'view' ),
														'readonly'    => true,
														'properties' => array(
															'items' => array(
																'description' => __( 'The items the shipping rate has calculated based on.', 'cart-rest-api-for-woocommerce' ),
																'type'        => 'string',
																'context'     => array( 'view' ),
																'readonly'    => true,
															),
														),
													),
												),
											),
										),
										'package_details' => array(
											'description' => __( 'The package details if any.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'index'           => array(
											'description' => __( 'The package index.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'chosen_method'   => array(
											'description' => __( 'The chosen method set from the available rates.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'formatted_destination' => array(
											'description' => __( 'The destination the package.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
								),
							),
						),
					),
				),
				'fees'           => array(
					'description' => __( 'The cart fees.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'[a-zA-Z0-9]' => array(
							'name' => array(
								'description' => __( 'The fee name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'fee'  => array(
								'description' => __( 'The fee value.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'taxes'          => array(
					'description' => __( 'The cart taxes.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'[A-Z-TAX-0-9]' => array(
							'description' => __( 'The store currency information.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'name'  => array(
									'description' => __( 'The tax name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'price' => array(
									'description' => __( 'The tax price.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
				'totals'         => array(
					'description' => __( 'The store currency information.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'subtotal'       => array(
							'description' => __( 'The subtotal of all items, shipping (if any) and fees (if any) before coupons applied (if any) to the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'subtotal_tax'   => array(
							'description' => __( 'The subtotal tax of all items, shipping (if any) and fees (if any) before coupons applied (if any) to the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'fee_total'      => array(
							'description' => __( 'The fee total.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'fee_tax'        => array(
							'description' => __( 'The fee tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'discount_total' => array(
							'description' => __( 'The discount total.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'discount_tax'   => array(
							'description' => __( 'The discount tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'shipping_total' => array(
							'description' => __( 'The shipping total of the selected packages.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'shipping_tax'   => array(
							'description' => __( 'The shipping tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'total'          => array(
							'description' => __( 'The total of everything in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'total_tax'      => array(
							'description' => __( 'The total tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'removed_items'  => array(
					'description' => __( 'Items that have been removed from the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'item_key'       => array(
								'description' => __( 'Unique ID of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'id'             => array(
								'description' => __( 'Product ID or Variation ID of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'           => array(
								'description' => __( 'The name of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'title'          => array(
								'description' => __( 'The title of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'price'          => array(
								'description' => __( 'The price of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'quantity'       => array(
								'description' => __( 'The quantity of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'float',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'totals'         => array(
								'description' => __( 'The totals of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'subtotal'     => array(
										'description' => __( 'The subtotal of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'subtotal_tax' => array(
										'description' => __( 'The subtotal tax of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total'        => array(
										'description' => __( 'The total of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total_tax'    => array(
										'description' => __( 'The total tax of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
							'slug'           => array(
								'description' => __( 'The product slug of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'meta'           => array(
								'description' => __( 'The meta data of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'product_type' => array(
										'description' => __( 'The product type of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'sku'          => array(
										'description' => __( 'The SKU of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'dimensions'   => array(
										'description' => __( 'The dimensions of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(
											'length' => array(
												'description' => __( 'The length of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'width'  => array(
												'description' => __( 'The width of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'height' => array(
												'description' => __( 'The height of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'unit'   => array(
												'description' => __( 'The unit measurement of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
										),
									),
									'weight'       => array(
										'description' => __( 'The weight of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'variation'    => array(
										'description' => __( 'The variation attributes of the item (if item is a variation of a variable product).', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(),
									),
								),
							),
							'backorders'     => array(
								'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'cart_item_data' => array(
								'description' => __( 'Custom item data applied to the item (if any).', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'properties'  => array(),
							),
							'featured_image' => array(
								'description' => __( 'The featured image of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'cross_sells'    => array(
					'description' => __( 'Items you may be interested in adding to the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'id'             => array(
							'description' => __( 'Product ID or Variation ID of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'           => array(
							'description' => __( 'The name of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'title'          => array(
							'description' => __( 'The title of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'slug'           => array(
							'description' => __( 'The slug of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'price'          => array(
							'description' => __( 'The price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'regular_price'  => array(
							'description' => __( 'The regular price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'sale_price'     => array(
							'description' => __( 'The sale price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'image'          => array(
							'description' => __( 'The image of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'average_rating' => array(
							'description' => __( 'The average rating of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'on_sale'        => array(
							'description' => __( 'The sale status of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'type'           => array(
							'description' => __( 'The product type of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'notices'        => array(
					'description' => __( 'The cart notices.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'success' => array(
							'description' => __( 'Notices for successful actions.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type' => 'string',
							),
						),
						'error'   => array(
							'description' => __( 'Notices for error actions.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type' => 'string',
							),
						),
					),
				),
			),
		);

		/**
		 * This filter is now deprecated and is replaced with `cocart_cart_items_schema`.
		 *
		 * @deprecated 3.1.0
		 */
		cocart_deprecated_filter( 'cocart_cart_schema', array( $schema['properties'] ), '3.1.0', 'cocart_cart_items_schema', __( 'Changed for the purpose of not overriding default properties.', 'cart-rest-api-for-woocommerce' ) );

		/**
		 * Extend the cart schema properties for items.
		 *
		 * Dev Note: Nothing needs to pass so your safe if you think you will remove any default properties.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$schema['properties']['items']['items']['properties'] += apply_filters( 'cocart_cart_items_schema', array() );

		return $schema;
	} // END get_public_cart_schema()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.1.0
	 * @return  array $params
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description' => __( 'Unique identifier for the cart or customer.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'required'    => false,
			),
			'fields'   => array(
				'description' => __( 'Specify each parent field you want to request separated by (,) in the cart response before the data is fetched.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'required'    => false,
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
		 * Extend the query parameters.
		 *
		 * Dev Note: Nothing needs to pass so your safe if you think you will remove any default parameters.
		 *
		 * @since 3.1.0
		 */
		$params += apply_filters( 'cocart_cart_query_parameters', array() );

		return $params;
	} // END get_collection_params()

} // END class
