<?php
/**
 * CoCart - Items controller
 *
 * Handles the request to view just the items in the cart with /cart/items endpoint.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API View Items controller class.
 *
 * @package CoCart\API
 */
class CoCart_Items_v2_Controller extends CoCart_Item_Controller {

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
		$controller = new CoCart_Cart_V2_Controller();

		$cart_contents = ! $controller->get_cart_instance()->is_empty() ? array_filter( $controller->get_cart_instance()->get_cart() ) : array();

		$items = $controller->get_items( $cart_contents );

		// Return message should the cart be empty.
		if ( empty( $cart_contents ) ) {
			$items = esc_html__( 'No items in the cart.', 'cart-rest-api-for-woocommerce' );
		}

		return CoCart_Response::get_response( $items, $this->namespace, $this->rest_base );
	} // END view_items()

} // END class
