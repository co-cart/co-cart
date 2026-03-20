<?php
/**
 * CoCart - Product Attribute by Slug controller
 *
 * Handles requests to the products/attributes/{slug} endpoint.
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
 * CoCart REST API v2 - Product Attribute by Slug controller class.
 *
 * Provides slug-based access to a single product attribute.
 * The slug is the clean form without the 'pa_' prefix (e.g. 'color', not 'pa_color').
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Product_Attributes_V2_Controller
 */
class CoCart_REST_Product_Attribute_By_Slug_V2_Controller extends CoCart_REST_Product_Attributes_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/attributes/(?P<slug>[\w-]+)';
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
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()

	/**
	 * Get a single attribute by its slug.
	 *
	 * @access public
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, WP_Error on failure.
	 */
	public function get_item( $request ) {
		$slug = sanitize_title( $request['slug'] );
		$id   = wc_attribute_taxonomy_id_by_name( $slug );

		if ( ! $id ) {
			return new \WP_Error( 'cocart_attribute_invalid_slug', __( 'Invalid attribute slug.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		$attribute = $this->get_attribute( $id );

		if ( is_wp_error( $attribute ) ) {
			return $attribute;
		}

		return rest_ensure_response( $this->prepare_item_for_response( $attribute, $request ) );
	} // END get_item()
} // END class
