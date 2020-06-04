<?php
/**
 * CoCart - Clear Cart controller
 *
 * Handles the request to clear the cart with /cart/clear endpoint.
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
 * REST API Clear Cart controller class.
 *
 * @package CoCart/API
 */
class CoCart_Clear_Cart_v2_Controller extends CoCart_Clear_Cart_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/clear';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Clear Cart - cocart/v2/cart/clear (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'clear_cart' ),
		) );
	} // register_routes()

} // END class
