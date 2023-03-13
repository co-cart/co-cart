<?php
/**
 * REST API: CoCart_API_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Cart\v1
 * @since   2.0.0 Introduced.
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets the items added to the cart. (API v1)
 *
 * Handles the requests to get the cart via /get-cart endpoint.
 *
 * @since 2.0.0 Introduced.
 */
class CoCart_API_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'get-cart';

	/**
	 * Register the routes for cart.
	 *
	 * @access public
	 *
	 * @since   2.0.0 Introduced.
	 * @version 2.5.0
	 */
	public function register_routes() {
		// Get Cart - cocart/v1/get-cart (GET)
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

		// Get Cart in Session - cocart/v1/get-cart/1654654321 (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_in_session' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Get Customers Cart saved via Persistent Cart - cocart/v1/get-cart/customer/1 (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/customer/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_customer' ),
				'permission_callback' => array( $this, 'get_permission_check' ),
				'args'                => $this->get_collection_params(),
			)
		);
	} // register_routes()

	/**
	 * Check if a given request can read the cart.
	 *
	 * @access public
	 *
	 * @since   2.0.0 Introduced.
	 * @version 2.6.2
	 *
	 * @return bool|WP_Error
	 */
	public function get_permission_check() {
		if ( ! current_user_can( 'administrator' ) ) {
			return new WP_Error( 'cocart_cannot_read_cart', __( 'Cannot read cart!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 401 ) );
		}

		return true;
	} // END get_permission_check()

	/**
	 * Get cart.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 2.8.4
	 *
	 * @param array  $data          Request data.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return array|WP_REST_Response
	 */
	public function get_cart( $data = array(), $cart_item_key = '' ) {
		$cart_contents = $this->get_cart_contents( $data, $cart_item_key );

		do_action( 'cocart_get_cart', $cart_contents );

		$show_raw = ! empty( $data['raw'] ) ? $data['raw'] : false;

		// Return cart contents raw if requested.
		if ( $show_raw ) {
			return $cart_contents;
		}

		return $this->get_response( $cart_contents, $this->rest_base );
	} // END get_cart()

	/**
	 * Get cart for a specific customer.
	 *
	 * @access public
	 *
	 * @since   2.0.0 Introduced.
	 * @version 2.1.0
	 *
	 * @param array $data Request data.
	 *
	 * @return array|WP_Error Response data.
	 */
	public function get_cart_customer( $data = array() ) {
		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'cocart_customer_missing', __( 'Customer ID is required!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$saved_cart = $this->get_saved_cart( $data );

		// If a saved cart exists then replace the carts content.
		if ( ! empty( $saved_cart ) ) {
			return $this->return_cart_contents( $data, $saved_cart, '' );
		}

		return $this->get_cart_contents( $data, '' );
	} // END get_cart_customer()

	/**
	 * Gets the cart contents.
	 *
	 * @access public
	 *
	 * @since   2.0.0 Introduced.
	 * @version 2.1.2
	 *
	 * @param array  $data          Request data.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return array $cart_contents Cart contents.
	 */
	public function get_cart_contents( $data = array(), $cart_item_key = '' ) {
		$cart_contents = isset( WC()->cart ) ? array_filter( WC()->cart->get_cart() ) : array();

		return $this->return_cart_contents( $data, $cart_contents, $cart_item_key );
	} // END get_cart_contents()

	/**
	 * Return cart contents.
	 *
	 * @access public
	 *
	 * @since   2.0.0 Introduced.
	 * @version 3.5.0
	 *
	 * @param array  $data          Request data.
	 * @param array  $cart_contents Cart contents.
	 * @param string $cart_item_key Cart item key.
	 * @param bool   $from_session  Whether the cart contents are from the session.
	 *
	 * @return array $cart_contents Cart contents.
	 */
	public function return_cart_contents( $data = array(), $cart_contents = array(), $cart_item_key = '', $from_session = false ) {
		if ( CoCart_Count_Items_Controller::get_cart_contents_count( array( 'return' => 'numeric' ), $cart_contents ) <= 0 || empty( $cart_contents ) ) {
			/**
			 * Filter response for empty cart.
			 *
			 * @since 2.0.8 Introduced.
			 */
			$empty_cart = apply_filters( 'cocart_return_empty_cart', array() );

			return $empty_cart;
		}

		$show_thumb = ! empty( $data['thumb'] ) ? $data['thumb'] : false;

		// Find the cart item key in the existing cart.
		if ( ! empty( $cart_item_key ) ) {
			$cart_item_key = $this->find_product_in_cart( $cart_item_key );

			return $cart_contents[ $cart_item_key ];
		}

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data']                  = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
				$cart_contents[ $item_key ]['data'] = $cart_item['data'];
			}

			$_product = apply_filters( 'cocart_item_product', $cart_item['data'], $cart_item, $item_key );

			// If product is no longer purchasable then don't return it and notify customer.
			if ( ! $_product->is_purchasable() ) {
				/* translators: %s: product name */
				$message = sprintf( __( '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.', 'cart-rest-api-for-woocommerce' ), $_product->get_name() );

				/**
				 * Filter message about item removed from the cart.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $_product Product data.
				 */
				$message = apply_filters( 'cocart_cart_item_removed_message', $message, $_product );

				WC()->cart->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.

				wc_add_notice( $message, 'error' );
			} else {
				// Adds the product name and title as new variables.
				$cart_contents[ $item_key ]['product_name']  = apply_filters( 'cocart_product_name', $_product->get_name(), $_product, $cart_item, $item_key );
				$cart_contents[ $item_key ]['product_title'] = apply_filters( 'cocart_product_title', $_product->get_title(), $_product, $cart_item, $item_key );

				// Add product price as a new variable.
				$cart_contents[ $item_key ]['product_price'] = html_entity_decode( strip_tags( wc_price( $_product->get_price() ) ) );

				// If product thumbnail is requested then add it to each item in cart.
				if ( $show_thumb ) {
					/**
					 * Gets the product featured image ID.
					 * If featured image does not exist then use first gallery image instead.
					 *
					 * @since 2.7.2 Introduced.
					 */
					$product_thumbnail_id = $_product->get_image_id();

					if ( ! $product_thumbnail_id ) {
						$parent_product = wc_get_product( $_product->get_parent_id() );

						if ( $parent_product ) {
							$parent_product->get_image_id();
						} else {
							$gallery_image_ids = $_product->get_gallery_image_ids();

							if ( ! empty( $gallery_image_ids ) ) {
								$product_thumbnail_id = array_shift( $gallery_image_ids );
							}
						}
					}

					$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $product_thumbnail_id, $cart_item, $item_key );

					if ( ! empty( $thumbnail_id ) ) {
						$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail' ) );

						/**
						 * Filters the source of the product thumbnail.
						 *
						 * @since 2.1.0 Introduced.
						 *
						 * @param string $thumbnail_src URL of the product thumbnail.
						 */
						$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src[0], $cart_item, $item_key );
					} else {
						$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', wc_placeholder_img_src( apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail' ) ), $cart_item, $item_key );
					}

					// Add main product image as a new variable.
					$cart_contents[ $item_key ]['product_image'] = esc_url( $thumbnail_src );
				}

				// This filter allows additional data to be returned for a specific item in cart.
				$cart_contents = apply_filters( 'cocart_cart_contents', $cart_contents, $item_key, $cart_item, $_product );
			}
		}

		/**
		 * Return cart content from session if set.
		 *
		 * @since 2.1.0 Introduced.
		 *
		 * @param $cart_contents Cart content.
		 */
		if ( $from_session ) {
			return apply_filters( 'cocart_return_cart_session_contents', $cart_contents );
		} else {
			return apply_filters( 'cocart_return_cart_contents', $cart_contents );
		}
	} // END return_cart_contents()

	/**
	 * Returns a customers saved cart from the database if one exists.
	 *
	 * @access public
	 *
	 * @since   2.0.0 Introduced.
	 * @version 2.0.9
	 *
	 * @param array $data The customer ID is a required variable.
	 *
	 * @return array $saved_cart Returns the cart content from the database.
	 */
	public function get_saved_cart( $data = array() ) {
		$saved_cart = array();

		$customer_id = ! empty( $data['id'] ) ? $data['id'] : 0;

		if ( $customer_id > 0 ) {
			$saved_cart_meta = get_user_meta( $customer_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

			if ( isset( $saved_cart_meta['cart'] ) ) {
				$saved_cart = array_filter( (array) $saved_cart_meta['cart'] );
			}
		}

		return $saved_cart;
	} // END get_saved_cart()

	/**
	 * Returns a saved cart in session if one exists.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 2.7.0
	 *
	 * @param array $data The cart key is a required variable.
	 *
	 * @return array $cart Returns the cart data from the database.
	 */
	public function get_cart_in_session( $data = array() ) {
		$cart_key = ! empty( $data['id'] ) ? $data['id'] : '';

		if ( empty( $cart_key ) ) {
			return new WP_Error( 'cocart_cart_key_missing', __( 'Cart Key is required!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		// Get the cart in the database.
		$handler = new CoCart_Session_Handler();
		$cart    = $handler->get_cart( $cart_key );

		// If no cart is saved with the ID specified return error.
		if ( empty( $cart ) ) {
			return new WP_Error( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		return $this->return_cart_contents( $data, maybe_unserialize( $cart['cart'] ), '', true );
	} // END get_cart_in_session()

	/**
	 * Validate the product ID or SKU ID.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @version 2.7.0
	 *
	 * @param int|string $product_id The product ID or SKU ID.
	 *
	 * @return int|WP_Error $product_id The product ID or WP_Error if not valid.
	 */
	protected function validate_product_id( $product_id ) {
		// If the product ID was used by a SKU ID, then look up the product ID and return it.
		if ( ! is_numeric( $product_id ) ) {
			$product_id_by_sku = (int) wc_get_product_id_by_sku( $product_id );

			if ( $product_id_by_sku > 0 ) {
				$product_id = $product_id_by_sku;
			}

			// Force product ID to be integer.
			$product_id = (int) $product_id;
		}

		if ( empty( $product_id ) ) {
			$message = __( 'Product ID number is required!', 'cart-rest-api-for-woocommerce' );
			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_product_id_required', $message, array( 'status' => 404 ) );
		}

		if ( ! is_numeric( $product_id ) ) {
			$message = __( 'Product ID must be numeric!', 'cart-rest-api-for-woocommerce' );
			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_product_id_not_numeric', $message, array( 'status' => 405 ) );
		}

		return $product_id;
	} // END validate_product_id()

	/**
	 * Validate the product quantity.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @version 2.6.2
	 *
	 * @param int $quantity The product quantity.
	 *
	 * @return WP_Error WP_Error if not valid.
	 */
	protected function validate_quantity( $quantity ) {
		if ( ! is_numeric( $quantity ) ) {
			return new WP_Error( 'cocart_quantity_not_numeric', __( 'Quantity must be numeric!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 405 ) );
		}
	} // END validate_quantity()

	/**
	 * Validate variable product.
	 *
	 * @access protected
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param int        $variation_id ID of the variation.
	 * @param array      $variation    Attribute values.
	 * @param WC_Product $product      The product data.
	 *
	 * @return array|WP_Error $variation_id ID of the variation or WP_Error if not valid.
	 */
	protected function validate_variable_product( int $variation_id, array $variation, \WC_Product $product ) {
		// Flatten data and format posted values.
		$variable_product_attributes = $this->get_variable_product_attributes( $product );

		// If we have a parent product and no variation ID, find the variation ID.
		if ( $product->is_type( 'variable' ) && $variation_id == 0 ) {
			$variation_id = $this->get_variation_id_from_variation_data( $variation, $product );
		}

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

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about invalid variation data.
				 *
				 * @param string $message         Message.
				 * @param string $attribute_label Attribute Label.
				 * @param array  $attribute       Allowed values.
				 */
				$message = apply_filters( 'cocart_invalid_variation_data_message', $message, $attribute_label, $attribute->get_slugs() );

				return new WP_Error( 'cocart_invalid_variation_data', $message, array( 'status' => 400 ) );
			}

			// If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
			if ( '' === $expected_value ) {
				$missing_attributes[] = $attribute_label;
			}
		}

		if ( ! empty( $missing_attributes ) ) {
			/* translators: %s: Attribute name. */
			$message = __( 'Missing variation data for variable product.', 'cart-rest-api-for-woocommerce' ) . ' ' . sprintf( _n( '%s is a required field.', '%s are required fields.', count( $missing_attributes ), 'cart-rest-api-for-woocommerce' ), wc_format_list_of_items( $missing_attributes ) );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about missing variation data.
			 *
			 * @param string $message            Message.
			 * @param string $missing_attributes Number of missing attributes.
			 * @param array  $missing_attributes List of missing attributes.
			 */
			$message = apply_filters( 'cocart_missing_variation_data_message', $message, count( $missing_attributes ), wc_format_list_of_items( $missing_attributes ) );

			return new WP_Error( 'cocart_missing_variation_data', $message, array( 'status' => 400 ) );
		}

		return $variation;
	} // END validate_variable_product()

	/**
	 * Try to match variation data to a variation ID and return the ID.
	 *
	 * @access protected
	 *
	 * @since 2.1.2 Introduced.
	 *
	 * @param array      $variation Submitted attributes.
	 * @param WC_Product $product   Product being added to the cart.
	 *
	 * @return int $variation_id Matching variation ID.
	 */
	protected function get_variation_id_from_variation_data( $variation, $product ) {
		$data_store   = \WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation( $product, $variation );

		if ( empty( $variation_id ) ) {
			$message = __( 'No matching variation found.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_no_variation_found', $message, array( 'status' => 400 ) );
		}

		return $variation_id;
	} // END get_variation_id_from_variation_data()

	/**
	 * Get product attributes from the variable product (which may be the parent if the product object is a variation).
	 *
	 * @access protected
	 *
	 * @since 2.1.2 Introduced.
	 *
	 * @param WC_Product $product Product being added to the cart.
	 *
	 * @return array $attributes List of attributes.
	 */
	protected function get_variable_product_attributes( $product ) {
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		if ( ! $product || 'trash' === $product->get_status() ) {
			$message = __( 'This product cannot be added to the cart.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_cart_invalid_parent_product', $message, array( 'status' => 403 ) );
		}

		return $product->get_attributes();
	} // END get_variable_product_attributes()

	/**
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @access protected
	 *
	 * @since   1.0.0 Introduced.
	 * @version 2.7.2
	 *
	 * @param int    $product_id     Contains the ID of the product.
	 * @param int    $quantity       Contains the quantity of the item.
	 * @param int    $variation_id   Contains the ID of the variation.
	 * @param array  $variation      Attribute values.
	 * @param array  $cart_item_data Extra cart item data we want to pass into the item.
	 * @param string $product_type   The product type.
	 *
	 * @return array|WP_Error $cart_item_data|$error Cart item data or error.
	 */
	protected function validate_product( $product_id = null, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array(), $product_type = '' ) {
		$product_id = $this->validate_product_id( $product_id );

		// Return failed product ID validation if any.
		if ( is_wp_error( $product_id ) ) {
			return $product_id;
		}

		$this->validate_quantity( $quantity );

		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		// Check if the product exists before continuing.
		if ( ! $product || 'trash' === $product->get_status() ) {
			if ( $product ) {
				/* translators: %s: Product Name. */
				$message = sprintf( __( 'Product "%s" no longer exists!', 'cart-rest-api-for-woocommerce' ), $product->get_name() );
			} else {
				$message = __( 'This product does not exist!', 'cart-rest-api-for-woocommerce' );
			}

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product does not exist.
			 *
			 * @param string     $message Message.
			 * @param WC_Product $product Product data.
			 */
			$message = apply_filters( 'cocart_product_does_not_exist_message', $message, $product );

			return new WP_Error( 'cocart_product_does_not_exist', $message, array( 'status' => 404 ) );
		}

		// Look up the product type if not passed.
		if ( empty( $product_type ) ) {
			$product_type = $product->get_type();
		}

		if ( $product->is_type( 'variation' ) ) {
			$product_id   = $product->get_parent_id();
			$variation_id = $product->get_id();
		}

		// Validate variable product.
		if ( $product_type === 'variable' || $product_type === 'variation' ) {
			$variation = $this->validate_variable_product( $variation_id, $variation, $product );

			if ( is_wp_error( $variation ) ) {
				return $variation;
			}

			// If variation validated, get variation ID to secure it if not already set.
			if ( $variation_id == 0 ) {
				$variation_id = $this->get_variation_id_from_variation_data( $variation, $product );
			}
		}

		$passed_validation = apply_filters( 'cocart_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $product_type );

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

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product failing validation.
			 *
			 * @param string     $message Message.
			 * @param WC_Product $product Product data.
			 */
			$message = apply_filters( 'cocart_product_failed_validation_message', $message, $product );

			return new WP_Error( 'cocart_product_failed_validation', $message, array( 'status' => 404 ) );
		}

		/**
		 * Filters the quantity for specified products.
		 *
		 * @param int   $quantity       The original quantity of the item.
		 * @param int   $product_id     The product ID.
		 * @param int   $variation_id   The variation ID.
		 * @param array $variation      The variation data.
		 * @param array $cart_item_data The cart item data.
		 */
		$quantity = apply_filters( 'cocart_add_to_cart_quantity', $quantity, $product_id, $variation_id, $variation, $cart_item_data );

		// Load cart item data - may be added by other plugins.
		$cart_item_data = (array) apply_filters( 'cocart_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity, $product_type );

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
		$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

		// Find the cart item key in the existing cart.
		$cart_item_key = $this->find_product_in_cart( $cart_id );

		// Force quantity to 1 if sold individually and check for existing item in cart.
		if ( $product->is_sold_individually() ) {
			/**
			 * Quantity for sold individual products can be filtered.
			 *
			 * @since 2.0.13 Introduced.
			 */
			$quantity = apply_filters( 'cocart_add_to_cart_sold_individually_quantity', 1 );

			$cart_contents = $this->get_cart();

			$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $cart_item_key && $cart_contents[ $cart_item_key ]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id );

			if ( $found_in_cart ) {
				/* translators: %s: Product Name */
				$message = sprintf( __( 'You cannot add another "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product->get_name() );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about product not being allowed to add another.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product Product data.
				 */
				$message = apply_filters( 'cocart_product_can_not_add_another_message', $message, $product );

				return new WP_Error( 'cocart_product_sold_individually', $message, array( 'status' => 403 ) );
			}
		}

		// Product is purchasable check.
		if ( ! $product->is_purchasable() ) {
			$message = __( 'Sorry, this product cannot be purchased.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product unable to be purchased.
			 *
			 * @param string     $message Message.
			 * @param WC_Product $product Product data.
			 */
			$message = apply_filters( 'cocart_product_cannot_be_purchased_message', $message, $product );

			return new WP_Error( 'cocart_cannot_be_purchased', $message, array( 'status' => 403 ) );
		}

		// Stock check - only check if we're managing stock and backorders are not allowed.
		if ( ! $product->is_in_stock() ) {
			/* translators: %s: Product name */
			$message = sprintf( __( 'You cannot add "%s" to the cart because the product is out of stock.', 'cart-rest-api-for-woocommerce' ), $product->get_name() );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product is out of stock.
			 *
			 * @param string     $message Message.
			 * @param WC_Product $product Product data.
			 */
			$message = apply_filters( 'cocart_product_is_out_of_stock_message', $message, $product );

			return new WP_Error( 'cocart_product_out_of_stock', $message, array( 'status' => 404 ) );
		}

		if ( ! $product->has_enough_stock( $quantity ) ) {
			/* translators: 1: Quantity Requested, 2: Product Name, 3: Quantity in Stock */
			$message = sprintf( __( 'You cannot add a quantity of %1$s for "%2$s" to the cart because there is not enough stock. - only %3$s remaining!', 'cart-rest-api-for-woocommerce' ), $quantity, $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ) );

			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_not_enough_in_stock', $message, array( 'status' => 403 ) );
		}

		// Stock check - this time accounting for whats already in-cart.
		if ( $product->managing_stock() ) {
			$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

			if ( isset( $products_qty_in_cart[ $product->get_stock_managed_by_id() ] ) && ! $product->has_enough_stock( $products_qty_in_cart[ $product->get_stock_managed_by_id() ] + $quantity ) ) {
				/* translators: 1: Quantity in Stock, 2: Quantity in Cart */
				$message = sprintf(
					__( 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'cart-rest-api-for-woocommerce' ),
					wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ),
					wc_format_stock_quantity_for_display( $products_qty_in_cart[ $product->get_stock_managed_by_id() ], $product )
				);

				CoCart_Logger::log( $message, 'error' );

				return new WP_Error( 'cocart_not_enough_stock_remaining', $message, array( 'status' => 403 ) );
			}
		}

		$response  = apply_filters( 'cocart_ok_to_add_response', '', $product, $product_id, $quantity );
		$ok_to_add = apply_filters( 'cocart_ok_to_add', true, $product, $product_id, $quantity );

		// If it is not OK to add the item, return an error response.
		if ( ! $ok_to_add ) {
			$error_msg = empty( $response ) ? __( 'This item can not be added to the cart.', 'cart-rest-api-for-woocommerce' ) : $response;

			CoCart_Logger::log( $error_msg, 'error' );

			return new WP_Error( 'cocart_not_ok_to_add_item', $error_msg, array( 'status' => 403 ) );
		}

		// Returns all valid data.
		return array(
			'product_id'     => $product_id,
			'quantity'       => $quantity,
			'variation_id'   => $variation_id,
			'variation'      => $variation,
			'cart_item_data' => $cart_item_data,
			'cart_item_key'  => $cart_item_key,
			'product_data'   => $product,
		);
	} // END validate_product()

	/**
	 * Check if product is in the cart and return cart item key if found.
	 *
	 * Cart item key will be unique based on the item and its properties, such as variations.
	 *
	 * @access public
	 *
	 * @since 2.0.0 Introduced.
	 *
	 * @param string $cart_item_key of product to find in the cart.
	 *
	 * @return string Returns the same cart item key if valid.
	 */
	public function find_product_in_cart( $cart_item_key = '' ) {
		if ( ! empty( $cart_item_key ) ) {
			if ( is_array( self::get_cart() ) && null !== self::get_cart( array(), $cart_item_key ) ) {
				return $cart_item_key;
			}
		}

		return '';
	} // END find_product_in_cart()

	/**
	 * Checks if the product in the cart has enough stock
	 * before updating the quantity.
	 *
	 * @access protected
	 *
	 * @since   1.0.6 Introduced.
	 * @version 2.1.2
	 *
	 * @param array   $current_data The current cart data.
	 * @param integer $quantity     The quantity to update to.
	 *
	 * @return bool|WP_Error True if has enough stock or error if not.
	 */
	protected function has_enough_stock( $current_data = array(), $quantity = 1 ) {
		$product_id      = ! isset( $current_data['product_id'] ) ? 0 : absint( $current_data['product_id'] );
		$variation_id    = ! isset( $current_data['variation_id'] ) ? 0 : absint( $current_data['variation_id'] );
		$current_product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( ! $current_product->has_enough_stock( $quantity ) ) {
			/* translators: 1: Quantity Requested, 2: Product Name 3: Quantity in Stock */
			$message = sprintf( __( 'You cannot add a quantity of %1$s for "%2$s" to the cart because there is not enough stock. - only %3$s remaining!', 'cart-rest-api-for-woocommerce' ), $quantity, $current_product->get_name(), wc_format_stock_quantity_for_display( $current_product->get_stock_quantity(), $current_product ) );

			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_not_enough_in_stock', $message, array( 'status' => 403 ) );
		}

		return true;
	} // END has_enough_stock()

	/**
	 * Look's up an item in the cart and returns it's data
	 * based on the condition it is being returned for.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 2.1.2
	 *
	 * @param string $cart_item_key The item we are looking up in the cart.
	 * @param string $condition     Default is 'add', other conditions are: container, update, remove, restore
	 *
	 * @return array $item The item data.
	 */
	public function get_cart_item( $cart_item_key, $condition = 'add' ) {
		$item = WC()->cart->get_cart_item( $cart_item_key );

		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // END get_cart_item()

	/**
	 * Returns either the default response of the
	 * API requested or a filtered response.
	 *
	 * @access public
	 *
	 * @since   2.7.0 Introduced.
	 * @version 2.8.4
	 *
	 * @param mixed  $response  The original response of the API requested.
	 * @param string $rest_base The API requested.
	 *
	 * @return WP_REST_Response The original or filtered response.
	 */
	public function get_response( $response, $rest_base = '' ) {
		if ( empty( $rest_base ) ) {
			$rest_base = 'cart';
		}

		$rest_base = str_replace( '-', '_', $rest_base );

		/**
		 * If the response is empty then either something seriously has gone wrong
		 * or the response was already filtered earlier and returned nothing.
		 */
		if ( $rest_base !== 'cart' && empty( $response ) ) {
			/* translators: %s: api route */
			$response = sprintf( __( 'Request returned nothing for "%s"! Please seek assistance.', 'cart-rest-api-for-woocommerce' ), rest_url( sprintf( '/%s/%s/', $this->namespace, $rest_base ) ) );
			CoCart_Logger::log( $response, 'error' );
		}

		// Set as true by default until store is ready to go to production.
		$default_response = apply_filters( 'cocart_return_default_response', true );

		if ( ! $default_response ) {
			$response = apply_filters( 'cocart_' . $rest_base . '_response', $response );
		}

		return new WP_REST_Response( $response, 200 );
	} // END get_response()

	/**
	 * Get the schema for returning the cart, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @since 2.1.2 Introduced.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Cart', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'items' => array(
					'description' => __( 'List of cart items.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'properties'  => array(
						'key'               => array(
							'description' => __( 'Unique identifier for the item within the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'product_id'        => array(
							'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'variation_id'      => array(
							'description' => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
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
	 * @version 2.7.0
	 * @return  array $params
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description'       => __( 'Unique identifier for the cart/customer.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'thumb'    => array(
				'description'       => __( 'Returns the URL of the product image thumbnail.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
