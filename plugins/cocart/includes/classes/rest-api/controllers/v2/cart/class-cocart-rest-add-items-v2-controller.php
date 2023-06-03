<?php
/**
 * REST API: CoCart_REST_Add_Items_v2_Controller class
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
 * This REST API controller handles requests to add grouped items or
 * custom multiple handler to the cart via "cocart/v2/cart/add-items" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_Add_Item_Controller
 */
class CoCart_REST_Add_Items_v2_Controller extends CoCart_Add_Item_Controller {

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
	protected $rest_base = 'cart/add-items';

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
		// Add Items - cocart/v2/cart/add-items (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_items_to_cart' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Add other bundled or grouped products to Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_items_to_cart( $request = array() ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
			$items      = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			$controller = new CoCart_REST_Cart_v2_Controller();

			// Validate product ID before continuing and return correct product ID if different.
			$product_id = $this->validate_product_id( $product_id );

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
			$add_items_to_cart_handler = apply_filters( 'cocart_add_items_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			if ( has_filter( 'cocart_add_items_to_cart_handler_' . $add_items_to_cart_handler ) ) {
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
				$items_added_to_cart = apply_filters( 'cocart_add_items_to_cart_handler_' . $add_items_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
			} else {
				$items_added_to_cart = $this->add_to_cart_handler_grouped( $product_id, $items, $request );
			}

			if ( ! is_wp_error( $items_added_to_cart ) ) {
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

				/**
				 * Added items to cart hook.
				 *
				 * Allows for additional requested data to be processed via a third party once items are added to the cart.
				 *
				 * @since 4.0.0 Introduced.
				 *
				 * @param WP_REST_Request $request Full details about the request.
				 * @param object          $controller The Cart controller class.
				 * @param string          $add_items_to_cart_handler The product type added to cart.
				 * @param array           $items_added_to_cart The products added to cart.
				 */
				do_action( 'cocart_added_items_to_cart', $request, $controller, $add_items_to_cart_handler, $items_added_to_cart );

				// Was it requested to return the items details after being added?
				if ( isset( $request['return_items'] ) && is_bool( $request['return_items'] ) && $request['return_items'] ) {
					$response = array();

					foreach ( $items_added_to_cart as $id => $item ) {
						$response[] = $controller->get_item( $item['data'], $item, $request );
					}
				} else {
					$response = $controller->get_cart_contents( $request );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			}

			return $items_added_to_cart;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_items_to_cart()

	/**
	 * Handle adding grouped product to the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param string          $product_id Contains the id of the container product to add to the cart.
	 * @param array           $items      Contains the quantity of the items to add to the cart.
	 * @param WP_REST_Request $request    Full details about the request.
	 *
	 * @return bool            success or not
	 */
	public function add_to_cart_handler_grouped( $product_id, $items, $request ) {
		try {
			$controller = new CoCart_REST_Cart_v2_Controller();

			$was_added_to_cart = false;
			$added_to_cart     = array();

			if ( ! empty( $items ) ) {
				$quantity_set = false;

				foreach ( $items as $item => $quantity ) {
					$quantity = wc_stock_amount( $quantity );

					if ( $quantity <= 0 ) {
						continue;
					}

					$quantity_set = true;

					// Product validation.
					$product_to_add = $controller->validate_product( $item, $quantity, 0, array(), array(), 'grouped', $request );

					/**
					 * If validation failed then return error response.
					 *
					 * @param $product_to_add
					 */
					if ( is_wp_error( $product_to_add ) ) {
						return $product_to_add;
					}

					// Suppress total recalculation until finished.
					remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

					// Add item to cart once validation is passed.
					$item_added = $this->add_item_to_cart( $product_to_add );

					if ( false !== $item_added ) {
						$was_added_to_cart      = true;
						$added_to_cart[ $item ] = $item_added;
					}

					add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );
				}

				if ( ! $was_added_to_cart && ! $quantity_set ) {
					throw new CoCart_Data_Exception( 'cocart_grouped_product_failed', __( 'Please choose the quantity of items you wish to add to your cart.', 'cart-rest-api-for-woocommerce' ), 404 );
				} elseif ( $was_added_to_cart ) {
					cocart_add_to_cart_message( $added_to_cart );

					// Calculate totals now all items in the group has been added to cart.
					$controller->get_cart_instance()->calculate_totals();

					return $added_to_cart;
				}
			} else {
				throw new CoCart_Data_Exception( 'cocart_grouped_product_empty', __( 'Please choose a product to add to your cart.', 'cart-rest-api-for-woocommerce' ), 404 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_to_cart_handler_grouped()

	/**
	 * Get the schema for adding items, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @since      3.0.0 Introduced.
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
			'title'      => 'cocart_cart_add_items',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'required'    => true,
					'description' => __( 'Unique identifier for the container product ID.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'quantity'     => array(
					'required'    => true,
					'description' => __( 'List of items and quantity in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
				),
				'email'        => array(
					'required'    => false,
					'description' => __( 'Customers billing email address.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'phone'        => array(
					'required'    => false,
					'description' => __( 'Customers billing phone number.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'return_items' => array(
					'required'    => false,
					'default'     => false,
					'description' => __( 'Returns the items details once added.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				),
			),
		);

		$schema['properties'] = apply_filters( 'cocart_add_items_schema', $schema['properties'], $this->rest_base );

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
	 * @since 3.0.0 Introduced.
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
			'id'           => array(
				'description'       => __( 'Unique identifier for the container product ID.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'     => array(
				'required'          => true,
				'description'       => __( 'List of items and quantity to add to the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'object',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'email'        => array(
				'required'          => false,
				'description'       => __( 'Set the customers billing email address.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'phone'        => array(
				'required'          => false,
				'description'       => __( 'Set the customers billing phone number.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_items' => array(
				'description' => __( 'Returns the items details once added.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
			),
		);

		/**
		 * Extends the query parameters.
		 *
		 * Dev Note: Nothing needs to pass so your safe if you think you will remove any default parameters.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$params += apply_filters( 'cocart_add_items_query_parameters', array() );

		return $params;
	} // END get_collection_params()

} // END class
