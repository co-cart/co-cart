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
			'args'     => array(
				'thumb' => array(
					'description' => __( 'Returns the URL of the product image thumbnail.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
					'type'        => 'boolean',
				),
			),
		) );
 
		// Get Cart of a Customer - cocart/v1/get-cart/1 (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/get-cart/(?P<id>[\d]+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_cart_customer' ),
			'permission_callback' => array( $this, 'get_permission_check' ),
			'args'                => array(
				'id' => array(
					'required'    => true,
					'description' => __( 'Unique identifier for the customer.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
				),
				'thumb' => array(
					'description' => __( 'Returns the URL of the product image thumbnail.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
					'type'        => 'boolean',
				),
			),
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
		$cart_contents = isset( WC()->cart ) ? WC()->cart->get_cart() : WC()->session->cart;

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
		if ( $this->get_cart_contents_count( array( 'return' => 'numeric' ) ) <= 0 || empty( $cart_contents ) ) {
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

			return $cart_contents[$cart_item_key];
		}

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
				$cart_contents[$item_key]['data'] = $cart_item['data'];
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
	 * Get cart contents count.
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 2.0.1
	 * @param   array $data
	 * @return  string|WP_REST_Response
	 */
	public static function get_cart_contents_count( $data = array() ) {
		$count = WC()->cart->get_cart_contents_count();

		$return = ! empty( $data['return'] ) ? $data['return'] : '';

		if ( $return != 'numeric' && $count <= 0 ) {
			return new WP_REST_Response( __( 'There are no items in the cart!', 'cart-rest-api-for-woocommerce' ), 200 );
		}

		return $count;
	} // END get_cart_contents_count()

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
	 * Validate the product id argument.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  int $product_id
	 * @return WP_Error
	 */
	protected function validate_product_id( $product_id ) {
		if ( $product_id <= 0 ) {
			return new WP_Error( 'cocart_product_id_required', __( 'Product ID number is required!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		if ( ! is_numeric( $product_id ) ) {
			return new WP_Error( 'cocart_product_id_not_numeric', __( 'Product ID must be numeric!', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}
	} // END validate_product_id()

	/**
	 * Validate the product quantity argument.
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
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  int $product_id
	 * @param  int $quantity
	 * @return WP_Error
	 */
	protected function validate_product( $product_id = null, $quantity = 1 ) {
		$this->validate_product_id( $product_id );

		$this->validate_quantity( $quantity );
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

} // END class
