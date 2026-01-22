<?php
/**
 * CoCart - Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Product_Categories_V2_Controller', 'CoCart_Product_Categories_V2_Controller' );

/**
 * CoCart REST API v2 - Product Categories controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Categories_Controller
 */
class CoCart_REST_Product_Categories_V2_Controller extends CoCart_Product_Categories_Controller {

	/**
	 * Route namespace. - Remove once new route registry is completed.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Version of route.
	 */
	protected $version = 'v2';

	/**
	 * Get version of route. - Remove once route abstract is created to extend from.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		return self::get_path_regex();
	}

	/**
	 * Prepare a single product category output for response.
	 *
	 * @access public
	 *
	 * @param WP_Term         $item    Term object.
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get category display type.
		$display_type = get_term_meta( $item->term_id, 'display_type', true );

		// Get category order.
		$menu_order = get_term_meta( $item->term_id, 'order', true );

		$data = array(
			'id'            => (int) $item->term_id,
			'parent_id'     => (int) $item->parent,
			'name'          => $item->name,
			'slug'          => $item->slug,
			'description'   => $item->description,
			'display'       => $display_type ? $display_type : 'default',
			'image'         => array(),
			'menu_order'    => (int) $menu_order,
			'product_count' => (int) $item->count,
		);

		// Get category image.
		$image_id = get_term_meta( $item->term_id, 'thumbnail_id', true );

		$thumbnail_id = ! empty( $image_id ) ? $image_id : get_option( 'woocommerce_placeholder_image', 0 );
		$thumbnail_id = apply_filters( 'cocart_products_category_thumbnail', $thumbnail_id );

		$image_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();
		$images      = array();

		if ( $image_id ) {
			$attachment = get_post( $image_id );

			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_products_category_thumbnail_size', 'woocommerce_thumbnail' ) );
			$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';
			$thumbnail_src = apply_filters( 'cocart_products_category_thumbnail_src', $thumbnail_src );

			// Get each image size of the attachment.
			foreach ( $image_sizes as $size ) {
				$images[ $size ] = current( wp_get_attachment_image_src( $thumbnail_id, $size ) );
			}

			$data['image'] = array(
				'id'   => (int) $image_id,
				'src'  => $images,
				'name' => get_the_title( $attachment ),
				'alt'  => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
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
		 * @param WP_REST_Response $response The response object.
		 * @param object           $item     The original term object.
		 * @param WP_REST_Request  $request  The request object.
		 */
		return apply_filters( "cocart_prepare_{$this->taxonomy}", $response, $item, $request ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	} // END prepare_item_for_response()

	/**
	 * Get the Category schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->taxonomy,
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the resource.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'parent_id'   => array(
					'description' => __( 'The ID for the parent of the resource.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'name'        => array(
					'description' => __( 'Category name.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'        => array(
					'description' => __( 'An alphanumeric identifier for the resource unique to its type.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'description' => array(
					'description' => __( 'HTML description of the resource.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'display'     => array(
					'description' => __( 'Category archive display type.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'default',
					'enum'        => array( 'default', 'products', 'subcategories', 'both' ),
					'context'     => array( 'view' ),
				),
				'image'       => array(
					'description' => __( 'Image data.', 'cocart-core' ),
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
							'description' => __( 'The resource thumbnail returned as an array of sizes.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(),
							'readonly'    => true,
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
				'menu_order'  => array(
					'description' => __( 'Menu order, used to custom sort the resource.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'count'       => array(
					'description' => __( 'Number of published products for the resource.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		// Fetch each image size.
		$attachment_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();

		foreach ( $attachment_sizes as $size ) {
			// Generate the product image URL properties for each attachment size.
			$this->schema['properties']['image']['properties']['src']['properties'][ $size ] = array(
				'description' => sprintf(
					/* translators: %s: Product image URL */
					__( 'Product image URL for "%s".', 'cocart-core' ),
					$size
				),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'format'      => 'uri',
				'readonly'    => true,
			);
		}

		return $this->add_additional_fields_schema( $schema );
	} // END get_item_schema()
}
