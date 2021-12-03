<?php
/**
 * CoCart REST API controller
 *
 * Handles requests to the cart endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @version 3.0.17
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 controller class.
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
				'schema' => array( $this, 'get_item_schema' ),
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
	 * @param   string $item_id   - The item we are looking up in the cart.
	 * @param   string $condition - Default is 'add', other conditions are: container, update, remove, restore.
	 * @return  array  $item      - Returns details of the item in the cart if it exists.
	 */
	public function get_cart_item( $item_id, $condition = 'add' ) {
		$item = isset( $this->get_cart_instance()->cart_contents[ $item_id ] ) ? $this->get_cart_instance()->cart_contents[ $item_id ] : array();

		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // EMD get_cart_item()

	/**
	 * Returns all cart items.
	 *
	 * @access public
	 * @param  callable $callback - Optional callback to apply to the array filter.
	 * @return array
	 */
	public function get_cart_items( $callback = null ) {
		return $callback ? array_filter( $this->get_cart_instance()->get_cart(), $callback ) : array_filter( $this->get_cart_instance()->get_cart() );
	} // END get_cart_items()

	/**
	 * Get cart.
	 *
	 * @access public
	 * @param  WP_REST_Request $request    - Full details about the request.
	 * @param  string          $deprecated - Originally the cart item key.
	 * @return WP_REST_Response
	 */
	public function get_cart( $request = array(), $deprecated = '' ) {
		$show_raw      = ! empty( $request['raw'] ) ? $request['raw'] : false; // Internal parameter request.
		$cart_contents = ! $this->get_cart_instance()->is_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

		/**
		 * Runs before getting cart. Useful for add-ons or 3rd party plugins.
		 *
		 * @since 3.0.0
		 * @param array           $cart_contents - Cart contents.
		 * @param WC_Cart         Cart object.
		 * @param WP_REST_Request $request       - Full details about the request.
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
		wc_deprecated_hook( 'cocart_get_cart', '3.0.0', null, null );

		$cart_contents = $this->return_cart_contents( $request, $cart_contents );

		return CoCart_Response::get_response( $cart_contents, $this->namespace, $this->rest_base );
	} // END get_cart()

	/**
	 * Return cart contents.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 3.0.3
	 * @param   WP_REST_Request $request - Full details about the request.
	 * @param   array           $cart_contents - Cart content.
	 * @param   boolean         $from_session - Identifies if the cart is called from a session.
	 * @param   deprected       $deprecated - Originally the cart item key.
	 * @return  array           $cart
	 */
	public function return_cart_contents( $request = array(), $cart_contents = array(), $deprecated = '', $from_session = false ) {
		// Calculate totals to be sure they are correct before returning cart contents.
		$this->get_cart_instance()->calculate_totals();

		// Get cart template.
		$cart_template = $this->get_cart_template( $request );

		// If the cart is completly empty or not exist then return nothing.
		if ( $this->get_cart_instance()->get_cart_contents_count() <= 0 && count( $this->get_cart_instance()->get_removed_cart_contents() ) <= 0 ) {
			/**
			 * Filter response for empty cart.
			 *
			 * @since   2.0.8
			 * @version 3.0.0
			 */
			cocart_deprecated_filter( 'cocart_return_empty_cart', array(), '3.0.0', 'cocart_empty_cart', __( 'But only if you are using API v2.', 'cart-rest-api-for-woocommerce' ) );

			return apply_filters( 'cocart_empty_cart', array() );
		}

		/**
		 * Return the default cart data if set to true.
		 *
		 * @since 3.0.0
		 */
		if ( ! empty( $request['default'] ) && $request['default'] ) {
			return $cart_contents;
		}

		// Other Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		// Returns each coupon applied and coupon total applied if store has coupons enabled.
		$coupons = wc_coupons_enabled() ? $this->get_cart_instance()->get_applied_coupons() : array();

		if ( ! empty( $coupons ) ) {
			foreach ( $coupons as $i => $coupon ) {
				$cart['coupons'][] = array(
					'coupon'      => wc_format_coupon_code( wp_unslash( $coupon ) ),
					'label'       => esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ),
					'saving'      => $this->coupon_html( $coupon, false ),
					'saving_html' => $this->coupon_html( $coupon ),
				);
			}
		}

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
					'total' => apply_filters( 'cocart_cart_totals_taxes_total', $this->prepare_money_response( $this->get_cart_instance()->get_taxes_total() ) ),
				);
			}
		}

		// Returns items and removed items.
		$cart['items']         = $this->get_items( $cart_contents, $show_thumb );
		$cart['removed_items'] = $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $show_thumb );

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
	 * @param   int|string $product_id - The product ID to validate.
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
	 * @version 3.0.17
	 * @param   int|float $quantity - The quantity to validate.
	 */
	protected function validate_quantity( $quantity ) {
		try {
			if ( ! is_numeric( $quantity ) ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_not_numeric', __( 'Quantity must be integer or a float value!', 'cart-rest-api-for-woocommerce' ), 405 );
			}

			/**
			 * This filter was added to support certain edge cases.
			 *
			 * @since 3.0.17
			 * @param int|float Minimum Quantity to validate with.
			 */
			$minimum_quantity = apply_filters( 'cocart_quantity_minimum_requirement', 1 );

			if ( 0 == $quantity || $quantity < $minimum_quantity ) {
				throw new CoCart_Data_Exception( 'cocart_quantity_invalid_amount', sprintf( __( 'Quantity must be set to a minimum of %s.', 'cart-rest-api-for-woocommerce' ), $minimum_quantity ), 405 );
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
	 * @param   int        $variation_id - ID of the variation.
	 * @param   array      $variation    - Attribute values.
	 * @param   WC_Product $product      - The product data.
	 * @return  array
	 */
	protected function validate_variable_product( $variation_id, $variation = array(), $product ) {
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
					 * @param string $message         - Message.
					 * @param string $attribute_label - Attribute Label.
					 * @param array  $attribute       - Allowed values.
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
				 * @param string $message            - Message.
				 * @param string $missing_attributes - Number of missing attributes.
				 * @param array  $missing_attributes - List of missing attributes.
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
	 * @param   array      $variation    - Submitted attributes.
	 * @param   WC_Product $product      - Product being added to the cart.
	 * @return  int        $variation_id - Matching variation ID.
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
	 * @param   WC_Product $product - Product being added to the cart.
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
	 * @since   1.0.0
	 * @version 3.0.12
	 * @param   int             $product_id   - Contains the ID of the product.
	 * @param   int|float       $quantity     - Contains the quantity of the item.
	 * @param   null            $deprecated   - Used to pass the variation id of the product to add to the cart.
	 * @param   array           $variation    - Contains the selected attributes.
	 * @param   array           $item_data    - Extra cart item data we want to pass into the item.
	 * @param   string          $product_type - The product type.
	 * @param   WP_REST_Request $request      - Full details about the request.
	 * @return  array
	 */
	protected function validate_product( $product_id = null, $quantity = 1, $deprecated = null, $variation = array(), $item_data = array(), $product_type = '', $request = array() ) {
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
				 * @param string     $message - Message.
				 * @param WC_Product $product - Product data.
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
			 * @param int|float $quantity     - The original quantity of the item.
			 * @param int       $product_id   - The product ID.
			 * @param int       $variation_id - The variation ID.
			 * @param array     $variation    - The variation data.
			 * @param array     $item_data  - - The cart item data.
			 */
			$quantity = apply_filters( 'cocart_add_to_cart_quantity', $quantity, $product_id, $variation_id, $variation, $item_data );

			// Validates the item quantity.
			$quantity = $this->validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $cart_id );

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
	 * @param   array     $current_data - Cart item details.
	 * @param   int|float $quantity     - The quantity to check stock.
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

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, array( 'status' => 403 ) );
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END has_enough_stock()

	/**
	 * Prepares a list of store currency data to return in responses.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_store_currency() {
		$position = get_option( 'woocommerce_currency_pos' );
		$symbol   = html_entity_decode( get_woocommerce_currency_symbol() );
		$prefix   = '';
		$suffix   = '';

		switch ( $position ) {
			case 'left_space':
				$prefix = $symbol . ' ';
				break;
			case 'left':
				$prefix = $symbol;
				break;
			case 'right_space':
				$suffix = ' ' . $symbol;
				break;
			case 'right':
				$suffix = $symbol;
				break;
		}

		return array(
			'currency_code'               => get_woocommerce_currency(),
			'currency_symbol'             => $symbol,
			'currency_minor_unit'         => wc_get_price_decimals(),
			'currency_decimal_separator'  => wc_get_price_decimal_separator(),
			'currency_thousand_separator' => wc_get_price_thousand_separator(),
			'currency_prefix'             => $prefix,
			'currency_suffix'             => $suffix,
		);
	} // END get_store_currency()

	/**
	 * Returns the cart key.
	 *
	 * @access public
	 * @param  WP_REST_Request $request - Full details about the request.
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
		$cart_key = isset( $request['cart_key'] ) ? $request['cart_key'] : $cart_key;

		return $cart_key;
	} // END get_cart_key()

	/**
	 * Get tax lines from the cart and format to match schema.
	 *
	 * @access protected
	 * @param  WC_Cart $cart - Cart class instance.
	 * @return array
	 */
	protected function get_tax_lines( $cart ) {
		$cart_tax_totals = $cart->get_tax_totals();
		$tax_lines       = array();

		foreach ( $cart_tax_totals as $code => $tax ) {
			$tax_lines[ $code ] = array(
				'name'  => $tax->label,
				'price' => $this->prepare_money_response( $tax->amount, wc_get_price_decimals() ),
			);
		}

		return $tax_lines;
	} // END get_tax_lines()

	/**
	 * Convert monetary values from WooCommerce to string based integers, using
	 * the smallest unit of a currency.
	 *
	 * @access  protected
	 * @since   3.0.0
	 * @version 3.0.2
	 * @param   string|float $amount        - Monetary amount with decimals.
	 * @param   int          $decimals      - Number of decimals the amount is formatted with.
	 * @param   int          $rounding_mode - Defaults to the PHP_ROUND_HALF_UP constant.
	 * @return  string       The new amount.
	 */
	protected function prepare_money_response( $amount, $decimals = 2, $rounding_mode = PHP_ROUND_HALF_UP ) {
		$amount = html_entity_decode( wp_strip_all_tags( $amount ) );

		return (string) intval(
			round(
				( (float) wc_format_decimal( $amount ) ) * ( 10 ** absint( $decimals ) ),
				0,
				absint( $rounding_mode )
			)
		);
	} // END prepare_money_response()

	/**
	 * Format variation data, for example convert slugs such as attribute_pa_size to Size.
	 *
	 * @access protected
	 * @param  array      $variation_data - Array of data from the cart.
	 * @param  WC_Product $product        - Product data.
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
	 * @param  WC_Cart $cart - Cart class instance.
	 * @return array
	 */
	public function get_fees( $cart ) {
		$cart_fees = $cart->get_fees();

		$fees = array();

		if ( ! empty( $cart_fees ) ) {
			foreach ( $cart_fees as $key => $fee ) {
				$fees[ $key ] = array(
					'name' => esc_html( $fee->name ),
					'fee'  => $this->prepare_money_response( $this->fee_html( $cart, $fee ), wc_get_price_decimals() ),
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
	 * @param   string|WC_Coupon $coupon    - Coupon data or code.
	 * @param   boolean          $formatted - Formats the saving amount.
	 * @return  string                      - The coupon in HTML.
	 */
	public function coupon_html( $coupon, $formatted = true ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}

		$amount = $this->get_cart_instance()->get_coupon_discount_amount( $coupon->get_code(), $this->get_cart_instance()->display_cart_ex_tax );

		if ( $formatted ) {
			$savings = html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
		} else {
			$savings = $this->prepare_money_response( $amount, wc_get_price_decimals() );
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
	 * @param  object $cart - Cart instance.
	 * @param  object $fee  - Fee data.
	 * @return string       - Returns the fee value.
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
	 * @param  WC_Product $product - Passes the product object if valid.
	 * @return WC_Product $product - Returns a product object if purchasable.
	 */
	public function validate_product_for_cart( $product ) {
		try {
			// Check if the product exists before continuing.
			if ( ! $product || 'trash' === $product->get_status() ) {
				$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about product that cannot be added to cart.
				 *
				 * @param string     $message - Message.
				 * @param WC_Product $product - Product data.
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
	 * @version 3.0.12
	 * @param   WC_Product $product      - Product object associated with the cart item.
	 * @param   int|float  $quantity     - The quantity to validate.
	 * @param   int        $product_id   - The product ID.
	 * @param   int        $variation_id - The variation ID.
	 * @param   array      $item_data    - The cart item data.
	 * @param   string     $cart_id      - Generated ID based on item in cart.
	 * @return  int|float  $quantity     - The quantity returned.
	 */
	public function validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $cart_id ) {
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
					 * @param string     $message - Message.
					 * @param WC_Product $product - Product data.
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
	 * @version 3.0.7
	 * @param   WC_Product $product  - Product object associated with the cart item.
	 * @param   int|float  $quantity - Quantity of product to validate availability.
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
				 * @param string     $message - Message.
				 * @param WC_Product $product - Product data.
				 */
				$message = apply_filters( 'cocart_product_is_out_of_stock_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_out_of_stock', $message, 404 );
			}

			if ( ! $product->has_enough_stock( $quantity ) ) {
				/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
				$message = sprintf( __( 'You cannot add the amount of %1$s for "%2$s" to the cart because there is not enough stock (%3$s remaining).', 'cart-rest-api-for-woocommerce' ), $quantity, $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ) );

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 403 );
			}

			if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
				$qty_in_cart = $this->get_cart_instance()->get_cart_item_quantities();

				if ( isset( $qty_in_cart[ $product->get_stock_managed_by_id() ] ) && ! $product->has_enough_stock( $qty_in_cart[ $product->get_stock_managed_by_id() ] + $quantity ) ) {
					$message = sprintf(
						/* translators: 1: product name, 2: Quantity in Stock, 3: Quantity in Cart */
						__( 'You cannot add that amount of "%1$s" to the cart &mdash; we have (%2$s remaining). You already have (%3$s) in your cart.', 'cart-rest-api-for-woocommerce' ),
						$product->get_name(),
						wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ),
						wc_format_stock_quantity_for_display( $qty_in_cart[ $product->get_stock_managed_by_id() ], $product )
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
	 * @param  WP_REST_Request $request - Full details about the request.
	 * @return $request
	 */
	public function filter_request_data( $request ) {
		return apply_filters( 'cocart_filter_request_data', $request );
	} // END filter_request_data()

	/**
	 * Get the main product slug even if the product type is a variation.
	 *
	 * @access public
	 * @param  WC_Product $object - The product object.
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
	 * @since   3.0.0
	 * @version 3.0.17
	 * @param   WC_Product $_product     - The product data of the item in the cart.
	 * @param   array      $cart_item    - The item in the cart containing the default cart item data.
	 * @param   string     $item_key     - The item key generated based on the details of the item.
	 * @param   boolean    $show_thumb   - Determines if requested to return the item featured thumbnail.
	 * @param   boolean    $removed_item - Determines if the item in the cart is removed.
	 * @return  array      $item         - Full details of the item in the cart and it's purchase limits.
	 */
	public function get_item( $_product, $cart_item = array(), $item_key = '', $show_thumb = true, $removed_item = false ) {
		$item = array(
			'item_key'       => $item_key,
			'id'             => $_product->get_id(),
			'name'           => apply_filters( 'cocart_cart_item_name', $_product->get_name(), $_product, $cart_item, $item_key ),
			'title'          => apply_filters( 'cocart_cart_item_title', $_product->get_title(), $_product, $cart_item, $item_key ),
			'price'          => apply_filters( 'cocart_cart_item_price', wc_format_decimal( $_product->get_price(), wc_get_price_decimals() ), $cart_item, $item_key ),
			'quantity'       => array(
				'value'        => apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item ),
				'min_purchase' => $_product->get_min_purchase_quantity(),
				'max_purchase' => $_product->get_max_purchase_quantity(),
			),
			'tax_data'       => $cart_item['line_tax_data'],
			'totals'         => array(
				'subtotal'     => apply_filters( 'cocart_cart_item_subtotal', $cart_item['line_subtotal'], $cart_item, $item_key ),
				'subtotal_tax' => $cart_item['line_subtotal_tax'],
				'total'        => $cart_item['line_total'],
				'tax'          => $cart_item['line_tax'],
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
	 * @param   array   $cart_contents - The cart contents passed.
	 * @param   boolean $show_thumb    - Determines if requested to return the item featured thumbnail.
	 * @return  array   $items         - Returns all items in the cart.
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
				 * @param string     $message  - Message.
				 * @param WC_Product $_product - Product data.
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
	 * @version 3.0.17
	 * @param   array   $removed_items - The removed cart contents passed.
	 * @param   boolean $show_thumb    - Determines if requested to return the item featured thumbnail.
	 * @return  array   $items         - Returns all removed items in the cart.
	 */
	public function get_removed_items( $removed_items = array(), $show_thumb = true ) {
		$items = array();

		foreach ( $removed_items as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
			}

			$_product = $cart_item['data'];

			$items[ $item_key ] = $this->get_item( $_product, $cart_item, $item_key, $show_thumb, true );

			// Move the quantity value to it's parent.
			$items[ $item_key ]['quantity'] = $items[ $item_key ]['quantity']['value'];
		}

		return $items;
	} // END get_removed_items()

	/**
	 * Removes all internal elements of an item that is not needed.
	 *
	 * @access private
	 * @param  array $cart_item - Before cart item data is modified.
	 * @return array $cart_item - Modified cart item data returned.
	 */
	private function prepare_item( $cart_item ) {
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
	 * @access public
	 * @return array
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
				'price'          => $cross_sell->get_price(),
				'regular_price'  => $cross_sell->get_regular_price(),
				'sale_price'     => $cross_sell->get_sale_price(),
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
	 * @access public
	 * @return array.
	 */
	public function get_shipping_details() {
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

			$rates = array();

			if ( 0 === $i ) {
				$package_key = 'default'; // Identifies the default package.
			}

			$details['packages'][ $package_key ] = array(
				/* translators: %d: shipping package number */
				'package_name'          => apply_filters( 'cocart_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping #%d', 'shipping packages', 'cart-rest-api-for-woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'cart-rest-api-for-woocommerce' ), $i, $package ),
				'rates'                 => $package['rates'],
				'package_details'       => implode( ', ', $product_names ),
				'index'                 => $i, // Shipping package number.
				'chosen_method'         => $chosen_method,
				'formatted_destination' => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
			);

			// Check that there are rates available for the package.
			if ( count( (array) $package['rates'] ) > 0 ) {
				// Return each rate.
				foreach ( $package['rates'] as $key => $method ) {
					$rates[ $key ] = array(
						'key'           => $key,
						'method_id'     => $method->get_method_id(),
						'instance_id'   => $method->instance_id,
						'label'         => $method->get_label(),
						'cost'          => $method->cost,
						'html'          => html_entity_decode( wp_strip_all_tags( wc_cart_totals_shipping_method_label( $method ) ) ),
						'taxes'         => $method->taxes,
						'chosen_method' => ( $chosen_method === $key ),
					);
				}
			}

			$details['packages'][ $package_key ]['rates'] = $rates;

			$package_key++; // Update package key for next inline if any.
		}

		return $details;
	} // END get_shipping_details()

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
	 * @access public
	 * @since  3.0.0
	 * @param  int            $product_id     - Contains the id of the product to add to the cart.
	 * @param  int|float      $quantity       - Contains the quantity of the item to add.
	 * @param  int            $variation_id   - ID of the variation being added to the cart.
	 * @param  array          $variation      - Attribute values.
	 * @param  array          $cart_item_data - Extra cart item data we want to pass into the item.
	 * @return string|boolean $item_key
	 */
	public function add_cart_item( $product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {
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
	 * Returns the customers details.
	 *
	 * @access protected
	 * @since  3.0.0
	 * @param  string $fields - The customer fields to return.
	 * @return array  Returns the customer details based on the field requested.
	 */
	protected function get_customer( $fields = 'billing' ) {
		$customer = $this->get_cart_instance()->get_customer();

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
	} // END get_customer()

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
	 * @version 3.0.17
	 * @param   WP_REST_Request $request - Full details about the request.
	 * @return  array - Returns the default cart response.
	 */
	protected function get_cart_template( $request = array() ) {
		return array(
			'cart_hash'      => $this->get_cart_instance()->get_cart_hash(),
			'cart_key'       => $this->get_cart_key( $request ),
			'currency'       => $this->get_store_currency(),
			'customer'       => array(
				'billing_address'  => $this->get_customer( 'billing' ),
				'shipping_address' => $this->get_customer( 'shipping' ),
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
				'subtotal'       => $this->prepare_money_response( $this->get_cart_instance()->get_subtotal(), wc_get_price_decimals() ),
				'subtotal_tax'   => $this->prepare_money_response( $this->get_cart_instance()->get_subtotal_tax(), wc_get_price_decimals() ),
				'fee_total'      => $this->prepare_money_response( $this->get_cart_instance()->get_fee_total(), wc_get_price_decimals() ),
				'fee_tax'        => $this->prepare_money_response( $this->get_cart_instance()->get_fee_tax(), wc_get_price_decimals() ),
				'discount_total' => $this->prepare_money_response( $this->get_cart_instance()->get_discount_total(), wc_get_price_decimals() ),
				'discount_tax'   => $this->prepare_money_response( $this->get_cart_instance()->get_discount_tax(), wc_get_price_decimals() ),
				'shipping_total' => $this->prepare_money_response( $this->get_cart_instance()->get_shipping_total(), wc_get_price_decimals() ),
				'shipping_tax'   => $this->prepare_money_response( $this->get_cart_instance()->get_shipping_tax(), wc_get_price_decimals() ),
				'total'          => $this->prepare_money_response( $this->get_cart_instance()->get_total(), wc_get_price_decimals() ),
				'total_tax'      => $this->prepare_money_response( $this->get_cart_instance()->get_total_tax(), wc_get_price_decimals() ),
			),
			'removed_items'  => array(),
			'cross_sells'    => $this->get_cross_sells(),
			'notices'        => $this->maybe_return_notices(),
		);
	} // END cart_template()

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
		 * @param string     $message - Message.
		 * @param WC_Product $product - Product data.
		 */
		$message = apply_filters( 'cocart_product_cannot_be_purchased_message', $message, $product );

		throw new CoCart_Data_Exception( 'cocart_cannot_be_purchased', $message, 403 );
	} // END throw_product_not_purchasable()

	/**
	 * Throws exception if the item key is not provided when either removing, updating or restoring the item.
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
	 * Get the schema for returning the cart, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  2.1.2
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Cart', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'items' => array(
					'description' => __( 'List of cart items.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'properties'  => array(
						'item_key'          => array(
							'description' => __( 'Unique identifier for the item within the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'product_id'        => array(
							'description' => __( 'Unique identifier for the product or variation ID.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'variation'         => array(
							'description' => __( 'Chosen attributes (for variations).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'attribute' => array(
										'description' => __( 'Variation attribute slug.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'value'     => array(
										'description' => __( 'Variation attribute value.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
						),
						'quantity'          => array(
							'description' => __( 'Quantity of this item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'float',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_tax_data'     => array(
							'description' => '',
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'subtotal' => array(
										'description' => __( 'Line subtotal tax data.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'integer',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total'    => array(
										'description' => __( 'Line total tax data.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'integer',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
						),
						'line_subtotal'     => array(
							'description' => __( 'Line subtotal (the price of the product before coupon discounts have been applied).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_subtotal_tax' => array(
							'description' => __( 'Line subtotal tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_total'        => array(
							'description' => __( 'Line total (the price of the product after coupon discounts have been applied).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_tax'          => array(
							'description' => __( 'Line total tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'product_name'      => array(
							'description' => __( 'Product name.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => ( 'view' ),
							'readonly'    => true,
						),
						'product_price'     => array(
							'description' => __( 'Current product price.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
					'readonly'    => true,
				),
			),
		);

		$schema['properties'] = apply_filters( 'cocart_cart_schema', $schema['properties'] );

		return $schema;
	} // END get_item_schema()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @return  array $params
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description' => __( 'Unique identifier for the cart or customer.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
			),
			'thumb'    => array(
				'description' => __( 'Returns the URL of the featured product image for each item in cart.', 'cart-rest-api-for-woocommerce' ),
				'default'     => true,
				'type'        => 'boolean',
			),
			'default'  => array(
				'description' => __( 'Return the default cart data if set to true.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
