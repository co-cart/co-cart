<?php
/**
 * CoCart - Totals controller
 *
 * Handles the request to get the totals of the cart with /cart/totals endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API/v2
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Totals controller class.
 *
 * @package CoCart/API
 */
class CoCart_Totals_v2_Controller extends CoCart_Totals_Controller {

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
	protected $rest_base = 'cart/totals';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart Totals - cocart/v2/cart/totals (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_totals' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'html' => array(
					'description' => __( 'Returns the totals pre-formatted.', 'cart-rest-api-for-woocommerce' ),
					'default' => false,
					'type'    => 'boolean',
				),
			),
		) );
	} // register_routes()

} // END class
