<?php
/**
 * REST API: CoCart_REST_Add_Item_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for adding items to cart via the REST API. (API v2)
 *
 * This REST API controller handles requests to add singular items to the cart
 * via "cocart/v2/cart/add-item" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_Add_Item_Controller
 */
class CoCart_REST_Add_Item_v2_Controller extends CoCart_Add_Item_Controller {

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
	 *
	 * @since 4.0.0 Allowed route to be requested in a batch request.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
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
				'allow_batch' => array( 'v1' => true ),
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Add to Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 * @since 4.0.0 Price query is added to determine if we need to cache the item for calculating totals.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function add_to_cart( $request = array() ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
			$quantity   = ! isset( $request['quantity'] ) ? 1 : wc_clean( wp_unslash( $request['quantity'] ) );
			$variation  = ! isset( $request['variation'] ) ? array() : $request['variation'];
			$item_data  = ! isset( $request['item_data'] ) ? array() : $request['item_data'];
			$price      = ! isset( $request['price'] ) ? '' : wc_clean( wp_unslash( $request['price'] ) );

			$controller = new CoCart_REST_Cart_v2_Controller();

			// Validate product ID before continuing and return correct product ID if different.
			$product_id = $controller->validate_product_id( $product_id );

			// Return error response if product ID is not found.
			if ( is_wp_error( $product_id ) ) {
				return $product_id;
			}

			// The product we are attempting to add to the cart.
			$adding_to_cart = wc_get_product( $product_id );
			$adding_to_cart = $controller->validate_product_for_cart( $adding_to_cart );

			// Return error response if product cannot be added to cart?
			if ( is_wp_error( $adding_to_cart ) ) {
				return $adding_to_cart;
			}

			// Filters additional requested data.
			$request = $controller->filter_request_data( $request );

			// Validate quantity before continuing and return formatted.
			$quantity = $controller->validate_quantity( $quantity, $adding_to_cart );

			if ( is_wp_error( $quantity ) ) {
				return $quantity;
			}

			/**
			 * Filters the add to cart handler.
			 *
			 * Allows you to identify which handler to use for the product
			 * type attempting to add to the cart using it's own validation method.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string     $adding_to_cart_handler The product type to identify handler.
			 * @param WC_Product $adding_to_cart         Product object.
			 */
			$add_to_cart_handler = apply_filters( 'cocart_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
				$item_added_to_cart = $this->add_to_cart_handler_variable( $product_id, $quantity, null, $variation, $item_data, $request );
			} elseif ( has_filter( 'cocart_add_to_cart_handler_' . $add_to_cart_handler ) ) {
				/**
				 * Filter allows to use a custom add to cart handler.
				 *
				 * Allows you to specify the handlers validation method for
				 * adding item to the cart.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string          $adding_to_cart_handler The product type to identify handler.
				 * @param WC_Product      $adding_to_cart         Product object.
				 * @param WP_REST_Request $request                Full details about the request.
				 */
				$item_added_to_cart = apply_filters( 'cocart_add_to_cart_handler_' . $add_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
			} else {
				$item_added_to_cart = $this->add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request );
			}

			if ( ! is_wp_error( $item_added_to_cart ) ) {
				/**
				 * Set customers billing email address.
				 *
				 * @since 3.1.0 Introduced.
				 */
				if ( isset( $request['email'] ) ) {
					$is_email = \WC_Validation::is_email( $request['email'] );

					if ( $is_email ) {
						WC()->customer->set_props(
							array(
								'billing_email' => trim( esc_html( $request['email'] ) ),
							)
						);
					}
				}

				/**
				 * Set customers billing phone number.
				 *
				 * @since 4.0.0 Introduced.
				 */
				if ( isset( $request['phone'] ) ) {
					$is_phone = \WC_Validation::is_phone( $request['phone'] );

					if ( $is_phone ) {
						WC()->customer->set_props(
							array(
								'billing_phone' => trim( esc_html( $request['phone'] ) ),
							)
						);
					}
				}

				cocart_add_to_cart_message( array( $item_added_to_cart['product_id'] => $item_added_to_cart['quantity'] ) );

				/**
				 * Filter overrides the cart item for anything extra.
				 *
				 * DEVELOPER WARNING: THIS FILTER CAN CAUSE HAVOC SO BE CAREFUL WHEN USING IT!
				 *
				 * @since 3.1.0 Introduced.
				 */
				$item_added_to_cart = apply_filters( 'cocart_override_cart_item', $item_added_to_cart, $request );

				/**
				 * Fires once an item has been added to cart.
				 *
				 * Allows for additional requested data to be processed via a third party once item is added to the cart.
				 *
				 * @since 4.0.0 Introduced.
				 *
				 * @param WP_REST_Request $request             Full details about the request.
				 * @param object          $controller          The Cart controller class.
				 * @param string          $add_to_cart_handler The product type added to cart.
				 * @param array           $item_added_to_cart  The product added to cart.
				 */
				do_action( 'cocart_added_item_to_cart', $request, $controller, $add_to_cart_handler, $item_added_to_cart );

				/**
				 * Cache cart item.
				 *
				 * This allows us to calculate the overridden price later if one was set.
				 *
				 * @since 3.1.0 Introduced.
				 * @since 4.0.0 Now checks if the price parameter is used and a salt key is provided.
				 */
				if ( ! empty( $price ) && ( maybe_cocart_require_salt() === $request->get_header( 'csaltk' ) ) ) {
					$controller->cache_cart_item( $item_added_to_cart );
				}

				/**
				 * Calculate the totals again here incase of custom data applied
				 * like a change of price for example so the response is upto date
				 * when returned.
				 *
				 * @since 3.1.0 Introduced.
				 */
				$controller->calculate_totals();

				// Was it requested to return the item details after being added?
				if ( isset( $request['return_item'] ) && is_bool( $request['return_item'] ) && $request['return_item'] ) {
					$response = $controller->get_item( $item_added_to_cart['data'], $item_added_to_cart, $request );
				} else {
					$response = $controller->get_cart_contents( $request );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			}

			return $item_added_to_cart;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_to_cart()

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @param string          $product_id Contains the id of the product to add to the cart.
	 * @param float           $quantity   Contains the quantity of the item to add to the cart.
	 * @param array           $item_data  Contains extra cart item data we want to pass into the item.
	 * @param WP_REST_Request $request    Full details about the request.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request = array() ) {
		$controller = new CoCart_REST_Cart_v2_Controller();

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
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param string          $product_id Contains the id of the product to add to the cart.
	 * @param float           $quantity   Contains the quantity of the item to add to the cart.
	 * @param null            $deprecated Used to pass the variation id of the product to add to the cart.
	 * @param array           $variation  Contains the selected attributes of a variation.
	 * @param array           $item_data  Contains extra cart item data we want to pass into the item.
	 * @param WP_REST_Request $request    Full details about the request.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_variable( $product_id, $quantity, $deprecated, $variation, $item_data, $request = array() ) {
		$controller = new CoCart_REST_Cart_v2_Controller();

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
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param array $product_to_add Passes details of the item ready to add to the cart.
	 *
	 * @return array $item_added Returns details of the added item in the cart.
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
			$controller = new CoCart_REST_Cart_v2_Controller();

			// If item_key is set, then the item is already in the cart so just update the quantity.
			if ( ! empty( $item_key ) ) {
				$cart_contents = $controller->get_cart( array( 'raw' => true ) );

				$new_quantity = $quantity + $cart_contents[ $item_key ]['quantity'];

				$controller->get_cart_instance()->set_quantity( $item_key, $new_quantity );

				$item_added = $controller->get_cart_item( $item_key, 'add' );

				/**
				 * Fires if item was added again to the cart with the quantity increased.
				 *
				 * @since 2.1.0 Introduced.
				 * @since 3.0.0 Added $request parameter.
				 *
				 * @param string $item_key      Item key of the item added again.
				 * @param array  $item_added    Item added to cart again.
				 * @param int    $new_quantity  New quantity of the item.
				 * @param array  $request       Full details about the request.
				 */
				do_action( 'cocart_item_added_updated_in_cart', $item_key, $item_added, $new_quantity, $request );
			} else {
				/**
				 * Filter the item to skip product validation as it is added to cart.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param bool  $validate_product Whether to validate the product or not.
				 * @param array $product_data     Contains the product data of the product to add to cart.
				 * @param int   $product_id       Contains the id of the product to add to the cart.
				 */
				if ( apply_filters( 'cocart_skip_woocommerce_item_validation', false, $product_data, $product_id ) ) {
					$item_key = $controller->add_cart_item( $product_id, $quantity, $variation_id, $variation, $item_data, $product_data );
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
					 * Fires if the item was added to cart.
					 *
					 * @since   2.1.0 Introduced.
					 * @version 3.0.0
					 *
					 * @param string          $item_key   Item key of the item added.
					 * @param array           $item_added Item added to cart.
					 * @param WP_REST_Request $request    Full details about the request.
					 */
					do_action( 'cocart_item_added_to_cart', $item_key, $item_added, $request );
				} else {
					/**
					 * If WooCommerce can provide a reason for the error then let that error message return first.
					 *
					 * @since 3.0.1 Introduced.
					 */
					$controller->convert_notices_to_exceptions( 'cocart_add_to_cart_error' );

					/* translators: %s: product name */
					$message = sprintf( __( 'You cannot add "%s" to your cart.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() );

					/**
					 * Filters message about product cannot be added to cart.
					 *
					 * @param string     $message      Message.
					 * @param WC_Product $product_data Product object.
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
	 * @access public
	 *
	 * @since      2.1.2 Introduced.
	 * @version    3.1.0
	 * @deprecated 4.0.0 Replaced with `get_public_item_schema()`.
	 *
	 * @see get_public_item_schema()
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		cocart_deprecated_function( __FUNCTION__, '4.0', 'get_public_item_schema' );

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_add_item',
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
				'email'       => array(
					'required'    => false,
					'description' => __( 'Customers billing email address.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'price'       => array(
					'required'    => false,
					'description' => __( 'Set a custom price for the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
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
	 * Retrieves the item schema for adding an item.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {
		$controller = new CoCart_REST_Cart_v2_Controller();

		// Cart schema.
		$schema = $controller->get_public_item_schema();

		return $schema;
	} // END get_public_item_schema()

	/**
	 * Get the query params for adding items.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.0.0 Added phone number parameter.
	 *
	 * @return array $params Query parameters for the endpoint.
	 */
	public function get_collection_params() {
		$controller = new CoCart_REST_Cart_v2_Controller();

		// Cart query parameters.
		$params = $controller->get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'id'          => array(
				'description'       => __( 'Unique identifier for the product or variation ID.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'    => array(
				'description'       => __( 'Quantity of this item to add to the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'default'           => '1',
				'required'          => true,
				'validate_callback' => array( $this, 'rest_validate_quantity_arg' ),
			),
			'variation'   => array(
				'description'       => __( 'Variable attributes that identify the variation of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'item_data'   => array(
				'description'       => __( 'Additional item data passed to make item unique.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'email'       => array(
				'description'       => __( 'Set the customers billing email address.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'phone'       => array(
				'description'       => __( 'Set the customers billing phone number.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'price'       => array(
				'description'       => __( 'Overrides the general or sale price with a custom price for the item if set.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_item' => array(
				'description' => __( 'Returns the item details once added.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'required'    => false,
				'type'        => 'boolean',
			),
		);

		/**
		 * Filters the query parameters for adding item to cart.
		 *
		 * This filter allows you to extend the query parameters without removing any default parameters.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$params += apply_filters( 'cocart_add_item_query_parameters', array() );

		return $params;
	} // END get_collection_params()

	/**
	 * Validates the quantity argument.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param int|float       $value   Number of quantity to validate.
	 * @param WP_REST_Request $request Full details about the request.
	 * @param string          $param   Argument parameters.
	 *
	 * @return bool True if the quantity is valid, false otherwise.
	 */
	public function rest_validate_quantity_arg( $value, $request, $param ) {
		if ( is_numeric( $value ) || is_float( $value ) ) {
			return true;
		}

		return false;
	} // END rest_validate_quantity_arg()

} // END class
