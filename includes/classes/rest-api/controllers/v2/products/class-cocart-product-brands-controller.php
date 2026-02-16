<?php
/**
 * REST API: CoCart_REST_Product_Brands_V2_Controller class.
 *
 * Handles requests to the products/brands endpoint.
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
 * Controller for returning products brands via the REST API (API v2).
 *
 * This REST API controller handles requests to return product details
 * via "cocart/v2/products/brands" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @extends CoCart_REST_Terms_V2_Controller
 */
class CoCart_REST_Product_Brands_V2_Controller extends CoCart_REST_Taxonomy_Terms_Controller {

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_brand';

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/brands';
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
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()
} // END class
