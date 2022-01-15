<?php
/**
 * CoCart - Items controller
 *
 * Handles the request to view just the items in the cart with /cart/items endpoint.
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
 * CoCart REST API v2 - View Items controller class.
 *
 * @package CoCart\API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Items_v2_Controller extends CoCart_Cart_V2_Controller {

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
	 */
	public function register_routes() {
		// Get Items - cocart/v2/cart/items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'view_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
			)
		);
	} // register_routes()

	/**
	 * Returns all items in the cart.
	 *
	 * @access public
	 * @return WP_REST_Response
	 */
	public function view_items() {
		$cart_contents = ! $this->get_cart_instance()->is_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

		$items = $this->get_items( $cart_contents );

		// Return message should the cart be empty.
		if ( empty( $cart_contents ) ) {
			$items = esc_html__( 'No items in the cart.', 'cart-rest-api-for-woocommerce' );
		}

		return CoCart_Response::get_response( $items, $this->namespace, $this->rest_base );
	} // END view_items()

	/**
	 * Get the query params.
	 *
	 * @access public
	 * @since  3.1.0
	 * @return array $params
	 */
	public function get_collection_params() {
		return parent::get_collection_params();
	} // END get_collection_params()

} // END class
