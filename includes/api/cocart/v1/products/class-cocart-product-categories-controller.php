<?php
/**
 * CoCart - Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
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
 * REST API Product Categories controller class.
 *
 * @package CoCart/API
 * @extends CoCart_REST_Terms_Controller
 */
class CoCart_Product_Categories_Controller extends CoCart_REST_Terms_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/categories';

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_cat';

	/**
	 * Prepare a single product category output for response.
	 *
	 * @access public
	 * @param  WP_Term         $item    Term object.
	 * @param  WP_REST_Request $request Request instance.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get category display type.
		$display_type = get_term_meta( $item->term_id, 'display_type', true );

		// Get category order.
		$menu_order = get_term_meta( $item->term_id, 'order', true );

		$data = array(
			'id'          => (int) $item->term_id,
			'name'        => $item->name,
			'slug'        => $item->slug,
			'parent'      => (int) $item->parent,
			'description' => $item->description,
			'display'     => $display_type ? $display_type : 'default',
			'image'       => null,
			'menu_order'  => (int) $menu_order,
			'count'       => (int) $item->count,
		);

		// Get category image.
		$image_id = get_term_meta( $item->term_id, 'thumbnail_id', true );

		if ( $image_id ) {
			$attachment = get_post( $image_id );

			$data['image'] = array(
				'id'                => (int) $image_id,
				'date_created'      => wc_rest_prepare_date_response( $attachment->post_date ),
				'date_created_gmt'  => wc_rest_prepare_date_response( $attachment->post_date_gmt ),
				'date_modified'     => wc_rest_prepare_date_response( $attachment->post_modified ),
				'date_modified_gmt' => wc_rest_prepare_date_response( $attachment->post_modified_gmt ),
				'src'               => wp_get_attachment_url( $image_id ),
				'name'              => get_the_title( $attachment ),
				'alt'               => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
			);
		}

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, 'view' );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item, $request ) );

		/**
		 * Filter a term item returned from the API.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @param WP_REST_Response  $response  The response object.
		 * @param object            $item      The original term object.
		 * @param WP_REST_Request   $request   Request used to generate the response.
		 */
		return apply_filters( "cocart_prepare_{$this->taxonomy}", $response, $item, $request );
	}

	/**
	 * Get the Category schema, conforming to JSON Schema.
	 *
	 * @access public
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->taxonomy,
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'Category name.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'        => array(
					'description' => __( 'An alphanumeric identifier for the resource unique to its type.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'parent'      => array(
					'description' => __( 'The ID for the parent of the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'description' => array(
					'description' => __( 'HTML description of the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'display'     => array(
					'description' => __( 'Category archive display type.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'default',
					'enum'        => array( 'default', 'products', 'subcategories', 'both' ),
					'context'     => array( 'view' ),
				),
				'image'       => array(
					'description' => __( 'Image data.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'id'                => array(
							'description' => __( 'Image ID.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
						),
						'date_created'      => array(
							'description' => __( "The date the image was created, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'date_created_gmt'  => array(
							'description' => __( 'The date the image was created, as GMT.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'date_modified'     => array(
							'description' => __( "The date the image was last modified, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'date_modified_gmt' => array(
							'description' => __( 'The date the image was last modified, as GMT.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'src'               => array(
							'description' => __( 'Image URL.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view' ),
						),
						'name'              => array(
							'description' => __( 'Image name.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'alt'               => array(
							'description' => __( 'Image alternative text.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
				),
				'menu_order'  => array(
					'description' => __( 'Menu order, used to custom sort the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'count'       => array(
					'description' => __( 'Number of published products for the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

}
