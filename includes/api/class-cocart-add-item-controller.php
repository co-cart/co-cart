<?php
/**
 * CoCart - Add Item controller
 *
 * Handles the request to add items to the cart with /add-item endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Add Item controller class.
 *
 * @package CoCart/API
 */
class CoCart_Add_Item_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'add-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Add Item - cocart/v1/add-item (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'add_to_cart' ),
			'args'     => array(
				'product_id' => array(
					'description'       => __( 'Unique identifier for the product ID.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'integer',
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					}
				),
				'quantity' => array(
					'description'       => __( 'The quantity amount of the item to add to cart.', 'cart-rest-api-for-woocommerce' ),
					'default'           => 1,
					'type'              => 'integer',
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					}
				),
				'variation_id' => array(
					'description'       => __( 'Unique identifier for the variation ID.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'integer',
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					}
				),
				'variation' => array(
					'validate_callback' => function( $param, $request, $key ) {
						return is_array( $param );
					}
				),
				'cart_item_data' => array(
					'validate_callback' => function( $param, $request, $key ) {
						return is_array( $param );
					}
				),
				'refresh_totals' => array(
					'description' => __( 'Re-calculates the totals once item has been added or the quantity of the item was updated.', 'cart-rest-api-for-woocommerce' ),
					'default'     => true,
					'type'        => 'boolean',
				)
			)
		) );
	} // register_routes()

	/**
	 * Add to Cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   array $data
	 * @return  WP_Error|WP_REST_Response
	 */
	public function add_to_cart( $data = array() ) {
		$product_id     = ! isset( $data['product_id'] ) ? 0 : absint( $data['product_id'] );
		$quantity       = ! isset( $data['quantity'] ) ? 1 : absint( $data['quantity'] );
		$variation_id   = ! isset( $data['variation_id'] ) ? 0 : absint( $data['variation_id'] );
		$variation      = ! isset( $data['variation'] ) ? array() : $data['variation'];
		$cart_item_data = ! isset( $data['cart_item_data'] ) ? array() : $data['cart_item_data'];

		$item_added = array();

		$this->validate_product( $product_id, $quantity );

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}

		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		/**
		 * Filters the quantity for specified products.
		 *
		 * @since 2.1.0
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
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_product_does_not_exist_message', $message );

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
			$quantity = 1;

			$cart_contents = $this->get_cart();

			$found_in_cart = apply_filters( 'cocart_add_to_cart_sold_individually_found_in_cart', $cart_item_key && $cart_contents[ $cart_item_key ]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id );

			if ( $found_in_cart ) {
				/* translators: %s: product name */
				$message = sprintf( __( 'You cannot add another "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about product not being allowed to add another.
				 *
				 * @since 2.1.0
				 * @param string     $message Message.
				 * @param WC_Product $product_data Product data.
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
			 * @since 2.1.0
			 * @param string     $message Message.
			 * @param WC_Product $product_data Product data.
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
			 * @since 2.1.0
			 * @param string     $message Message.
			 * @param WC_Product $product_data Product data.
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

		// If cart_item_key is set, then the item is already in the cart so just update the quantity.
		if ( $cart_item_key ) {
			$cart_contents = $this->get_cart( array( 'raw' => true ) );

			$new_quantity  = $quantity + $cart_contents[ $cart_item_key ]['quantity'];

			WC()->cart->set_quantity( $cart_item_key, $new_quantity, $data['refresh_totals'] );

			$item_added = $this->get_cart_item( $cart_item_key, 'add' );
		} else {
			// Add item to cart.
			$item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			// Return response to added item to cart or return error.
			if ( $item_key ) {
				// Re-calculate cart totals once item has been added.
				if ( $data['refresh_totals'] ) {
					WC()->cart->calculate_totals();
				}

				$item_added = $this->get_cart_item( $item_key, 'add' );

				do_action( 'cocart_item_added_to_cart', $item_key, $item_added );
			} else {
				/* translators: %s: product name */
				$message = sprintf( __( 'You cannot add "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about product cannot be added to cart.
				 *
				 * @since 2.1.0
				 * @param string     $message Message.
				 * @param WC_Product $product_data Product data.
				 */
				$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product_data );

				return new WP_Error( 'cocart_cannot_add_to_cart', $message, array( 'status' => 500 ) );
			}
		}

		$response = '';

		// Was it requested to return the whole cart once item added?
		if ( $data['return_cart'] ) {
			$response = $this->get_cart_contents( $data );
		} else if ( is_array( $item_added ) ) {
			$response = $item_added;
		}

		return new WP_REST_Response( $response, 200 );
	} // END add_to_cart()

} // END class
