<?php
/**
 * REST API: CoCart_REST_Remove_Items_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v2
 * @since   4.0.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for removing all items in the cart (API v2).
 *
 * This REST API controller handles the request to remove just the items
 * in the cart via "cocart/v2/cart/items" endpoint.
 *
 * @since 4.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_v2_Controller
 */
class CoCart_REST_Remove_Items_v2_Controller extends CoCart_REST_Cart_v2_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/items';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Remove Items - cocart/v2/cart/items (DELETE).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	} // register_routes()

	/**
	 * Removes all items in the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function remove_items() {
		try {
			$cart_contents = ! $this->get_cart_instance()->is_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

			$items = $this->get_items( $cart_contents );

			foreach ( $items as $item_key => $item ) {
				$this->get_cart_instance()->remove_cart_item( $item_key );
			}

			// Calculates the cart totals now items have been removed.
			$this->get_cart_instance()->calculate_totals();

			return CoCart_Response::get_response( array(), $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END remove_items()

	/**
	 * Get the query params for the cart.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array The query params.
	 */
	public function get_collection_params() {
		return array(
			'cart_key' => array(
				'description'       => __( 'Unique identifier for the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	} // END get_collection_params()

} // END class
