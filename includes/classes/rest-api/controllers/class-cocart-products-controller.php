<?php
/**
 * REST API: Abstract Products controller.
 *
 * Provides shared query and retrieval methods for all product endpoints.
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
 * Abstract REST API Products controller class.
 *
 * NOTE THAT ONLY CODE RELEVANT FOR THE PRODUCTS ENDPOINTS SHOULD BE INCLUDED INTO THIS CLASS.
 *
 * @extends WP_REST_Controller
 */
abstract class CoCart_REST_Products_Controller extends WP_REST_Controller {

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
	 * Get post types.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_post_types() {
		return array( 'product', 'product_variation' );
	} // END get_post_types()

	/**
	 * Get object.
	 *
	 * @access protected
	 *
	 * @param int $id Object ID.
	 *
	 * @return WC_Product The product object.
	 */
	protected function get_object( $id ) {
		return wc_get_product( $id );
	} // END get_object()

	/**
	 * Get objects.
	 *
	 * @access protected
	 *
	 * @param array $query_args Query args.
	 *
	 * @return array|WP_Error
	 */
	protected function get_objects( $query_args ) {
		// Hook custom SQL clauses for performant filtering via wc_product_meta_lookup.
		add_filter( 'posts_clauses', array( $this, 'add_query_clauses' ), 10, 2 );

		$query       = new WP_Query();
		$result      = $query->query( $query_args );
		$page        = (int) $query_args['paged'];
		$total_posts = $query->found_posts;

		if ( $total_posts < 1 && $page > 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		remove_filter( 'posts_clauses', array( $this, 'add_query_clauses' ), 10 );

		$max_pages = (int) ceil( $total_posts / (int) $query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new \WP_Error(
				'cocart_products_invalid_page_number',
				__( 'The page number requested is larger than the number of products available.', 'cocart-core' ),
				array( 'status' => 400 )
			);
		}

		// Prime post caches before converting IDs to product objects.
		if ( is_callable( '_prime_post_caches' ) ) {
			_prime_post_caches( $result );
		}

		return array(
			'objects' => array_map( array( $this, 'get_object' ), $result ),
			'total'   => (int) $total_posts,
			'paged'   => $page,
			'pages'   => $max_pages,
		);
	} // END get_objects()

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
		$args = array(
			'offset'              => $request['offset'],
			'order'               => strtoupper( $request['order'] ),
			'orderby'             => strtolower( $request['orderby'] ),
			'paged'               => $request['page'],
			'post__in'            => $request['include'],
			'post__not_in'        => $request['exclude'], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			'posts_per_page'      => $request['per_page'],
			'post_parent__in'     => $request['parent'],
			'post_parent__not_in' => $request['parent_exclude'],
			'search'              => $request['search'],
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
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'reverse_alpha':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
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
			case 'review_count':
			case 'on_sale_first':
			case 'featured_first':
			case 'price_asc':
			case 'price_desc':
			case 'sales':
			case 'popularity':
			case 'rating':
				// Ordering handled by add_query_clauses() using wc_product_meta_lookup indexed columns.
				break;
		}

		// Taxonomy query to filter products by type, category, tag and attribute.
		$tax_query = array();

		$terms = array();

		// Filter product types to include.
		if ( ! empty( $request['include_types'] ) ) {
			$terms = $request['include_types'];
		} elseif ( ! empty( $request['type'] ) ) {
			// Filter by a single product type.
			if ( 'variation' === $request['type'] ) {
				$args['post_type'] = 'product_variation';
			} else {
				$terms = $request['type'];

				$tax_query[] = array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $terms,
				);
			}
		}

