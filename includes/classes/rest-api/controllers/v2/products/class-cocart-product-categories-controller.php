<?php
/**
 * CoCart - Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @version 5.0.0
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
 * @extends CoCart_REST_Taxonomy_Terms_Controller
 */
class CoCart_REST_Product_Categories_V2_Controller extends CoCart_REST_Taxonomy_Terms_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Taxonomy.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_cat';

	/**
	 * Get the path regex for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/categories';
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
		// Get base V2 response with field filtering.
		$response = parent::prepare_item_for_response( $item, $request );
		$data     = $response->get_data();
		$fields   = $this->get_fields_for_response( $request );

		// Add category-specific fields.

		// Parent ID.
		if ( rest_is_field_included( 'parent_id', $fields ) ) {
			$data['parent_id'] = (int) $item->parent;
		}

		// Display type.
		if ( rest_is_field_included( 'display', $fields ) ) {
			$display_type    = get_term_meta( $item->term_id, 'display_type', true );
			$data['display'] = $display_type ? $display_type : 'default';
		}

		// Image.
		if ( rest_is_field_included( 'image', $fields ) ) {
			$data['image'] = array();

			$image_id     = get_term_meta( $item->term_id, 'thumbnail_id', true );
			$thumbnail_id = ! empty( $image_id ) ? $image_id : get_option( 'woocommerce_placeholder_image', 0 );
			$thumbnail_id = apply_filters( 'cocart_products_category_thumbnail', $thumbnail_id );

			if ( $image_id ) {
				$attachment  = get_post( $image_id );
				$image_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();
				$images      = array();

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
		}

		// Menu order.
		if ( rest_is_field_included( 'menu_order', $fields ) ) {
			$menu_order         = get_term_meta( $item->term_id, 'order', true );
			$data['menu_order'] = (int) $menu_order;
		}

		$response->set_data( $data );

		return $response;
	} // END prepare_item_for_response()

	/**
	 * Get the Category schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		// Get base V2 schema.
		$schema = parent::get_item_schema();

		// Add category-specific properties.
		$schema['properties']['parent_id'] = array(
			'description' => __( 'The ID for the parent of the resource.', 'cocart-core' ),
			'type'        => 'integer',
			'context'     => array( 'view' ),
		);

		$schema['properties']['display'] = array(
			'description' => __( 'Category archive display type.', 'cocart-core' ),
			'type'        => 'string',
			'default'     => 'default',
			'enum'        => array( 'default', 'products', 'subcategories', 'both' ),
			'context'     => array( 'view' ),
		);

		$schema['properties']['image'] = array(
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
		);

		$schema['properties']['menu_order'] = array(
			'description' => __( 'Menu order, used to custom sort the resource.', 'cocart-core' ),
			'type'        => 'integer',
			'context'     => array( 'view' ),
		);

		// Fetch each image size.
		$attachment_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();

		foreach ( $attachment_sizes as $size ) {
			// Generate the product image URL properties for each attachment size.
			$schema['properties']['image']['properties']['src']['properties'][ $size ] = array(
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

		return $schema;
	} // END get_item_schema()
} // END class
