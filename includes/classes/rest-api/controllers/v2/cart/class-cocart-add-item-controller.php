<?php
/**
 * REST API: CoCart_REST_Add_Item_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Add_Item_V2_Controller', 'CoCart_Add_Item_V2_Controller' );

/**
 * Controller for adding items to cart via the REST API. (API v2)
 *
 * This REST API controller handles requests to add singular items to the cart
 * via "cocart/v2/cart/add-item" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Add_Item_V2_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/add-item';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/add-item';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_to_cart' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Allowed route to be requested in a batch request.
	 *
	 * @deprecated 5.0.0 No longer use.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Add Item - cocart/v2/cart/add-item (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Add to Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function add_to_cart( $request ) {
		try {
			$cart = $this->get_cart_instance();

			$params = ! is_array( $request ) ? $request->get_params() : $request;

			$request = array_merge( array(
				'id'        => '0',
				'quantity'  => 1,
				'variation' => array(),
				'item_data' => array(),
			), $params );

			$request['id'] = wc_clean( wp_unslash( $request['id'] ) );

			// Validate product ID before continuing and return correct product ID if SKU was used.
			$request['id'] = CoCart_Utilities_Cart_Helpers::validate_product_id( $request['id'] );

			// Return error response if product ID is not found.
			if ( is_wp_error( $request['id'] ) ) {
				return $request['id'];
			}

			// The product we are attempting to add to the cart.
			$product = CoCart_Utilities_Cart_Helpers::validate_product_for_cart( $request );

			// Product type.
			$request['product_type'] = $product->get_type();

			// Filter requested data and variation data if any.
			$request = $this->filter_request_data( $this->parse_variation_data( $request, $product ) );

			if ( is_wp_error( $request ) ) {
				return $request;
			}

			// Generate an ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $cart->generate_cart_id( $request['id'], $request['variation_id'], $request['variation'], $request['item_data'] );

			// Find the cart item key in the existing cart.
			$existing_item_key = $this->find_product_in_cart( $item_key );

			$quantity_limits = new CoCart_Utilities_Quantity_Limits();

			if ( ! $request['container_item'] ) {
				// Validate quantity before continuing if item is singular and return formatted.
				$request['quantity'] = CoCart_Utilities_Cart_Helpers::validate_quantity( $request['quantity'], $product );

				// Update quantity for item already in cart.
				if ( $existing_item_key ) {
					$cart_item = $cart->cart_contents[ $existing_item_key ];

					$new_quantity = $request['quantity'] + $cart_item['quantity'];

					// Check the quantity limits for new quantity requested.
					$quantity_validation = $quantity_limits->validate_cart_item_quantity( $new_quantity, $cart_item );

					if ( is_wp_error( $quantity_validation ) ) {
						throw new CoCart_Data_Exception( $quantity_validation->get_error_code(), $quantity_validation->get_error_message(), 400 );
					}

					// Set new quantity for item.
					$cart->set_quantity( $existing_item_key, $new_quantity, true );

					/**
					 * Fires after item is added again to the cart with the quantity increased.
					 *
					 * @since 2.1.0 Introduced.
					 * @since 3.0.0 Added the request object as parameter.
					 * @since 5.0.0 Moved the request object parameter to be first.
					 *
					 * @param WP_REST_Request $request       The request object.
					 * @param string          $item_key      Item key of the item.
					 * @param array           $cart_item     Item in cart.
					 * @param int             $new_quantity  New quantity of the item.
					 */
					do_action( 'cocart_item_added_updated_in_cart', $request, $item_key, $cart_item, $new_quantity );

					cocart_add_to_cart_message( array( $request['id'] => $request['quantity'] ) );

					$response = $this->get_cart( $request );

					$response = rest_ensure_response( $response );
					$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

					return $response;
				}

				// The quantity of item added to the cart.
				$request['quantity'] = CoCart_Utilities_Cart_Helpers::set_cart_item_quantity( $request );

				if ( is_wp_error( $request['quantity'] ) ) {
					return $request['quantity'];
				}
			}

			// $requested_quantity = $request['quantity'];

			/**
			 * Filters the add to cart handler.
			 *
			 * Allows you to identify which handler to use for the product
			 * type your attempting to add item to the cart using it's own validation method.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string     $product_type The product type to identify handler.
			 * @param WC_Product $product      The product object.
			 */
			$handler = apply_filters( 'cocart_add_to_cart_handler', $request['product_type'], $product );

			switch ( $handler ) {
				case 'grouped':
					$item_added = $this->add_to_cart_handler_grouped( $request, $cart );
					break;
				default:
					if ( has_filter( 'cocart_add_to_cart_handler_' . $handler ) ) {
						/**
						 * Filter allows to use a custom add to cart handler.
						 *
						 * Allows you to specify the handlers validation method for
						 * adding item to the cart.
						 *
						 * Example use for filter: 'cocart_add_to_cart_handler_subscription'.
						 *
						 * @since 2.1.0 Introduced.
						 * @since 5.0.0 Added `$cart` instance as parameter.
						 *
						 * @param WC_Product      $product The product object.
						 * @param WP_REST_Request $request The request object.
						 * @param WC_Cart         $cart    Cart class instance.
						 */
						$item_added = apply_filters( 'cocart_add_to_cart_handler_' . $handler, $product, $request, $cart ); // Custom handler.
					} else {
						$item_added = $this->add_cart_item( $request, $product, $cart );
					}

					if ( is_wp_error( $item_added ) ) {
						return $item_added;
					}

					break;
			}

			// Return response to added item to cart or return error.
			if ( $item_added ) {
				// Return item details.
				$item_added = $cart->cart_contents[ $item_key ];

				/**
				 * Hook: Fires once an item has been added to cart.
				 *
				 * @since 2.1.0 Introduced.
				 * @since 3.0.0 Added the request object as parameter.
				 * @since 5.0.0 Moved the request object parameter to be first.
				 *
				 * @param WP_REST_Request $request    The request object.
				 * @param string          $item_key   Item key of the item added.
				 * @param array           $item_added Item added to cart.
				 */
				do_action( 'cocart_item_added_to_cart', $request, $item_key, $item_added );

				if ( ! isset( $request['no_notice'] ) ) {
					cocart_add_to_cart_message( array( $request['id'] => $request['quantity'] ) );
				}
			} else {
				/**
				 * If WooCommerce can provide a reason for the error then let that error message return first.
				 *
				 * @since 3.0.1 Introduced.
				 */
				CoCart_Utilities_Cart_Helpers::convert_notices_to_exceptions( 'cocart_add_to_cart_error' );

				$message = sprintf(
					/* translators: %s: product name */
					__( 'You cannot add "%s" to your cart.', 'cocart-core' ),
					$product->get_name()
				);

				/**
				 * Filters message about product cannot be added to cart.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product );
				throw new CoCart_Data_Exception( 'cocart_cannot_add_to_cart', $message, 403 );
			}

			/**
			 * Hook: Fires after an item has been added to cart.
			 *
			 * Allows for additional requested data to be processed such as modifying the price of the item.
			 *
			 * @since 4.1.0 Introduced.
			 *
			 * @hooked: set_new_price - 1
			 * @hooked: add_customer_billing_details - 10
			 *
			 * @param array           $item_added   The product added to cart.
			 * @param WP_REST_Request $request      The request object.
			 * @param string          $product_type The product type added to cart.
			 * @param object          $controller   The cart controller.
			 */
			do_action( 'cocart_after_item_added_to_cart', $item_added, $request, $request['product_type'], $this );

			// Was it requested to return the item details after being added?
			if ( isset( $request['return_item'] ) && is_bool( $request['return_item'] ) && $request['return_item'] ) {
				/**
				 * Calculate the totals.
				 *
				 * Updates the totals once the item is added including any modifications to the item after.
				 *
				 * @since 3.1.0 Introduced.
				 */
				$this->calculate_totals();

				if ( is_array( $item_added ) ) {
					$response = array();

					foreach ( $item_added as $id => $item ) {
						$response[] = $this->get_item( $item['data'], $item, $request );
					}
				} else {
					$response = $this->get_item( $item_added['data'], $item_added, $request );
				}

				$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );
			} else {
				$request['dont_calculate'] = true; // May need to remove this line. Will see after beta feedback testing.
				$response                  = $this->get_cart( $request );
			}

			$response = rest_ensure_response( $response );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END add_to_cart()

	/**
	 * Handle adding grouped product to the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 5.0.0 Moved to `add-item` endpoint instead.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @param WC_Cart         $cart    The current cart.
	 *
	 * @return bool|array success or not
	 */
	public function add_to_cart_handler_grouped( $request, $cart ) {
		try {
			$was_added_to_cart = false;
			$added_to_cart     = array();

			$items = is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : false;

			if ( ! empty( $items ) ) {
				$quantity_set = false;

				foreach ( $items as $item => $quantity ) {
					$request['id'] = $item; // Override the request ID to the ID of the item in group.

					// The product we are attempting to add to the cart.
					$product = CoCart_Utilities_Cart_Helpers::validate_product_for_cart( $request );

					$quantity            = wc_stock_amount( $quantity );
					$request['quantity'] = $quantity; // Override the request quantity to the quantity of the item in group.

					// Validate quantity before continuing if item is singular and return formatted.
					$request['quantity'] = CoCart_Utilities_Cart_Helpers::validate_quantity( $request['quantity'], $product );

					if ( $quantity <= 0 ) {
						continue;
					}

					$quantity_set = true;

					// Generate an ID based on product ID, variation ID, variation data, and other cart item data.
					$item_key = $cart->generate_cart_id( $item, 0, array(), array() );

					// Suppress total recalculation until finished.
					remove_action( 'woocommerce_add_to_cart', array( $cart, 'calculate_totals' ), 20, 0 );

					$item_added = $this->add_cart_item( $request, $product, $cart );

					if ( false !== $item_added ) {
						$was_added_to_cart      = true;
						$added_to_cart[ $item ] = $item_added;
					}

					add_action( 'woocommerce_add_to_cart', array( $cart, 'calculate_totals' ), 20, 0 );
				}

				if ( ! $was_added_to_cart && ! $quantity_set ) {
					throw new CoCart_Data_Exception( 'cocart_grouped_product_failed', __( 'Please choose the quantity of items you wish to add to your cart.', 'cocart-core' ), 404 );
				} elseif ( $was_added_to_cart ) {
					// Calculate totals now all items in the group has been added to cart.
					$this->calculate_totals();

					$response = $added_to_cart;
				}
			} else {
				throw new CoCart_Data_Exception( 'cocart_grouped_product_empty', __( 'Please choose a product to add to your cart.', 'cocart-core' ), 404 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}

		return $response;
	} // END add_to_cart_handler_grouped()

	/**
	 * Get the query params for adding items.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 3.1.0 Added email and price parameters.
	 * @since 4.1.0 Added phone number parameter.
	 *
	 * @return array $params Query parameters for the endpoint.
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'id'          => array(
				'description'       => __( 'Unique identifier for the product or variation ID.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'    => array(
				'description'       => __( 'Quantity of this item to add to the cart. Can be a number or an array.', 'cocart-core' ),
				'type'              => array( 'string', 'array' ),
				'default'           => '1',
				'required'          => true,
				'sanitize_callback' => 'rest_sanitize_quantity_arg',
				'validate_callback' => 'rest_validate_quantity_arg',
			),
			'variation'   => array(
				'description'       => __( 'Variable attributes that identify the variation of the item.', 'cocart-core' ),
				'type'              => 'object',
				'items'             => array(
					'type'       => 'object',
					'properties' => array(
						'attribute' => array(
							'description'       => __( 'Variation attribute name.', 'cocart-core' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'value'     => array(
							'description'       => __( 'Variation attribute value.', 'cocart-core' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				),
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'item_data'   => array(
				'description'       => __( 'Additional item data passed to make item unique.', 'cocart-core' ),
				'type'              => 'object',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'email'       => array(
				'description'       => __( 'Set the customers billing email address.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_email',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'phone'       => array(
				'description'       => __( 'Set the customers billing phone number.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'price'       => array(
				'description'       => __( 'Set a custom price for the item. Overrides the general or sale price.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_item' => array(
				'description'       => __( 'Returns the item details once added.', 'cocart-core' ),
				'default'           => false,
				'required'          => false,
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'validate_callback' => 'rest_validate_request_arg',
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

	// ** Deprecated functions **//

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @deprecated 5.0.0 No longer use.
	 *
	 * @param string          $product_id The product ID.
	 * @param float           $quantity   The item quantity.
	 * @param array           $item_data  Contains extra cart item data we want to pass into the item.
	 * @param WP_REST_Request $request    The request object.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_simple( $product_id, $quantity, $item_data, $request ) {
		cocart_deprecated_function( 'CoCart_REST_Add_Item_V2_Controller::add_to_cart_handler_simple', '5.0.0' );

		$product_to_add = $this->validate_product( $request, $product_id, $quantity, 0, array(), $item_data, 'simple' );

		// If validation failed then return error response.
		if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}

		// Add item to cart once validation is passed.
		$item_added = $this->add_item_to_cart( $product_to_add, $request );

		cocart_add_to_cart_message( array( $product_id => $quantity ) );

		return $item_added;
	} // END add_to_cart_handler_simple()

	/**
	 * Handle adding variable products to the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 5.0.0 No longer use.
	 *
	 * @param string          $product_id   The product ID.
	 * @param float           $quantity     The item quantity.
	 * @param null            $variation_id The variation ID.
	 * @param array           $variation    The variation attributes.
	 * @param array           $item_data    Contains extra cart item data we want to pass into the item.
	 * @param WP_REST_Request $request      The request object.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_variable( $product_id, $quantity, $variation_id, $variation, $item_data, $request ) {
		cocart_deprecated_function( 'CoCart_REST_Add_Item_V2_Controller::add_to_cart_handler_variable', '5.0.0' );

		$product_to_add = $this->validate_product( $request, $product_id, $quantity, $variation_id, $variation, $item_data, 'variable' );

		// If validation failed then return error response.
		if ( is_wp_error( $product_to_add ) ) {
			return $product_to_add;
		}

		// Add item to cart once validation is passed.
		$item_added = $this->add_item_to_cart( $product_to_add, $request );

		cocart_add_to_cart_message( array( $product_id => $quantity ) );

		return $item_added;
	} // END add_to_cart_handler_variable()

	/**
	 * Adds the item to the cart once passed validation.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 5.0.0 No longer use.
	 *
	 * @param array           $product_to_add Passes details of the item ready to add to the cart.
	 * @param WP_REST_Request $request        The request object.
	 *
	 * @return array $item_added Returns details of the added item in the cart.
	 */
	public function add_item_to_cart( array $product_to_add, $request ) {
		cocart_deprecated_function( 'CoCart_REST_Add_Item_V2_Controller::add_item_to_cart', '5.0.0' );

		$product_id   = $product_to_add['product_id'];
		$quantity     = $product_to_add['quantity'];
		$variation_id = $product_to_add['variation_id'];
		$variation    = $product_to_add['variation'];
		$item_data    = $product_to_add['item_data'];
		$item_key     = $product_to_add['item_key'];
		$product_data = $product_to_add['product_data'];

		try {
			// If item_key is set, then the item is already in the cart so just update the quantity.
			if ( ! empty( $item_key ) ) {
				$cart_contents = $this->get_cart_contents();

				/**
				 * If the item key was not found in cart then we need to reset it as the item may have been
				 * manipulated by adding cart item data via code or a plugin. This helps prevent undefined errors.
				 */
				if ( ! in_array( $item_key, $cart_contents ) ) {
					$product_to_add['item_key'] = ''; // Clear previous item key.
					return $this->add_item_to_cart( $product_to_add, $request );
				}

				$new_quantity = $quantity + $cart_contents[ $item_key ]['quantity'];

				$this->get_cart_instance()->set_quantity( $item_key, $new_quantity, false );

				$item_added = $this->get_cart_item( $item_key, 'add' );

				/**
				 * Fires if item was added again to the cart with the quantity increased.
				 *
				 * @since 2.1.0 Introduced.
				 * @since 3.0.0 Added the request object as parameter.
				 *
				 * @param string          $item_key      Item key of the item added again.
				 * @param array           $item_added    Item added to cart again.
				 * @param int             $new_quantity  New quantity of the item.
				 * @param WP_REST_Request $request       The request object.
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
				 * @param int   $product_id       The product ID.
				 */
				if ( apply_filters( 'cocart_skip_woocommerce_item_validation', false, $product_data, $product_id ) ) {
					$item_key = $this->add_cart_item( $product_id, $quantity, $variation_id, $variation, $item_data, $product_data );
				} else {
					$item_key = $this->get_cart_instance()->add_to_cart( $product_id, $quantity, $variation_id, $variation, $item_data );
				}

				// Return response to added item to cart or return error.
				if ( $item_key ) {
					// Return item details.
					$item_added = $this->get_cart_item( $item_key, 'add' );

					/**
					 * Hook: Fires once an item has been added to cart.
					 *
					 * @since 2.1.0 Introduced.
					 * @since 3.0.0 Added the request object as parameter.
					 *
					 * @param string          $item_key   Item key of the item added.
					 * @param array           $item_added Item added to cart.
					 * @param WP_REST_Request $request    The request object.
					 */
					do_action( 'cocart_item_added_to_cart', $item_key, $item_added, $request );
				} else {
					/**
					 * If WooCommerce can provide a reason for the error then let that error message return first.
					 *
					 * @since 3.0.1 Introduced.
					 */
					CoCart_Utilities_Cart_Helpers::convert_notices_to_exceptions( 'cocart_add_to_cart_error' );

					$message = sprintf(
						/* translators: %s: product name */
						__( 'You cannot add "%s" to your cart.', 'cocart-core' ),
						$product_data->get_name()
					);

					/**
					 * Filters message about product cannot be added to cart.
					 *
					 * @param string     $message      Message.
					 * @param WC_Product $product_data The product object.
					 */
					$message = apply_filters( 'cocart_product_cannot_add_to_cart_message', $message, $product_data );

					throw new CoCart_Data_Exception( 'cocart_cannot_add_to_cart', $message, 403 );
				}
			}

			return $item_added;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END add_item_to_cart()
} // END class
