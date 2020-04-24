<?php
/**
 * CoCart REST API controller
 *
 * Handles requests to the cart endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.0.0
 * @version  2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API controller class.
 *
 * @package CoCart REST API/API
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
	protected $rest_base = '';

	/**
	 * Register the routes for cart.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 2.1.0
	 */
	public function register_routes() {
		// Get Cart - cocart/v1/get-cart (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/get-cart', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_cart' ),
			'args'     => $this->get_collection_params()
		) );
 
		// Get Customers Cart saved via Persistent Cart - cocart/v1/get-cart/customer/1 (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/get-cart/customer/(?P<id>[\d]+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_cart_customer' ),
			'permission_callback' => array( $this, 'get_permission_check' ),
			'args'     => $this->get_collection_params()
		) );

		// Get Cart Saved - cocart/v1/get-cart/saved (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/get-cart/saved', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_cart_saved' ),
			'args'                => array(
				'id' => array(
					'required'    => true,
					'description' => __( 'An alphanumeric identifier for the cart in session.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'thumb' => array(
					'description' => __( 'Returns the URL of the product image thumbnail.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
					'type'        => 'boolean',
				),
			),
		) );
	} // register_routes()

	/**
	 * Check if a given request can read the cart.
	 *
	 * @access public
	 * @since  2.0.0
	 * @return bool|WP_Error
	 */
	public function get_permission_check() {
		if ( ! current_user_can( 'administrator' ) ) {
			return new WP_Error( 'cocart_cannot_read_cart', __( 'Cannot read cart!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		return true;
	} // END get_permission_check()

	/**
	 * Get cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.0.0
	 * @param   array  $data
	 * @param   string $cart_item_key
	 * @return  array|WP_REST_Response
	 */
	public function get_cart( $data = array(), $cart_item_key = '' ) {
		$cart_contents = $this->get_cart_contents( $data, $cart_item_key );

		do_action( 'cocart_get_cart', $cart_contents );

		$show_raw = ! empty( $data['raw'] ) ? $data['raw'] : false;

		// Return cart contents raw if requested.
		if ( $show_raw ) {
			return $cart_contents;
		}

		return new WP_REST_Response( $cart_contents, 200 );
	} // END get_cart()

	/**
	 * Get cart for a specific customer.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 2.1.0
	 * @param   array  $data
	 * @param   string $cart_item_key
	 * @return  array|WP_Error
	 */
	public function get_cart_customer( $data = array() ) {
		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'cocart_customer_missing', __( 'Customer ID is required!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		$saved_cart = $this->get_saved_cart( $data );

		// If a saved cart exists then replace the carts content.
		if ( ! empty( $saved_cart ) ) {
			return $this->return_cart_contents( $saved_cart, $data, '' );
		}

		return $this->get_cart_contents( $data, '' );
	} // END get_cart_customer()

	/**
	 * Get cart saved in database.
	 *
	 * @access public
	 * @since  2.1.0
	 * @param  array  $data
	 * @return array|WP_Error
	 */
	public function get_cart_saved( $data = array() ) {
		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'cocart_session_id_missing', __( 'Cart Session ID is required!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		// Get saved cart in session.
		$saved_cart = $this->get_saved_cart_in_session( $data, 'cart_contents' );

		// If no error returned then return cart contents.
		if ( ! is_wp_error( $saved_cart ) ) {
			return $this->return_cart_contents( $saved_cart, $data, '' );
		}

		return array();
	} // END get_cart_saved()

	/**
	 * Gets the cart contents.
	 *
	 * @access public
	 * @since  2.0.0
	 * @param  array  $data
	 * @param  string $cart_item_key
	 * @return array  $cart_contents
	 */
	public function get_cart_contents( $data = array(), $cart_item_key = '' ) {
		$cart_contents = isset( WC()->cart ) ? WC()->cart->get_cart() : array();

		return $this->return_cart_contents( $cart_contents, $data, $cart_item_key );
	} // END get_cart_contents()

	/**
	 * Return cart contents.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 2.1.0
	 * @param   array  $cart_contents
	 * @param   array  $data
	 * @param   string $cart_item_key
	 * @return  array  $cart_contents
	 */
	public function return_cart_contents( $cart_contents, $data = array(), $cart_item_key = '' ) {
		if ( CoCart_Count_Items_Controller::get_cart_contents_count( array( 'return' => 'numeric' ) ) <= 0 || empty( $cart_contents ) ) {
			/**
			 * Filter response for empty cart.
			 *
			 * @since 2.0.8
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
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
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
				 * @since 2.1.0
				 * @param string     $message Message.
				 * @param WC_Product $_product Product data.
				 */
				$message = apply_filters( 'cocart_cart_item_removed_message', $message, $_product );

				wc_add_notice( $message, 'error' );
			} else {
				// Adds the product name and title as new variables.
				$cart_contents[ $item_key ]['product_name']  = apply_filters( 'cocart_product_name', $_product->get_name(), $_product, $cart_item, $item_key );
				$cart_contents[ $item_key ]['product_title'] = apply_filters( 'cocart_product_title', $_product->get_title(), $_product, $cart_item, $item_key );

				// Add product price as a new variable.
				$cart_contents[ $item_key ]['product_price'] = html_entity_decode( strip_tags( wc_price( $_product->get_price() ) ) );

				// If main product thumbnail is requested then add it to each item in cart.
				if ( $show_thumb ) {
					$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $_product->get_image_id(), $cart_item, $item_key );

					$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail' ) );

					/**
					 * Filters the source of the product thumbnail.
					 *
					 * @since 2.1.0
					 * @param string $thumbnail_src URL of the product thumbnail.
					 */
					$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src[0], $cart_item, $item_key );

					// Add main product image as a new variable.
					$cart_contents[ $item_key ]['product_image'] = esc_url( $thumbnail_src );
				}

				// This filter allows additional data to be returned for a specific item in cart.
				$cart_contents = apply_filters( 'cocart_cart_contents', $cart_contents, $item_key, $cart_item, $_product );
			}
		}

		// The cart contents is returned and can be filtered.
		return apply_filters( 'cocart_return_cart_contents', $cart_contents );
	} // END return_cart_contents()

	/**
	 * Returns a customers saved cart from the database if one exists.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 2.0.9
	 * @param   array $data       The customer ID is a required variable.
	 * @return  array $saved_cart Returns the cart content from the database.
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
	 * @since  2.1.0
	 * @param  array  $data       The cart key is a required variable.
	 * @param  string $return     Returns specified data. Default is `cart_contents`.
	 * @return array  $saved_cart Returns the cart data from the database.
	 */
	public function get_saved_cart_in_session( $data = array(), $return = 'cart_contents' ) {
		$cart_key = ! empty( $data['id'] ) ? $data['id'] : '';

		$saved_cart = CoCart_API_Session::get_cart( $cart_key );

		// If no cart is saved with the ID specified return error.
		if ( empty( $saved_cart ) ) {
			return new WP_Error( 'cocart_cart_in_session_not_valid', __( 'Cart in Session is not valid!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		// Return specified data from cart.
		if ( isset( $saved_cart->$return ) ) {
			$saved_cart = $saved_cart->$return;
		}

		return $saved_cart;
	} // END get_saved_cart_in_session()


	/**
	 * Validate the product id.
	 *
	 * @access  protected
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   int|string $product_id
	 * @return  int|WP_Error
	 */
	protected function validate_product_id( $product_id ) {
		// If the product ID was used by a SKU ID, then look up the product ID and return it.
		if ( is_string( $product_id ) ) {
			$product_id_by_sku = wc_get_product_id_by_sku( $product_id );

			if ( $product_id_by_sku > 0 ) {
				return $product_id_by_sku;
			}
		}

		if ( empty( $product_id ) ) {
			return new WP_Error( 'cocart_product_id_required', __( 'Product ID number is required!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		if ( ! is_numeric( $product_id ) ) {
			return new WP_Error( 'cocart_product_id_not_numeric', __( 'Product ID must be numeric!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}
	} // END validate_product_id()

	/**
	 * Validate the product quantity.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  int $quantity
	 * @return WP_Error
	 */
	protected function validate_quantity( $quantity ) {
		if ( ! is_numeric( $quantity ) ) {
			return new WP_Error( 'cocart_quantity_not_numeric', __( 'Quantity must be numeric!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}
	} // END validate_quantity()

	/**
	 * Validate variable product.
	 *
	 * @access protected
	 * @since  2.1.0
	 * @param  int    $product_id     - Contains the id of the product.
	 * @param  int    $quantity       - Contains the quantity of the item.
	 * @param  int    $variation_id   - ID of the variation.
	 * @param  array  $variation      - Attribute values.
	 * @param  array  $cart_item_data - Extra cart item data we want to pass into the item.
	 * @param  string $product_type   - The product type.
	 * @return WP_Error
	 */
	protected function validate_variable_product( $product_id, $quantity, $variation_id, $variation, $cart_item_data, $product_data ) {
		if ( $variation_id == 0 ) {
			$message = __( 'Can not add a variable product without specifying a variation!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about variable product failing validation.
			 *
			 * @param string $message - Message.
			 */
			$message = apply_filters( 'cocart_variable_product_failed_validation_message', $message );

			return new WP_Error( 'cocart_variable_product_failed_validation', $message, array( 'status' => 500 ) );
		}
	} // END validate_variable_product()

	/**
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @access  protected
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   int    $product_id     - Contains the ID of the product.
	 * @param   int    $quantity       - Contains the quantity of the item.
	 * @param   int    $variation_id   - Contains the ID of the variation.
	 * @param   array  $variation      - Attribute values.
	 * @param   array  $cart_item_data - Extra cart item data we want to pass into the item.
	 * @param   string $product_type   - The product type.
	 * @return  array|WP_Error
	 */
	protected function validate_product( $product_id = null, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array(), $product_type = '' ) {
		$this->validate_product_id( $product_id );

		$this->validate_quantity( $quantity );

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}

		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		// Look up the product type if not passed.
		if ( empty( $product_type ) ) {
			$product_type = $product_data->get_type();
		}

		$passed_validation = apply_filters( 'cocart_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $product_type );

		if ( ! $passed_validation ) {
			$message = __( 'Product did not pass validation!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product failing validation.
			 *
			 * @param string     $message      - Message.
			 * @param WC_Product $product_data - Product data.
			 */
			$message = apply_filters( 'cocart_product_failed_validation_message', $message, $product_data );

			return new WP_Error( 'cocart_product_failed_validation', $message, array( 'status' => 500 ) );
		}

		// Validate variable product.
		if ( $product_type === 'variable' || $product_type === 'variation' ) {
			$this->validate_variable_product( $product_id, $quantity, $variation_id, $variation, $cart_item_data, $product_data );
		}

		/**
		 * Filters the quantity for specified products.
		 *
		 * @param int   $quantity       - The original quantity of the item.
		 * @param int   $product_id     - The product ID.
		 * @param int   $variation_id   - The variation ID.
		 * @param array $variation      - The variation data.
		 * @param array $cart_item_data - The cart item data.
		 */
		$quantity = apply_filters( 'cocart_add_to_cart_quantity', $quantity, $product_id, $variation_id, $variation, $cart_item_data );

		if ( $quantity <= 0 || ! $product_data || 'trash' === $product_data->get_status() ) {
			if ( $product_data ) {
				$message = sprintf( __( 'Product "%s" either does not exist or something is preventing it from being added!', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );
			} else {
				$message = __( 'This product does not exist!', 'cart-rest-api-for-woocommerce' );
			}

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product does not exist.
			 *
			 * @param string     $message      - Message.
			 * @param WC_Product $product_data - Product data.
			 */
			$message = apply_filters( 'cocart_product_does_not_exist_message', $message, $product_data );

			return new WP_Error( 'cocart_product_does_not_exist', $message, array( 'status' => 500 ) );
		}

		// Load cart item data - may be added by other plugins.
		$cart_item_data = (array) apply_filters( 'cocart_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
		$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

		// Find the cart item key in the existing cart.
		$cart_item_key = $this->find_product_in_cart( $cart_id );

		// Force quantity to 1 if sold individually and check for existing item in cart.
		if ( $product_data->is_sold_individually() ) {
			/**
			 * Quantity for sold individual products can be filtered.
			 *
			 * @since 2.0.13
			 */
			$quantity = apply_filters( 'cocart_add_to_cart_sold_individually_quantity', 1 );

			$cart_contents = $this->get_cart();

			$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $cart_item_key && $cart_contents[ $cart_item_key ]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id );

			if ( $found_in_cart ) {
				/* translators: %s: product name */
				$message = sprintf( __( 'You cannot add another "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about product not being allowed to add another.
				 *
				 * @param string     $message      - Message.
				 * @param WC_Product $product_data - Product data.
				 */
				$message = apply_filters( 'cocart_product_can_not_add_another_message', $message, $product_data );

				return new WP_Error( 'cocart_product_sold_individually', $message, array( 'status' => 500 ) );
			}
		}

		// Product is purchasable check.
		if ( ! $product_data->is_purchasable() ) {
			$message = __( 'Sorry, this product cannot be purchased.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product unable to be purchased.
			 *
			 * @param string     $message      - Message.
			 * @param WC_Product $product_data - Product data.
			 */
			$message = apply_filters( 'cocart_product_cannot_be_purchased_message', $message, $product_data );

			return new WP_Error( 'cocart_cannot_be_purchased', $message, array( 'status' => 500 ) );
		}

		// Stock check - only check if we're managing stock and backorders are not allowed.
		if ( ! $product_data->is_in_stock() ) {
			/* translators: %s: product name */
			$message = sprintf( __( 'You cannot add "%s" to the cart because the product is out of stock.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product is out of stock.
			 *
			 * @param string     $message      - Message.
			 * @param WC_Product $product_data - Product data.
			 */
			$message = apply_filters( 'cocart_product_is_out_of_stock_message', $message, $product_data );

			return new WP_Error( 'cocart_product_out_of_stock', $message, array( 'status' => 500 ) );
		}

		if ( ! $product_data->has_enough_stock( $quantity ) ) {
			/* translators: 1: quantity requested, 2: product name, 3: quantity in stock */
			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_not_enough_in_stock', sprintf( __( 'You cannot add a quantity of %1$s for "%2$s" to the cart because there is not enough stock. - only %3$s remaining!', 'cart-rest-api-for-woocommerce' ), $quantity, $product_data->get_name(), wc_format_stock_quantity_for_display( $product_data->get_stock_quantity(), $product_data ) ), array( 'status' => 500 ) );
		}

		// Stock check - this time accounting for whats already in-cart.
		if ( $product_data->managing_stock() ) {
			$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

			if ( isset( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] ) && ! $product_data->has_enough_stock( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] + $quantity ) ) {
				/* translators: 1: quantity in stock, 2: quantity in cart */

				CoCart_Logger::log( $message, 'error' );

				return new WP_Error(
					'cocart_not_enough_stock_remaining',
					sprintf(
						__( 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'cart-rest-api-for-woocommerce' ),
						wc_format_stock_quantity_for_display( $product_data->get_stock_quantity(), $product_data ),
						wc_format_stock_quantity_for_display( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ], $product_data )
					),
					array( 'status' => 500 )
				);
			}
		}

		$response  = apply_filters( 'cocart_ok_to_add_response', '', $product_data, $product_id, $quantity );
		$ok_to_add = apply_filters( 'cocart_ok_to_add', true, $product_data, $product_id, $quantity );

		// If it is not OK to add the item, return an error response.
		if ( ! $ok_to_add ) {
			$error_msg = empty( $response ) ? __( 'This item can not be added to the cart.', 'cart-rest-api-for-woocommerce' ) : $response;

			CoCart_Logger::log( $error_msg, 'error' );

			return new WP_Error( 'cocart_not_ok_to_add_item', $error_msg, array( 'status' => 500 ) );
		}

		// Returns all valid data.
		return array(
			'product_id'     => $product_id,
			'quantity'       => $quantity,
			'variation_id'   => $variation_id,
			'variation'      => $variation,
			'cart_item_data' => $cart_item_data,
			'cart_item_key'  => $cart_item_key,
			'product_data'   => $product_data
		);
	} // END validate_product()

	/**
	 * Check if product is in the cart and return cart item key if found.
	 *
	 * Cart item key will be unique based on the item and its properties, such as variations.
	 *
	 * @access public
	 * @since  2.0.0
	 * @param  string $cart_item_key of product to find in the cart.
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
	 * @access  protected
	 * @since   1.0.6
	 * @version 2.0.0
	 * @param   array   $current_data
	 * @param   integer $quantity
	 * @return  bool|WP_Error
	 */
	protected function has_enough_stock( $current_data = array(), $quantity = 1 ) {
		$product_id      = ! isset( $current_data['product_id'] ) ? 0 : absint( $current_data['product_id'] );
		$variation_id    = ! isset( $current_data['variation_id'] ) ? 0 : absint( $current_data['variation_id'] );
		$current_product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( ! $current_product->has_enough_stock( $quantity ) ) {
			/* translators: 1: quantity requested, 2: product name 3: quantity in stock */
			return new WP_Error( 'cocart_not_enough_in_stock', sprintf( __( 'You cannot add a quantity of %1$s for "%2$s" to the cart because there is not enough stock. - only %3$s remaining!', 'cart-rest-api-for-woocommerce' ), $quantity, $current_product->get_name(), wc_format_stock_quantity_for_display( $current_product->get_stock_quantity(), $current_product ) ), array( 'status' => 500 ) );
		}

		return true;
	} // END has_enough_stock()

	/**
	 * Look's up an item in the cart and returns it's data 
	 * based on the condition it is being returned for.
	 *
	 * @access public
	 * @since  2.1.0
	 * @param  string $item_key
	 * @param  string $condition - Default is 'add', other conditions are: container, update, remove, restore
	 * @return array  $item
	 */
	public function get_cart_item( $item_key, $condition = 'add' ) {
		$item = WC()->cart->get_cart_item( $item_key );

		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // END get_cart_item()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access public
	 * @since  2.1.0
	 * @return array $params
	 */
	public function get_collection_params() {
		$params = array(
			'id' => array(
				'description' => __( 'Unique identifier for the cart/customer.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
			),
			'thumb' => array(
				'description' => __( 'Returns the URL of the product image thumbnail.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
