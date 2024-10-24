<?php
/**
 * REST API: CoCart_REST_Cart_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Cart_V2_Controller', 'CoCart_Cart_V2_Controller' );

/**
 * Main cart controller that gets the requested cart in session
 * containing customers information, items added,
 * shipping options (if any), totals and more. (API v2)
 *
 * This REST API controller handles the request to get the cart
 * via "cocart/v2/cart" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_API_Controller
 */
class CoCart_REST_Cart_V2_Controller extends CoCart_API_Controller {

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
	 * Schema.
	 *
	 * @var array
	 */
	protected $schema = array();

	/**
	 * Register the routes for cart.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	} // END register_routes()

	/**
	 * Gets the cart instance so we only call it once in the API.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return WC_Cart The cart object.
	 */
	public function get_cart_instance() {
		$cart = WC()->cart;

		if ( ! $cart || ! $cart instanceof \WC_Cart ) {
			throw new CoCart_Data_Exception( 'cocart_cart_error', esc_html__( 'Unable to retrieve cart.', 'cart-rest-api-for-woocommerce' ), 500 );
		}

		return $cart;
	} // END get_cart_instance()

	/**
	 * Return a cart item from the cart.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @param string $item_id   The item we are looking up in the cart.
	 * @param string $condition Default is 'add', other conditions are: container, update, remove, restore.
	 *
	 * @return array $item Returns details of the item in the cart if it exists.
	 */
	public function get_cart_item( $item_id, $condition = 'add' ) {
		$item = isset( $this->get_cart_instance()->cart_contents[ $item_id ] ) ? $this->get_cart_instance()->cart_contents[ $item_id ] : array();

		/**
		 * Filters the cart item before it is returned.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param array  $item      Details of the item in the cart if it exists.
		 * @param string $condition Condition of item. Default: "add", Option: "add", "remove", "restore", "update".
		 */
		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // EMD get_cart_item()

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
	 * Get cart.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @deprecated 3.0.0 No longer use `$cart_item_key` parameter. Left for declaration compatibility.
	 *
	 * @param WP_REST_Request $request       The request object.
	 * @param string          $cart_item_key Originally the cart item key.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function get_cart( $request = array(), $cart_item_key = null ) {
		$show_raw      = ! empty( $request['raw'] ) ? $request['raw'] : false; // Internal parameter request.
		$cart_contents = ! $this->is_completely_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

		// Return cart contents raw if requested.
		if ( $show_raw ) {
			return $cart_contents;
		}

		/**
		 * Filter allows you to modify the cart contents before it calculate totals.
		 *
		 * WARNING: Unsetting any default data will cause the API to fail. Only use this filter if really necessary.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @hooked: check_cart_validity - 0
		 * @hooked: check_cart_item_stock - 10
		 * @hooked: check_cart_coupons - 15
		 *
		 * @param array           $cart_contents The cart contents.
		 * @param WC_Cart         $cart          The cart object.
		 * @param WP_REST_Request $request       The request object.
		 */
		$cart_contents = apply_filters( 'cocart_before_get_cart', $cart_contents, $this->get_cart_instance(), $request );

		// Ensures the cart totals are calculated before an API response is returned.
		$this->calculate_totals();

		/**
		 * Filter allows you to modify the cart contents after it has calculated totals.
		 *
		 * WARNING: Unsetting any default data will cause the API to fail. Only use this filter if really necessary.
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @param array           $cart_contents The cart contents.
		 * @param WC_Cart         $cart          The cart object.
		 * @param WP_REST_Request $request       The request object.
		 */
		$cart_contents = apply_filters( 'cocart_after_get_cart', $cart_contents, $this->get_cart_instance(), $request );

		$cart_contents = $this->return_cart_contents( $request, $cart_contents );

		return CoCart_Response::get_response( $cart_contents, $this->namespace, $this->rest_base );
	} // END get_cart()

	/**
	 * Return cart contents.
	 *
	 * @access public
	 *
	 * @since 2.0.0 Introduced.
	 *
	 * @deprecated 3.0.0 No longer use `$cart_item_key` and `$from_session` parameters. Left for declaration compatibility.
	 *
	 * @param WP_REST_Request $request       The request object.
	 * @param array           $cart_contents Cart content.
	 * @param array           $cart_item_key Originally the cart item key.
	 * @param bool            $from_session  Identifies if the cart is called from a session.
	 *
	 * @return array $cart Returns cart contents.
	 */
	public function return_cart_contents( $request = array(), $cart_contents = array(), $cart_item_key = null, $from_session = false ) {
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		/**
		 * Return the default cart data if set to true.
		 *
		 * @since 3.0.0 Introduced.
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
			 * @since 3.0.0 Introduced.
			 */
			return apply_filters( 'cocart_empty_cart', $cart_template );
		}

		$cart_instance = $this->get_cart_instance();

		// Defines an empty cart template.
		$cart = array();

		if ( array_key_exists( 'coupons', $cart_template ) ) {
			// Returns each coupon applied and coupon total applied if store has coupons enabled.
			$coupons = CoCart_Utilities_Cart_Helpers::are_coupons_enabled() ? $cart_instance->get_applied_coupons() : array();

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $code ) {
					$coupon = new WC_Coupon( $code );

					$cart['coupons'][] = array(
						'coupon'        => wc_format_coupon_code( wp_unslash( $code ) ),
						'label'         => esc_attr( wc_cart_totals_coupon_label( $code, false ) ),
						'discount_type' => $coupon->get_discount_type(),
						'saving'        => $this->coupon_html( $code, false ),
						'saving_html'   => $this->coupon_html( $code ),
					);
				}
			}
		}

		if ( array_key_exists( 'taxes', $cart_template ) ) {
			// Return calculated tax based on store settings and customer details.
			if ( wc_tax_enabled() && ! $cart_instance->display_prices_including_tax() ) {
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
					$cart['taxes'] = $this->get_tax_lines( $cart_instance );
				} else {
					$cart['taxes'] = array(
						'label' => esc_html( WC()->countries->tax_or_vat() ) . $estimated_text,
						'total' => apply_filters( 'cocart_cart_totals_taxes_total', cocart_prepare_money_response( $cart_instance->get_taxes_total(), wc_get_price_decimals() ) ),
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

		/**
		 * Filters the cart contents before it is returned.
		 *
		 * @since 3.0.0 Introduced.
		 * @since 4.1.0 Added the request object and the cart object as parameters.
		 *
		 * @deprecated 4.1.0 No longer use `$from_session` parameter.
		 *
		 * @param array           $cart          The cart before it's returned.
		 * @param WP_REST_Request $request       The request object.
		 * @param object          $cart_instance The cart object.
		 */
		$cart = apply_filters( 'cocart_cart', $cart, $request, $cart_instance );

		return $cart;
	} // END return_cart_contents()

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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
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
	 * @param WC_Product $product  The product object.
	 *
	 * @return int|float|\WP_Error
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
	 * @access protected
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
	 * @param WC_Product $product   The product object.
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_variation_id_from_variation_data()

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
	 * @param WC_Product $product The product object.
	 *
	 * @return array $attributes Product attributes.
	 */
	protected function get_variable_product_attributes( $product ) {
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
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @deprecated 3.0.0 `$variation_id` parameter is no longer used.
	 *
	 * @param int             $product_id   The product ID.
	 * @param int|float       $quantity     The item quantity.
	 * @param null            $variation_id The variation ID.
	 * @param array           $variation    The variation attributes.
	 * @param array           $item_data    Extra cart item data we want to pass into the item.
	 * @param string          $product_type The product type.
	 * @param WP_REST_Request $request      The request object.
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

			// If variables are not valid then return error response.
			if ( is_wp_error( $variation ) ) {
				return $variation;
			}

			/**
			 * Filters add to cart validation.
			 *
			 * @since 2.1.2 Introduced.
			 * @since 3.1.0 Added the request object as parameter.
			 *
			 * @param bool            true          Default is true to allow the product to pass validation.
			 * @param int             $product_id   The product ID.
			 * @param int             $quantity     The item quantity.
			 * @param int             $variation_id The variation ID.
			 * @param array           $variation    The variation attributes.
			 * @param object          $item_data    Extra cart item data we want to pass into the item.
			 * @param string          $product_type The product type.
			 * @param WP_REST_Request $request      The request object.
			 */
			$passed_validation = apply_filters( 'cocart_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation, $item_data, $product_type, $request );

			// If validation returned an error return error response.
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
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_product_failed_validation_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_failed_validation', $message, 400 );
			}

			/**
			 * Filter allows other plugins to add their own cart item data.
			 *
			 * @since 2.1.2 Introduced.
			 * @since 3.1.0 Added the request object as parameter.
			 *
			 * @param array           $item_data    Extra cart item data we want to pass into the item.
			 * @param int             $product_id   The product ID.
			 * @param null            $variation_id The variation ID.
			 * @param int|float       $quantity     The item quantity.
			 * @param string          $product_type The product type.
			 * @param WP_REST_Request $request      The request object.
			 */
			$item_data = (array) apply_filters( 'cocart_add_cart_item_data', $item_data, $product_id, $variation_id, $quantity, $product_type, $request );

			// Generate an ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $item_data );

			// Find the cart item key in the existing cart.
			$item_key = $this->find_product_in_cart( $item_key );

			/**
			 * Filters the quantity for specified products.
			 *
			 * @since 2.1.2 Introduced.
			 *
			 * @param int|float $quantity     The original quantity of the item.
			 * @param int       $product_id   The product ID.
			 * @param int       $variation_id The variation ID.
			 * @param array     $variation    The variation data.
			 * @param array     $item_data    The cart item data.
			 */
			$quantity = apply_filters( 'cocart_add_to_cart_quantity', $quantity, $product_id, $variation_id, $variation, $item_data );

			// Validates the item quantity.
			$quantity = $this->validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $item_key );

			// If product validation returned an error return error response.
			if ( is_wp_error( $quantity ) ) {
				return $quantity;
			}

			// Validates the item before adding to cart.
			$is_valid = $this->validate_add_to_cart( $product, $quantity );

			// If product validation returned an error return error response.
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
				$message = sprintf(
					/* translators: 1: Quantity Requested, 2: Product Name 3: Quantity in Stock */
					__( 'You cannot add a quantity of (%1$s) for "%2$s" to the cart because there is not enough stock. - only (%3$s remaining)!', 'cart-rest-api-for-woocommerce' ),
					$quantity,
					$current_product->get_name(),
					wc_format_stock_quantity_for_display( $current_product->get_stock_quantity(), $current_product )
				);

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
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @deprecated 3.1.0 Use `cocart_get_store_currency()` instead.
	 *
	 * @see cocart_get_store_currency()
	 *
	 * @return array
	 */
	public function get_store_currency() {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::get_store_currency', '3.1', 'cocart_get_store_currency' );

		return cocart_get_store_currency();
	} // END get_store_currency()

	/**
	 * Returns the cart key.
	 *
	 * @access public
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::get_cart_key()
	 *
	 * @return string Cart key.
	 */
	public function get_cart_key() {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::get_cart_key', '4.2.0', 'CoCart_Utilities_Cart_Helpers::get_cart_key' );

		return CoCart_Utilities_Cart_Helpers::get_cart_key();
	} // END get_cart_key()

	/**
	 * Get tax lines from the cart and format to match schema.
	 *
	 * @access protected
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Tax lines.
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
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @deprecated 3.1.0 Use `cocart_prepare_money_response()` function instead.
	 *
	 * @see cocart_prepare_money_response()
	 *
	 * @param string|float $amount        Monetary amount with decimals.
	 * @param int          $decimals      Number of decimals the amount is formatted with.
	 * @param int          $rounding_mode Defaults to the PHP_ROUND_HALF_UP constant.
	 *
	 * @return string The new amount.
	 */
	public function prepare_money_response( $amount, $decimals = 2, $rounding_mode = PHP_ROUND_HALF_UP ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::prepare_money_response', '3.1', 'cocart_prepare_money_response' );

		return cocart_prepare_money_response( $amount, $decimals, $rounding_mode );
	} // END prepare_money_response()

	/**
	 * Format variation data, for example convert slugs such as attribute_pa_size to Size.
	 *
	 * @access protected
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param array      $variation_data Array of data from the cart.
	 * @param WC_Product $product        The product object.
	 *
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
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @see cocart_prepare_money_response()
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
					'fee'  => cocart_prepare_money_response( $this->fee_html( $cart, $fee ), wc_get_price_decimals() ),
				);
			}
		}

		return $fees;
	} // END get_fees()

	/**
	 * Get coupon in HTML.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @see cocart_prepare_money_response()
	 *
	 * @param string|WC_Coupon $coupon    Coupon data or code.
	 * @param boolean          $formatted Formats the saving amount.
	 *
	 * @return string Returns coupon amount.
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
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param object $cart Cart instance.
	 * @param object $fee  Fee data.
	 *
	 * @return string Returns the fee value.
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
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return WC_Product $product Returns a product object if purchasable.
	 */
	public function validate_product_for_cart( $product ) {
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
	 * Validates item quantity and checks if sold individually.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @deprecated 4.0.0 Removed $cart_id parameter as it is the same as $item_key.
	 *
	 * @param WC_Product $product      The product object.
	 * @param int|float  $quantity     The quantity to validate.
	 * @param int        $product_id   The product ID.
	 * @param int        $variation_id The variation ID.
	 * @param array      $item_data    The cart item data.
	 * @param string     $item_key     Generated ID based on the product information when added to the cart.
	 *
	 * @return float $quantity The quantity returned.
	 */
	public function validate_item_quantity( $product, $quantity, $product_id, $variation_id, $item_data, $item_key ) {
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

				$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $item_key && $cart_contents[ $item_key ]['quantity'] > 0, $product_id, $variation_id, $item_data, $item_key );

				if ( $found_in_cart ) {
					$message = sprintf(
						/* translators: %s: Product Name */
						__( "You cannot add another '%s' to your cart.", 'cart-rest-api-for-woocommerce' ),
						$product->get_name()
					);

					/**
					 * Filters message about product not being allowed to add another.
					 *
					 * @since 3.0.0 Introduced.
					 *
					 * @param string     $message Message.
					 * @param WC_Product $product The product object.
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
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WC_Product $product  The product object.
	 * @param int|float  $quantity Quantity of product to validate availability.
	 */
	public function validate_add_to_cart( $product, $quantity ) {
		try {
			// Product is purchasable check.
			if ( ! $product->is_purchasable() ) {
				CoCart_Utilities_Cart_Helpers::throw_product_not_purchasable( $product );
			}

			// Stock check - only check if we're managing stock and backorders are not allowed.
			if ( ! $product->is_in_stock() ) {
				$message = sprintf(
					/* translators: %s: Product name */
					__( 'You cannot add "%s" to the cart because the product is out of stock.', 'cart-rest-api-for-woocommerce' ),
					$product->get_name()
				);

				/**
				 * Filters message about product is out of stock.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_product_is_out_of_stock_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_product_out_of_stock', $message, 404 );
			}

			if ( ! $product->has_enough_stock( $quantity ) ) {
				$stock_quantity = wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product );

				if ( $stock_quantity > 0 ) {
					$message = sprintf(
						/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
						__( 'You cannot add the amount of %1$s for "%2$s" to the cart because there is not enough stock, only (%3$s) remaining.', 'cart-rest-api-for-woocommerce' ),
						$quantity,
						$product->get_name(),
						$stock_quantity
					);
				} else {
					$message = sprintf(
						/* translators: 1: Product Name */
						__( 'You cannot add %1$s to the cart as it is no longer in stock.', 'cart-rest-api-for-woocommerce' ),
						$product->get_name()
					);
				}

				/**
				 * Filters message about product not having enough stock.
				 *
				 * @since 3.1.0 Introduced.
				 *
				 * @param string     $message        Message.
				 * @param WC_Product $product        The product object.
				 * @param int        $stock_quantity Quantity remaining.
				 */
				$message = apply_filters( 'cocart_product_not_enough_stock_message', $message, $product, $stock_quantity );

				throw new CoCart_Data_Exception( 'cocart_not_enough_in_stock', $message, 404 );
			}

			// Stock check - this time accounting for whats already in-cart and look up what's reserved.
			if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
				$qty_remaining = CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product( $product );
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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_add_to_cart()

	/**
	 * Filters additional requested data.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return $request
	 */
	public function filter_request_data( $request ) {
		/**
		 * Filters additional requested data.
		 *
		 * @since 3.0.0 Introduced.
		 */
		return apply_filters( 'cocart_filter_request_data', $request );
	} // END filter_request_data()

	/**
	 * Get the main product slug even if the product type is a variation.
	 *
	 * @access public
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return string The product slug.
	 */
	public function get_product_slug( $product ) {
		$product_type = $product->get_type();

		if ( 'variation' === $product_type ) {
			$product = wc_get_product( $product->get_parent_id() );

			$product_slug = $product->get_slug();
		} else {
			$product_slug = $product->get_slug();
		}

		return $product_slug;
	} // END get_product_slug()

	/**
	 * Get a single item from the cart and present the data required.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WC_Product $_product     The product object.
	 * @param array      $cart_item    The cart item data.
	 * @param string     $item_key     The item key generated based on the details of the item.
	 * @param boolean    $show_thumb   Determines if requested to return the item featured thumbnail.
	 * @param boolean    $removed_item Determines if the item in the cart is removed.
	 *
	 * @return array $item Returns the item prepared for the cart response.
	 */
	public function get_item( $_product, $cart_item = array(), $item_key = '', $show_thumb = true, $removed_item = false ) {
		$tax_display_mode = CoCart_Utilities_Product_Helpers::get_tax_display_mode();
		$price_function   = CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode( $tax_display_mode );

		$item = array(
			'item_key'       => $item_key,
			'id'             => $_product->get_id(),
			/**
			 * Filter allows the product name of the item to change.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string     $product_name Product name.
			 * @param WC_Product $_product     The product object.
			 * @param array      $cart_item    The cart item data.
			 * @param string     $item_key     The item key generated based on the details of the item.
			 */
			'name'           => apply_filters( 'cocart_cart_item_name', $_product->get_name(), $_product, $cart_item, $item_key ),
			/**
			 * Filter allows the product title of the item to change.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string     $product_title Product title.
			 * @param WC_Product $_product      The product object.
			 * @param array      $cart_item     The cart item data.
			 * @param string     $item_key      The item key generated based on the details of the item.
			 */
			'title'          => apply_filters( 'cocart_cart_item_title', $_product->get_title(), $_product, $cart_item, $item_key ),
			/**
			 * Filter allows the price of the item to change.
			 *
			 * Warning: This filter does not represent the true value that totals will be calculated on.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string $product_price Product price.
			 * @param array  $cart_item     The cart item data.
			 * @param string $item_key      The item key generated based on the details of the item.
			 */
			'price'          => apply_filters( 'cocart_cart_item_price', cocart_prepare_money_response( $price_function( $_product ), wc_get_price_decimals() ), $cart_item, $item_key ),
			'quantity'       => array(
				/**
				 * Filter allows the quantity of the item to change.
				 *
				 * Warning: This filter does not represent the quantity of the item that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_quantity Item quantity.
				 * @param string $item_key      The item key generated based on the details of the item.
				 * @param array  $cart_item     The cart item data.
				 */
				'value'        => apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item ),
				'min_purchase' => $_product->get_min_purchase_quantity(),
				'max_purchase' => $_product->get_max_purchase_quantity(),
			),
			'totals'         => array(
				/**
				 * Filter allows the subtotal of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_subtotal Item subtotal.
				 * @param array  $cart_item     The cart item data.
				 * @param string $item_key      The item key generated based on the details of the item.
				 */
				'subtotal'     => apply_filters( 'cocart_cart_item_subtotal', cocart_prepare_money_response( $cart_item['line_subtotal'], wc_get_price_decimals() ), $cart_item, $item_key ),
				/**
				 * Filter allows the subtotal tax of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_subtotal_tax Item subtotal tax.
				 * @param array  $cart_item         The cart item data.
				 * @param string $item_key          The item key generated based on the details of the item.
				 */
				'subtotal_tax' => apply_filters( 'cocart_cart_item_subtotal_tax', $cart_item['line_subtotal_tax'], $cart_item, $item_key ),
				/**
				 * Filter allows the total of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_total Item total.
				 * @param array  $cart_item  The cart item data.
				 * @param string $item_key   The item key generated based on the details of the item.
				 */
				'total'        => apply_filters( 'cocart_cart_item_total', $cart_item['line_total'], $cart_item, $item_key ),
				/**
				 * Filter allows the tax of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_tax  Item tax.
				 * @param array  $cart_item The cart item data.
				 * @param string $item_key  The item key generated based on the details of the item.
				 */
				'tax'          => apply_filters( 'cocart_cart_item_tax', $cart_item['line_tax'], $cart_item, $item_key ),
			),
			'slug'           => $this->get_product_slug( $_product ),
			'meta'           => array(
				'product_type' => $_product->get_type(),
				'sku'          => $_product->get_sku(),
				'dimensions'   => array(),
				'weight'       => $_product->has_weight() ? (string) wc_get_weight( $_product->get_weight() * (int) $cart_item['quantity'], get_option( 'woocommerce_weight_unit' ) ) : '0.0',
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
			/**
			 * Filter allows you to alter the remaining cart item data.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param array  $cart_item The cart item data.
			 * @param string $item_key  Generated ID based on the product information when added to the cart.
			 */
			$item['cart_item_data'] = apply_filters( 'cocart_cart_item_data', $cart_item, $item_key );
		}

		// If thumbnail is requested then add it to each item in cart.
		if ( $show_thumb ) {
			$thumbnail_id = ! empty( $_product->get_image_id() ) ? $_product->get_image_id() : get_option( 'woocommerce_placeholder_image', 0 );

			/**
			 * Filters the item thumbnail ID.
			 *
			 * @since 2.0.0 Introduced.
			 * @since 3.0.0 Added $removed_item parameter.
			 *
			 * @param int    $thumbnail_id Product thumbnail ID.
			 * @param array  $cart_item    Cart item.
			 * @param string $item_key     Item key.
			 * @param bool   $removed_item Determines if the item in the cart is removed.
			 */
			$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $thumbnail_id, $cart_item, $item_key, $removed_item );

			/**
			 * Filters the thumbnail size of the product image.
			 *
			 * @since 2.0.0 Introduced.
			 * @since 3.0.0 Added $removed_item parameter.
			 *
			 * @param bool $removed_item Determines if the item in the cart is removed.
			 */
			$thumbnail_size = apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail', $removed_item );

			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
			$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';

			/**
			 * Filters the source of the product thumbnail.
			 *
			 * @since 2.1.0 Introduced.
			 * @since 3.0.0 Added parameter $removed_item.
			 *
			 * @param string $thumbnail_src URL of the product thumbnail.
			 * @param array  $cart_item     Cart item.
			 * @param string $item_key      Item key.
			 * @param bool   $removed_item  Determines if the item in the cart is removed.
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
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param array           $cart_contents The cart contents passed.
	 * @param WP_REST_Request $request       The request object.
	 * @param boolean         $show_thumb    Determines if requested to return the item featured thumbnail.
	 *
	 * @return array $items Returns all items in the cart.
	 */
	public function get_items( $cart_contents = array(), $show_thumb = true ) {
		$items = array();

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : ( ! empty( $cart_item['product_id'] ) ? $cart_item['product_id'] : 0 ) );
			}

			$product = $cart_item['data'];

			/**
			 * Filter allows you to alter the item product data returned.
			 *
			 * @since 2.0.0 Introduced.
			 *
			 * @param WC_Product $product   The product object.
			 * @param array      $cart_item The cart item data.
			 * @param string     $item_key  The item key currently looped.
			 */
			$product = apply_filters( 'cocart_item_product', $product, $cart_item, $item_key );

			$items[ $item_key ] = $this->get_item( $product, $cart_item, $item_key, $show_thumb );

			/**
			 * Filter allows additional data to be returned for a specific item in cart.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param array      $items     Array of items in the cart.
			 * @param string     $item_key  The item key currently looped.
			 * @param array      $cart_item The cart item data.
			 * @param WC_Product $product   The product object.
			 */
			$items = apply_filters( 'cocart_cart_items', $items, $item_key, $cart_item, $product );
		}

		return $items;
	} // END get_items()

	/**
	 * Gets the cart removed items.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param array   $removed_items The removed cart contents passed.
	 * @param boolean $show_thumb    Determines if requested to return the item featured thumbnail.
	 *
	 * @return array $items Returns all removed items in the cart.
	 */
	public function get_removed_items( $removed_items = array(), $show_thumb = true ) {
		$items = array();

		foreach ( $removed_items as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : ( ! empty( $cart_item['product_id'] ) ? $cart_item['product_id'] : 0 ) );
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
	 *
	 * @since 3.0.0 Introduced.
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
	 * Returns cross sells based on the items in the cart.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Prices now return as monetary values.
	 *
	 * @return array $cross_sells Returns cross sells.
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

		/**
		 * Filters the cross sell items.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param WP_REST_Request $request The request object.
		 */
		$cross_sells = apply_filters( 'cocart_cross_sells', $cross_sells );

		return $cross_sells;
	} // END get_cross_sells()

	/**
	 * Returns shipping details.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @return array Shipping details.
	 */
	public function get_shipping_details() {
		if ( ! CoCart_Utilities_Cart_Helpers::is_shipping_enabled() ) {
			return array();
		}

		// Get shipping packages.
		$available_packages = WC()->shipping->get_packages();

		$details = array(
			'total_packages'          => count( (array) $available_packages ),
			'show_package_details'    => count( (array) $available_packages ) > 1,
			'has_calculated_shipping' => WC()->customer->has_calculated_shipping(),
			'packages'                => array(),
		);

		$packages      = array();
		$package_key   = 1;
		$chosen_method = ''; // Leave blank until a method has been selected.

		foreach ( $available_packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
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

			if ( 0 === $i ) {
				$package_key = 'default'; // Identifies the default package.
			}

			// Check that there are rates available for the package.
			if ( count( (array) $package['rates'] ) > 0 ) {
				$shipping_name = ( ( $i + 1 ) > 1 ) ? sprintf(
					/* translators: %d: shipping package number */
					_x( 'Shipping #%d', 'shipping packages', 'cart-rest-api-for-woocommerce' ),
					( $i + 1 )
				) : _x( 'Shipping', 'shipping packages', 'cart-rest-api-for-woocommerce' );

				$packages[ $package_key ] = array(
					/**
					 * Filters the package name for the shipping method.
					 *
					 * @since 3.0.0 Introduced.
					 *
					 * @param string $shipping_name Shipping name.
					 * @param int    $i
					 * @param array  $package
					 */
					'package_name'          => apply_filters( 'cocart_shipping_package_name', $shipping_name, $i, $package ),
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

				$packages[ $package_key ]['rates'] = $rates;
			}

			++$package_key; // Update package key for next inline if any.
		}

		/**
		 * Filter allows you to alter the shipping packages returned.
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @param array   $packages      Available shipping packages.
		 * @param array   $chosen_method Chosen shipping method.
		 * @param WC_Cart                The cart object.
		 */
		$details['packages'] = apply_filters( 'cocart_available_shipping_packages', $packages, $chosen_method, $this->get_cart_instance() );

		return $details;
	} // END get_shipping_details()

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

		$notices = $notice_count > 0 ? $this->print_notices( $all_notices ) : array();

		return $notices;
	} // END maybe_return_notices()

	/**
	 * Returns messages and errors which are stored in the session, then clears them.
	 *
	 * @access protected
	 *
	 * @uses cocart_get_notice_types()
	 *
	 * @param array $all_notices Return notices already fetched.
	 *
	 * @return array
	 */
	protected function print_notices( $all_notices = array() ) {
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

	/**
	 * Adds item to cart internally rather than WC.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param int        $product_id     The product ID.
	 * @param int        $quantity       The item quantity.
	 * @param int        $variation_id   The variation ID.
	 * @param array      $variation      The variation attributes.
	 * @param array      $cart_item_data Extra cart item data we want to pass into the item.
	 * @param WC_Product $product_data   The product object.
	 *
	 * @return string|boolean $item_key Cart item key or false if error.
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

			/**
			 * Fires after item has been added to cart.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string $item_key       Generated ID based on the product information provided.
			 * @param int    $product_id     The product ID.
			 * @param int    $quantity       The item quantity.
			 * @param int    $variation_id   The variation ID.
			 * @param array  $variation      The variation attributes.
			 * @param array  $cart_item_data Extra cart item data we want to pass into the item.
			 */
			do_action( 'cocart_add_to_cart', $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			return $item_key;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_cart_item()

	/**
	 * Cache cart item.
	 *
	 * @see CoCart_Cart_Cache::set_cached_item()
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.1.0 No longer used here.
	 *
	 * @param array $item_added_to_cart Cart item to cache.
	 */
	public function cache_cart_item( $item_added_to_cart ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::cache_cart_item', '4.1' );

		$item_key = $item_added_to_cart['key'];

		CoCart_Cart_Cache::set_cached_item( $item_key, $item_added_to_cart );
	} // END cache_cart_item()

	/**
	 * Returns the customers details from fields.
	 *
	 * @access protected
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::get_customer_fields()
	 *
	 * @param string           $fields   The customer fields to return.
	 * @param WC_Customer|null $customer The customer object or nothing.
	 *
	 * @return array Returns the customer details based on the field requested.
	 */
	protected function get_customer_fields( $fields = 'billing', $customer = '' ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::get_customer_fields', '4.2.0', 'CoCart_Utilities_Cart_Helpers::get_customer_fields' );

		return CoCart_Utilities_Cart_Helpers::get_customer_fields( $fields, $customer );
	} // END get_customer_fields()

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
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::convert_notices_to_exceptions()
	 *
	 * @param string $error_code Error code for the thrown exceptions.
	 */
	public static function convert_notices_to_exceptions( $error_code = 'unknown_server_error' ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::convert_notices_to_exceptions', '4.2.0', 'CoCart_Utilities_Cart_Helpers::convert_notices_to_exceptions' );

		CoCart_Utilities_Cart_Helpers::convert_notices_to_exceptions( $error_code );
	} // END convert_notices_to_exceptions()

	/**
	 * Get cart template.
	 *
	 * Used as a base even if the cart is empty along with
	 * customer information should the user be logged in.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @access protected
	 *
	 * @since 3.0.3 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array Returns the default cart response.
	 */
	protected function get_cart_template( $request = array() ) {
		$fields = ! empty( $request['fields'] ) ? $request['fields'] : '';

		if ( ! empty( $fields ) ) {
			return self::get_cart_template_limited( $request );
		}

		// Other Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		return array(
			'cart_hash'      => ! empty( $this->get_cart_instance()->get_cart_hash() ) ? $this->get_cart_instance()->get_cart_hash() : __( 'No items in cart so no hash', 'cart-rest-api-for-woocommerce' ),
			'cart_key'       => CoCart_Utilities_Cart_Helpers::get_cart_key(),
			'currency'       => cocart_get_store_currency(),
			'customer'       => array(
				'billing_address'  => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_cart_instance()->get_customer() ),
				'shipping_address' => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_cart_instance()->get_customer() ),
			),
			'items'          => array(),
			'item_count'     => $this->get_cart_instance()->get_cart_contents_count(),
			'items_weight'   => (string) wc_get_weight( $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) ),
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
				'total'          => cocart_prepare_money_response( $this->get_cart_instance()->get_total( 'edit' ), wc_get_price_decimals() ),
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
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array $template Returns requested cart response.
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
					$template['cart_key'] = CoCart_Utilities_Cart_Helpers::get_cart_key();
					break;
				case 'currency':
					$template['currency'] = cocart_get_store_currency();
					break;
				case 'customer':
					if ( ! empty( $child_field ) ) {
						if ( 'billing_address' === $child_field ) {
							$template['customer']['billing_address'] = CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_cart_instance()->get_customer() );
						}
						if ( 'shipping_address' === $child_field ) {
							$template['customer']['shipping_address'] = CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_cart_instance()->get_customer() );
						}
					} else {
						$template['customer'] = array(
							'billing_address'  => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_cart_instance()->get_customer() ),
							'shipping_address' => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_cart_instance()->get_customer() ),
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
					$template['items_weight'] = (string) wc_get_weight( $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) );
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
								$template['totals']['total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_total( 'edit' ), wc_get_price_decimals() );
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
							'total'          => cocart_prepare_money_response( $this->get_cart_instance()->get_total( 'edit' ), wc_get_price_decimals() ),
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
	 *
	 * @since 3.0.4 Introduced.
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::throw_product_not_purchasable()
	 *
	 * @param WC_Product $product The product object.
	 */
	protected function throw_product_not_purchasable( $product ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::throw_product_not_purchasable', '4.2.0', 'CoCart_Utilities_Cart_Helpers::throw_product_not_purchasable' );

		CoCart_Utilities_Cart_Helpers::throw_product_not_purchasable( $product );
	} // END throw_product_not_purchasable()

	/**
	 * Gets the quantity of a product across line items.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return int Quantity of the product.
	 */
	protected function get_product_quantity_in_cart( $product ) {
		$product_quantities = $this->get_cart_instance()->get_cart_item_quantities();
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
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product()
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return int Remaining stock.
	 */
	protected function get_remaining_stock_for_product( $product ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::get_remaining_stock_for_product', '4.2.0', 'CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product' );

		return CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product( $product );
	} // END get_remaining_stock_for_product()

	/**
	 * Throws exception if the item key is not provided when either removing, updating or restoring the item.
	 *
	 * @throws CoCart_Data_Exception If an error notice is detected, Exception is thrown.
	 *
	 * @access protected
	 *
	 * @since 3.0.17 Introduced.
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Cart_Helpers::throw_missing_item_key()
	 *
	 * @param string $item_key Generated ID based on the product information when added to the cart.
	 * @param string $status   Status of which we are checking the item key.
	 *
	 * @return string $item_key Generated ID based on the product information when added to the cart.
	 */
	protected function throw_missing_item_key( $item_key, $status ) {
		cocart_deprecated_function( 'CoCart_REST_Cart_V2_Controller::throw_missing_item_key', '4.2.0', 'CoCart_Utilities_Cart_Helpers::throw_missing_item_key' );

		return CoCart_Utilities_Cart_Helpers::throw_missing_item_key( $item_key, $status );
	} // END throw_missing_item_key()

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
	 * Retrieves the item schema for returning the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_cart',
			'type'       => 'object',
			'properties' => array(
				'cart_hash'      => array(
					'description' => __( 'A Unique hash key of the carts contents.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_key'       => array(
					'description' => __( 'The cart key identifying the cart in session.', 'cart-rest-api-for-woocommerce' ),
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
						'currency_symbol_pos'         => array(
							'description' => __( 'The currency symbol position to which the currency needs to return for the prices.', 'cart-rest-api-for-woocommerce' ),
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
										'type'        => 'string',
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
					'type'        => 'string',
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
										'type'        => 'string',
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
						'info'    => array(
							'description' => __( 'Notices for informational actions.', 'cart-rest-api-for-woocommerce' ),
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
		 * @deprecated 3.1.0 Use `cocart_cart_items_schema` filter instead.
		 *
		 * @see cocart_cart_items_schema
		 */
		cocart_do_deprecated_filter( 'cocart_cart_schema', '3.1.0', 'cocart_cart_items_schema', __( 'Changed for the purpose of not overriding default properties.', 'cart-rest-api-for-woocommerce' ), array( $this->schema['properties'] ) );

		/**
		 * Extend the cart schema properties for items.
		 *
		 * This filter allows you to extend the cart schema properties for items without removing any default properties.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$this->schema['properties']['items']['items']['properties'] += apply_filters( 'cocart_cart_items_schema', array() );

		return $this->schema;
	} // END get_public_item_schema()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
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
			'fields'   => array(
				'description'       => __( 'Specify each parent field you want to request separated by (,) in the response before the data is fetched.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'thumb'    => array(
				'description'       => __( 'True if you want to return the URL of the featured product image for each item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'default'           => true,
				'type'              => 'boolean',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'default'  => array(
				'description'       => __( 'Return the default cart data if set to true.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
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
