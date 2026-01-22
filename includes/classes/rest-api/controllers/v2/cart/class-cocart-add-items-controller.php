<?php
/**
 * REST API: CoCart_REST_Add_Items_V2_Controller class
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

class_alias( 'CoCart_REST_Add_Items_V2_Controller', 'CoCart_Add_Items_V2_Controller' );

/**
 * Controller for adding items to cart via the REST API. (API v2)
 *
 * This REST API controller handles requests to add grouped items or
 * custom multiple handler to the cart via "cocart/v2/cart/add-items" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Add_Item_V2_Controller
 */
class CoCart_REST_Add_Items_V2_Controller extends CoCart_REST_Add_Item_V2_Controller {

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/add-items';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/add-items';
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
				'callback'            => array( $this, 'add_items_to_cart' ),
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
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Add Items - cocart/v2/cart/add-items (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Add other bundled or grouped products to Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_items_to_cart( $request ) {
		try {
			$cart = $this->get_cart_instance();

			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
			$items      = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			// Validate product ID before continuing and return correct product ID if different.
			$product_id = CoCart_Utilities_Cart_Helpers::validate_product_id( $product_id );

			// Return error response if product ID is not found.
			if ( is_wp_error( $product_id ) ) {
				return $product_id;
			}

			// The product we are attempting to add to the cart.
			$adding_to_cart = wc_get_product( $product_id );
			$adding_to_cart = CoCart_Utilities_Cart_Helpers::validate_product_for_cart( $adding_to_cart );

			// Return error response if product cannot be added to cart?
			if ( is_wp_error( $adding_to_cart ) ) {
				return $adding_to_cart;
			}

			/**
			 * Filters the add to cart handler.
			 *
			 * Allows you to identify which handler to use for the product
			 * type attempting to add to the cart using it's own validation method.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string     $product_type   The product type to identify handler.
			 * @param WC_Product $adding_to_cart The product object
			 */
			$add_items_to_cart_handler = apply_filters( 'cocart_add_items_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			if ( has_filter( 'cocart_add_items_to_cart_handler_' . $add_items_to_cart_handler ) ) {
				/**
				 * Filter allows to use a custom add to cart handler.
				 *
				 * Allows you to specify the handlers validation method for
				 * adding item to the cart.
				 *
				 * Example: "cocart_add_items_to_cart_handler_grouped"
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param WC_Product      $adding_to_cart The product object
				 * @param WP_REST_Request $request        The request object.
				 */
				$items_added_to_cart = apply_filters( 'cocart_add_items_to_cart_handler_' . $add_items_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
			} else {
				$items_added_to_cart = $this->add_to_cart_handler_grouped( $request, $cart );
			}

			if ( is_wp_error( $items_added_to_cart ) ) {
				return $items_added_to_cart;
			}

			/**
			 * Hook: Fires once items have been added to cart.
			 *
			 * Allows for additional requested data to be processed such as modifying the price of the item.
			 *
			 * @since 4.1.0 Introduced.
			 *
			 * @hooked: set_new_price - 1
			 * @hooked: add_customer_billing_details - 10
			 *
			 * @param bool|array      $items_added_to_cart       The product added to cart.
			 * @param WP_REST_Request $request                   The request object.
			 * @param string          $add_items_to_cart_handler The product type added to cart.
			 * @param object          $controller                The controller.
			 */
			do_action( 'cocart_after_items_added_to_cart', $items_added_to_cart, $request, $add_items_to_cart_handler, $this );

			// Was it requested to return the items details after being added?
			if ( isset( $request['return_items'] ) && is_bool( $request['return_items'] ) && $request['return_items'] ) {
				$response = array();

				foreach ( $items_added_to_cart as $id => $item ) {
					$response[] = $this->get_item( $item['data'], $item, $request );
				}
			} else {
				$request['dont_calculate'] = true;
				$response                  = $this->get_cart( $request );
			}

			$response = rest_ensure_response( $response );
			$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END add_items_to_cart()

	/**
	 * Get the query params for adding items.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return array $params Query parameters for the endpoint.
	 */
	public function get_collection_params() {
		// Get main cart query parameters.
		$params = parent::get_collection_params();

		// Override parameters for this route.
		$params['quantity'] = array(
			'required'          => true,
			'description'       => __( 'List of items and quantity to add to the cart.', 'cocart-core' ),
			'type'              => 'array',
			'sanitize_callback' => 'rest_sanitize_quantity_arg',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['return_items'] = $params['return_item'];
		unset( $params['return_item'] );

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
