<?php
/**
 * CoCart - Remove Item controller
 *
 * Handles the request to remove items in the cart with /cart/remove-item endpoint.
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
 * REST API Remove Item controller class.
 *
 * @package CoCart\API
 */
class CoCart_Remove_Item_v2_Controller extends CoCart_Item_Controller {

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
	protected $rest_base = 'cart/remove-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Remove Item - cocart/v2/remove-item (DELETE)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'args' => $this->get_collection_params(),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'remove_item' ),
				'permission_callback' => '__return_true',
			),
		) );
	} // register_routes()

} // END class
