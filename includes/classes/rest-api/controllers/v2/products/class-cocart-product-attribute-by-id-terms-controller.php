<?php
/**
 * CoCart - Product Attribute Terms controller
 *
 * Handles requests to the products/attributes/<attributes_id> endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

// namespace CoCart\REST\Controllers\V2\Products;

// use CoCart\REST\Controllers\CoCart_REST_Taxonomy_Terms_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Attribute Terms controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Taxonomy_Terms_Controller
 */
class CoCart_REST_Product_Attribute_Terms_V2_Controller extends CoCart_REST_Taxonomy_Terms_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Get the path regex for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/attributes/(?P<attribute_id>[\d]+)/terms';
	} // END get_path_regex()

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
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()

	/**
	 * Prepare a single product attribute term output for response.
	 *
	 * @access public
	 *
	 * @param \WP_Term         $item    Term object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get base response with field filtering from parent.
		$response = parent::prepare_item_for_response( $item, $request );
		$data     = $response->get_data();
		$fields   = $this->get_fields_for_response( $request );

		// Menu order.
		if ( rest_is_field_included( 'menu_order', $fields ) ) {
			$data['menu_order'] = (int) get_term_meta( $item->term_id, 'order_' . $this->taxonomy, true );
		}

		$response->set_data( $data );

		return $response;
	} // END prepare_item_for_response()

	/**
	 * Prepare links for the request.
	 *
	 * Attribute terms have no frontend permalinks, only REST links.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param \WP_Term         $term    Term object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array Links for the given term.
	 */
	protected function prepare_links( $term, $request ) {
		$route = $request->get_route();

		// Build self link — append term ID if this is a collection route.
		$self_route = preg_match( '/\/\d+$/', $route ) ? $route : $route . '/' . $term->term_id;

		// Build collection link — strip the trailing term ID.
		$collection_route = preg_replace( '/\/\d+$/', '', $self_route );

		$links = array(
			'self'       => array(
				'href' => rest_url( ltrim( $self_route, '/' ) ),
			),
			'collection' => array(
				'href' => rest_url( ltrim( $collection_route, '/' ) ),
			),
		);

		return $links;
	} // END prepare_links()

	/**
	 * Get the Attribute Term's schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		// Get base schema from parent.
		$schema = parent::get_item_schema();

		// Override title for attribute terms.
		$schema['title'] = 'product_attribute_term';

		// Add attribute term-specific properties.
		$schema['properties']['menu_order'] = array(
			'description' => __( 'Menu order, used to custom sort the attribute term.', 'cocart-core' ),
			'type'        => 'integer',
			'context'     => array( 'view' ),
		);

		return $schema;
	} // END get_item_schema()
} // END class
