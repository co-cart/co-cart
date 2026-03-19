<?php
/**
 * Abstract: CoCart_REST_Cart_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   5.0.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Rest Cart Controller Class.
 *
 * NOTE THAT ONLY CODE RELEVANT FOR THE CART ENDPOINTS SHOULD BE INCLUDED INTO THIS CLASS.
 *
 * @since 5.0.0 Introduced.
 */
abstract class CoCart_REST_Cart_Controller {

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->get_path_regex();
	}

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart';
	}

	/**
	 * Permission callback checks if the cart was initialized.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return \WP_Error|bool Returns error if failed else true.
	 */
	public function check_cart_instance() {
		$cart_instance = $this->get_cart_instance();

		if ( ! is_wp_error( $cart_instance ) ) {
			return true;
		}

		return $cart_instance;
	} // END check_cart_instance()

	/**
	 * Gets the cart instance so we only call it once in the API.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return \WP_Error|\WC_Cart Error response or the cart object.
	 */
	public function get_cart_instance() {
		$cart = WC()->cart;

		if ( is_wp_error( $cart ) ) {
			return $cart;
		}

		try {
			if ( ! $cart || ! $cart instanceof \WC_Cart ) {
				throw new CoCart_Data_Exception( 'cocart_cart_error', esc_html__( 'Unable to retrieve cart.', 'cocart-core' ), 500 );
			}

			return $cart;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_cart_instance()

	/**
	 * Gets the cart contents.
	 *
	 * @access public
	 *
	 * @since 2.0.0 Introduced.
	 *
	 * @deprecated 5.0.0 No longer use `$request` and `$cart_item_key` parameters.
	 *
	 * @see CoCart_REST_Cart_Controller::is_completely_empty()
	 *
	 * @return array $cart_contents The cart contents.
	 */
	public function get_cart_contents() {
		$cart = $this->get_cart_instance();

		$cart_contents = ! $this->is_completely_empty() ? $cart->cart_contents : array();

		return $cart_contents;
	} // END get_cart_contents()

	/**
	 * Return a cart item from the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 3.0.0 Added filter to alter details of the item based on the condition of the request.
	 *
	 * @param string $item_id   The item we are looking up in the cart.
	 * @param string $condition Default is 'add', other conditions are: container, update, remove, restore.
	 *
	 * @return array $item Returns details of the item in the cart if it exists.
	 */
	public function get_cart_item( $item_id, $condition = 'add' ) {
		$cart_contents = $this->get_cart_contents();
		$item          = isset( $cart_contents[ $item_id ] ) ? $cart_contents[ $item_id ] : array();

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
		$cart = $this->get_cart_instance();
		return $callback ? array_filter( $cart->get_cart(), $callback ) : array_filter( $cart->get_cart() );
	} // END get_cart_items()

	/**
	 * Get hashes for items in the current cart. Useful for tracking changes.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_cart_hashes() {
		$cart = $this->get_cart_instance();

		return array(
			'line_items' => $cart->get_cart_hash(),
			'shipping'   => md5( wp_json_encode( array( $cart->shipping_methods, WC()->session->get( 'chosen_shipping_methods' ) ) ) ),
			'fees'       => md5( wp_json_encode( $cart->get_fees() ) ),
			'coupons'    => md5( wp_json_encode( $cart->get_applied_coupons() ) ),
			'taxes'      => md5( wp_json_encode( $cart->get_taxes() ) ),
		);
	} // END get_cart_hashes()

	/**
	 * Returns true if the cart is completely empty.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @see CoCart_REST_Cart_Controller::get_removed_cart_contents_count()
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
	 * Ensures the cart totals are calculated before an API response is returned.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Calculate shipping was removed here because it's called already by calculate_totals.
	 */
	public function calculate_totals() {
		$cart = $this->get_cart_instance();
		$cart->calculate_fees();
		$cart->calculate_totals();
	} // END calculate_totals()

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
	 * Filter data for add to cart requests.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array $request Add to cart request params.
	 *
	 * @return array Updated request array.
	 */
	protected function filter_request_data( $request ) {
		$request['quantity']       = rest_sanitize_quantity_arg( $request['quantity'] );
		$request['variation_id']   = 0;
		$request['container_item'] = false; // By default an item is individual not a container of many.

		$product = wc_get_product( $request['id'] );

		if ( $product->is_type( 'variation' ) ) {
			$request['id']           = $product->get_parent_id();
			$request['variation_id'] = $product->get_id();
		}

		// Set cart item data - maybe added by other plugins.
		$request['item_data'] = CoCart_Utilities_Cart_Helpers::set_cart_item_data( $request );

		// Validates if item is sold individually.
		if ( $product->is_sold_individually() ) {
			$request['quantity'] = CoCart_Utilities_Cart_Helpers::set_cart_item_quantity_sold_individually( $request );
		}

		// If the quantity parameter is an array then we assume they are a list of items bundled together.
		if ( is_array( $request['quantity'] ) ) {
			$request['container_item'] = true;
		}

		return $request;
	} // END filter_request_data()

	/**
	 * If variations are set, validate and format the values ready to add to the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array $request Add to cart request params.
	 *
	 * @return array Updated request array.
	 */
	protected function parse_variation_data( $request, $product ) {
		// Remove variation request if not needed.
		if ( ! $product->is_type( array( 'variation', 'variable' ) ) ) {
			$request['variation'] = array();
			return $request;
		}

		// Flatten data and format posted values.
		$variable_product_attributes = CoCart_Utilities_Cart_Helpers::get_variable_product_attributes( $product );
		$request['variation']        = $this->sanitize_variation_data( $request['variation'], $variable_product_attributes );

		// If we have a parent product, find the variation ID.
		if ( $product->is_type( 'variable' ) ) {
			$request['id'] = CoCart_Utilities_Product_Helpers::get_variation_id_from_variation_data( $request, $product );
		}

		$request = CoCart_Utilities_Cart_Helpers::validate_variable_product( $request, $product, $variable_product_attributes );

		return $request;
	} // END parse_variation_data()

	/**
	 * Format and sanitize variation data.
	 *
	 * Labels are converted to names (e.g. Size to pa_size), and values are cleaned.
	 *
	 * @access protected
	 *
	 * @since 4.3.30 Introduced.
	 * @since 5.0.0  Moved to cart abstract controller.
	 *
	 * @param array $variation_data              Key value pairs of attributes and values.
	 * @param array $variable_product_attributes Product attributes we're expecting.
	 *
	 * @return array Sanitized variation attribute data.
	 */
	protected function sanitize_variation_data( $variation_data, $variable_product_attributes ) {
		$return = array();

		foreach ( $variable_product_attributes as $attribute ) {
			if ( ! $attribute['is_variation'] ) {
				continue;
			}

			// Sanitized attribute (same as the product page) e.g. attribute_size.
			$variation_attribute_name = wc_variation_attribute_name( $attribute['name'] );
			if ( isset( $variation_data[ $variation_attribute_name ] ) ) {
				$return[ $variation_attribute_name ] =
					$attribute['is_taxonomy']
						?
						sanitize_title( $variation_data[ $variation_attribute_name ] )
						:
						html_entity_decode(
							wc_clean( $variation_data[ $variation_attribute_name ] ),
							ENT_QUOTES,
							get_bloginfo( 'charset' )
						);
				continue;
			}

			// Attribute labels e.g. Size.
			$attribute_label           = wc_attribute_label( $attribute['name'] );
			$lowercase_attribute_label = strtolower( $attribute_label );
			if ( isset( $variation_data[ $attribute_label ] ) || isset( $variation_data[ $lowercase_attribute_label ] ) ) {

				// Check both the original and lowercase attribute label.
				$attribute_label = isset( $variation_data[ $attribute_label ] ) ? $attribute_label : $lowercase_attribute_label;

				$return[ $variation_attribute_name ] =
					$attribute['is_taxonomy']
						?
						sanitize_title( $variation_data[ $attribute_label ] )
						:
						html_entity_decode(
							wc_clean( $variation_data[ $attribute_label ] ),
							ENT_QUOTES,
							get_bloginfo( 'charset' )
						);
				continue;
			}

			// Attribute slugs e.g. pa_size.
			if ( isset( $variation_data[ $attribute['name'] ] ) ) {
				$return[ $variation_attribute_name ] =
					$attribute['is_taxonomy']
						?
						sanitize_title( $variation_data[ $attribute['name'] ] )
						:
						html_entity_decode(
							wc_clean( $variation_data[ $attribute['name'] ] ),
							ENT_QUOTES,
							get_bloginfo( 'charset' )
						);
			}
		}

		return $return;
	} // END sanitize_variation_data()

	/**
	 * Check if product is in the cart and return cart item key if found.
	 *
	 * Cart item key will be unique based on the item and its properties, such as variations.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param string $cart_item_key Cart item key of product to find in the cart.
	 *
	 * @return string Returns the same cart item key if valid.
	 */
	public function find_product_in_cart( $cart_item_key = '' ) {
		if ( ! empty( $cart_item_key ) ) {
			$cart_contents = $this->get_cart_contents();

			if ( is_array( $cart_contents ) && ! empty( $cart_contents[ $cart_item_key ] ) ) {
				return $cart_item_key;
			}
		}

		return '';
	} // END find_product_in_cart()

	/**
	 * Validates if item is sold individually.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @see CoCart_REST_Cart_Controller::get_cart_contents()
	 *
	 * @param WP_REST_Request $request The request object.
	 * @param WC_Product      $product The product object.
	 *
	 * @return float $quantity The quantity returned.
	 */
	public function is_product_sold_individually( $request, $product ) {
		try {
			$cart_contents = $this->get_cart_contents();

			$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $item_key && $cart_contents[ $item_key ]['quantity'] > 0, $request['id'], $request['variation_id'], $request['item_data'], $item_key );

			if ( $found_in_cart ) {
				$message = sprintf(
					/* translators: %s: Product Name */
					__( "You cannot add another '%s' to your cart.", 'cocart-core' ),
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

			return $request['quantity'];
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END is_product_sold_individually()

	/**
	 * Adds item to cart.
	 *
	 * Uses internal WooCommerce filters and hooks.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 5.0.0 Rewritten to support WooCommerce better.
	 *
	 * @param int        $product_id     The product ID.
	 * @param int        $quantity       The item quantity.
	 * @param int        $variation_id   The variation ID.
	 * @param array      $variation      The variation attributes.
	 * @param array      $cart_item_data The cart item data
	 * @param WC_Product $product_data   The product object.
	 *
	 * @return string|boolean $item_key Cart item key or false if error.
	 */
	public function add_cart_item( $request, WC_Product $product, WC_Cart $cart ) {
		try {
			// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $cart->generate_cart_id( $request['id'], $request['variation_id'], $request['variation'], $request['item_data'] );

			// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
			/**
			 * Filters the item being added to the cart.
			 *
			 * @since 2.5.0
			 *
			 * @internal Matches filter name in WooCommerce core.
			 *
			 * @param array  $item_data Array of cart item data being added to the cart.
			 * @param string $item_key  Id of the item in the cart.
			 *
			 * @return array Updated cart item data.
			 */
			$cart->cart_contents[ $item_key ] = apply_filters(
				'woocommerce_add_cart_item',
				array_merge(
					$request['item_data'],
					array(
						'key'          => $item_key,
						'product_id'   => $request['id'],
						'variation_id' => $request['variation_id'],
						'variation'    => $request['variation'],
						'quantity'     => $request['quantity'],
						'data'         => $product,
						'data_hash'    => wc_get_cart_item_data_hash( $product ),
					)
				),
				$item_key
			);

			/**
			 * Filters the entire cart contents when the cart changes.
			 *
			 * @since 2.5.0
			 *
			 * @internal Matches filter name in WooCommerce core.
			 *
			 * @param array $cart_contents Array of all cart items.
			 *
			 * @return array Updated array of all cart items.
			 */
			$cart->cart_contents = apply_filters( 'woocommerce_cart_contents_changed', $cart->cart_contents );

			cocart_do_deprecated_filter( 'cocart_cart_contents_changed', '5.0.0', 'woocommerce_cart_contents_changed', __( 'Use WooCommerce core filter instead.', 'cocart-core' ), $cart->cart_contents );

			/**
			 * Fires when an item is added to the cart.
			 *
			 * This hook fires when an item is added to the cart. WooCommerce core add to cart events trigger the same hook.
			 *
			 * @since 2.5.0 Introduced into WooCommerce core
			 *
			 * @internal Matches action name in WooCommerce core.
			 *
			 * @param string  $item_key       ID of the item in the cart.
			 * @param integer $id             ID of the product added to the cart.
			 * @param integer $quantity       Quantity of the item added to the cart.
			 * @param integer $variation_id   Variation ID of the product added to the cart.
			 * @param array   $variation      Array of variation data.
			 * @param array   $cart_item_data Array of other cart item data.
			 */
			do_action(
				'woocommerce_add_to_cart',
				$item_key,
				$request['id'],
				$request['quantity'],
				$request['variation_id'],
				$request['variation'],
				$request['item_data']
			);

			/**
			 * Fires after item has been added to cart.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @deprecated 5.0.0 Replaced with internal WooCommerce core hook.
			 *
			 * @see woocommerce_add_to_cart
			 *
			 * @param string $item_key       Generated ID based on the product information provided.
			 * @param int    $product_id     The product ID.
			 * @param int    $quantity       The item quantity.
			 * @param int    $variation_id   The variation ID.
			 * @param array  $variation      The variation attributes.
			 * @param array  $cart_item_data The cart item data
			 */
			cocart_do_deprecated_action( 'cocart_add_to_cart', '5.0.0', 'woocommerce_add_to_cart', '', array( $item_key, $request['id'], $request['quantity'], $request['variation_id'], $request['variation'], $request['item_data'] ) );

			return $item_key;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END add_cart_item()

	/**
	 * Get the query params for the cart.
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
				'description'       => __( 'Unique identifier for the cart.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

	/**
	 * Extends the query parameters for the cart.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 */
	public function add_additional_params_to_cart( $params ) {
		/**
		 * This filter allows you to extend the query parameters without removing any default parameters.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @return array $params The query params.
		 */
		$params += apply_filters( 'cocart_cart_query_parameters', array() );

		return $params;
	} // END add_additional_params_to_cart()
}
