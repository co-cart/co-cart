<?php
/**
 * REST API: Products v1 controller.
 *
 * Handles requests to the /products/ endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products\v1
 * @since   3.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product controller class.
 *
 * @extends CoCart_REST_Products_Controller
 */
class CoCart_Products_Controller extends CoCart_REST_Products_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v1';

	/**
	 * Register the routes for products.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Products - cocart/v1/products (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Get a single product - cocart/v1/products/32 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the product.', 'cocart-core' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'context' => $this->get_context_param(
							array(
								'default' => 'view',
							)
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Get a collection of products.
	 *
	 * @access public
	 *
	 * @since 3.10.7 Checks if query results return as an error.
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

		$response->header( 'X-WP-Total', (int) $query_results['total'] );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base          = $this->rest_base;
		$attrib_prefix = '(?P<';

		if ( strpos( $base, $attrib_prefix ) !== false ) {
			$attrib_names = array();

			preg_match( '/\(\?P<[^>]+>.*\)/', $base, $attrib_names, PREG_OFFSET_CAPTURE );

			foreach ( $attrib_names as $attrib_name_match ) {
				$beginning_offset = strlen( $attrib_prefix );
				$attrib_name_end  = strpos( $attrib_name_match[0], '>', $attrib_name_match[1] );
				$attrib_name      = substr( $attrib_name_match[0], $beginning_offset, $attrib_name_end - $beginning_offset );

				if ( isset( $request[ $attrib_name ] ) ) {
					$base = str_replace( "(?P<$attrib_name>[\d]+)", $request[ $attrib_name ], $base );
				}
			}
		}

		$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	} // END get_items()

	/**
	 * Prepare a single product output for response.
	 *
	 * @access public
	 *
	 * @param WC_Product      $product The product object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $product, $request ) {
		// Check what product type before returning product data.
		if ( $product->get_type() !== 'variation' ) {
			$data = $this->get_product_data( $product );
		} else {
			$data = $this->get_variation_product_data( $product );
		}

		// Add review data to products if requested.
		if ( $request['show_reviews'] ) {
			$data['reviews'] = $this->get_reviews( $product );
		}

		// Add variations to variable products. Returns just IDs by default.
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$variations = $product->get_children();

			foreach ( $variations as $variation_product ) {
				// If requested to return variations then fetch them.
				if ( $request['return_variations'] ) {
					$variation_object                         = new WC_Product_Variation( $variation_product );
					$data['variations'][ $variation_product ] = $this->get_variation_product_data( $variation_object );
				} else {
					$data['variations'][ $variation_product ] = array( 'id' => $variation_product );
				}
			}
		}

		// Add grouped products data.
		if ( $product->is_type( 'grouped' ) && $product->has_child() ) {
			$data['grouped_products'] = $product->get_children();
		}

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $product, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Product       $product  The product object.
		 * @param WP_REST_Request  $request  The request object.
		 */
		return apply_filters( 'cocart_prepare_product_object', $response, $product, $request );
	} // END prepare_object_for_response()

	/**
	 * Get taxonomy terms.
	 *
	 * @access protected
	 *
	 * @param WC_Product $product  The product object.
	 * @param string     $taxonomy Taxonomy slug.
	 *
	 * @return array
	 */
	protected function get_taxonomy_terms( $product, $taxonomy = 'cat' ) {
		$terms = array();

		foreach ( wc_get_object_terms( $product->get_id(), 'product_' . $taxonomy ) as $term ) {
			$terms[] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => urldecode( $term->slug ),
			);
		}

		return $terms;
	} // END get_taxonomy_terms()

	/**
	 * Get the images for a product or product variation.
	 *
	 * @access protected
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
	 * @return array $images
	 */
	protected function get_images( $product ) {
		$images           = array();
		$attachment_ids   = array();
		$attachment_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();

		// Add featured image.
		if ( $product->get_image_id() ) {
			$attachment_ids[] = $product->get_image_id();
		}

		// Add gallery images.
		$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

		$attachments = array();

		// Build image data.
		foreach ( $attachment_ids as $position => $attachment_id ) {
			$attachment_post = get_post( $attachment_id );
			if ( is_null( $attachment_post ) ) {
				continue;
			}

			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( $attachment_id, $size ) );
			}

			$images[] = array(
				'id'                => (int) $attachment_id,
				'date_created'      => wc_rest_prepare_date_response( $attachment_post->post_date, false ),
				'date_created_gmt'  => wc_rest_prepare_date_response( strtotime( $attachment_post->post_date_gmt ) ),
				'date_modified'     => wc_rest_prepare_date_response( $attachment_post->post_modified, false ),
				'date_modified_gmt' => wc_rest_prepare_date_response( strtotime( $attachment_post->post_modified_gmt ) ),
				'src'               => $attachments,
				'name'              => get_the_title( $attachment_id ),
				'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position'          => (int) $position,
			);
		}

		// Set a placeholder image if the product has no images set.
		if ( empty( $images ) ) {
			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( get_option( 'woocommerce_placeholder_image', 0 ), $size ) );
			}

			$images[] = array(
				'id'                => 0,
				'date_created'      => wc_rest_prepare_date_response( current_time( 'mysql' ), false ), // Default to now.
				'date_created_gmt'  => wc_rest_prepare_date_response( time() ), // Default to now.
				'date_modified'     => wc_rest_prepare_date_response( current_time( 'mysql' ), false ),
				'date_modified_gmt' => wc_rest_prepare_date_response( time() ),
				'src'               => $attachments,
				'name'              => __( 'Placeholder', 'cocart-core' ),
				'alt'               => __( 'Placeholder', 'cocart-core' ),
				'position'          => 0,
			);
		}

		return $images;
	} // END get_images()

	/**
	 * Get attribute options.
	 *
	 * @access protected
	 *
	 * @param int   $product_id Product ID.
	 * @param array $attribute  Attribute data.
	 *
	 * @return array
	 */
	protected function get_attribute_options( $product_id, $attribute ) {
		if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
			return wc_get_product_terms(
				$product_id,
				$attribute['name'],
				array(
					'fields' => 'names',
				)
			);
		} elseif ( isset( $attribute['value'] ) ) {
			return array_map( 'trim', explode( '|', $attribute['value'] ) );
		}

		return array();
	} // END get_attribute_options()

	/**
	 * Get the attributes for a product or product variation.
	 *
	 * @access protected
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
	 * @return array
	 */
	protected function get_attributes( $product ) {
		$attributes = array();

		if ( $product->is_type( 'variation' ) || $product->is_type( 'subscription_variation' ) ) {
			$_product = wc_get_product( $product->get_parent_id() );

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
						'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
						'option' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
					);
				} else {
					$attributes[ 'attribute_' . $name ] = array(
						'id'     => 0,
						'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
						'option' => $attribute,
					);
				}
			}
		} else {
			foreach ( $product->get_attributes() as $attribute ) {
				$attribute_id = 'attribute_' . str_replace( ' ', '-', strtolower( sanitize_title( $attribute->get_name() ) ) );

				$attributes[ $attribute_id ] = array(
					'id'                   => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name( $attribute['name'] ) : 0,
					'name'                 => $this->get_attribute_taxonomy_name( $attribute['name'], $product ),
					'position'             => (int) $attribute['position'],
					'is_attribute_visible' => (bool) $attribute['is_visible'],
					'used_for_variation'   => (bool) $attribute['is_variation'],
					'options'              => $this->get_attribute_options( $product->get_id(), $attribute ),
				);
			}
		}

		return $attributes;
	} // END get_attributes()

	/**
	 * Get product data.
	 *
	 * @access protected
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array
	 */
	protected function get_product_data( $product ) {
		$rating_count = $product->get_rating_count();
		$review_count = $product->get_review_count();
		$average      = $product->get_average_rating();

		$data = array(
			'id'                    => $product->get_id(),
			'name'                  => $product->get_name( 'view' ),
			'slug'                  => urldecode( $product->get_slug( 'view' ) ),
			'permalink'             => urldecode( $product->get_permalink() ),
			'date_created'          => wc_rest_prepare_date_response( $product->get_date_created( 'view' ), false ),
			'date_created_gmt'      => wc_rest_prepare_date_response( $product->get_date_created( 'view' ) ),
			'date_modified'         => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ), false ),
			'date_modified_gmt'     => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ) ),
			'type'                  => $product->get_type(),
			'featured'              => $product->is_featured(),
			'catalog_visibility'    => $product->get_catalog_visibility( 'view' ),
			'description'           => $product->get_description( 'view' ),
			'short_description'     => $product->get_short_description( 'view' ),
			'sku'                   => $product->get_sku( 'view' ),
			'price'                 => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price( 'view' ) ) ) ),
			'regular_price'         => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_regular_price( 'view' ) ) ) ),
			'sale_price'            => $product->get_sale_price( 'view' ) ? html_entity_decode( wp_strip_all_tags( wc_price( $product->get_sale_price( 'view' ) ) ) ) : '',
			'date_on_sale_from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ), false ),
			'date_on_sale_from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ) ),
			'date_on_sale_to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ), false ),
			'date_on_sale_to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ) ),
			'on_sale'               => $product->is_on_sale( 'view' ),
			'purchasable'           => $product->is_purchasable(),
			'total_sales'           => $product->get_total_sales( 'view' ),
			'virtual'               => $product->is_virtual(),
			'downloadable'          => $product->is_downloadable(),
			'external_url'          => $product->is_type( 'external' ) ? $product->get_product_url( 'view' ) : '',
			'button_text'           => $product->is_type( 'external' ) ? $product->get_button_text( 'view' ) : '',
			'manage_stock'          => $product->managing_stock(),
			'stock_quantity'        => $product->get_stock_quantity( 'view' ),
			'has_options'           => $product->has_options(),
			'in_stock'              => $product->is_in_stock(),
			'stock_status'          => $product->get_stock_status( 'view' ),
			'backorders'            => $product->get_backorders( 'view' ),
			'backorders_allowed'    => $product->backorders_allowed(),
			'backordered'           => $product->is_on_backorder(),
			'low_stock_amount'      => $product->get_low_stock_amount( 'view' ),
			'sold_individually'     => $product->is_sold_individually(),
			'weight'                => array(
				'value' => $product->get_weight( 'view' ),
				'unit'  => get_option( 'woocommerce_weight_unit' ),
			),
			'dimensions'            => array(
				'length' => $product->get_length( 'view' ),
				'width'  => $product->get_width( 'view' ),
				'height' => $product->get_height( 'view' ),
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			),
			'shipping_required'     => $product->needs_shipping(),
			'reviews_allowed'       => $product->get_reviews_allowed(),
			'average_rating'        => $average,
			'rating_count'          => $rating_count,
			'review_count'          => $review_count,
			'rating_html'           => html_entity_decode( wp_strip_all_tags( wc_get_rating_html( $average, $rating_count ) ) ),
			'reviews'               => array(),
			'related_ids'           => array_map( 'absint', array_values( wc_get_related_products( $product->get_id(), apply_filters( 'cocart_products_get_related_products_limit', 5 ) ) ) ),
			'upsell_ids'            => array_map( 'absint', $product->get_upsell_ids( 'view' ) ),
			'cross_sell_ids'        => array_map( 'absint', $product->get_cross_sell_ids( 'view' ) ),
			'parent_id'             => $product->get_parent_id( 'view' ),
			'categories'            => $this->get_taxonomy_terms( $product ),
			'tags'                  => $this->get_taxonomy_terms( $product, 'tag' ),
			'images'                => $this->get_images( $product ),
			'attributes'            => $this->get_attributes( $product ),
			'default_attributes'    => $this->get_default_attributes( $product ),
			'variations'            => array(),
			'grouped_products'      => array(),
			'menu_order'            => $product->get_menu_order( 'view' ),
			'meta_data'             => CoCart_Utilities_Product_Helpers::get_meta_data( $product ),
			'add_to_cart'           => array(
				'text'        => $product->add_to_cart_text(),
				'description' => $product->add_to_cart_description(),
			),
		);

		return $data;
	} // END get_product_data()

	/**
	 * Get variation product data.
	 *
	 * @access protected
	 *
	 * @param WC_Variation_Product $product The product object.
	 *
	 * @return array
	 */
	protected function get_variation_product_data( $product ) {
		$data = array(
			'id'                    => $product->get_id(),
			'name'                  => $product->get_name( 'view' ),
			'slug'                  => urldecode( $product->get_slug( 'view' ) ),
			'permalink'             => urldecode( $product->get_permalink() ),
			'date_created'          => wc_rest_prepare_date_response( $product->get_date_created( 'view' ), false ),
			'date_created_gmt'      => wc_rest_prepare_date_response( $product->get_date_created( 'view' ) ),
			'date_modified'         => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ), false ),
			'date_modified_gmt'     => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ) ),
			'description'           => $product->get_description( 'view' ),
			'sku'                   => $product->get_sku( 'view' ),
			'price'                 => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price( 'view' ) ) ) ),
			'regular_price'         => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_regular_price( 'view' ) ) ) ),
			'sale_price'            => $product->get_sale_price( 'view' ) ? html_entity_decode( wp_strip_all_tags( wc_price( $product->get_sale_price( 'view' ) ) ) ) : '',
			'date_on_sale_from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ), false ),
			'date_on_sale_from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ) ),
			'date_on_sale_to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ), false ),
			'date_on_sale_to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ) ),
			'on_sale'               => $product->is_on_sale( 'view' ),
			'purchasable'           => $product->is_purchasable(),
			'total_sales'           => $product->get_total_sales( 'view' ),
			'virtual'               => $product->is_virtual(),
			'downloadable'          => $product->is_downloadable(),
			'manage_stock'          => $product->managing_stock(),
			'stock_quantity'        => $product->get_stock_quantity( 'view' ),
			'in_stock'              => $product->is_in_stock(),
			'stock_status'          => $product->get_stock_status( 'view' ),
			'backorders'            => $product->get_backorders( 'view' ),
			'backorders_allowed'    => $product->backorders_allowed(),
			'backordered'           => $product->is_on_backorder(),
			'low_stock_amount'      => $product->get_low_stock_amount( 'view' ),
			'weight'                => array(
				'value' => $product->get_weight( 'view' ),
				'unit'  => get_option( 'woocommerce_weight_unit' ),
			),
			'dimensions'            => array(
				'length' => $product->get_length( 'view' ),
				'width'  => $product->get_width( 'view' ),
				'height' => $product->get_height( 'view' ),
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			),
			'shipping_required'     => $product->needs_shipping(),
			'images'                => $this->get_images( $product ),
			'attributes'            => $this->get_attributes( $product ),
			'menu_order'            => $product->get_menu_order( 'view' ),
			'meta_data'             => CoCart_Utilities_Product_Helpers::get_meta_data( $product ),
		);

		return $data;
	} // END get_variation_product_data()

	/**
	 * Get the Product's schema, conforming to JSON Schema.
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
					'description' => __( 'Unique identifier for the product.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'                  => array(
					'description' => __( 'Product name.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'slug'                  => array(
					'description' => __( 'Product slug.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'permalink'             => array(
					'description' => __( 'Product permalink.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created'          => array(
					'description' => __( "The date the product was created, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created_gmt'      => array(
					'description' => __( 'The date the product was created, as GMT.', 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified'         => array(
					'description' => __( "The date the product was last modified, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified_gmt'     => array(
					'description' => __( 'The date the product was last modified, as GMT.', 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'type'                  => array(
					'description' => __( 'Product type. Default values are `simple | variable | variation` but other types maybe also be available with other product type extensions.', 'cocart-core' ),
					'type'        => 'string',
					'enum'        => array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'featured'              => array(
					'description' => __( 'Featured product.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'catalog_visibility'    => array(
					'description' => __( 'Catalog visibility. Default is visible. Other values are `any | catalog | search and hidden`.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'visible',
					'enum'        => array( 'any', 'visible', 'catalog', 'search', 'hidden' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'description'           => array(
					'description' => __( 'Product description.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'short_description'     => array(
					'description' => __( 'Product short description.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sku'                   => array(
					'description' => __( 'Unique identifier.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'price'                 => array(
					'description' => __( 'The current price of the product. Returns formatted.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'regular_price'         => array(
					'description' => __( 'The regular price of the product. Returns formatted.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sale_price'            => array(
					'description' => __( 'The sale price of the product. Returns formatted.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_from'     => array(
					'description' => __( "Start date of sale price, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_from_gmt' => array(
					'description' => __( 'Start date of sale price, as GMT.', 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_to'       => array(
					'description' => __( "End date of sale price, in the site's timezone.", 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_to_gmt'   => array(
					'description' => __( 'End date of sale price, as GMT.', 'cocart-core' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'on_sale'               => array(
					'description' => __( 'Shows if the product is on sale.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'purchasable'           => array(
					'description' => __( 'Shows if the product can be bought.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'total_sales'           => array(
					'description' => __( 'Amount of sales.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'virtual'               => array(
					'description' => __( 'Shows if the product is virtual.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'downloadable'          => array(
					'description' => __( 'Shows if the product is downloadable.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'external_url'          => array(
					'description' => __( 'Product external URL. Only for external products.', 'cocart-core' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'button_text'           => array(
					'description' => __( 'Product external button text. Only for external products.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'manage_stock'          => array(
					'description' => __( 'Stock management at product level.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'stock_quantity'        => array(
					'description' => __( 'Stock quantity.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'has_options'           => array(
					'description' => __( 'Determines whether or not the product has additional options that need selecting before adding to cart.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'in_stock'              => array(
					'description' => __( 'Determines if product is listed as "in stock" or "out of stock".', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backorders'            => array(
					'description' => __( 'If managing stock, this controls if backorders are allowed.', 'cocart-core' ),
					'type'        => 'string',
					'default'     => 'no',
					'enum'        => array( 'no', 'notify', 'yes' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backorders_allowed'    => array(
					'description' => __( 'Are backorders allowed?', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backordered'           => array(
					'description' => __( 'Shows if the product is on backordered.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sold_individually'     => array(
					'description' => __( 'Allow one of the item to be bought in a single order.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'weight'                => array(
					'description' => sprintf(
						/* translators: %s: weight unit */
						__( 'Product weight (%s).', 'cocart-core' ),
						$weight_unit
					),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'value' => array(
							'description' => __( 'Product weight value.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'unit'  => array(
							'description' => __( 'Product weight unit.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
					'readonly'    => true,
				),
				'dimensions'            => array(
					'description' => __( 'Product dimensions.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'length' => array(
							'description' => sprintf(
								/* translators: %s: dimension unit */
								__( 'Product length (%s).', 'cocart-core' ),
								$dimension_unit
							),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'width'  => array(
							'description' => sprintf(
								/* translators: %s: dimension unit */
								__( 'Product width (%s).', 'cocart-core' ),
								$dimension_unit
							),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'height' => array(
							'description' => sprintf(
								/* translators: %s: dimension unit */
								__( 'Product height (%s).', 'cocart-core' ),
								$dimension_unit
							),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
					'readonly'    => true,
				),
				'shipping_required'     => array(
					'description' => __( 'Shows if the product need to be shipped.', 'cocart-core' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'reviews_allowed'       => array(
					'description' => __( 'Shows if reviews are allowed.', 'cocart-core' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'reviews'               => array(
					'description' => __( 'Lists product reviews, when requested.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'review_id'       => array(
							'description' => __( 'Review ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'author_name'     => array(
							'description' => __( 'Author name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'author_url'      => array(
							'description' => __( 'Author URL.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'review_comment'  => array(
							'description' => __( 'Review comment.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'review_date'     => array(
							'description' => __( "Review date, in the site's timezone.", 'cocart-core' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'review_date_gmt' => array(
							'description' => __( 'Review date, as GMT.', 'cocart-core' ),
							'type'        => 'date-time',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'rating'          => array(
							'description' => __( 'Rating number.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'verified'        => array(
							'description' => __( 'Shows if the product review is verified.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
					'readonly'    => true,
				),
				'average_rating'        => array(
					'description' => __( 'Reviews average rating.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'rating_count'          => array(
					'description' => __( 'Amount of reviews that the product has.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'review_count'          => array(
					'description' => __( 'Amount of reviews that the product have.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'rating_html'           => array(
					'description' => __( 'Returns the rating of the product in html.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'related_ids'           => array(
					'description' => __( 'List of related products IDs.', 'cocart-core' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'upsell_ids'            => array(
					'description' => __( 'List of up-sell products IDs.', 'cocart-core' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cross_sell_ids'        => array(
					'description' => __( 'List of cross-sell products IDs.', 'cocart-core' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'parent_id'             => array(
					'description' => __( 'Product parent ID.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'categories'            => array(
					'description' => __( 'List of product categories.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'   => array(
								'description' => __( 'Category ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name' => array(
								'description' => __( 'Category name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'slug' => array(
								'description' => __( 'Category slug.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'tags'                  => array(
					'description' => __( 'List of product tags.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'   => array(
								'description' => __( 'Tag ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name' => array(
								'description' => __( 'Tag name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'slug' => array(
								'description' => __( 'Tag slug.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'images'                => array(
					'description' => __( 'List of product images.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                => array(
								'description' => __( 'Image ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
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
								'type'        => 'array',
								'format'      => 'uri',
								'context'     => array( 'view' ),
								'properties'  => array(),
								'readonly'    => true,
							),
							'name'              => array(
								'description' => __( 'Image name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'alt'               => array(
								'description' => __( 'Image alternative text.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'position'          => array(
								'description' => __( 'Image position. 0 means that the image is featured.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'attributes'            => array(
					'description' => __( 'List of attributes.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                   => array(
								'description' => __( 'Attribute ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'                 => array(
								'description' => __( 'Attribute name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'position'             => array(
								'description' => __( 'Attribute position.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'is_attribute_visible' => array(
								'description' => __( "Is the attribute visible on the \"Additional information\" tab in the product's page.", 'cocart-core' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'used_for_variation'   => array(
								'description' => __( 'Can the attribute be used as variation?', 'cocart-core' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'options'              => array(
								'description' => __( 'List of available term names of the attribute.', 'cocart-core' ),
								'type'        => 'array',
								'context'     => array( 'view' ),
								'items'       => array(
									'type' => 'string',
								),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'default_attributes'    => array(
					'description' => __( 'Defaults variation attributes.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'     => array(
								'description' => __( 'Attribute ID.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'   => array(
								'description' => __( 'Attribute name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'option' => array(
								'description' => __( 'Selected attribute term name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'variations'            => array(
					'description' => __( 'List of all variation IDs and data if requested true with `return_variations` parameter.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                 => array(
								'description' => __( 'Unique identifier for the product.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'               => array(
								'description' => __( 'Product name.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'slug'               => array(
								'description' => __( 'Product slug.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'permalink'          => array(
								'description' => __( 'Product permalink.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'date_created'       => array(
								'description' => __( "The date the product was created, in the site's timezone.", 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'date_created_gmt'   => array(
								'description' => __( 'The date the product was created, as GMT.', 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'date_modified'      => array(
								'description' => __( "The date the product was last modified, in the site's timezone.", 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'date_modified_gmt'  => array(
								'description' => __( 'The date the product was last modified, as GMT.', 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'purchasable'        => array(
								'description' => __( 'Shows if the product can be bought.', 'cocart-core' ),
								'type'        => 'boolean',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'total_sales'        => array(
								'description' => __( 'Amount of sales.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'virtual'            => array(
								'description' => __( 'If the product is virtual.', 'cocart-core' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'downloadable'       => array(
								'description' => __( 'If the product is downloadable.', 'cocart-core' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'manage_stock'       => array(
								'description' => __( 'Stock management at product level.', 'cocart-core' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'stock_quantity'     => array(
								'description' => __( 'Stock quantity.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'in_stock'           => array(
								'description' => __( 'Determines if product is listed as "in stock" or "out of stock".', 'cocart-core' ),
								'type'        => 'boolean',
								'default'     => true,
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'backorders'         => array(
								'description' => __( 'If managing stock, this controls if backorders are allowed.', 'cocart-core' ),
								'type'        => 'string',
								'default'     => 'no',
								'enum'        => array( 'no', 'notify', 'yes' ),
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'backorders_allowed' => array(
								'description' => __( 'Are backorders allowed?', 'cocart-core' ),
								'type'        => 'boolean',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'backordered'        => array(
								'description' => __( 'Shows if the product is on backordered.', 'cocart-core' ),
								'type'        => 'boolean',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'low_stock_amount'   => array(
								'description' => __( 'Low stock amount.', 'cocart-core' ),
								'type'        => 'int',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'weight'             => array(
								'description' => sprintf(
									/* translators: %s: weight unit */
									__( 'Product weight (%s).', 'cocart-core' ),
									$weight_unit
								),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'properties'  => array(
									'value' => array(
										'description' => __( 'Product weight value.', 'cocart-core' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'unit'  => array(
										'description' => __( 'Product weight unit.', 'cocart-core' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
								'readonly'    => true,
							),
							'dimensions'         => array(
								'description' => __( 'Product dimensions.', 'cocart-core' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'properties'  => array(
									'length' => array(
										'description' => sprintf(
											/* translators: %s: dimension unit */
											__( 'Product length (%s).', 'cocart-core' ),
											$dimension_unit
										),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'width'  => array(
										'description' => sprintf(
											/* translators: %s: dimension unit */
											__( 'Product width (%s).', 'cocart-core' ),
											$dimension_unit
										),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'height' => array(
										'description' => sprintf(
											/* translators: %s: dimension unit */
											__( 'Product height (%s).', 'cocart-core' ),
											$dimension_unit
										),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
								'readonly'    => true,
							),
							'shipping_required'  => array(
								'description' => __( 'Shows if the product need to be shipped.', 'cocart-core' ),
								'type'        => 'boolean',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'images'             => array(
								'description' => __( 'List of product images.', 'cocart-core' ),
								'type'        => 'array',
								'context'     => array( 'view' ),
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'id'               => array(
											'description' => __( 'Image ID.', 'cocart-core' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'date_created'     => array(
											'description' => __( "The date the image was created, in the site's timezone.", 'cocart-core' ),
											'type'        => 'date-time',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'date_created_gmt' => array(
											'description' => __( 'The date the image was created, as GMT.', 'cocart-core' ),
											'type'        => 'date-time',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'date_modified'    => array(
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
										'src'              => array(
											'description' => __( 'Image URL.', 'cocart-core' ),
											'type'        => 'array',
											'format'      => 'uri',
											'context'     => array( 'view' ),
											'properties'  => array(),
											'readonly'    => true,
										),
										'name'             => array(
											'description' => __( 'Image name.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'alt'              => array(
											'description' => __( 'Image alternative text.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'position'         => array(
											'description' => __( 'Image position. 0 means that the image is featured.', 'cocart-core' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
								),
								'readonly'    => true,
							),
							'attributes'         => array(
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
											'readonly'    => true,
										),
										'name'   => array(
											'description' => __( 'Attribute name.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'option' => array(
											'description' => __( 'Option value of attribute.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
								),
								'readonly'    => true,
							),
							'menu_order'         => array(
								'description' => __( 'Menu order, used to custom sort products.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'meta_data'          => array(
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
											'readonly'    => true,
										),
										'value' => array(
											'description' => __( 'Meta value.', 'cocart-core' ),
											'type'        => 'mixed',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
								),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'grouped_products'      => array(
					'description' => __( 'List of grouped products ID.', 'cocart-core' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'menu_order'            => array(
					'description' => __( 'Menu order, used to custom sort products.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
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
								'readonly'    => true,
							),
							'value' => array(
								'description' => __( 'Meta value.', 'cocart-core' ),
								'type'        => 'mixed',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'add_to_cart'           => array(
					'description' => __( 'Add to Cart button.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'text'        => array(
							'description' => __( 'Text', 'cocart-core' ),
							'type'        => 'string',
							'default'     => __( 'Add to Cart', 'cocart-core' ),
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'description' => array(
							'description' => __( 'Description', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
					'readonly'    => true,
				),
			),
		);

		// Fetch each image size.
		$attachment_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();

		foreach ( $attachment_sizes as $size ) {
			// Generate the product image URL properties for each attachment size.
			$schema['properties']['images']['items']['properties']['src']['properties'][ $size ] = array(
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

			// Generate the variation product image URL properties for each attachment size.
			if ( isset( $schema['properties']['variations']['items']['properties']['images']['items']['properties']['src']['properties'] ) ) {
				$schema['properties']['variations']['items']['properties']['images']['items']['properties']['src']['properties'][ $size ] = array(
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
		}

		/**
		 * Filter allows you to modify or extend the product schema properties for the v1 API.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param array $properties The schema properties.
		 */
		$schema['properties'] = apply_filters( 'cocart_rest_v1_product_schema', $schema['properties'] );

		return $this->add_additional_fields_schema( $schema );
	} // END get_item_schema()
} // END class
