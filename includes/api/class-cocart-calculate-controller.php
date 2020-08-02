<?php
/**
 * CoCart - Calculate controller
 *
 * Handles the request to calculate the cart with /cart/calculate endpoint.
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
 * REST API Calculate controller class.
 *
 * @package CoCart/API
 */
class CoCart_Calculate_v2_Controller extends CoCart_Calculate_Controller {

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
	protected $rest_base = 'cart/calculate';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Calculate Cart Total - cocart/v2/cart/calculate (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'calculate_totals' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'return' => array(
					'default'     => false,
					'description' => __( 'Returns the cart totals once calculated.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				)
			)
		) );
	} // register_routes()

} // END class
