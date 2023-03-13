<?php
/**
 * REST API: CoCart_REST_Count_Items_v2_Controller class
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
 * Controller for counting items in the cart (API v2).
 *
 * This REST API controller handles the request to count the items
 * in the cart via "cocart/v2/cart/items/count" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_v2_Controller
 */
class CoCart_REST_Count_Items_v2_Controller extends CoCart_REST_Cart_v2_Controller {

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
	protected $rest_base = 'cart/items/count';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Count Items in Cart - cocart/v2/cart/items/count (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_contents_count' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Get cart contents count.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $cart_contents Cart contents to count items.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_cart_contents_count( $request = array(), $cart_contents = array() ) {
		try {
			$return        = ! empty( $request['return'] ) ? $request['return'] : '';
			$removed_items = isset( $request['removed_items'] ) ? $request['removed_items'] : false;

			if ( empty( $cart_contents ) ) {
				// Return count for removed items in cart.
				if ( isset( $request['removed_items'] ) && is_bool( $request['removed_items'] ) && $request['removed_items'] ) {
					$count = $this->get_removed_cart_contents_count();
				} else {
					// Return count for items in cart.
					$count = $this->get_cart_instance()->get_cart_contents_count();
				}
			} else {
				// Counts all items from the quantity variable.
				$count = array_sum( wp_list_pluck( $cart_contents, 'quantity' ) );
			}

			if ( 'numeric' !== $return && $count <= 0 ) {
				$message = __( 'No items in the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about no items in the cart.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_no_items_in_cart_message', $message );

				throw new CoCart_Data_Exception( 'cocart_no_items_in_cart', $message, 404 );
			}

			return CoCart_Response::get_response( $count, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_contents_count()

	/**
	 * Retrieves the item schema for returning the item count.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_cart_count_items',
			'type'       => 'object',
			'properties' => array(
				'removed_items' => array(
					'required'    => false,
					'default'     => false,
					'description' => __( 'Returns count for removed items from the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				),
			),
		);

		return $schema;
	} // END get_public_item_schema()

	/**
	 * Get the query params for counting items.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Count Items parameters.
		$params += array(
			'removed_items' => array(
				'description' => __( 'Set as true to count items removed from the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
			),
			'return'        => array(
				'description'       => __( 'Internal parameter. No description.', 'cart-rest-api-for-woocommerce' ),
				'required'          => false,
				'default'           => 'numeric',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
