<?php
/**
 * CoCart - Product Attributes controller
 *
 * Handles requests to the products/attributes endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

// namespace CoCart\REST\Controllers\V2\Products;

// use CoCart\REST\Controllers\CoCart_WC_Attributes_Controller;
// use CoCart\REST\Utilities\CoCart_REST_Utilities_Pagination;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Attributes controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_WC_Attributes_Controller
 */
class CoCart_REST_Product_Attributes_V2_Controller extends CoCart_WC_Attributes_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Attribute name.
	 *
	 * @var string
	 */
	protected $attribute = '';

	/**
	 * Get the path regex for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/attributes';
	} // END get_path_regex()

	/**
	 * Check if a given request has access to read the attributes.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	} // END get_items_permissions_check()

	/**
	 * Check if a given request has access to read an attribute.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! $this->get_taxonomy( $request ) ) {
			return new \WP_Error( 'cocart_attribute_invalid', __( 'Attribute does not exist.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		return true;
	} // END get_item_permissions_check()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @since 5.0.0 Introduced.
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

	/**
	 * Get all attributes.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	public function get_items( $request ) {
		$data = array();

		foreach ( wc_get_attribute_taxonomies() as $attribute_obj ) {
			$attribute = wc_get_attribute( (int) $attribute_obj->attribute_id );
			if ( is_null( $attribute ) ) {
				continue;
			}
			$data[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $attribute, $request )
			);
		}

		$total_items = count( $data );

		$response = rest_ensure_response( $data );

		// Add pagination headers (attributes list doesn't support per_page, so max_pages is always 1).
		$response = ( new CoCart_REST_Utilities_Pagination() )->add_headers( $response, $request, $total_items, 1 );

		return $response;
	} // END get_items()

	/**
	 * Get the query params for collections
	 *
	 * @access public
	 * @return array
	 */
	public function get_collection_params() {
		$params            = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );

		return $params;
	} // END get_collection_params()

	/**
	 * Get attribute name.
	 *
	 * @access protected
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return string
	 */
	protected function get_taxonomy( $request ) {
		if ( '' !== $this->taxonomy ) {
			return $this->taxonomy;
		}

		if ( $request['id'] ) {
			$name = wc_attribute_taxonomy_name_by_id( (int) $request['id'] );

			$this->taxonomy = $name;
		}

		return $this->taxonomy;
	} // END get_taxonomy()

	/**
	 * Get attribute data.
	 *
	 * Uses WooCommerce's object cache via wc_get_attribute() to avoid direct DB queries.
	 *
	 * @access protected
	 *
	 * @param int $id Attribute ID.
	 *
	 * @return stdClass|\WP_Error Normalized attribute object or WP_Error on failure.
	 */
	protected function get_attribute( $id ) {
		$attribute = wc_get_attribute( $id );

		if ( is_null( $attribute ) ) {
			return new \WP_Error( 'cocart_attribute_invalid', __( 'Attribute does not exist.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		return $attribute;
	} // END get_attribute()
} // END class
