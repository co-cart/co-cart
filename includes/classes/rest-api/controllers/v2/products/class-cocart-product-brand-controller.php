<?php
/**
 * REST API: CoCart_REST_Product_Brand_V2_Controller class (Single Item)
 *
 * Handles requests to the products/brands/{id} endpoint.
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
 * Controller for returning a single product brand via the REST API (API v2).
 *
 * This REST API controller handles requests to return a single product brand
 * via "cocart/v2/products/brands/{id}" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @extends CoCart_REST_Product_Brands_V2_Controller
 */
class CoCart_REST_Product_Brand_V2_Controller extends CoCart_REST_Product_Brands_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public static function get_path_regex() {
		return '/products/brands/(?P<id>[\d]+)';
	} // END get_path_regex()

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		return self::get_path_regex();
	} // END get_path()

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
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()
} // END class
