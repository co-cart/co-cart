<?php
/**
 * REST API: Products v1 controller.
 *
 * Handles requests to the /products/ endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products\v1
 * @since   3.1.0
 * @version 3.7.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product controller class.
 *
 * @package CoCart Products/API
 * @extends WP_REST_Controller
 */
class CoCart_Products_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'products';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'product';

	/**
	 * Register the routes for products.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Products - cocart/v1/products (GET)
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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get a single product - cocart/v1/products/32 (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get post types.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_post_types() {
		return array( 'product', 'product_variation' );
	} // END get_post_types()

	/**
	 * Get object.
	 *
	 * @access protected
	 * @param  int $id Object ID.
	 * @return WC_Data
	 */
	protected function get_object( $id ) {
		return wc_get_product( $id );
	} // END get_object()

	/**
	 * Get objects.
	 *
	 * @access protected
	 * @param  array $query_args Query args.
	 * @return array
	 */
	protected function get_objects( $query_args ) {
		$query       = new WP_Query();
		$result      = $query->query( $query_args );
		$total_posts = $query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		return array(
			'objects' => array_map( array( $this, 'get_object' ), $result ),
			'total'   => (int) $total_posts,
			'pages'   => (int) ceil( $total_posts / (int) $query->query_vars['posts_per_page'] ),
		);
	} // END get_objects()

	/**
	 * Get a collection of products.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$query_args    = $this->prepare_objects_query( $request );
		$query_results = $this->get_objects( $query_args );

		$objects = array();

		foreach ( $query_results['objects'] as $object ) {
			$data      = $this->prepare_object_for_response( $object, $request );
			$objects[] = $this->prepare_response_for_collection( $data );
		}

		$page      = (int) $query_args['paged'];
		$max_pages = $query_results['pages'];

		$response = rest_ensure_response( $objects );
		$response->header( 'X-WP-Total', $query_results['total'] );
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
	 * Prepare links for the request.
	 *
	 * @access protected
	 * @param  WC_Product      $product Product object.
	 * @param  WP_REST_Request $request Request object.
	 * @return array Links for the given product.
	 */
	protected function prepare_links( $product, $request ) {
		$links = array(
			'self'       => array(
				'permalink' => get_permalink( $product->get_id() ),
				'href'      => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $product->get_id() ) ),
			),
			'collection' => array(
				'permalink' => wc_get_page_permalink( 'shop' ),
				'href'      => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( $product->get_parent_id() ) {
			$links['parent_product'] = array(
				'permalink' => get_permalink( $product->get_parent_id() ),
				'href'      => rest_url( sprintf( '/%s/products/%d', $this->namespace, $product->get_parent_id() ) ),
			);
		}

		// If product is a variable product, return links to all variations.
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$variations = $product->get_children();

			foreach ( $variations as $variation_product ) {
				$links['variations'][ $variation_product ] = array(
					'permalink' => get_permalink( $variation_product ),
					'href'      => rest_url( sprintf( '/%s/products/%d/variations/%d', $this->namespace, $product->get_id(), $variation_product ) ),
				);
			}
		}

		return $links;
	} // END prepare_links()

	/**
	 * Prepare a single product output for response.
	 *
	 * @access public
	 * @param  WC_Data         $object  Object data.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $object, $request ) {
		// Check what product type before returning product data.
		if ( $object->get_type() !== 'variation' ) {
			$data = $this->get_product_data( $object );
		} else {
			$data = $this->get_variation_product_data( $object );
		}

		// Add review data to products if requested.
		if ( $request['show_reviews'] ) {
			$data['reviews'] = $this->get_reviews( $object );
		}

		// Add variations to variable products. Returns just IDs by default.
		if ( $object->is_type( 'variable' ) && $object->has_child() ) {
			$variations = $object->get_children();

			foreach ( $variations as $variation_product ) {
				$data['variations'][ $variation_product ] = array( 'id' => $variation_product );

				// If requested to return variations then fetch them.
				if ( $request['return_variations'] ) {
					$variation_object                         = new WC_Product_Variation( $variation_product );
					$data['variations'][ $variation_product ] = $this->get_variation_product_data( $variation_object );
				}
			}
		}

		// Add grouped products data.
		if ( $object->is_type( 'grouped' ) && $object->has_child() ) {
			$data['grouped_products'] = $object->get_children();
		}

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Data          $object   Object data.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'cocart_prepare_product_object', $response, $object, $request );
	} // END prepare_object_for_response()

	/**
	 * Get a single item.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object || 0 === $object->get_id() ) {
			return new WP_Error( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$data     = $this->prepare_object_for_response( $object, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // END get_item()

	/**
	 * Prepare objects query.
	 *
	 * @access protected
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = array(
			'offset'              => $request['offset'],
			'order'               => ! empty( $request['order'] ) ? strtoupper( $request['order'] ) : 'DESC',
			'orderby'             => ! empty( $request['orderby'] ) ? strtolower( $request['orderby'] ) : get_option( 'woocommerce_default_catalog_orderby' ),
			'paged'               => $request['page'],
			'post__in'            => $request['include'],
			'post__not_in'        => $request['exclude'],
			'posts_per_page'      => $request['per_page'],
			'post_parent__in'     => $request['parent'],
			'post_parent__not_in' => $request['parent_exclude'],
			'name'                => $request['slug'],
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'date_query'          => array(),
			'post_type'           => 'product',
		);

		// If searching for a specific SKU, allow any post type.
		if ( ! empty( $request['sku'] ) ) {
			$args['post_type'] = $this->get_post_types();
		}

		// If order by is not set then use WooCommerce default catalog setting.
		if ( empty( $args['orderby'] ) ) {
			$args['orderby'] = get_option( 'woocommerce_default_catalog_orderby' );
		}

		switch ( $args['orderby'] ) {
			case 'id':
				$args['orderby'] = 'ID'; // ID must be capitalized.
				break;
			case 'menu_order':
				$args['orderby'] = 'menu_order title';
				break;
			case 'include':
				$args['orderby'] = 'post__in';
				break;
			case 'name':
			case 'slug':
				$args['orderby'] = 'name';
				break;
			case 'alphabetical':
				$args['orderby']  = 'title';
				$args['order']    = 'ASC';
				$args['meta_key'] = '';
				break;
			case 'reverse_alpha':
				$args['orderby']  = 'title';
				$args['order']    = 'DESC';
				$args['meta_key'] = '';
				break;
			case 'title':
				$args['orderby'] = 'title';
				$args['order']   = ( 'DESC' === $args['order'] ) ? 'DESC' : 'ASC';
				break;
			case 'relevance':
				$args['orderby'] = 'relevance';
				$args['order']   = 'DESC';
				break;
			case 'rand':
				$args['orderby'] = 'rand';
				break;
			case 'date':
				$args['orderby'] = 'date ID';
				$args['order']   = ( 'ASC' === $args['order'] ) ? 'ASC' : 'DESC';
				break;
			case 'by_stock':
				$args['orderby']  = array(
					'meta_value_num' => 'DESC',
					'title'          => 'ASC',
				);
				$args['meta_key'] = '_stock';
				break;
			case 'review_count':
				$args['orderby']  = array(
					'meta_value_num' => 'DESC',
					'title'          => 'ASC',
				);
				$args['meta_key'] = '_wc_review_count';
				break;
			case 'on_sale_first':
				$args['orderby']      = array(
					'meta_value_num' => 'DESC',
					'title'          => 'ASC',
				);
				$args['meta_key']     = '_sale_price';
				$args['meta_value']   = 0;
				$args['meta_compare'] = '>=';
				$args['meta_type']    = 'NUMERIC';
				break;
			case 'featured_first':
				$args['orderby']  = array(
					'meta_value' => 'DESC',
					'title'      => 'ASC',
				);
				$args['meta_key'] = '_featured';
				break;
			case 'price_asc':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'ASC';
				$args['meta_key'] = '_price';
				break;
			case 'price_desc':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				$args['meta_key'] = '_price';
				break;
			case 'sales':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales';
				break;
			case 'rating':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				$args['meta_key'] = '_wc_average_rating';
				break;
		}

		// Taxonomy query to filter products by type, category, tag and attribute.
		$tax_query = array();

		// Filter product type by slug.
		if ( ! empty( $request['type'] ) ) {
			if ( 'variation' === $request['type'] ) {
				$args['post_type'] = 'product_variation';
			} else {
				$tax_query[] = array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $request['type'],
				);
			}
		}

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		$operator_mapping = array(
			'in'     => 'IN',
			'not_in' => 'NOT IN',
			'and'    => 'AND',
		);

		// Map between taxonomy name and arg key.
		$taxonomies = array(
			'product_cat' => 'category',
			'product_tag' => 'tag',
		);

		// Set tax_query for each passed arg.
		foreach ( $taxonomies as $taxonomy => $key ) {
			if ( ! empty( $request[ $key ] ) ) {
				$operator    = $request[ $key . '_operator' ] && isset( $operator_mapping[ $request[ $key . '_operator' ] ] ) ? $operator_mapping[ $request[ $key . '_operator' ] ] : 'IN';
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => is_numeric( $request[ $key ] ) ? 'term_id' : 'slug',
					'terms'    => $request[ $key ],
					'operator' => $operator,
				);
			}
		}

		// Filter by attributes.
		if ( ! empty( $request['attributes'] ) ) {
			$att_queries = array();

			foreach ( $request['attributes'] as $attribute ) {
				if ( empty( $attribute['term_id'] ) && empty( $attribute['slug'] ) ) {
					continue;
				}

				if ( in_array( $attribute['attribute'], wc_get_attribute_taxonomy_names(), true ) ) {
					$operator      = isset( $attribute['operator'], $operator_mapping[ $attribute['operator'] ] ) ? $operator_mapping[ $attribute['operator'] ] : 'IN';
					$att_queries[] = array(
						'taxonomy' => $attribute['attribute'],
						'field'    => ! empty( $attribute['term_id'] ) ? 'term_id' : 'slug',
						'terms'    => ! empty( $attribute['term_id'] ) ? $attribute['term_id'] : $attribute['slug'],
						'operator' => $operator,
					);
				}
			}

			if ( 1 < count( $att_queries ) ) {
				// Add relation arg when using multiple attributes.
				$relation    = $request['attribute_relation'] && isset( $operator_mapping[ $request['attribute_relation'] ] ) ? $operator_mapping[ $request['attribute_relation'] ] : 'IN';
				$tax_query[] = array(
					'relation' => $relation,
					$att_queries,
				);
			} else {
				$tax_query = array_merge( $tax_query, $att_queries );
			}
		}

		// Build tax_query if taxonomies are set.
		if ( ! empty( $tax_query ) ) {
			if ( ! empty( $args['tax_query'] ) ) {
				$args['tax_query'] = array_merge( $tax_query, $args['tax_query'] );
			} else {
				$args['tax_query'] = $tax_query;
			}
		}

		// Hide free products.
		if ( ! empty( $request['hide_free'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'DECIMAL',
				)
			);
		}

		// Filter featured.
		if ( is_bool( $request['featured'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
				'operator' => true === $request['featured'] ? 'IN' : 'NOT IN',
			);
		}

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
			$args['meta_query'] = $this->add_meta_query( $args, cocart_get_min_max_price_meta_query( $request ) ); // WPCS: slow query ok.
		}

		// Filter product in stock or out of stock.
		if ( is_bool( $request['stock_status'] ) ) {
			$args['meta_query'] = $this->add_meta_query( // WPCS: slow query ok.
				$args,
				array(
					'key'   => '_stock_status',
					'value' => true === $request['stock_status'] ? 'instock' : 'outofstock',
				)
			);
		}

		// Filter by on sale products.
		if ( is_bool( $request['on_sale'] ) ) {
			$on_sale_key = $request['on_sale'] ? 'post__in' : 'post__not_in';
			$on_sale_ids = wc_get_product_ids_on_sale();

			// Use 0 when there's no on sale products to avoid return all products.
			$on_sale_ids = empty( $on_sale_ids ) ? array( 0 ) : $on_sale_ids;

			$args[ $on_sale_key ] = $on_sale_ids;
		}

		// Filter by Catalog Visibility
		$catalog_visibility = $request->get_param( 'catalog_visibility' );
		$visibility_options = wc_get_product_visibility_options();

		if ( in_array( $catalog_visibility, array_keys( $visibility_options ), true ) ) {
			$exclude_from_catalog = 'search' === $catalog_visibility ? '' : 'exclude-from-catalog';
			$exclude_from_search  = 'catalog' === $catalog_visibility ? '' : 'exclude-from-search';

			$args['tax_query'][] = array(
				'taxonomy'      => 'product_visibility',
				'field'         => 'name',
				'terms'         => array( $exclude_from_catalog, $exclude_from_search ),
				'operator'      => 'hidden' === $catalog_visibility ? 'AND' : 'NOT IN',
				'rating_filter' => true,
			);
		}

		// Filter by Product Rating
		$rating = $request->get_param( 'rating' );

		if ( ! empty( $rating ) ) {
			$rating_terms = array();

			foreach ( $rating as $value ) {
				$rating_terms[] = 'rated-' . $value;
			}

			$args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => $rating_terms,
			);
		}

		return apply_filters( 'cocart_prepare_objects_query', $args, $request );
	} // END prepare_objects_query()

	/**
	 * Get taxonomy terms.
	 *
	 * @access protected
	 * @param  WC_Product $product  Product instance.
	 * @param  string     $taxonomy Taxonomy slug.
	 * @return array
	 */
	protected function get_taxonomy_terms( $product, $taxonomy = 'cat' ) {
		$terms = array();

		foreach ( wc_get_object_terms( $product->get_id(), 'product_' . $taxonomy ) as $term ) {
			$terms[] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			);
		}

		return $terms;
	} // END get_taxonomy_terms()

	/**
	 * Get the images for a product or product variation.
	 *
	 * @access protected
	 * @param  WC_Product|WC_Product_Variation $product Product instance.
	 * @return array $images
	 */
	protected function get_images( $product ) {
		$images           = array();
		$attachment_ids   = array();
		$attachment_sizes = apply_filters( 'cocart_products_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );

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
				'name'              => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'alt'               => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'position'          => 0,
			);
		}

		return $images;
	} // END get_images()

	/**
	 * Get the reviews for a product.
	 *
	 * @access protected
	 * @param  WC_Product|WC_Product_Variation $product Product instance.
	 * @return array $reviews
	 */
	protected function get_reviews( $product ) {
		$args           = array(
			'post_id'      => $product->get_id(),
			'comment_type' => 'review',
		);
		$comments_query = new WP_Comment_Query();
		$comments       = $comments_query->query( $args );

		$reviews = array();

		foreach ( $comments as $key => $review ) {
			$reviews[ $key ] = array(
				'review_id'       => $review->comment_ID,
				'author_name'     => ucfirst( $review->comment_author ),
				'author_url'      => $review->comment_author_url,
				'review_comment'  => $review->comment_content,
				'review_date'     => $review->comment_date,
				'review_date_gmt' => $review->comment_date_gmt,
				'rating'          => get_comment_meta( $review->comment_ID, 'rating', true ),
				'verified'        => get_comment_meta( $review->comment_ID, 'verified', true ),
			);
		}

		return $reviews;
	} // END get_reviews()

	/**
	 * Get product attribute taxonomy name.
	 *
	 * @access protected
	 * @param  string     $slug    Taxonomy name.
	 * @param  WC_Product $product Product data.
	 * @return string
	 */
	protected function get_attribute_taxonomy_name( $slug, $product ) {
		$attributes = $product->get_attributes();

		if ( ! isset( $attributes[ $slug ] ) ) {
			return str_replace( 'pa_', '', $slug );
		}

		$attribute = $attributes[ $slug ];

		// Taxonomy attribute name.
		if ( $attribute->is_taxonomy() ) {
			$taxonomy = $attribute->get_taxonomy_object();
			return $taxonomy->attribute_label;
		}

		// Custom product attribute name.
		return $attribute->get_name();
	} // END get_attribute_taxonomy_name()

	/**
	 * Get default attributes.
	 *
	 * @access protected
	 * @param  WC_Product $product Product instance.
	 * @return array
	 */
	protected function get_default_attributes( $product ) {
		$default = array();

		if ( $product->is_type( 'variable' ) ) {
			foreach ( array_filter( (array) $product->get_default_attributes(), 'strlen' ) as $key => $value ) {
				if ( 0 === strpos( $key, 'pa_' ) ) {
					$default[ 'attribute_' . $key ] = array(
						'id'     => wc_attribute_taxonomy_id_by_name( $key ),
						'name'   => $this->get_attribute_taxonomy_name( $key, $product ),
						'option' => $value,
					);
				} else {
					$default[ 'attribute_' . $key ] = array(
						'id'     => 0,
						'name'   => $this->get_attribute_taxonomy_name( $key, $product ),
						'option' => $value,
					);
				}
			}
		}

		return $default;
	} // END get_default_attributes()

	/**
	 * Get attribute options.
	 *
	 * @access protected
	 * @param  int   $product_id Product ID.
	 * @param  array $attribute  Attribute data.
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
	 * @param  WC_Product|WC_Product_Variation $product Product instance.
	 * @return array
	 */
	protected function get_attributes( $product ) {
		$attributes = array();

		if ( $product->is_type( 'variation' ) ) {
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
				$attribute_id = 'attribute_' . str_replace( ' ', '-', strtolower( $attribute['name'] ) );

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
	 * @param  WC_Product $product Product instance.
	 * @return array
	 */
	protected function get_product_data( $product ) {
		$rating_count = $product->get_rating_count( 'view' );
		$review_count = $product->get_review_count( 'view' );
		$average      = $product->get_average_rating( 'view' );

		$data = array(
			'id'                    => $product->get_id(),
			'name'                  => $product->get_name( 'view' ),
			'slug'                  => $product->get_slug( 'view' ),
			'permalink'             => $product->get_permalink(),
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
			'price'                 => html_entity_decode( strip_tags( wc_price( $product->get_price( 'view' ) ) ) ),
			'regular_price'         => html_entity_decode( strip_tags( wc_price( $product->get_regular_price( 'view' ) ) ) ),
			'sale_price'            => $product->get_sale_price( 'view' ) ? html_entity_decode( strip_tags( wc_price( $product->get_sale_price( 'view' ) ) ) ) : '',
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
			'reviews_allowed'       => $product->get_reviews_allowed( 'view' ),
			'average_rating'        => $average,
			'rating_count'          => $rating_count,
			'review_count'          => $review_count,
			'rating_html'           => html_entity_decode( strip_tags( wc_get_rating_html( $average, $rating_count ) ) ),
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
			'meta_data'             => $product->get_meta_data(),
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
	 * @param  WC_Variation_Product $product Product instance.
	 * @return array
	 */
	protected function get_variation_product_data( $product ) {
		$data = array(
			'id'                    => $product->get_id(),
			'name'                  => $product->get_name( 'view' ),
			'slug'                  => $product->get_slug( 'view' ),
			'permalink'             => $product->get_permalink(),
			'date_created'          => wc_rest_prepare_date_response( $product->get_date_created( 'view' ), false ),
			'date_created_gmt'      => wc_rest_prepare_date_response( $product->get_date_created( 'view' ) ),
			'date_modified'         => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ), false ),
			'date_modified_gmt'     => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ) ),
			'description'           => $product->get_description( 'view' ),
			'sku'                   => $product->get_sku( 'view' ),
			'price'                 => html_entity_decode( strip_tags( wc_price( $product->get_price( 'view' ) ) ) ),
			'regular_price'         => html_entity_decode( strip_tags( wc_price( $product->get_regular_price( 'view' ) ) ) ),
			'sale_price'            => $product->get_sale_price( 'view' ) ? html_entity_decode( strip_tags( wc_price( $product->get_sale_price( 'view' ) ) ) ) : '',
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
			'meta_data'             => $product->get_meta_data(),
		);

		return $data;
	} // END get_variation_product_data()

	/**
	 * Add meta query.
	 *
	 * @access protected
	 * @since  3.4.1 Introduced. (Was suppose to be introduced in 3.1.0 but forgot to commit the function until 3.4.1 🤦‍♂️)
	 * @param  array $args       Query args.
	 * @param  array $meta_query Meta query.
	 * @return array
	 */
	protected function add_meta_query( $args, $meta_query ) {
		if ( empty( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}

		$args['meta_query'][] = $meta_query;

		return $args['meta_query'];
	} // END add_meta_query()

	/**
	 * Get the Product's schema, conforming to JSON Schema.
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
					'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'                  => array(
					'description' => __( 'Product name.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'slug'                  => array(
					'description' => __( 'Product slug.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'permalink'             => array(
					'description' => __( 'Product permalink.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created'          => array(
					'description' => __( "The date the product was created, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created_gmt'      => array(
					'description' => __( 'The date the product was created, as GMT.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified'         => array(
					'description' => __( "The date the product was last modified, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified_gmt'     => array(
					'description' => __( 'The date the product was last modified, as GMT.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'type'                  => array(
					'description' => __( 'Product type.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'simple',
					'enum'        => array_keys( wc_get_product_types() ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'featured'              => array(
					'description' => __( 'Featured product.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'catalog_visibility'    => array(
					'description' => __( 'Catalog visibility.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'visible',
					'enum'        => array( 'any', 'visible', 'catalog', 'search', 'hidden' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'description'           => array(
					'description' => __( 'Product description.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'short_description'     => array(
					'description' => __( 'Product short description.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sku'                   => array(
					'description' => __( 'Unique identifier.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'price'                 => array(
					'description' => __( 'Current product price.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'regular_price'         => array(
					'description' => __( 'Product regular price.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sale_price'            => array(
					'description' => __( 'Product sale price.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_from'     => array(
					'description' => __( "Start date of sale price, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_from_gmt' => array(
					'description' => __( 'Start date of sale price, as GMT.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_to'       => array(
					'description' => __( "End date of sale price, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_on_sale_to_gmt'   => array(
					'description' => __( 'End date of sale price, as GMT.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'on_sale'               => array(
					'description' => __( 'Shows if the product is on sale.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'purchasable'           => array(
					'description' => __( 'Shows if the product can be bought.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'total_sales'           => array(
					'description' => __( 'Amount of sales.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'virtual'               => array(
					'description' => __( 'If the product is virtual.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'downloadable'          => array(
					'description' => __( 'If the product is downloadable.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'external_url'          => array(
					'description' => __( 'Product external URL. Only for external products.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'button_text'           => array(
					'description' => __( 'Product external button text. Only for external products.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'manage_stock'          => array(
					'description' => __( 'Stock management at product level.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'stock_quantity'        => array(
					'description' => __( 'Stock quantity.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'has_options'           => array(
					'description' => __( 'Determines whether or not the product has additional options that need selecting before adding to cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'in_stock'              => array(
					'description' => __( 'Determines if product is listed as "in stock" or "out of stock".', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backorders'            => array(
					'description' => __( 'If managing stock, this controls if backorders are allowed.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'no',
					'enum'        => array( 'no', 'notify', 'yes' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backorders_allowed'    => array(
					'description' => __( 'Are backorders allowed?', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'backordered'           => array(
					'description' => __( 'Shows if the product is on backordered.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'sold_individually'     => array(
					'description' => __( 'Allow one of the item to be bought in a single order.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'weight'                => array(
					/* translators: %s: weight unit */
					'description' => sprintf( __( 'Product weight (%s).', 'cart-rest-api-for-woocommerce' ), $weight_unit ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'dimensions'            => array(
					'description' => __( 'Product dimensions.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'length' => array(
							/* translators: %s: dimension unit */
							'description' => sprintf( __( 'Product length (%s).', 'cart-rest-api-for-woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'width'  => array(
							/* translators: %s: dimension unit */
							'description' => sprintf( __( 'Product width (%s).', 'cart-rest-api-for-woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'height' => array(
							/* translators: %s: dimension unit */
							'description' => sprintf( __( 'Product height (%s).', 'cart-rest-api-for-woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
					'readonly'    => true,
				),
				'shipping_required'     => array(
					'description' => __( 'Shows if the product need to be shipped.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'reviews_allowed'       => array(
					'description' => __( 'Shows if reviews are allowed.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'reviews'               => array(
					'description' => __( 'Returns a list of product review IDs', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'average_rating'        => array(
					'description' => __( 'Reviews average rating.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'rating_count'          => array(
					'description' => __( 'Amount of reviews that the product has.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'review_count'          => array(
					'description' => __( 'Amount of reviews that the product have.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'rating_html'           => array(
					'description' => __( 'Returns the rating of the product in html.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'related_ids'           => array(
					'description' => __( 'List of related products IDs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'upsell_ids'            => array(
					'description' => __( 'List of up-sell products IDs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cross_sell_ids'        => array(
					'description' => __( 'List of cross-sell products IDs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'parent_id'             => array(
					'description' => __( 'Product parent ID.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'categories'            => array(
					'description' => __( 'List of product categories.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'   => array(
								'description' => __( 'Category ID.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
							'name' => array(
								'description' => __( 'Category name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'slug' => array(
								'description' => __( 'Category slug.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'tags'                  => array(
					'description' => __( 'List of product tags.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'   => array(
								'description' => __( 'Tag ID.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
							'name' => array(
								'description' => __( 'Tag name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'slug' => array(
								'description' => __( 'Tag slug.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
					'readonly'    => true,
				),
				'images'                => array(
					'description' => __( 'List of product images.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
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
								'type'        => 'array',
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
							'position'          => array(
								'description' => __( 'Image position. 0 means that the image is featured.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
						),
					),
					'readonly'    => true,
				),
				'attributes'            => array(
					'description' => __( 'List of attributes.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                   => array(
								'description' => __( 'Attribute ID.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
							'name'                 => array(
								'description' => __( 'Attribute name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
							),
							'position'             => array(
								'description' => __( 'Attribute position.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
							),
							'is_attribute_visible' => array(
								'description' => __( "Is the attribute visible on the \"Additional information\" tab in the product's page.", 'cart-rest-api-for-woocommerce' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
							),
							'used_for_variation'   => array(
								'description' => __( 'Can the attribute be used as variation?', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'boolean',
								'default'     => false,
								'context'     => array( 'view' ),
							),
							'options'              => array(
								'description' => __( 'List of available term names of the attribute.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'array',
								'context'     => array( 'view' ),
								'items'       => array(
									'type' => 'string',
								),
							),
						),
					),
					'readonly'    => true,
				),
				'default_attributes'    => array(
					'description' => __( 'Defaults variation attributes.', 'cart-rest-api-for-woocommerce' ),
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
					'readonly'    => true,
				),
				'variations'            => array(
					'description' => __( 'List of all variations and data.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'items'       => array(
						'type' => 'object',
					),
					'readonly'    => true,
				),
				'grouped_products'      => array(
					'description' => __( 'List of grouped products ID.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'integer',
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'menu_order'            => array(
					'description' => __( 'Menu order, used to custom sort products.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
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
					'readonly'    => true,
				),
				'add_to_cart'           => array(
					'description' => __( 'Add to Cart button.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'text'        => array(
							'description' => __( 'Text', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'default'     => __( 'Add to Cart', 'cart-rest-api-for-woocommerce' ),
							'context'     => array( 'view' ),
						),
						'description' => array(
							'description' => __( 'Description', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	} // END get_item_schema()

	/**
	 * Add the schema from additional fields to an schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @access protected
	 * @param  array $schema Schema array.
	 * @return array $schema
	 */
	protected function add_additional_fields_schema( $schema ) {
		if ( empty( $schema['title'] ) ) {
			return $schema;
		}

		/**
		 * Can't use $this->get_object_type otherwise we cause an inf loop.
		 */
		$object_type = $schema['title'];

		$additional_fields = $this->get_additional_fields( $object_type );

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['schema'] ) {
				continue;
			}

			$schema['properties'][ $field_name ] = $field_options['schema'];
		}

		$schema['properties'] = apply_filters( 'cocart_' . $object_type . '_schema', $schema['properties'] );

		return $schema;
	} // END add_additional_fields_schema()

	/**
	 * Get the query params for collections of products.
	 *
	 * @access public
	 * @return array $params
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['slug']               = array(
			'description'       => __( 'Limit result set to products with a specific slug.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['type']               = array(
			'description'       => __( 'Limit result set to products assigned a specific type.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['sku']                = array(
			'description'       => __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['hide_free']          = array(
			'description'       => __( 'Limit result set to hide free products.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['featured']           = array(
			'description'       => __( 'Limit result set to featured products.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['category']           = array(
			'description'       => __( 'Limit result set to products assigned a specific category ID or slug.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['category_operator']  = array(
			'description'       => __( 'Operator to compare product category terms.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'not in', 'and' ),
			'default'           => 'in',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['tag']                = array(
			'description'       => __( 'Limit result set to products assigned a specific tag ID or slug.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['tag_operator']       = array(
			'description'       => __( 'Operator to compare product tags.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'not in', 'and' ),
			'default'           => 'in',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['stock_status']       = array(
			'description'       => __( 'Limit result set to products with specified stock status.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array_keys( wc_get_product_stock_status_options() ),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['on_sale']            = array(
			'description'       => __( 'Limit result set to products on sale.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['min_price']          = array(
			'description'       => __( 'Limit result set to products based on a minimum price.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['max_price']          = array(
			'description'       => __( 'Limit result set to products based on a maximum price.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['show_reviews']       = array(
			'description'       => __( 'Returns product reviews for all products or an individual product.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['return_variations']  = array(
			'description'       => __( 'Returns all variations for variable products.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['attributes']         = array(
			'description' => __( 'Limit result set to products with selected global attributes.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'array',
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'attribute' => array(
						'description'       => __( 'Attribute taxonomy name.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'string',
						'sanitize_callback' => 'wc_sanitize_taxonomy_name',
					),
					'term_id'   => array(
						'description'       => __( 'List of attribute term IDs.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'integer',
						),
						'sanitize_callback' => 'wp_parse_id_list',
					),
					'slug'      => array(
						'description'       => __( 'List of attribute slug(s). If a term ID is provided, this will be ignored.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
						),
						'sanitize_callback' => 'wp_parse_slug_list',
					),
					'operator'  => array(
						'description' => __( 'Operator to compare product attribute terms.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'enum'        => array( 'in', 'not in', 'and' ),
					),
				),
			),
			'default'     => array(),
		);
		$params['attribute_relation'] = array(
			'description'       => __( 'The logical relationship between attributes when filtering across multiple at once.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'and' ),
			'default'           => 'and',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['catalog_visibility'] = array(
			'description'       => __( 'Determines if hidden or visible catalog products are shown.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array( 'any', 'visible', 'catalog', 'search', 'hidden' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['rating']             = array(
			'description'       => __( 'Limit result set to products with a certain average rating.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
				'enum' => range( 1, 5 ),
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['orderby']            = array(
			'description'       => __( 'Sort collection by object attribute.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'enum'              => array(
				'date',
				'id',
				'menu_order',
				'include',
				'title',
				'slug',
				'name',
				'popularity',
				'alphabetical',
				'reverse_alpha',
				'by_stock',
				'review_count',
				'on_sale_first',
				'featured_first',
				'price_asc',
				'price_desc',
				'sales',
				'rating',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	} // END get_collection_params()

} // END class
