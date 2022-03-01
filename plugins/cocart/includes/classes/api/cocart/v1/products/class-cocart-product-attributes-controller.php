<?php
/**
 * CoCart - Product Attributes controller
 *
 * Handles requests to the products/attributes endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v1
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Attributes controller class.
 *
 * @package CoCart/API
 * @extends CoCart_REST_Terms_Controller
 */
class CoCart_Product_Attributes_Controller extends CoCart_REST_Terms_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/attributes';

	/**
	 * Attribute name.
	 *
	 * @var string
	 */
	protected $attribute = '';

	/**
	 * Check if a given request has access to read the attributes.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Check if a given request has access to read a attribute.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! $this->get_taxonomy( $request ) ) {
			return new WP_Error( 'cocart_attribute_invalid', __( 'Attribute does not exist.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Get all attributes.
	 *
	 * @access public
	 * @param  WP_REST_Request $request
	 * @return array
	 */
	public function get_items( $request ) {
		$attributes = wc_get_attribute_taxonomies();
		$data       = array();

		foreach ( $attributes as $attribute_obj ) {
			$attribute = $this->prepare_item_for_response( $attribute_obj, $request );
			$attribute = $this->prepare_response_for_collection( $attribute );
			$data[]    = $attribute;
		}

		$response = rest_ensure_response( $data );

		// This API call always returns all product attributes due to retrieval from the object cache.
		$response->header( 'X-WP-Total', count( $data ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Get a single attribute.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Request|WP_Error
	 */
	public function get_item( $request ) {
		$attribute = $this->get_attribute( (int) $request['id'] );

		if ( is_wp_error( $attribute ) ) {
			return $attribute;
		}

		$response = $this->prepare_item_for_response( $attribute, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Prepare a single product attribute output for response.
	 *
	 * @access public
	 * @param  obj             $item Term object.
	 * @param  WP_REST_Request $request
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array(
			'id'           => (int) $item->attribute_id,
			'name'         => $item->attribute_label,
			'slug'         => wc_attribute_taxonomy_name( $item->attribute_name ),
			'type'         => $item->attribute_type,
			'order_by'     => $item->attribute_orderby,
			'has_archives' => (bool) $item->attribute_public,
		);

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, 'view' );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter a attribute item returned from the API.
		 *
		 * Allows modification of the product attribute data right before it is returned.
		 *
		 * @param WP_REST_Response  $response  The response object.
		 * @param object            $item      The original attribute object.
		 * @param WP_REST_Request   $request   Request used to generate the response.
		 */
		return apply_filters( 'cocart_prepare_product_attribute', $response, $item, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param object $attribute Attribute object.
	 * @return array Links for the given attribute.
	 */
	protected function prepare_links( $attribute, $request = array() ) {
		$base  = '/' . $this->namespace . '/' . $this->rest_base;
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $attribute->attribute_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		return $links;
	}

	/**
	 * Get the Attribute's schema, conforming to JSON Schema.
	 *
	 * @access public
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'product_attribute',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'         => array(
					'description' => __( 'Attribute name.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'         => array(
					'description' => __( 'An alphanumeric identifier for the resource unique to its type.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'type'         => array(
					'description' => __( 'Type of attribute.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'select',
					'enum'        => array_keys( wc_get_attribute_types() ),
					'context'     => array( 'view' ),
				),
				'order_by'     => array(
					'description' => __( 'Sort order.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'menu_order',
					'enum'        => array( 'menu_order', 'name', 'name_num', 'id' ),
					'context'     => array( 'view' ),
				),
				'has_archives' => array(
					'description' => __( 'Attribute has archives?', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

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
	}

	/**
	 * Get attribute name.
	 *
	 * @access protected
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return string
	 */
	protected function get_taxonomy( $request ) {
		if ( '' !== $this->attribute ) {
			return $this->attribute;
		}

		if ( $request['id'] ) {
			$name = wc_attribute_taxonomy_name_by_id( (int) $request['id'] );

			$this->attribute = $name;
		}

		return $this->attribute;
	}

	/**
	 * Get attribute data.
	 *
	 * @access protected
	 * @param  int $id Attribute ID.
	 * @global $wpdb
	 * @return stdClass|WP_Error
	 */
	protected function get_attribute( $id ) {
		global $wpdb;

		$attribute = $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
			WHERE attribute_id = %d
		 ",
				$id
			)
		);

		if ( is_wp_error( $attribute ) || is_null( $attribute ) ) {
			return new WP_Error( 'cocart_attribute_invalid', __( 'Attribute does not exist.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		return $attribute;
	}

}
