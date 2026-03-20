<?php
/**
 * CoCart - Product Attribute by Slug, Term by ID controller
 *
 * Handles requests to the products/attributes/{attribute_slug}/terms/{id} endpoint.
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
 * CoCart REST API v2 - Product Attribute by Slug, Term by ID controller class.
 *
 * Provides slug-based attribute access with numeric term ID lookup.
 *
 * Route: GET /products/attributes/{attribute_slug}/terms/{id}
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Product_Attribute_Terms_By_Slug_V2_Controller
 */
class CoCart_REST_Product_Attribute_By_Slug_Term_By_ID_V2_Controller extends CoCart_REST_Product_Attribute_Terms_By_Slug_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/attributes/(?P<attribute_slug>[\w-]+)/terms/(?P<id>[\d]+)';
	} // END get_path_regex()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array Method arguments.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()
} // END class
