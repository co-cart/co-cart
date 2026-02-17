<?php
/**
 * CoCart - Product Attribute by Slug, Term by Slug controller
 *
 * Handles requests to the products/attributes/{attribute_slug}/terms/{term_slug} endpoint.
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
 * CoCart REST API v2 - Product Attribute by Slug, Term by Slug controller class.
 *
 * Provides fully slug-based access to a single attribute term.
 *
 * Route: GET /products/attributes/{attribute_slug}/terms/{term_slug}
 * Example: GET /products/attributes/color/terms/blue
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Product_Attribute_Terms_By_Slug_V2_Controller
 */
class CoCart_REST_Product_Attribute_By_Slug_Term_By_Slug_V2_Controller extends CoCart_REST_Product_Attribute_Terms_By_Slug_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/attributes/(?P<attribute_slug>[\w-]+)/terms/(?P<term_slug>[\w-]+)';
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

	/**
	 * Get a single term by its slug within the resolved attribute taxonomy.
	 *
	 * @access public
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, WP_Error on failure.
	 */
	public function get_item( $request ) {
		$taxonomy = $this->get_taxonomy( $request );

		if ( ! $taxonomy ) {
			return new \WP_Error( 'cocart_attribute_invalid_slug', __( 'Invalid attribute slug.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		$term = get_term_by( 'slug', sanitize_title( $request['term_slug'] ), $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			return new \WP_Error( 'cocart_term_invalid_slug', __( 'Invalid term slug.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $term, $request ) );
	} // END get_item()
} // END class
