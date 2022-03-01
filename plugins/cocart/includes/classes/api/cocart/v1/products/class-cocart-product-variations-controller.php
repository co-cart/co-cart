<?php
/**
 * CoCart - Product Variations controller
 *
 * Handles requests to the /products/variations endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v1
 * @since    3.1.0
 * @license  GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API variations controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Products_Controller
 */
class CoCart_Product_Variations_Controller extends CoCart_Products_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v1';

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
	 * Register the routes for product variations.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Products - cocart/v1/products/32/variations (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get Products - cocart/v1/products/32/variations/148 (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'product_id' => array(
							'description' => __( 'Unique identifier for the variable product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
						),
						'id'         => array(
							'description' => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Prepare a single variation output for response.
	 *
	 * @access public
	 * @param  WC_Data         $object  Object data.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $object, $request ) {
		$data = array(
			'id'                    => $object->get_id(),
			'date_created'          => wc_rest_prepare_date_response( $object->get_date_created(), false ),
			'date_created_gmt'      => wc_rest_prepare_date_response( $object->get_date_created() ),
			'date_modified'         => wc_rest_prepare_date_response( $object->get_date_modified(), false ),
			'date_modified_gmt'     => wc_rest_prepare_date_response( $object->get_date_modified() ),
			'description'           => wc_format_content( $object->get_description() ),
			'permalink'             => $object->get_permalink(),
			'sku'                   => $object->get_sku(),
			'price'                 => $object->get_price(),
			'regular_price'         => $object->get_regular_price(),
			'sale_price'            => $object->get_sale_price(),
			'date_on_sale_from'     => wc_rest_prepare_date_response( $object->get_date_on_sale_from(), false ),
			'date_on_sale_from_gmt' => wc_rest_prepare_date_response( $object->get_date_on_sale_from() ),
			'date_on_sale_to'       => wc_rest_prepare_date_response( $object->get_date_on_sale_to(), false ),
			'date_on_sale_to_gmt'   => wc_rest_prepare_date_response( $object->get_date_on_sale_to() ),
			'on_sale'               => $object->is_on_sale(),
			'purchasable'           => $object->is_purchasable(),
			'virtual'               => $object->is_virtual(),
			'downloadable'          => $object->is_downloadable(),
			'manage_stock'          => $object->managing_stock(),
			'stock_quantity'        => $object->get_stock_quantity(),
			'stock_status'          => $object->get_stock_status(),
			'backorders'            => $object->get_backorders(),
			'backorders_allowed'    => $object->backorders_allowed(),
			'backordered'           => $object->is_on_backorder(),
			'weight'                => $object->get_weight(),
			'dimensions'            => array(
				'length' => $object->get_length(),
				'width'  => $object->get_width(),
				'height' => $object->get_height(),
			),
			'image'                 => $this->get_image( $object ),
			'attributes'            => $this->get_attributes( $object ),
			'menu_order'            => $object->get_menu_order(),
			'meta_data'             => $object->get_meta_data(),
		);

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to object type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Data          $object   Object data.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object", $response, $object, $request );
	}

	/**
	 * Get the image for a product variation.
	 *
	 * @access protected
	 * @param  WC_Product_Variation $variation Variation data.
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
	}

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 * @param  WC_Data         $object  Object data.
	 * @param  WP_REST_Request $request Request object.
	 * @return array                   Links for the given post.
	 */
	protected function prepare_links( $object, $request ) {
		$product_id = (int) $request['product_id'];

		$base = str_replace( '(?P<product_id>[\d]+)', $product_id, $this->rest_base );

		$links = array(
			'self'           => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $base, $object->get_id() ) ),
			),
			'collection'     => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $base ) ),
			),
			'parent_product' => array(
				'href'      => rest_url( sprintf( '/%s/products/%d', $this->namespace, $product_id ) ),
				'permalink' => get_permalink( $product_id ),
			),
		);
		return $links;
	}

	/**
	 * Get the Variation's schema, conforming to JSON Schema.
	 *
	 * @access public
	 * @return array
	 */
	public function get_item_schema() {
		$weight_unit    = get_option( 'woocommerce_weight_unit' );
		$dimension_unit = get_option( 'woocommerce_dimension_unit' );

		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id'                    => array(
					'description' => __( 'Unique identifier for the resource.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created'          => array(
					'description' => __( "The date the variation was created, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified'         => array(
					'description' => __( "The date the variation was last modified, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'description'           => array(
					'description' => __( 'Variation description.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'permalink'             => array(
					'description' => __( 'Variation URL.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sku'                   => array(
					'description' => __( 'Unique identifier.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'price'                 => array(
					'description' => __( 'Current variation price.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'regular_price'         => array(
					'description' => __( 'Variation regular price.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'sale_price'            => array(
					'description' => __( 'Variation sale price.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'date_on_sale_from'     => array(
					'description' => __( "Start date of sale price, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'date_on_sale_from_gmt' => array(
					'description' => __( 'Start date of sale price, as GMT.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'date_on_sale_to'       => array(
					'description' => __( "End date of sale price, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'date_on_sale_to_gmt'   => array(
					'description' => __( "End date of sale price, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
				),
				'on_sale'               => array(
					'description' => __( 'Shows if the variation is on sale.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'purchasable'           => array(
					'description' => __( 'Shows if the variation can be bought.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'virtual'               => array(
					'description' => __( 'If the variation is virtual.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'downloadable'          => array(
					'description' => __( 'If the variation is downloadable.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'manage_stock'          => array(
					'description' => __( 'Stock management at variation level.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
				),
				'stock_quantity'        => array(
					'description' => __( 'Stock quantity.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'stock_status'          => array(
					'description' => __( 'Controls the stock status of the product.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'instock',
					'enum'        => array_keys( wc_get_product_stock_status_options() ),
					'context'     => array( 'view' ),
				),
				'backorders'            => array(
					'description' => __( 'If managing stock, this controls if backorders are allowed.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'no',
					'enum'        => array( 'no', 'notify', 'yes' ),
					'context'     => array( 'view' ),
				),
				'backorders_allowed'    => array(
					'description' => __( 'Shows if backorders are allowed.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backordered'           => array(
					'description' => __( 'Shows if the variation is on backordered.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'weight'                => array(
					/* translators: %s: weight unit */
					'description' => sprintf( __( 'Variation weight (%s).', 'cart-rest-api-for-woocommerce' ), $weight_unit ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'dimensions'            => array(
					'description' => __( 'Variation dimensions.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'length' => array(
							/* translators: %s: dimension unit */
							'description' => sprintf( __( 'Variation length (%s).', 'cart-rest-api-for-woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'width'  => array(
							/* translators: %s: dimension unit */
							'description' => sprintf( __( 'Variation width (%s).', 'cart-rest-api-for-woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'height' => array(
							/* translators: %s: dimension unit */
							'description' => sprintf( __( 'Variation height (%s).', 'cart-rest-api-for-woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
				),
				'image'                 => array(
					'description' => __( 'Variation image data.', 'cart-rest-api-for-woocommerce' ),
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
				'attributes'            => array(
					'description' => __( 'List of attributes.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'     => array(
								'description' => __( 'Attribute ID.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
							'name'   => array(
								'description' => __( 'Attribute name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
							'option' => array(
								'description' => __( 'Selected attribute term name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
						),
					),
				),
				'menu_order'            => array(
					'description' => __( 'Menu order, used to custom sort products.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'meta_data'             => array(
					'description' => __( 'Meta data.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'    => array(
								'description' => __( 'Meta ID.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'key'   => array(
								'description' => __( 'Meta key.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
							'value' => array(
								'description' => __( 'Meta value.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'mixed',
								'context'     => array( 'view' ),
							),
						),
					),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Prepare objects query.
	 *
	 * @access protected
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = parent::prepare_objects_query( $request );

		// Set post_status.
		$args['post_status'] = 'publish';

		// Filter by sku.
		if ( ! empty( $request['sku'] ) ) {
			$skus = explode( ',', $request['sku'] );

			// Include the current string as a SKU too.
			if ( 1 < count( $skus ) ) {
				$skus[] = $request['sku'];
			}

			$args['meta_query'] = $this->add_meta_query( // WPCS: slow query ok.
				$args,
				array(
					'key'     => '_sku',
					'value'   => $skus,
					'compare' => 'IN',
				)
			);
		}

		// Price filter.
		if ( ! empty( $request['min_price'] ) || ! empty( $request['max_price'] ) ) {
			$args['meta_query'] = $this->add_meta_query( $args, wc_get_min_max_price_meta_query( $request ) );  // WPCS: slow query ok.
		}

		// Filter product based on stock_status.
		if ( ! empty( $request['stock_status'] ) ) {
			$args['meta_query'] = $this->add_meta_query( // WPCS: slow query ok.
				$args,
				array(
					'key'   => '_stock_status',
					'value' => $request['stock_status'],
				)
			);
		}

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
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @access public
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
			'description'       => __( 'Limit result set to products with specified stock status.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array_keys( wc_get_product_stock_status_options() ),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

}
