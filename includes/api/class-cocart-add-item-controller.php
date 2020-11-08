<?php
/**
 * CoCart - Add Item controller
 *
 * Handles the request to add items to the cart with /cart/add-item endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API Add Item v2 controller class.
 *
 * @package CoCart\API
 */
class CoCart_Add_Item_v2_Controller extends CoCart_Add_Item_Controller {

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
	protected $rest_base = 'cart/add-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Add Item - cocart/v2/cart/add-item (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_to_cart' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params()
			),
			'schema' => array( $this, 'get_item_schema' )
		) );
	} // register_routes()

	/**
	 * Add to Cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  WP_REST_Response
	 */
	public function add_to_cart( $request = array() ) {
		$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
		$quantity   = ! isset( $request['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $request['quantity'] ) );
		$variation  = ! isset( $request['variation'] ) ? array() : $request['variation'];
		$item_data  = ! isset( $request['item_data'] ) ? array() : $request['item_data'];

		$controller = new CoCart_Cart_V2_Controller();

		// Filters additional requested data.
		$request = $controller->filter_request_data( $request );

		// Validate product ID before continuing and return correct product ID if different.
		$product_id = $this->validate_product_id( $product_id );

		// Return failed product ID validation if any.
		if ( is_wp_error( $product_id ) ) {
			return $product_id;
		}

		// The product we are attempting to add to the cart.
		$adding_to_cart = wc_get_product( $product_id );
		$adding_to_cart = $controller->validate_product_for_cart( $adding_to_cart );

		// Add to cart handlers
		$add_to_cart_handler = apply_filters( 'cocart_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
			$was_added_to_cart = $this->add_to_cart_handler_variable( $product_id, $quantity, null, $variation, $item_data, $request );
		} elseif ( has_filter( 'cocart_add_to_cart_handler_' . $add_to_cart_handler ) ) {
			$was_added_to_cart = apply_filters( 'cocart_add_to_cart_handler_' . $add_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
		} else {
			$was_added_to_cart = $this->add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request );
		}

		// Was it requested to return the whole cart once item added?
		if ( isset( $request['return_cart'] ) && is_bool( $request['return_cart'] ) && $request['return_cart'] ) {
			$response = $controller->get_cart_contents( $request );
		} else {
			$response = $was_added_to_cart;
		}

		return $this->get_response( $response, $this->rest_base );
	} // END add_to_cart()

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   int|string      $product_id - Contains the id of the product to add to the cart.
	 * @param   int|float       $quantity   - Contains the quantity of the item to add to the cart.
	 * @param   array           $item_data  - Contains extra cart item data we want to pass into the item.
	 * @param   WP_REST_Request $request    - Full details about the request.
	 * @return  bool            success or not
	 */
	public function add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request ) {
		$controller = new CoCart_Cart_V2_Controller();

		$product_to_add = $controller->validate_product( $product_id, $quantity, 0, array(), $item_data, 'simple', $request );

		// If validation failed then return the error response.
		/*if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}*/

		// Add item to cart once validation is passed.
		$item_added = $this->add_item_to_cart( $product_to_add );

		return $item_added;
	} // END add_to_cart_handler_simple()

	/**
	 * Handle adding variable products to the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   int|string      $product_id - Contains the id of the product to add to the cart.
	 * @param   int|float       $quantity   - Contains the quantity of the item to add to the cart.
	 * @param   array           $variation  - Contains the selected attributes of a variation.
	 * @param   array           $item_data  - Contains extra cart item data we want to pass into the item.
	 * @param   WP_REST_Request $request    - Full details about the request.
	 * @return  bool            success or not
	 */
	public function add_to_cart_handler_variable( $product_id, $quantity, $deprecated = null, $variation, $item_data, $request ) {
		$controller = new CoCart_Cart_V2_Controller();

		$product_to_add = $controller->validate_product( $product_id, $quantity, $deprecated, $variation, $item_data, 'variable', $request );

		// If validation failed then return the error response.
		/*if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}*/

		// Add item to cart once validation is passed.
		$item_added = $this->add_item_to_cart( $product_to_add );

		return $item_added;
	} // END add_to_cart_handler_variable()

	/**
	 * Adds the item to the cart once passed validation.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   array           $product_to_add - Passes details of the item ready to add to the cart.
	 * @param   WP_REST_Request $request        - Full details about the request.
	 * @return  array           $item_added      - Returns details of the added item in the cart.
	 */
	public function add_item_to_cart( $product_to_add = array() ) {
		$product_id     = $product_to_add['product_id'];
		$quantity       = $product_to_add['quantity'];
		$variation_id   = $product_to_add['variation_id'];
		$variation      = $product_to_add['variation'];
		$item_data      = $product_to_add['item_data'];
		$item_key       = $product_to_add['item_key'];
		$product_data   = $product_to_add['product_data'];
		$request        = $product_to_add['request'];

		$controller = new CoCart_Cart_V2_Controller();

		// If item_key is set, then the item is already in the cart so just update the quantity.
		if ( ! empty( $item_key ) ) {
			$cart_contents = $controller->get_cart( array( 'raw' => true ) );

			$new_quantity = $quantity + $cart_contents[ $item_key ]['quantity'];

			WC()->cart->set_quantity( $item_key, $new_quantity );

			$item_added = $controller->get_cart_item( $item_key, 'add' );

			/**
			 * Action hook will trigger if item was added again but updated in cart.
			 *
			 * @since   2.1.0
			 * @version 3.0.0
			 * @param   string $item_key
			 * @param   array  $item_added
			 * @param   int    $new_quantity
			 * @param   array  $request
			 */
			do_action( 'cocart_item_added_updated_in_cart', $item_key, $item_added, $new_quantity, $request );
		} else {
			// Add item to cart.
			$item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $item_data );

			// Return response to added item to cart or return error.
			if ( $item_key ) {
				// Re-calculate cart totals once item has been added.
				WC()->cart->calculate_totals();

				// Return item details.
				$item_added = $controller->get_cart_item( $item_key, 'add' );

				/**
				 * 
				 * @since   2.
				 * @version 3.0.0
				 * @param   $item_key
				 * @param   $item_added
				 * @param   $request
				 */
				do_action( 'cocart_item_added_to_cart', $item_key, $item_added, $request );
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

				throw new CoCart_Data_Exception( 'cocart_cannot_add_to_cart', $message, 403 );
			}
		}

		return $item_added;
	} // END add_item_to_cart()

	/**
	 * Get the query params for adding items.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 2.7.2
	 * @return  array $params
	 */
	public function get_collection_params() {
		$params = array(
			'product_id'     => array(
				'description'       => __( 'Unique identifier for the product or variation ID.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'       => array(
				'required'          => true,
				'default'           => 1,
				'description'       => __( 'Quantity of this item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'float',
				'validate_callback' => function( $value, $request, $param ) {
					return is_numeric( $value );
				},
			),
			'variation'      => array(
				'required'          => false,
				'description'       => __( 'The variation attributes that identity the variation of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'item_data' => array(
				'required'          => false,
				'description'       => __( 'Additional item data passed to make item unique.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_cart' => array(
				'description' => __( 'Returns the cart once item is added.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
