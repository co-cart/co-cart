<?php
/**
 * REST API: Abstract Product Variations controller.
 *
 * Provides shared query and retrieval methods for all product variation endpoints.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract product variations controller class.
 *
 * @extends CoCart_REST_Products_Controller
 */
abstract class CoCart_REST_Product_Variations_Controller extends CoCart_REST_Products_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/(?P<product_id>[\d]+)/variations';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'product_variation';

	/**
	 * Get a collection of variations.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response The response, or an error.
	 */
	public function get_items( $request ) {
		$query_args    = $this->prepare_objects_query( $request );
		$query_results = $this->get_objects( $query_args );

		if ( is_wp_error( $query_results ) ) {
			return $query_results;
		}

		$objects = array();

		foreach ( $query_results['objects'] as $object ) {
			$data      = $this->prepare_object_for_response( $object, $request );
			$objects[] = $this->prepare_response_for_collection( $data );
		}

		$page      = $query_results['paged'];
		$max_pages = $query_results['pages'];

		$response = rest_ensure_response( $objects );
		$response = ( new CoCart_REST_Utilities_Pagination() )->add_headers( $response, $request, $query_results['total'], $max_pages );

		return $response;
	} // END get_items()

	/**
	 * Get the image for a product variation.
	 *
	 * @access protected
	 *
	 * @param WC_Product_Variation $variation Variation data.
	 *
	 * @return array
	 */
	protected function get_image( $variation ) {
		if ( ! $variation->get_image_id() ) {
			return;
		}

		$attachment_id    = $variation->get_image_id();
		$attachment_post  = get_post( $attachment_id );
		$attachment_sizes = apply_filters( 'cocart_products_variation_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );

		if ( is_null( $attachment_post ) ) {
			return;
		}

		$attachment = array();

		// Get each image size of the attachment.
		foreach ( $attachment_sizes as $size ) {
			$attachment[ $size ] = current( wp_get_attachment_image_src( $attachment_id, $size ) );
		}

		if ( ! isset( $image ) ) {
			return array(
				'id'                => (int) $attachment_id,
				'date_created'      => wc_rest_prepare_date_response( $attachment_post->post_date, false ),
				'date_created_gmt'  => wc_rest_prepare_date_response( strtotime( $attachment_post->post_date_gmt ) ),
				'date_modified'     => wc_rest_prepare_date_response( $attachment_post->post_modified, false ),
				'date_modified_gmt' => wc_rest_prepare_date_response( strtotime( $attachment_post->post_modified_gmt ) ),
				'src'               => $attachment,
				'name'              => get_the_title( $attachment_id ),
				'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			);
		}
	} // END get_image()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param \WC_Product      $product The product object.
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $product, $request ) {
		$variation_id = $product->get_id();
		$parent_id    = $product->get_parent_id();

		$links = array(
			'self'       => array(
				'href' => rest_url( $this->build_rest_path( 'products/%d/variations/%d', array( $parent_id, $variation_id ) ) ),
			),
			'collection' => array(
				'href' => rest_url( $this->build_rest_path( 'products/%d/variations', array( $parent_id ) ) ),
			),
			'up'         => array(
				'href'      => rest_url( $this->build_rest_path( 'products/%d', array( $parent_id ) ) ),
				'permalink' => cocart_get_permalink( get_permalink( $parent_id ) ),
			),
		);

		return $links;
	} // END prepare_links()

	/**
	 * Get the Variation's schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$weight_unit    = get_option( 'woocommerce_weight_unit' );
		$dimension_unit = get_option( 'woocommerce_dimension_unit' );

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id'                    => array(
					'description' => __( 'Unique identifier for the resource.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created'          => array(
					'description' => __( "The date the variation was created, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified'         => array(
					'description' => __( "The date the variation was last modified, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'description'           => array(
					'description' => __( 'Variation description.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'permalink'             => array(
					'description' => __( 'Variation URL.', 'cocart-core' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sku'                   => array(
					'description' => __( 'Unique identifier.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'price'                 => array(
					'description' => __( 'Current variation price.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'regular_price'         => array(
					'description' => __( 'Variation regular price.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'sale_price'            => array(
					'description' => __( 'Variation sale price.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'date_on_sale_from'     => array(
					'description' => __( "Start date of sale price, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'date_on_sale_from_gmt' => array(
					'description' => __( 'Start date of sale price, as GMT.', 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'date_on_sale_to'       => array(
					'description' => __( "End date of sale price, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'date_on_sale_to_gmt'   => array(
					'description' => __( "End date of sale price, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'on_sale'               => array(
					'description' => __( 'Shows if the variation is on sale.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'purchasable'           => array(
					'description' => __( 'Shows if the variation can be bought.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'virtual'               => array(
					'description' => __( 'If the variation is virtual.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'downloadable'          => array(
					'description' => __( 'If the variation is downloadable.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'manage_stock'          => array(
					'description' => __( 'Stock management at variation level.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'stock_quantity'        => array(
					'description' => __( 'Stock quantity.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'stock_status'          => array(
					'description' => __( 'Controls the stock status of the product.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'instock',
					'enum'        => array_keys( wc_get_product_stock_status_options() ),
					'context'     => array( 'view' ),
				),
				'backorders'            => array(
					'description' => __( 'If managing stock, this controls if backorders are allowed.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'no',
					'enum'        => array( 'no', 'notify', 'yes' ),
					'context'     => array( 'view' ),
				),
				'backorders_allowed'    => array(
					'description' => __( 'Shows if backorders are allowed.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backordered'           => array(
					'description' => __( 'Shows if the variation is on backordered.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'weight'                => array(
					'description' => sprintf(
						/* translators: %s: weight unit */
						__( 'Variation weight (%s).', 'cocart-core' ),
						$weight_unit
					),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'dimensions'            => array(
					'description' => __( 'Variation dimensions.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'length' => array(
							'description' => sprintf(
								/* translators: %s: dimension unit */
								__( 'Variation length (%s).', 'cocart-core' ),
								$dimension_unit
							),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'width'  => array(
							'description' => sprintf(
								/* translators: %s: dimension unit */
								__( 'Variation width (%s).', 'cocart-core' ),
								$dimension_unit
							),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'height' => array(
							'description' => sprintf(
								/* translators: %s: dimension unit */
								__( 'Variation height (%s).', 'cocart-core' ),
								$dimension_unit
							),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
				),
				'image'                 => array(
					'description' => __( 'Variation image data.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'id'                => array(
							'description' => __( 'Image ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
						),
						'date_created'      => array(
							'description' => __( "The date the image was created, in the site's timezone.", 'cocart-core' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'date_created_gmt'  => array(
							'description' => __( 'The date the image was created, as GMT.', 'cocart-core' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'date_modified'     => array(
							'description' => __( "The date the image was last modified, in the site's timezone.", 'cocart-core' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'date_modified_gmt' => array(
							'description' => __( 'The date the image was last modified, as GMT.', 'cocart-core' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'src'               => array(
							'description' => __( 'Image URL.', 'cocart-core' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view' ),
						),
						'name'              => array(
							'description' => __( 'Image name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'alt'               => array(
							'description' => __( 'Image alternative text.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
				),
				'attributes'            => array(
					'description' => __( 'List of attributes.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'     => array(
								'description' => __( 'Attribute ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
							'name'   => array(
								'description' => __( 'Attribute name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
							'option' => array(
								'description' => __( 'Selected attribute term name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
						),
					),
				),
				'menu_order'            => array(
					'description' => __( 'Menu order, used to custom sort products.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'meta_data'             => array(
					'description' => __( 'Meta data.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'    => array(
								'description' => __( 'Meta ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'key'   => array(
								'description' => __( 'Meta key.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
							'value' => array(
								'description' => __( 'Meta value.', 'cocart-core' ),
								'type'        => 'mixed',
								'context'     => array( 'view' ),
							),
						),
					),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );
	} // END get_item_schema()

	/**
	 * Prepare objects query.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = parent::prepare_objects_query( $request );

		// Set post_status.
		$args['post_status'] = 'publish';

		// Filter by on sale products.
		if ( is_bool( $request['on_sale'] ) ) {
			$on_sale_key = $request['on_sale'] ? 'post__in' : 'post__not_in';
			$on_sale_ids = wc_get_product_ids_on_sale();

			// Use 0 when there's no on sale products to avoid return all products.
			$on_sale_ids = empty( $on_sale_ids ) ? array( 0 ) : $on_sale_ids;

			$args[ $on_sale_key ] += $on_sale_ids;
		}

		// Force the post_type argument, since it's not a user input variable.
		if ( ! empty( $request['sku'] ) ) {
			$args['post_type'] = array( 'product', 'product_variation' );
		} else {
			$args['post_type'] = $this->post_type;
		}

		$args['post_parent'] = $request['product_id'];

		return $args;
	} // END prepare_objects_query()

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		unset(
			$params['in_stock'],
			$params['type'],
			$params['featured'],
			$params['category'],
			$params['tag'],
			$params['attribute'],
			$params['attribute_term']
		);

		$params['stock_status'] = array(
			'description'       => __( 'Limit result set to products with specified stock status.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array_keys( wc_get_product_stock_status_options() ),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	} // END get_collection_params()
} // END class
