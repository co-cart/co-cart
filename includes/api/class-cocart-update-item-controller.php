<?php
/**
 * CoCart - Update Item controller
 *
 * Handles the request to update items in the cart with /cart/update-item endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Item controller class.
 *
 * @package CoCart\API
 */
class CoCart_Update_Item_v2_Controller extends CoCart_Item_Controller {

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
	protected $rest_base = 'cart/update-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Update Item - cocart/v2/cart/update-item (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'args' => $this->get_collection_params(),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'quantity' => array(
						'default'           => 1,
						'type'              => 'float',
						'validate_callback' => function( $value, $request, $param ) {
							return is_numeric( $value );
						}
					),
				),
			),
		) );
	} // register_routes()

} // END class
