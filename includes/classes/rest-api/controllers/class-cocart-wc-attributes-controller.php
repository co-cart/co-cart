<?php
/**
 * REST API: CoCart_WC_Attributes_Controller class.
 *
 * Abstract base controller for WooCommerce product attributes.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// namespace CoCart\REST\Controllers;

// use CoCart\REST\CoCart_REST_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract base class for WooCommerce attribute controllers.
 *
 * Provides common functionality for handling WooCommerce product attributes
 * which are stored in wp_woocommerce_attribute_taxonomies (not standard WP terms).
 *
 * @since 5.0.0 Introduced.
 *
 * @extends CoCart_REST_Controller
 */
abstract class CoCart_WC_Attributes_Controller extends CoCart_REST_Controller {

	/**
	 * Get attributes from WooCommerce.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array Attribute objects.
	 */
	protected function get_attributes() {
		return wc_get_attribute_taxonomies();
	} // END get_attributes()

	/**
	 * Prepare a single product attribute output for response.
	 *
	 * This method provides the base implementation with field filtering
	 * and conditional link preparation. Child classes can override to add additional
	 * fields specific to their attribute type.
	 *
	 * V2 controllers can call parent::prepare_item_for_response() and extend the data.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param object           $item    Attribute object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get fields being requested.
		$fields = $this->get_fields_for_response( $request );

		$data = array();

		// ID.
		if ( rest_is_field_included( 'id', $fields ) ) {
			$data['id'] = (int) $item->id;
		}

		// Name.
		if ( rest_is_field_included( 'name', $fields ) ) {
			$data['name'] = $item->name;
		}

		// Slug — strip the 'pa_' prefix so clients see 'color' not 'pa_color'.
		if ( rest_is_field_included( 'slug', $fields ) ) {
			$data['slug'] = wc_attribute_taxonomy_slug( $item->slug );
		}

		// Type.
		if ( rest_is_field_included( 'type', $fields ) && wc_has_custom_attribute_types() ) {
			$data['type'] = $item->type;
		}

		// Order by.
		if ( rest_is_field_included( 'order_by', $fields ) ) {
			$data['order_by'] = $item->order_by;
		}

		// Has archives.
		if ( rest_is_field_included( 'has_archives', $fields ) ) {
			$data['has_archives'] = (bool) $item->has_archives;
		}

		// Terms — list of term slugs for this attribute.
		// $item->slug is already the full taxonomy name (e.g. 'pa_color') from wc_get_attribute().
		if ( rest_is_field_included( 'terms', $fields ) ) {
			$terms         = get_terms( array(
				'taxonomy'   => $item->slug,
				'hide_empty' => false,
			) );
			$data['terms'] = ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'slug' ) : array();
		}

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, 'view' );

		$response = rest_ensure_response( $data );

		// Only prepare links if requested (WordPress 6.1+ optimization).
		if ( rest_is_field_included( '_links', $fields ) || rest_is_field_included( '_embedded', $fields ) ) {
			$response->add_links( $this->prepare_links( $item, $request ) );
		}

		/**
		 * Filter a attribute item returned from the API.
		 *
		 * Allows modification of the product attribute data right before it is returned.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param object            $item     The original attribute object.
		 * @param \WP_REST_Request  $request  The request object.
		 */
		return apply_filters( 'cocart_prepare_product_attribute', $response, $item, $request );
	} // END prepare_item_for_response()

	/**
	 * Get the Attribute's schema, conforming to JSON Schema.
	 *
	 * This method provides the base schema. Child classes can override
	 * to add additional properties specific to their attribute type.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		// Return cached schema if available.
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'product_attribute',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the resource.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'         => array(
					'description' => __( 'Attribute name.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'         => array(
					'description' => __( 'An alphanumeric identifier for the resource unique to its type.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'type'         => array(
					'description' => __( 'Type of attribute.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'select',
					'enum'        => array_keys( wc_get_attribute_types() ),
					'context'     => array( 'view' ),
				),
				'order_by'     => array(
					'description' => __( 'Sort order.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'menu_order',
					'enum'        => array( 'menu_order', 'name', 'name_num', 'id' ),
					'context'     => array( 'view' ),
				),
				'has_archives' => array(
					'description' => __( 'Attribute has archives?', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'terms'        => array(
					'description' => __( 'List of term slugs for this attribute.', 'cocart-core' ),
					'type'        => 'array',
					'items'       => array( 'type' => 'string' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		$this->schema = $this->add_additional_fields_schema( $this->schema );

		return $this->schema;
	} // END get_item_schema()

	/**
	 * Prepare links for the request.
	 *
	 * Attributes have no frontend permalinks, only REST links.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param object           $attribute Attribute object.
	 * @param \WP_REST_Request $request   Request object.
	 *
	 * @return array Links for the given attribute.
	 */
	protected function prepare_links( $attribute, $request ) {
		$base = ltrim( $this->build_rest_path( 'products/attributes' ), '/' );

		$links = array(
			'self'       => array(
				'href' => rest_url( $base . '/' . $attribute->id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'terms'      => array(
				'href' => rest_url( $base . '/' . $attribute->id . '/terms' ),
			),
		);

		return $links;
	} // END prepare_links()
} // END class
