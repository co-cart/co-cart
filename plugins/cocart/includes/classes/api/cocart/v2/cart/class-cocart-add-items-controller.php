<?php
/**
 * CoCart - Add Items controller
 *
 * Handles the request to add items to the cart with /cart/add-items endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Add Items controller class.
 *
 * @package CoCart\API
 */
class CoCart_Add_Items_v2_Controller extends CoCart_Add_Item_Controller {

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
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Add other bundled or grouped products to Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  WP_REST_Response
	 */
	public function add_items_to_cart( $request = array() ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
			$items      = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			$controller = new CoCart_Cart_V2_Controller();

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

			// Add to cart handlers.
			$add_items_to_cart_handler = apply_filters( 'cocart_add_items_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			if ( has_filter( 'cocart_add_items_to_cart_handler_' . $add_items_to_cart_handler ) ) {
				$was_added_to_cart = apply_filters( 'cocart_add_items_to_cart_handler_' . $add_items_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
			} else {
				$was_added_to_cart = $this->add_to_cart_handler_grouped( $product_id, $items, $request );
			}

			if ( ! is_wp_error( $was_added_to_cart ) ) {
				/**
				 * Set customers billing email address.
				 *
				 * @since 3.1.0
				 */
				if ( isset( $request['email'] ) ) {
					WC()->customer->set_props(
						array(
							'billing_email' => trim( esc_html( $request['email'] ) ),
						)
					);
				}

				// Was it requested to return the items details after being added?
				if ( isset( $request['return_items'] ) && is_bool( $request['return_items'] ) && $request['return_items'] ) {
					$response = array();

					foreach ( $was_added_to_cart as $id => $item ) {
						$response[] = $controller->get_item( $item['data'], $item, $item['key'], true );
					}
				} else {
					$response = $controller->get_cart_contents( $request );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			}

			return $was_added_to_cart;
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
	 * @param  string          $product_id - Contains the id of the container product to add to the cart.
	 * @param  array           $items      - Contains the quantity of the items to add to the cart.
	 * @param  WP_REST_Request $request    - Full details about the request.
	 * @return bool            success or not
	 */
	public function add_to_cart_handler_grouped( $product_id, $items, $request ) {
		try {
			$controller = new CoCart_Cart_V2_Controller();

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
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @return  array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Add Items', 'cart-rest-api-for-woocommerce' ),
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
	 * Get the query params for adding items.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @return  array $params
	 */
	public function get_collection_params() {
		$controller = new CoCart_Cart_V2_Controller();

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
			'return_items' => array(
				'description' => __( 'Returns the items details once added.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
			),
		);

		/**
		 * Extend the query parameters.
		 *
		 * Dev Note: Nothing needs to pass so your safe if you think you will remove any default parameters.
		 *
		 * @since 3.1.0
		 */
		$params += apply_filters( 'cocart_add_items_query_parameters', array() );

		return $params;
	} // END get_collection_params()

} // END class
