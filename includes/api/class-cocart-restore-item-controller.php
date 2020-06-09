<?php
/**
 * CoCart - Restore Item controller
 *
 * Handles the request to restore items in the cart with /cart/restore-item endpoint.
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
 * REST API Restore Item controller class.
 *
 * @package CoCart/API
 */
class CoCart_Restore_Item_v2_Controller extends CoCart_Item_Controller {

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
	protected $rest_base = 'cart/restore-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Restore Item - cocart/v2/restore-item (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'args' => $this->get_collection_params(),
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'restore_item' ),
			),
		) );
	} // register_routes()

} // END class
