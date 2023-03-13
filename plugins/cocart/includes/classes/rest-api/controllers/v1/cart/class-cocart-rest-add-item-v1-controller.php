<?php
/**
 * REST API: CoCart_Add_Item_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v1
 * @since   2.1.0 Introduced.
 * @version 2.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for adding items to cart via the REST API. (API v1)
 *
 * This REST API controller handles requests to add items to the cart
 * via cocart/v1/add-item endpoint.
 *
 * @since 2.1.0 Introduced.
 *
 * @see CoCart_API_Controller
 */
class CoCart_Add_Item_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'add-item';

	/**
	 * Registers the route for adding items to the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 2.5.0 Added permission callback set to return true due to a change to the REST API in WordPress v5.5
	 */
	public function register_routes() {
		// Add Item - cocart/v1/add-item (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_to_cart' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Adds the requested item to the cart.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 2.7.0
	 *
	 * @see CoCart_Add_Item_Controller::add_to_cart_handler_variable()
	 * @see CoCart_Add_Item_Controller::add_to_cart_handler_simple()
	 * @see CoCart_API_Controller::validate_product_id()
	 * @see CoCart_API_Controller::get_cart_contents()
	 * @see CoCart_API_Controller::get_response()
	 * @see Logger::log()
	 *
	 * @param WP_REST_Request $data Full details about the request.
	 *
	 * @return WP_Error on failure, WP_REST_Response on success.
	 */
	public function add_to_cart( $data = array() ) {
		$product_id     = ! isset( $data['product_id'] ) ? 0 : wc_clean( wp_unslash( $data['product_id'] ) );
		$quantity       = ! isset( $data['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $data['quantity'] ) );
		$variation_id   = ! isset( $data['variation_id'] ) ? 0 : absint( wp_unslash( $data['variation_id'] ) );
		$variation      = ! isset( $data['variation'] ) ? array() : $data['variation'];
		$cart_item_data = ! isset( $data['cart_item_data'] ) ? array() : $data['cart_item_data'];

		// Validate product ID before continuing and return correct product ID if different.
		$product_id = $this->validate_product_id( $product_id );

		// Return failed product ID validation if any.
		if ( is_wp_error( $product_id ) ) {
			return $product_id;
		}

		// The product we are attempting to add to the cart.
		$adding_to_cart = wc_get_product( $product_id );

		// Return error if item does not exist.
		if ( ! $adding_to_cart ) {
			$message = __( 'This product does not exist!', 'cart-rest-api-for-woocommerce' );

			CoCart\Logger::log( $message, 'error' );

			return new WP_Error( 'cocart_product_does_not_exist', $message, array( 'status' => 404 ) );
		}

		/**
		 * Add to cart handlers.
		 *
		 * This filter allows you to identify which handler to use for the product
		 * type attempting to add to the cart.
		 *
		 * @param string $adding_to_cart_handler The product type to identify handler.
		 * @param WC_Product $adding_to_cart The product object.
		 */
		$add_to_cart_handler = apply_filters( 'cocart_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
			$was_added_to_cart = $this->add_to_cart_handler_variable( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
		} elseif ( has_filter( 'cocart_add_to_cart_handler_' . $add_to_cart_handler ) ) {
			$was_added_to_cart = apply_filters( 'cocart_add_to_cart_handler_' . $add_to_cart_handler, $adding_to_cart ); // Custom handler.
		} else {
			$was_added_to_cart = $this->add_to_cart_handler_simple( $product_id, $quantity, $cart_item_data );
		}

		// Return error response if it is an error.
		if ( is_wp_error( $was_added_to_cart ) ) {
			return $was_added_to_cart;
		} else {
			$response = '';

			// Was it requested to return the whole cart once item added?
			if ( $data['return_cart'] ) {
				$response = $this->get_cart_contents( $data );
			} elseif ( is_array( $was_added_to_cart ) ) {
				$response = $was_added_to_cart;
			}

			return $this->get_response( $response, $this->rest_base );
		}
	} // END add_to_cart()

	/**
	 * Handles adding simple products to the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @see CoCart_API_Controller::validate_product_id()
	 * @see CoCart_API_Controller::add_item_to_cart()
	 *
	 * @param  int|string $product_id     Contains the id of the product to add to the cart.
	 * @param  int|float  $quantity       Contains the quantity of the item to add to the cart.
	 * @param  array      $cart_item_data Contains extra cart item data we want to pass into the item.
	 * @return bool       success or not
	 */
	public function add_to_cart_handler_simple( $product_id, $quantity, $cart_item_data ) {
		$product_to_add = $this->validate_product( $product_id, $quantity, 0, array(), $cart_item_data, 'simple' );

		// If validation failed then return the error response.
		if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}

		// Add item to cart once validation is passed.
		$item_added = $this->add_item_to_cart( $product_to_add );

		return $item_added;
	} // END add_to_cart_handler_simple()

	/**
	 * Handles adding variable products to the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @see CoCart_API_Controller::validate_product_id()
	 * @see CoCart_API_Controller::add_item_to_cart()
	 *
	 * @param int|string $product_id     Contains the id of the product to add to the cart.
	 * @param int|float  $quantity       Contains the quantity of the item to add to the cart.
	 * @param int|string $variation_id   Contains the id of the product to add to the cart.
	 * @param array      $cart_item_data Contains extra cart item data we want to pass into the item.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_variable( $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$product_to_add = $this->validate_product( $product_id, $quantity, $variation_id, $variation, $cart_item_data, 'variable' );

		// If validation failed then return the error response.
		if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}

		// Add item to cart once validation is passed.
		$item_added = $this->add_item_to_cart( $product_to_add );

		return $item_added;
	} // END add_to_cart_handler_variable()

	/**
	 * Adds the item to the cart once passed validation.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 4.0.0
	 *
	 * @see CoCart_API_Controller::get_cart()
	 * @see CoCart_API_Controller::get_cart_item()
	 * @see CoCart_API_Controller::return_additional_item_data()
	 * @see Logger::log()
	 *
	 * @param array $product_to_add Passes details of the item ready to add to the cart.
	 *
	 * @return array $item_added    Returns details of the added item in the cart.
	 */
	public function add_item_to_cart( $product_to_add = array() ) {
		$product_id     = $product_to_add['product_id'];
		$quantity       = $product_to_add['quantity'];
		$variation_id   = $product_to_add['variation_id'];
		$variation      = $product_to_add['variation'];
		$cart_item_data = $product_to_add['cart_item_data'];
		$cart_item_key  = $product_to_add['cart_item_key'];
		$product_data   = $product_to_add['product_data'];

		// If cart_item_key is set, then the item is already in the cart so just update the quantity.
		if ( ! empty( $cart_item_key ) ) {
			$cart_contents = $this->get_cart( array( 'raw' => true ) );

			$new_quantity = $quantity + $cart_contents[ $cart_item_key ]['quantity'];

			// Set new quantity for item.
			WC()->cart->set_quantity( $cart_item_key, $new_quantity );

			// Return item details.
			$item_added = $this->return_additional_item_data( $this->get_cart_item( $cart_item_key, 'add' ), $cart_item_key );

			/**
			 * Fires if item was added again to the cart but updated the quantity.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string $cart_item_key Item key of the item added again.
			 * @param array  $item_added    Item added to cart again.
			 * @param int    $new_quantity  New quantity of the item.
			 */
			do_action( 'cocart_item_added_updated_in_cart', $cart_item_key, $item_added, $new_quantity );
		} else {
			// Add item to cart.
			$item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			// Return response to added item to cart or return error.
			if ( $item_key ) {
				// Re-calculate cart totals once item has been added.
				WC()->cart->calculate_totals();

				// Return item details.
				$item_added = $this->return_additional_item_data( $this->get_cart_item( $item_key, 'add' ), $item_key );

				do_action( 'cocart_item_added_to_cart', $item_key, $item_added );
			} else {
				/* translators: %s: product name */
				$message = sprintf( __( 'You cannot add "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

				CoCart\Logger::log( $message, 'error' );

				/**
				 * Filters message about product cannot be added to cart.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product_data Product data.
				 */
				$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product_data );

				return new WP_Error( 'cocart_cannot_add_to_cart', $message, array( 'status' => 403 ) );
			}
		}

		return $item_added;
	} // END add_item_to_cart()

	/**
	 * Applies additional item data to return.
	 *
	 * @access public
	 *
	 * @since 2.2.0 Introduced.
	 * @since 2.6.0 Fetch product data if product data does not already exist with item data.
	 *
	 * @param array  $item_added Item added to cart.
	 * @param string $item_key   Item key of the item added.
	 *
	 * @return array $item_added Item added to cart with additional data.
	 */
	public function return_additional_item_data( $item_added, $item_key = '' ) {
		/**
		 * If product data is missing then get product data.
		 *
		 * @since 2.6.0 Introduced.
		 */
		if ( ! isset( $item_added['data'] ) ) {
			$item_added['data'] = wc_get_product( $item_added['variation_id'] ? $item_added['variation_id'] : $item_added['product_id'] );
		}

		$_product = $item_added['data'];

		// Adds the product name and title.
		$item_added['product_name']  = apply_filters( 'cocart_item_added_product_name', $_product->get_name(), $_product, $item_key );
		$item_added['product_title'] = apply_filters( 'cocart_item_added_product_title', $_product->get_title(), $_product, $item_key );

		// Add product price.
		$item_added['product_price'] = html_entity_decode( strip_tags( wc_price( $_product->get_price() ) ) );

		/**
		 * This filter allows additional data to be returned.
		 *
		 * @param array $item_added Item added to cart.
		 * @param string $item_key Item key of the item added.
		 */
		$item_added = apply_filters( 'cocart_item_added', $item_added, $item_key );

		return $item_added;
	} // END return_additional_item_data()

	/**
	 * Get the schema for adding an item, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @since   2.1.2 Introduced.
	 * @version 2.7.2
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Add Item', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'product_id'     => array(
					'required'    => true,
					'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'quantity'       => array(
					'required'    => true,
					'default'     => 1,
					'description' => __( 'Quantity amount.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'float',
				),
				'variation_id'   => array(
					'required'    => false,
					'description' => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
				),
				'variation'      => array(
					'required'    => false,
					'description' => __( 'Variation attributes that identity the variation of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
				),
				'cart_item_data' => array(
					'required'    => false,
					'description' => __( 'Additional item data to make the item unique.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
				),
				'return_cart'    => array(
					'required'    => false,
					'default'     => false,
					'description' => __( 'Returns the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				),
			),
		);

		$schema['properties'] = apply_filters( 'cocart_add_item_schema', $schema['properties'] );

		return $schema;
	} // END get_item_schema()

	/**
	 * Get the query params for adding items.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 2.7.2
	 *
	 * @return array $params Query parameters for adding items.
	 */
	public function get_collection_params() {
		$params = array(
			'product_id'     => array(
				'description'       => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'       => array(
				'required'          => true,
				'default'           => 1,
				'description'       => __( 'The quantity amount of the item to add to cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'float',
				'validate_callback' => function( $value, $request, $param ) {
					return is_numeric( $value );
				},
			),
			'variation_id'   => array(
				'required'          => false,
				'description'       => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'variation'      => array(
				'required'          => false,
				'description'       => __( 'The variation attributes that identity the variation of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'cart_item_data' => array(
				'required'          => false,
				'description'       => __( 'Additional item data passed to make item unique.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_cart'    => array(
				'required'          => false,
				'default'           => false,
				'description'       => __( 'Returns the cart once item is added.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
