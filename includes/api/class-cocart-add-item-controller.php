<?php
/**
 * CoCart - Add Item controller
 *
 * Handles the request to add items to the cart with /cart/add-item endpoint.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @version  3.0.1
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
		// Add Item - cocart/v2/cart/add-item (POST).
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
	 * Add to Cart.
	 *
	 * @throws  CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  WP_REST_Response
	 */
	public function add_to_cart( $request = array() ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
			$quantity   = ! isset( $request['quantity'] ) ? 1 : wc_clean( wp_unslash( $request['quantity'] ) );
			$variation  = ! isset( $request['variation'] ) ? array() : $request['variation'];
			$item_data  = ! isset( $request['item_data'] ) ? array() : $request['item_data'];

			$controller = new CoCart_Cart_V2_Controller();

			// Filters additional requested data.
			$request = $controller->filter_request_data( $request );

			// Validate product ID before continuing and return correct product ID if different.
			$product_id = $controller->validate_product_id( $product_id );

			if ( is_wp_error( $product_id ) ) {
				return $product_id;
			}

			// Validate quantity before continuing and return formatted.
			$quantity = $controller->validate_quantity( $quantity );

			if ( is_wp_error( $quantity ) ) {
				return $quantity;
			}

			// The product we are attempting to add to the cart.
			$adding_to_cart = wc_get_product( $product_id );
			$adding_to_cart = $controller->validate_product_for_cart( $adding_to_cart );

			// Return error response if product cannot be added to cart?
			if ( is_wp_error( $adding_to_cart ) ) {
				return $adding_to_cart;
			}

			// Add to cart handlers.
			$add_to_cart_handler = apply_filters( 'cocart_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
				$was_added_to_cart = $this->add_to_cart_handler_variable( $product_id, $quantity, null, $variation, $item_data, $request );
			} elseif ( has_filter( 'cocart_add_to_cart_handler_' . $add_to_cart_handler ) ) {
				$was_added_to_cart = apply_filters( 'cocart_add_to_cart_handler_' . $add_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
			} else {
				$was_added_to_cart = $this->add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request );
			}

			if ( ! is_wp_error( $was_added_to_cart ) ) {
				cocart_add_to_cart_message( array( $was_added_to_cart['product_id'] => $was_added_to_cart['quantity'] ) );

				// Was it requested to return the item details after being added?
				if ( isset( $request['return_item'] ) && is_bool( $request['return_item'] ) && $request['return_item'] ) {
					$response = $controller->get_item( $was_added_to_cart['data'], $was_added_to_cart, $was_added_to_cart['key'], true );
				} else {
					$response = $controller->get_cart_contents( $request );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			}

			return $was_added_to_cart;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_to_cart()

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   string          $product_id - Contains the id of the product to add to the cart.
	 * @param   float           $quantity   - Contains the quantity of the item to add to the cart.
	 * @param   array           $item_data  - Contains extra cart item data we want to pass into the item.
	 * @param   WP_REST_Request $request    - Full details about the request.
	 * @return  bool            success or not
	 */
	public function add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request = array() ) {
		$controller = new CoCart_Cart_V2_Controller();

		$product_to_add = $controller->validate_product( $product_id, $quantity, 0, array(), $item_data, 'simple', $request );

		/**
		 * If validation failed then return error response.
		 *
		 * @param $product_to_add
		 */
		if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}

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
	 * @param   string          $product_id - Contains the id of the product to add to the cart.
	 * @param   float           $quantity   - Contains the quantity of the item to add to the cart.
	 * @param   null            $deprecated - Used to pass the variation id of the product to add to the cart.
	 * @param   array           $variation  - Contains the selected attributes of a variation.
	 * @param   array           $item_data  - Contains extra cart item data we want to pass into the item.
	 * @param   WP_REST_Request $request    - Full details about the request.
	 * @return  bool            success or not
	 */
	public function add_to_cart_handler_variable( $product_id, $quantity, $deprecated = null, $variation, $item_data, $request = array() ) {
		$controller = new CoCart_Cart_V2_Controller();

		$product_to_add = $controller->validate_product( $product_id, $quantity, $deprecated, $variation, $item_data, 'variable', $request );

		/**
		 * If validation failed then return error response.
		 *
		 * @param $product_to_add
		 */
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
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.1
	 * @param   array $product_to_add - Passes details of the item ready to add to the cart.
	 * @return  array $item_added     - Returns details of the added item in the cart.
	 */
	public function add_item_to_cart( $product_to_add = array() ) {
		$product_id   = $product_to_add['product_id'];
		$quantity     = $product_to_add['quantity'];
		$variation_id = $product_to_add['variation_id'];
		$variation    = $product_to_add['variation'];
		$item_data    = $product_to_add['item_data'];
		$item_key     = $product_to_add['item_key'];
		$product_data = $product_to_add['product_data'];
		$request      = $product_to_add['request'];

		try {
			$controller = new CoCart_Cart_V2_Controller();

			// If item_key is set, then the item is already in the cart so just update the quantity.
			if ( ! empty( $item_key ) ) {
				$cart_contents = $controller->get_cart( array( 'raw' => true ) );

				$new_quantity = $quantity + $cart_contents[ $item_key ]['quantity'];

				$controller->get_cart_instance()->set_quantity( $item_key, $new_quantity );

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
				/**
				 * Add item to cart without WC product validation.
				 *
				 * @since 3.0.0
				 * @param bool
				 * @param $product_data Contains the product data of the product to add to cart.
				 * @param $product_id   Contains the id of the product to add to the cart.
				 */
				if ( apply_filters( 'cocart_skip_woocommerce_item_validation', false, $product_data, $product_id ) ) {
					$item_key = $controller->add_cart_item( $product_id, $quantity, $variation_id, $variation, $item_data );
				} else {
					$item_key = $controller->get_cart_instance()->add_to_cart( $product_id, $quantity, $variation_id, $variation, $item_data );
				}

				// Return response to added item to cart or return error.
				if ( $item_key ) {
					// Re-calculate cart totals once item has been added.
					$controller->get_cart_instance()->calculate_totals();

					// Return item details.
					$item_added = $controller->get_cart_item( $item_key, 'add' );

					/**
					 * Action hook will trigger if the item was added.
					 *
					 * @since   2.1.0
					 * @version 3.0.0
					 * @param   $item_key
					 * @param   $item_added
					 * @param   $request
					 */
					do_action( 'cocart_item_added_to_cart', $item_key, $item_added, $request );
				} else {
					/**
					 * If WooCommerce can provide a reason for the error then let that error message return first.
					 *
					 * @since 3.0.1
					 */
					$controller->convert_notices_to_exceptions( 'cocart_add_to_cart_error' );

					/* translators: %s: product name */
					$message = sprintf( __( 'You cannot add "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

					/**
					 * Filters message about product cannot be added to cart.
					 *
					 * @param string     $message      Message.
					 * @param WC_Product $product_data Product data.
					 */
					$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product_data );

					throw new CoCart_Data_Exception( 'cocart_cannot_add_to_cart', $message, 403 );
				}
			}

			return $item_added;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_item_to_cart()

	/**
	 * Get the schema for adding an item, conforming to JSON Schema.
	 *
	 * @access  public
	 * @since   2.1.2
	 * @version 3.0.0
	 * @return  array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Add Item', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'required'    => true,
					'description' => __( 'Unique identifier for the product or variation ID.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'quantity'    => array(
					'required'    => true,
					'default'     => '1',
					'description' => __( 'Quantity amount.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'variation'   => array(
					'required'    => false,
					'description' => __( 'Variation attributes that identity the variation of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
				),
				'item_data'   => array(
					'required'    => false,
					'description' => __( 'Additional item data to make the item unique.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
				),
				'return_item' => array(
					'required'    => false,
					'default'     => false,
					'description' => __( 'Returns the item details once added.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				),
			),
		);

		$schema['properties'] = apply_filters( 'cocart_add_item_schema', $schema['properties'], $this->rest_base );

		return $schema;
	} // END get_item_schema()

	/**
	 * Get the query params for adding items.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @return  array $params
	 */
	public function get_collection_params() {
		$controller = new CoCart_Cart_V2_Controller();

		$params = array_merge(
			$controller->get_collection_params(),
			array(
				'id'          => array(
					'required'          => true,
					'description'       => __( 'Unique identifier for the product or variation ID.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'quantity'    => array(
					'required'          => true,
					'default'           => '1',
					'description'       => __( 'Quantity of this item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'validate_callback' => array( $this, 'rest_validate_quantity_arg' ),
				),
				'variation'   => array(
					'required'          => false,
					'description'       => __( 'The variation attributes that identity the variation of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'object',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'item_data'   => array(
					'required'          => false,
					'description'       => __( 'Additional item data passed to make item unique.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'object',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'return_item' => array(
					'description' => __( 'Returns the item details once added.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
					'type'        => 'boolean',
				),
			)
		);

		return $params;
	} // END get_collection_params()

	/**
	 * Validates the quantity argument.
	 *
	 * @access public
	 * @since  3.0.0
	 * @param int|float       $value   - Number of quantity to validate.
	 * @param WP_REST_Request $request - Full details about the request.
	 * @param string          $param   - Argument parameters.
	 * @return bool
	 */
	public function rest_validate_quantity_arg( $value, $request, $param ) {
		if ( is_numeric( $value ) || is_float( $value ) ) {
			return true;
		}

		return false;
	} // END rest_validate_quantity_arg()

} // END class
