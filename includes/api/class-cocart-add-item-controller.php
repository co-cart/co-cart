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
					'description' => __( 'Unique identifier for the product ID.', 'cart-rest-api-for-woocommerce' ),
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
					'description'       => __( 'Additional item data passed to make item unique.', 'cart-rest-api-for-woocommerce' ),
					'validate_callback' => function( $param, $request, $key ) {
						return is_array( $param );
					}
				),
				'return_cart' => array(
					'description' => __( 'Returns the whole cart once item is added.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
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
	 * @param   array $data - Passed parameters.
	 * @return  WP_Error|WP_REST_Response
	 */
	public function add_to_cart( $data = array() ) {
		$product_id     = ! isset( $data['product_id'] ) ? 0 : wc_clean( wp_unslash( $data['product_id'] ) );
		$variation      = ! isset( $data['variation'] ) ? array() : $data['variation'];
		$cart_item_data = ! isset( $data['cart_item_data'] ) ? array() : $data['cart_item_data'];

		// Validate product ID before continuing.
		$this->validate_product_id( $product_id );

		// The product we are attempting to add to the cart.
		$adding_to_cart = wc_get_product( $product_id );

		// Return error if item does not exist.
		if ( ! $adding_to_cart ) {
			$message = __( 'This product does not exist!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_product_does_not_exist', $message, array( 'status' => 500 ) );
		}

		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		// Add to cart handlers
		$add_to_cart_handler = apply_filters( 'cocart_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
			$was_added_to_cart = self::add_to_cart_handler_variable( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
		} elseif ( has_action( 'cocart_add_to_cart_handler_' . $add_to_cart_handler ) ) {
			do_action( 'cocart_add_to_cart_handler_' . $add_to_cart_handler ); // Custom handler.
		} else {
			$was_added_to_cart = self::add_to_cart_handler_simple( $product_id, $quantity, $cart_item_data );
		}

		$response = '';

		// Was it requested to return the whole cart once item added?
		if ( $data['return_cart'] ) {
			$response = $this->get_cart_contents( $data );
		} else if ( is_array( $was_added_to_cart ) ) {
			$response = $was_added_to_cart;
		}

		return new WP_REST_Response( $response, 200 );
	} // END add_to_cart()

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @access public
	 * @since  2.1.0
	 * @param  int   $product_id     - Contains the id of the product to add to the cart.
	 * @param  int   $quantity       - Contains the quantity of the item to add to the cart.
	 * @param  array $cart_item_data - Contains extra cart item data we want to pass into the item.
	 * @return bool  success or not
	 */
	public function add_to_cart_handler_simple( $product_id, $quantity, $cart_item_data ) {
		$cart_item_key = $this->validate_product( $product_id, $quantity, 0, array(), $cart_item_data, 'simple' );

		// If cart_item_key is set, then the item is already in the cart so just update the quantity.
		if ( $cart_item_key ) {
			$cart_contents = $this->get_cart( array( 'raw' => true ) );

			$new_quantity  = $quantity + $cart_contents[ $cart_item_key ]['quantity'];

			WC()->cart->set_quantity( $cart_item_key, $new_quantity );

			$item_added = $this->get_cart_item( $cart_item_key, 'add' );
		} else {
			// Add item to cart.
			$item_key = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

			// Return response to added item to cart or return error.
			if ( $item_key ) {

				// Re-calculate cart totals once item has been added.
				WC()->cart->calculate_totals();

				// Return item details.
				$item_added = $this->get_cart_item( $item_key, 'add' );

				do_action( 'cocart_item_added_to_cart', $item_key, $item_added );
			} else {
				/* translators: %s: product name */
				$message = sprintf( __( 'You cannot add "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about product cannot be added to cart.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product_data Product data.
				 */
				$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product_data );

				return new WP_Error( 'cocart_cannot_add_to_cart', $message, array( 'status' => 500 ) );
			}
		}

		return $item_added;
	} // END add_to_cart_handler_simple()

	/**
	 * Handle adding variable products to the cart.
	 *
	 * @access public
	 * @since  2.1.0
	 * @param  int  $product_id Product ID to add to the cart.
	 * @return bool success or not
	 */
	public function add_to_cart_handler_variable( $product_id, $quantity ) {
		$cart_item_key = $this->validate_product( $product_id, $quantity, $variation_id, $variation, $cart_item_data, 'variable' );

		// If cart_item_key is set, then the item is already in the cart so just update the quantity.
		if ( $cart_item_key ) {
			$cart_contents = $this->get_cart( array( 'raw' => true ) );

			$new_quantity  = $quantity + $cart_contents[ $cart_item_key ]['quantity'];

			WC()->cart->set_quantity( $cart_item_key, $new_quantity );

			$item_added = $this->get_cart_item( $cart_item_key, 'add' );
		} else {
			// Add item to cart.
			$item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			// Return response to added item to cart or return error.
			if ( $item_key ) {
				// Re-calculate cart totals once item has been added.
				WC()->cart->calculate_totals();

				// Return item details.
				$item_added = $this->get_cart_item( $item_key, 'add' );

				do_action( 'cocart_item_added_to_cart', $item_key, $item_added );
			} else {
				/* translators: %s: product name */
				$message = sprintf( __( 'You cannot add "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about product cannot be added to cart.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product_data Product data.
				 */
				$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product_data );

				return new WP_Error( 'cocart_cannot_add_to_cart', $message, array( 'status' => 500 ) );
			}
		}

		return $item_added;
	} // END add_to_cart_handler_variable()

} // END class
