<?php
/**
 * CoCart - Product Tag controller (Single Item)
 *
 * Handles requests to the products/tags/{id} endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Tag (Single) controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Product_Tags_V2_Controller
 */
class CoCart_REST_Product_Tag_V2_Controller extends CoCart_REST_Product_Tags_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/tags/(?P<id>[\d]+)';
	} // END get_path_regex()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array Method arguments.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'id'      => array(
						'description' => __( 'Unique identifier for the resource.', 'cocart-core' ),
						'type'        => 'integer',
					),
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()
} // END class
