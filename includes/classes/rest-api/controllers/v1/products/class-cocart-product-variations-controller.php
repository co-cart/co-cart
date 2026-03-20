<?php
/**
 * CoCart - Product Variations controller
 *
 * Handles requests to the /products/variations endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v1
 * @since   3.1.0
 * @license GPL-3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API variations controller class.
 *
 * @extends CoCart_REST_Product_Variations_Controller
 */
class CoCart_Product_Variations_Controller extends CoCart_REST_Product_Variations_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v1';

	/**
	 * Register the routes for product variations.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Products - cocart/v1/products/32/variations (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cocart-core' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Get Products - cocart/v1/products/32/variations/148 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'product_id' => array(
							'description' => __( 'Unique identifier for the variable product.', 'cocart-core' ),
							'type'        => 'integer',
						),
						'id'         => array(
							'description' => __( 'Unique identifier for the variation.', 'cocart-core' ),
							'type'        => 'integer',
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // END register_routes()

	/**
	 * Prepare a single variation output for response.
	 *
	 * @access public
	 *
	 * @param WC_Product      $product The product object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $product, $request ) {
		$data = array(
			'id'                    => $product->get_id(),
			'date_created'          => wc_rest_prepare_date_response( $product->get_date_created(), false ),
			'date_created_gmt'      => wc_rest_prepare_date_response( $product->get_date_created() ),
			'date_modified'         => wc_rest_prepare_date_response( $product->get_date_modified(), false ),
			'date_modified_gmt'     => wc_rest_prepare_date_response( $product->get_date_modified() ),
			'description'           => wc_format_content( $product->get_description() ),
			'permalink'             => $product->get_permalink(),
			'sku'                   => $product->get_sku(),
			'price'                 => $product->get_price(),
			'regular_price'         => $product->get_regular_price(),
			'sale_price'            => $product->get_sale_price(),
			'date_on_sale_from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from(), false ),
			'date_on_sale_from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from() ),
			'date_on_sale_to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to(), false ),
			'date_on_sale_to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to() ),
			'on_sale'               => $product->is_on_sale(),
			'purchasable'           => $product->is_purchasable(),
			'virtual'               => $product->is_virtual(),
			'downloadable'          => $product->is_downloadable(),
			'manage_stock'          => $product->managing_stock(),
			'stock_quantity'        => $product->get_stock_quantity(),
			'stock_status'          => $product->get_stock_status(),
			'backorders'            => $product->get_backorders(),
			'backorders_allowed'    => $product->backorders_allowed(),
			'backordered'           => $product->is_on_backorder(),
			'weight'                => $product->get_weight(),
			'dimensions'            => array(
				'length' => $product->get_length(),
				'width'  => $product->get_width(),
				'height' => $product->get_height(),
			),
			'image'                 => $this->get_image( $product ),
			'attributes'            => $this->get_attributes( $product ),
			'menu_order'            => $product->get_menu_order(),
			'meta_data'             => CoCart_Utilities_Product_Helpers::get_meta_data( $product ),
		);

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $product, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to object type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Product       $product  The product object.
		 * @param WP_REST_Request  $request  The request object.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object", $response, $product, $request ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	} // END prepare_object_for_response()

	/**
	 * Get the attributes for a product variation.
	 *
	 * @access protected
	 *
	 * @param WC_Product_Variation $product The variation object.
	 *
	 * @return array
	 */
	protected function get_attributes( $product ) {
		$attributes = array();

		$parent_product = wc_get_product( $product->get_parent_id() );

		foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
			$name = str_replace( 'attribute_', '', $attribute_name );

			if ( ! $attribute ) {
				continue;
			}

			// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
			if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
				$option_term = get_term_by( 'slug', $attribute, $name );

				$attributes[ 'attribute_' . $name ] = array(
					'id'     => wc_attribute_taxonomy_id_by_name( $name ),
					'name'   => $this->get_attribute_taxonomy_name( $name, $parent_product ),
					'option' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
				);
			} else {
				$attributes[ 'attribute_' . $name ] = array(
					'id'     => 0,
					'name'   => $this->get_attribute_taxonomy_name( $name, $parent_product ),
					'option' => $attribute,
				);
			}
		}

		return $attributes;
	} // END get_attributes()
} // END class