		// Filter product types to exclude.
		if ( ! empty( $request['exclude_types'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $request['exclude_types'],
				'operator' => 'NOT IN',
			);
		}

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		// Set date query column.
		if ( ! empty( $request['after'] ) || ! empty( $request['before'] ) ) {
			$args['date_query'][0]['column'] = 'post_date';
		}

		$operator_mapping = array(
			'in'     => 'IN',
			'not_in' => 'NOT IN',
			'and'    => 'AND',
		);

		// Gets all registered product taxonomies and prefixes them with `tax_`.
		// This is needed to avoid situations where a user registers a new product taxonomy with the same name as default field.
		// eg an `sku` taxonomy will be mapped to `tax_sku`.
		$all_product_taxonomies = array_map(
			function ( $value ) {
				return '_unstable_tax_' . $value;
			},
			get_taxonomies( array( 'object_type' => array( 'product' ) ), 'names' )
		);

		// Map between taxonomy name and arg key.
		$default_taxonomies = array(
			'product_cat'   => 'category',
			'product_tag'   => 'tag',
			'product_brand' => 'brand',
		);

		$taxonomies = array_merge( $all_product_taxonomies, $default_taxonomies );

		// Set tax_query for each passed arg.
		foreach ( $taxonomies as $taxonomy => $key ) {
			if ( ! empty( $request[ $key ] ) ) {
				$type        = is_numeric( $request[ $key ][0] ) ? 'term_id' : 'slug';
				$operator    = $request[ $key . '_operator' ] && isset( $operator_mapping[ $request[ $key . '_operator' ] ] ) ? $operator_mapping[ $request[ $key . '_operator' ] ] : 'IN';
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => $type,
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
		if ( ! empty( $tax_query ) && 'product_variation' !== $args['post_type'] ) {
			if ( ! empty( $args['tax_query'] ) ) {
				$args['tax_query'] = array_merge( $tax_query, $args['tax_query'] ); // phpcs:ignore
			} else {
				$args['tax_query'] = $tax_query; // phpcs:ignore
			}
		} else {
			// For product_variations we need to convert the tax_query to a meta_query.
			if ( ! empty( $args['tax_query'] ) ) {
				$args['meta_query'] = $this->convert_tax_query_to_meta_query( array_merge( $tax_query, $args['tax_query'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			} else {
				$args['meta_query'] = $this->convert_tax_query_to_meta_query( $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}
		}

		// Virtual filter — handled by add_query_clauses() via wc_product_meta_lookup.virtual.
		if ( isset( $request['virtual'] ) ) {
			$args['virtual'] = $request['virtual'];
		}

		// Hide free products — handled by add_query_clauses() via wc_product_meta_lookup.min_price.
		if ( ! empty( $request['hide_free'] ) ) {
			$args['hide_free'] = true;
		}

		// Filter featured — uses cached transient (same pattern as on_sale).
		if ( is_bool( $request['featured'] ) ) {
			$featured_key = $request['featured'] ? 'post__in' : 'post__not_in';
			$featured_ids = wc_get_featured_product_ids();
			$featured_ids = empty( $featured_ids ) ? array( 0 ) : $featured_ids;

			$args[ $featured_key ] = $featured_ids;
		}

		// SKU filter — handled by add_query_clauses() via wc_product_meta_lookup.sku.
		if ( ! empty( $request['sku'] ) ) {
			$args['sku'] = $request['sku'];
		}

		// Global Unique ID filter — handled by add_query_clauses() via wc_product_meta_lookup.global_unique_id.
		if ( ! empty( $request['global_unique_id'] ) ) {
			$args['global_unique_id'] = $request['global_unique_id'];
		}

		// Price filter — handled by add_query_clauses() via wc_product_meta_lookup.min_price/max_price.
		if ( ! empty( $request['min_price'] ) ) {
			$args['min_price'] = $request['min_price'];
		}
		if ( ! empty( $request['max_price'] ) ) {
			$args['max_price'] = $request['max_price'];
		}

		// Stock status filter — handled by add_query_clauses() via wc_product_meta_lookup.stock_status.
		if ( ! empty( $request['stock_status'] ) ) {
			$args['stock_status'] = $request['stock_status'];
		}

		// Filter by on sale products.
		if ( is_bool( $request['on_sale'] ) ) {
			$on_sale_key = $request['on_sale'] ? 'post__in' : 'post__not_in';
			$on_sale_ids = wc_get_product_ids_on_sale();

			// Use 0 when there's no on sale products to avoid return all products.
			$on_sale_ids = empty( $on_sale_ids ) ? array( 0 ) : $on_sale_ids;

			$args[ $on_sale_key ] = $on_sale_ids;
		}

		// Filter by Catalog Visibility.
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

		// Rating filter — handled by add_query_clauses() via wc_product_meta_lookup.average_rating.
		$rating = $request->get_param( 'rating' );

		if ( ! empty( $rating ) ) {
			$args['rating'] = $rating;
		}

		return apply_filters( 'cocart_prepare_objects_query', $args, $request );
	} // END prepare_objects_query()

	/**
	 * Convert the tax_query to a meta_query which is needed to support filtering by attributes for variations.
	 *
	 * @access public
	 *
	 * @since 3.11.0 Introduced.
	 *
	 * @param array $tax_query The tax_query to convert.
	 *
	 * @return array
	 */
	public function convert_tax_query_to_meta_query( $tax_query ) {
		$meta_query = array();

		foreach ( $tax_query as $tax_query_item ) {
			$taxonomy = $tax_query_item['taxonomy'];
			$terms    = $tax_query_item['terms'];

			$meta_key = 'attribute_' . $taxonomy;

			$meta_query[] = array(
				'key'   => $meta_key,
				'value' => $terms,
			);

			if ( isset( $tax_query_item['operator'] ) ) {
				$meta_query[ count( $meta_query ) - 1 ]['compare'] = $tax_query_item['operator'];
			}
		}

		return $meta_query;
	} // END convert_tax_query_to_meta_query()

	/**
	 * Add meta query.
	 *
	 * @access protected
	 *
	 * @since 3.4.1 Introduced.
	 *
	 * @param array $args       Query args.
	 * @param array $meta_query Meta query.
	 *
	 * @return array
	 */
	protected function add_meta_query( $args, $meta_query ) {
		if ( empty( $args['meta_query'] ) ) {
			$args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, WPCS: slow query ok.
		}

		$args['meta_query'][] = $meta_query;

		return $args['meta_query'];
	} // END add_meta_query()

	/**
	 * Append the product sorting table join to the SQL query.
	 *
	 * Uses WooCommerce's wc_product_meta_lookup table for indexed
	 * filtering on SKU, stock status, and price columns.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param string $sql The existing JOIN clause.
	 *
	 * @return string
	 */
	protected function append_product_sorting_table_join( $sql ) {
		global $wpdb;

		if ( ! strstr( $sql, 'wc_product_meta_lookup' ) ) {
			$sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
		}

		return $sql;
	} // END append_product_sorting_table_join()

	/**
	 * Add custom query clauses for product filtering.
	 *
	 * Hooks into posts_clauses to add WHERE/JOIN conditions using the
	 * wc_product_meta_lookup table for performant filtering.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array    $args     Query clauses.
	 * @param WP_Query $wp_query The WP_Query instance.
	 *
	 * @return array Modified query clauses.
	 */
	public function add_query_clauses( $args, $wp_query ) {
		global $wpdb;

		// Enhanced search: match post_title and SKU.
		if ( $wp_query->get( 'search' ) ) {
			$search         = '%' . $wpdb->esc_like( $wp_query->get( 'search' ) ) . '%';
			$search_query   = wc_product_sku_enabled()
				? $wpdb->prepare( " AND ( $wpdb->posts.post_title LIKE %s OR wc_product_meta_lookup.sku LIKE %s ) ", $search, $search )
				: $wpdb->prepare( " AND $wpdb->posts.post_title LIKE %s ", $search );
			$args['where'] .= $search_query;
			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
		}

		// SKU filter via indexed column.
		if ( $wp_query->get( 'sku' ) ) {
			$skus = explode( ',', $wp_query->get( 'sku' ) );

			if ( 1 < count( $skus ) ) {
				$skus[] = $wp_query->get( 'sku' );
			}

			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= ' AND wc_product_meta_lookup.sku IN (\'' . implode( "','", array_map( 'esc_sql', $skus ) ) . '\')';
		}

		// Stock status filter via indexed column.
		if ( $wp_query->get( 'stock_status' ) ) {
			$stock_statuses = (array) $wp_query->get( 'stock_status' );
			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= ' AND wc_product_meta_lookup.stock_status IN (\'' . implode( "','", array_map( 'esc_sql', $stock_statuses ) ) . '\')';
		} elseif ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= " AND wc_product_meta_lookup.stock_status NOT IN ('outofstock')";
		}

		// Price filter via indexed columns with tax awareness.
		if ( $wp_query->get( 'min_price' ) || $wp_query->get( 'max_price' ) ) {
			$args = $this->add_price_filter_clauses( $args, $wp_query );
		}

		// Virtual filter via indexed column.
		if ( $wp_query->get( 'virtual' ) !== '' ) {
			$virtual        = wc_string_to_bool( $wp_query->get( 'virtual' ) ) ? 1 : 0;
			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= $wpdb->prepare( ' AND wc_product_meta_lookup.virtual = %d ', $virtual );
		}

		// Hide free products via indexed column.
		if ( $wp_query->get( 'hide_free' ) ) {
			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= ' AND wc_product_meta_lookup.min_price > 0 ';
		}

		// Global Unique ID filter via lookup table column.
		if ( $wp_query->get( 'global_unique_id' ) ) {
			$global_unique_ids = array_map( 'trim', explode( ',', $wp_query->get( 'global_unique_id' ) ) );
			$args['join']      = $this->append_product_sorting_table_join( $args['join'] );
			$args['where']    .= ' AND wc_product_meta_lookup.global_unique_id IN (\'' . implode( "','", array_map( 'esc_sql', $global_unique_ids ) ) . '\')';
		}

		// Rating filter via lookup table column.
		if ( $wp_query->get( 'rating' ) ) {
			$rating_values  = array_map( 'absint', (array) $wp_query->get( 'rating' ) );
			$args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= ' AND ROUND( wc_product_meta_lookup.average_rating, 0 ) IN (' . implode( ',', $rating_values ) . ')';
		}

		// Ordering via indexed columns.
		$orderby = $wp_query->get( 'orderby' );
		if ( in_array( $orderby, array( 'price_asc', 'price_desc', 'sales', 'popularity', 'rating', 'by_stock', 'review_count', 'on_sale_first', 'featured_first' ), true ) ) {
			if ( 'featured_first' !== $orderby ) {
				$args['join'] = $this->append_product_sorting_table_join( $args['join'] );
			}

			switch ( $orderby ) {
				case 'price_asc':
					$args['orderby'] = ' wc_product_meta_lookup.min_price ASC, wc_product_meta_lookup.product_id ASC ';
					break;
				case 'price_desc':
					$args['orderby'] = ' wc_product_meta_lookup.max_price DESC, wc_product_meta_lookup.product_id DESC ';
					break;
				case 'sales':
				case 'popularity':
					$args['orderby'] = ' wc_product_meta_lookup.total_sales DESC, wc_product_meta_lookup.product_id DESC ';
					break;
				case 'rating':
					$args['orderby'] = ' wc_product_meta_lookup.average_rating DESC, wc_product_meta_lookup.product_id DESC ';
					break;
				case 'by_stock':
					$args['orderby'] = ' wc_product_meta_lookup.stock_quantity DESC, ' . $wpdb->posts . '.post_title ASC ';
					break;
				case 'review_count':
					$args['orderby'] = ' wc_product_meta_lookup.rating_count DESC, ' . $wpdb->posts . '.post_title ASC ';
					break;
				case 'on_sale_first':
					$args['orderby'] = ' wc_product_meta_lookup.onsale DESC, ' . $wpdb->posts . '.post_title ASC ';
					break;
				case 'featured_first':
					$featured_ids = wc_get_featured_product_ids();
					if ( ! empty( $featured_ids ) ) {
						$ids_str         = implode( ',', array_map( 'absint', $featured_ids ) );
						$args['orderby'] = " FIELD( {$wpdb->posts}.ID, {$ids_str} ) DESC, {$wpdb->posts}.post_title ASC ";
					} else {
						$args['orderby'] = " {$wpdb->posts}.post_title ASC ";
					}
					break;
			}
		}

		return $args;
	} // END add_query_clauses()

	/**
	 * Add price filter clauses using the wc_product_meta_lookup table.
	 *
	 * Provides tax-aware price filtering using indexed min_price/max_price columns.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array    $args     Query clauses.
	 * @param WP_Query $wp_query The WP_Query instance.
	 *
	 * @return array Modified query clauses.
	 */
	protected function add_price_filter_clauses( $args, $wp_query ) {
		global $wpdb;

		$adjust_for_taxes = $this->adjust_price_filters_for_displayed_taxes();
		$args['join']     = $this->append_product_sorting_table_join( $args['join'] );

		if ( $wp_query->get( 'min_price' ) ) {
			$min_price = floatval( $wp_query->get( 'min_price' ) );

			if ( $adjust_for_taxes ) {
				$args['where'] .= $this->get_price_filter_query_for_displayed_taxes( $min_price, 'max_price', '>=' );
			} else {
				$args['where'] .= $wpdb->prepare( ' AND wc_product_meta_lookup.max_price >= %f ', $min_price );
			}
		}

		if ( $wp_query->get( 'max_price' ) ) {
			$max_price = floatval( $wp_query->get( 'max_price' ) );

			if ( $adjust_for_taxes ) {
				$args['where'] .= $this->get_price_filter_query_for_displayed_taxes( $max_price, 'min_price', '<=' );
			} else {
				$args['where'] .= $wpdb->prepare( ' AND wc_product_meta_lookup.min_price <= %f ', $max_price );
			}
		}

		return $args;
	} // END add_price_filter_clauses()

	/**
	 * Determine if price filters need adjustment for displayed taxes.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return bool
	 */
	protected function adjust_price_filters_for_displayed_taxes() {
		$dominated_tax = wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax();

		return $dominated_tax;
	} // END adjust_price_filters_for_displayed_taxes()

	/**
	 * Generate a price filter query that accounts for displayed taxes.
	 *
	 * Creates an OR clause that checks each tax class's adjusted price.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param float  $price_filter The price to filter by.
	 * @param string $column       The column to compare (min_price or max_price).
	 * @param string $operator     The comparison operator (>= or <=).
	 *
	 * @return string SQL clause.
	 */
	protected function get_price_filter_query_for_displayed_taxes( $price_filter, $column, $operator ) {
		global $wpdb;

		$tax_classes = array_merge( array( '' ), \WC_Tax::get_tax_classes() );
		$or_queries  = array();

		foreach ( $tax_classes as $tax_class ) {
			$adjusted_price = $this->adjust_price_filter_for_tax_class( $price_filter, $tax_class );
			$or_queries[]   = $wpdb->prepare(
				"( wc_product_meta_lookup.tax_class = %s AND wc_product_meta_lookup.{$column} {$operator} %f )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$tax_class,
				$adjusted_price
			);
		}

		// Include products with no tax class set (standard rate).
		$adjusted_price = $this->adjust_price_filter_for_tax_class( $price_filter, '' );
		$or_queries[]   = $wpdb->prepare(
			"( wc_product_meta_lookup.tax_class IS NULL AND wc_product_meta_lookup.{$column} {$operator} %f )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$adjusted_price
		);

		return ' AND (' . implode( ' OR ', $or_queries ) . ')';
	} // END get_price_filter_query_for_displayed_taxes()

	/**
	 * Adjust a price filter value for a specific tax class.
	 *
	 * Removes the tax component from the price if taxes are displayed inclusive.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param float  $price     The price to adjust.
	 * @param string $tax_class The tax class.
	 *
	 * @return float Adjusted price.
	 */
	protected function adjust_price_filter_for_tax_class( $price, $tax_class ) {
		$tax_rates = \WC_Tax::get_rates( $tax_class );

		if ( $tax_rates ) {
			$taxes      = \WC_Tax::calc_exclusive_tax( $price, $tax_rates );
			$tax_amount = array_sum( $taxes );
			return $price - $tax_amount;
		}

		return $price;
	} // END adjust_price_filter_for_tax_class()

	/**
	 * Get a single item.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response The response, or an error.
	 */
	public function get_item( $request ) {
		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object || 0 === $object->get_id() || 'publish' !== $object->get_status() ) {
			return new \WP_Error( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		$data     = $this->prepare_object_for_response( $object, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // END get_item()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array Links for the given product.
	 */
	protected function prepare_links( $product ) {
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
	 * Get the reviews for a product.
	 *
	 * @access protected
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
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
	 *
	 * @param string     $slug    Taxonomy name.
	 * @param WC_Product $product The product object.
	 *
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
	 *
	 * @param WC_Product $product The product object.
	 *
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
	 * Get the query params for collections of products.
	 *
	 * @access public
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['slug']               = array(
			'description'       => __( 'Limit result set to products with a specific slug.', 'cocart-core' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['after']              = array(
			'description'       => __( 'Limit response to products created after a given ISO8601 compliant date.', 'cocart-core' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['before']             = array(
			'description'       => __( 'Limit response to products created before a given ISO8601 compliant date.', 'cocart-core' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['exclude_types']      = array(
			'description'       => __( 'Exclude products with any of the types from result set.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'string',
				'enum' => array_keys( wc_get_product_types() ),
			),
			'sanitize_callback' => 'wp_parse_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['include_types']      = array(
			'description'       => __( 'Limit result set to products with any of the types.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'string',
				'enum' => array_keys( wc_get_product_types() ),
			),
			'sanitize_callback' => 'wp_parse_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['type']               = array(
			'description'       => __( 'Limit result set to products assigned a specific type.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['sku']                = array(
			'description'       => __( 'Limit result set to products with specific SKU(s). Use commas to separate.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['global_unique_id']   = array(
			'description'       => __( 'Limit result set to products with a specific Global Unique ID (GTIN, UPC, EAN, or ISBN).', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['virtual']            = array(
			'description'       => __( 'Limit result set to virtual products.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['hide_free']          = array(
			'description'       => __( 'Limit result set to hide free products.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['featured']           = array(
			'description'       => __( 'Limit result set to featured products.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['category']           = array(
			'description'       => __( 'Limit result set to products assigned a set of category IDs or slugs, separated by commas.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'wp_parse_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['category_operator']  = array(
			'description'       => __( 'Operator to compare product category terms.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'not in', 'and' ),
			'default'           => 'in',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['tag']                = array(
			'description'       => __( 'Limit result set to products assigned a set of tag IDs or slugs, separated by commas.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'wp_parse_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['tag_operator']       = array(
			'description'       => __( 'Operator to compare product tags.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'not in', 'and' ),
			'default'           => 'in',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['brand']              = array(
			'description'       => __( 'Limit result set to products assigned a set of brand IDs or slugs, separated by commas.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'wp_parse_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['brand_operator']     = array(
			'description'       => __( 'Operator to compare product brand terms.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'not_in', 'and' ),
			'default'           => 'in',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['stock_status']       = array(
			'description'       => __( 'Limit result set to products with specified stock status.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array_keys( wc_get_product_stock_status_options() ),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['on_sale']            = array(
			'description'       => __( 'Limit result set to products on sale.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['min_price']          = array(
			'description'       => __( 'Limit result set to products based on a minimum price.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['max_price']          = array(
			'description'       => __( 'Limit result set to products based on a maximum price.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['show_reviews']       = array(
			'description'       => __( 'Returns product reviews for all products or an individual product.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['return_variations']  = array(
			'description'       => __( 'Returns all variations for variable products.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'wc_string_to_bool',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['attributes']         = array(
			'description' => __( 'Limit result set to products with selected global attributes.', 'cocart-core' ),
			'type'        => 'array',
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'attribute' => array(
						'description'       => __( 'Attribute taxonomy name.', 'cocart-core' ),
						'type'              => 'string',
						'sanitize_callback' => 'wc_sanitize_taxonomy_name',
					),
					'term_id'   => array(
						'description'       => __( 'List of attribute term IDs.', 'cocart-core' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'integer',
						),
						'sanitize_callback' => 'wp_parse_id_list',
					),
					'slug'      => array(
						'description'       => __( 'List of attribute slug(s). If a term ID is provided, this will be ignored.', 'cocart-core' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
						),
						'sanitize_callback' => 'wp_parse_slug_list',
					),
					'operator'  => array(
						'description' => __( 'Operator to compare product attribute terms.', 'cocart-core' ),
						'type'        => 'string',
						'enum'        => array( 'in', 'not in', 'and' ),
					),
				),
			),
			'default'     => array(),
		);
		$params['attribute_relation'] = array(
			'description'       => __( 'The logical relationship between attributes when filtering across multiple at once.', 'cocart-core' ),
			'type'              => 'string',
			'enum'              => array( 'in', 'and' ),
			'default'           => 'and',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['catalog_visibility'] = array(
			'description'       => __( 'Determines if hidden or visible catalog products are shown.', 'cocart-core' ),
			'type'              => 'string',
			'default'           => 'visible',
			'enum'              => array( 'any', 'visible', 'catalog', 'search', 'hidden' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['rating']             = array(
			'description'       => __( 'Limit result set to products with a certain average rating.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
				'enum' => range( 1, 5 ),
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['offset']             = array(
			'description' => __( 'Offset the result set by a specific number of items.', 'cocart-core' ),
			'type'        => 'integer',
		);
		$params['order']              = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'cocart-core' ),
			'type'              => 'string',
			'default'           => 'DESC',
			'enum'              => array( 'ASC', 'DESC' ),
			'sanitize_callback' => 'sanitize_text_field',
		);
		$params['orderby']            = array(
			'description'       => __( 'Sort collection by product attribute.', 'cocart-core' ),
			'type'              => 'string',
			'default'           => get_option( 'woocommerce_default_catalog_orderby', 'date' ),
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
				'relevance',
				'rand',
			),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['exclude']            = array( // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'description'       => __( 'Ensure result set excludes specific IDs.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['include']            = array(
			'description'       => __( 'Limit result set to specific IDs.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['parent']             = array(
			'description'       => __( 'Limit result set to products with particular parent IDs.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['parent_exclude']     = array(
			'description'       => __( 'Limit result set to all products except those of a particular parent ID.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		return $params;
	} // END get_collection_params()

	/**
	 * Add the schema from additional fields to an schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @access protected
	 *
	 * @param array $schema Schema array.
	 *
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

		$schema['properties'] = apply_filters( "cocart_{$object_type}_schema", $schema['properties'] ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		return $schema;
	} // END add_additional_fields_schema()
} // END class
