<?php
/**
 * CoCart - Count Items controller
 *
 * Handles the request to count the items in the cart with /cart/items/count endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Count Items controller class.
 *
 * @package CoCart/API
 */
class CoCart_Count_Items_v2_Controller extends CoCart_Count_Items_Controller {

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
		// Count Items in Cart - cocart/v2/cart/items/count (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_cart_contents_count' ),
			'args'     => array(
				'return' => array(
					'default' => 'numeric'
				),
			),
		) );
	} // register_routes()

} // END class
