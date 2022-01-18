<?php
/**
 * CoCart - Count Items controller
 *
 * Handles the request to count the items in the cart with /cart/items/count endpoint.
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
 * CoCart REST API v2 - Count Items controller class.
 *
 * @package CoCart\API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Count_Items_v2_Controller extends CoCart_Cart_V2_Controller {

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
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Get cart contents count.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.1.0
	 * @param   WP_REST_Request $request       - Full details about the request.
	 * @param   array           $cart_contents - Cart contents to count items.
	 * @return  WP_REST_Response
	 */
	public function get_cart_contents_count( $request = array(), $cart_contents = array() ) {
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
			$message = __( 'There are no items in the cart!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'notice' );

			/**
			 * Filters message about no items in the cart.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_no_items_in_cart_message', $message );

			return CoCart_Response::get_response( $message, $this->namespace, $this->rest_base );
		}

		return CoCart_Response::get_response( $count, $this->namespace, $this->rest_base );
	} // END get_cart_contents_count()

	/**
	 * Get the schema for returning the item count, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  3.0.0
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Count Items in Cart', 'cart-rest-api-for-woocommerce' ),
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
	} // END get_item_schema()

	/**
	 * Get the query params for counting items.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.1.0
	 * @return  array $params
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
