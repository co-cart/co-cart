<?php
/**
 * CoCart - Add Item controller
 *
 * Handles the request to add items to the cart with /cart/add-item endpoint.
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
 * CoCart REST API Add Item v2 controller class.
 *
 * @package CoCart/API
 */
class CoCart_Add_Item_v2_Controller extends CoCart_Add_Item_Controller {

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
	protected $rest_base = 'cart/add-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Add Item - cocart/v2/cart/add-item (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'add_to_cart' ),
				'args'     => $this->get_collection_params()
			),
			'schema' => array( $this, 'get_item_schema' )
		) );
	} // register_routes()

} // END class
