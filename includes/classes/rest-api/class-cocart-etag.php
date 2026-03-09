<?php
/**
 * REST API: CoCart_ETag class.
 *
 * Handles ETag generation and validation for conditional requests.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   4.9.0 Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles ETag support for CoCart REST API.
 *
 * @since 4.9.0 Introduced.
 */
class CoCart_ETag {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		// Check that we are only requesting for our API.
		if ( ! CoCart::is_rest_api_request() ) {
			return;
		}

		// Check If-None-Match early before cart loads.
		add_filter( 'rest_pre_dispatch', array( $this, 'check_conditional_request' ), 5, 3 );

		// Add ETag header to responses.
		add_filter( 'rest_pre_serve_request', array( $this, 'add_etag_header' ), 5, 4 );

		// Expose ETag in CORS headers.
		add_filter( 'rest_exposed_cors_headers', array( $this, 'expose_etag_header' ) );
		add_filter( 'rest_allowed_cors_headers', array( $this, 'allow_if_none_match_header' ) );
	}

	/**
	 * Get cart routes that support ETag.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array Regex patterns for cart routes.
	 */
	protected function get_cart_etag_routes() {
		$cart_routes = array(
			'/^cocart\/v2\/cart$/',
			'/^cocart\/v2\/cart\/items$/',
			'/^cocart\/v2\/cart\/items\/count$/',
			'/^cocart\/v2\/cart\/totals$/',
			'/^cocart\/v2\/cart\/add-item$/',
			'/^cocart\/v2\/cart\/add-items$/',
			'/^cocart\/v2\/cart\/item/',
			'/^cocart\/v2\/cart\/update$/',
			'/^cocart\/v2\/cart\/clear$/',
			'/^cocart\/v2\/cart\/calculate$/',
		);

		/**
		 * Filter additional cart routes to support ETag.
		 *
		 * @since 4.9.0 Introduced.
		 *
		 * @param array $routes Array of regex patterns for routes.
		 */
		$additional_routes = apply_filters( 'cocart_etag_cart_routes', array() );

		return array_merge( $cart_routes, $additional_routes );
	} // END get_cart_etag_routes()

	/**
	 * Get product routes that support ETag.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array Regex patterns for product routes.
	 */
	protected function get_product_etag_routes() {
		$product_routes = array(
			'/^cocart\/v2\/products$/',
			'/^cocart\/v2\/products\/\d+$/',                         // Products by ID.
			'/^cocart\/v2\/products\/\d+\/variations$/',             // Product variations collection.
			'/^cocart\/v2\/products\/\d+\/variations\/\d+$/',        // Single product variation.
			'/^cocart\/v2\/products\/categories$/',                  // Product categories collection.
			'/^cocart\/v2\/products\/categories\/\d+$/',             // Single product category.
			'/^cocart\/v2\/products\/tags$/',                        // Product tags collection.
			'/^cocart\/v2\/products\/tags\/\d+$/',                   // Single product tag.
			'/^cocart\/v2\/products\/attributes$/',                  // Product attributes collection.
			'/^cocart\/v2\/products\/attributes\/\d+$/',             // Single product attribute.
			'/^cocart\/v2\/products\/attributes\/\d+\/terms$/',      // Product attribute terms collection.
			'/^cocart\/v2\/products\/attributes\/\d+\/terms\/\d+$/', // Single product attribute term.
			'/^cocart\/v2\/products\/reviews$/',                     // Product reviews collection.
			'/^cocart\/v2\/products\/reviews\/\d+$/',                // Single product review.
		);

		/**
		* Filter additional product routes to support ETag.
		*
		* @since 4.9.0 Introduced.
		*
		* @param array $routes Array of regex patterns for routes.
		*/
		$additional_routes = apply_filters( 'cocart_etag_product_routes', array() );

		return array_merge( $product_routes, $additional_routes );
	} // END get_product_etag_routes()

	/**
	 * Get all routes that support ETag.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array Regex patterns for all routes.
	 */
	protected function get_etag_routes() {
		$routes = array_merge(
			$this->get_cart_etag_routes(),
			$this->get_product_etag_routes()
		);

		/**
		 * Filter additional routes that support ETag.
		 *
		 * @since 4.9.0 Introduced.
		 *
		 * @param array $routes Array of regex patterns for routes.
		 */
		return apply_filters( 'cocart_etag_routes', $routes );
	} // END get_etag_routes()

	/**
	 * Check if route supports ETag.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $route The request route.
	 *
	 * @return bool True if route supports ETag.
	 */
	protected function route_supports_etag( $route ) {
		$route = ltrim( $route, '/' );

		foreach ( $this->get_etag_routes() as $pattern ) {
			if ( preg_match( $pattern, $route ) ) {
				return true;
			}
		}

		return false;
	} // END route_supports_etag()

	/**
	 * Check if route is a cart route.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $route The request route.
	 *
	 * @return bool True if route is a cart route.
	 */
	protected function is_cart_route( $route ) {
		$route = ltrim( $route, '/' );

		foreach ( $this->get_cart_etag_routes() as $pattern ) {
			if ( preg_match( $pattern, $route ) ) {
				return true;
			}
		}

		return false;
	} // END is_cart_route()

	/**
	 * Check if route is a product route.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $route The request route.
	 *
	 * @return bool True if route is a product route.
	 */
	protected function is_product_route( $route ) {
		$route = ltrim( $route, '/' );

		foreach ( $this->get_product_etag_routes() as $pattern ) {
			if ( preg_match( $pattern, $route ) ) {
				return true;
			}
		}

		return false;
	} // END is_product_route()

	/**
	 * Get product ID from route.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $route The request route.
	 *
	 * @return int|null Product ID or null if not a single product route.
	 */
	protected function get_product_id_from_route( $route ) {
		$route = ltrim( $route, '/' );

		if ( preg_match( '/^cocart\/v2\/products\/(\d+)$/', $route, $matches ) ) {
			return (int) $matches[1];
		}

		return null;
	} // END get_product_id_from_route()

	/**
	 * Get ETag hash for a single product.
	 *
	 * Uses wp_wc_product_meta_lookup table for efficient single-query access
	 * to product data including price, stock, and stock status.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return string|null Product ETag hash or null if not found.
	 */
	protected function get_single_product_etag_hash( $product_id ) {
		global $wpdb;

		$lookup_table = $wpdb->prefix . 'wc_product_meta_lookup';

		// Get product data from posts and lookup table in a single query.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$product_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT p.post_modified, l.min_price, l.max_price, l.stock_quantity, l.stock_status, l.onsale
				FROM {$wpdb->posts} p
				LEFT JOIN %i l ON p.ID = l.product_id
				WHERE p.ID = %d AND p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'",
				$lookup_table,
				$product_id
			)
		);

		if ( empty( $product_data ) || empty( $product_data->post_modified ) ) {
			return null;
		}

		// Build hash from all relevant product data.
		$hash_data = sprintf(
			'cocart_product_%d_%s_%s_%s_%s_%s_%s%s',
			$product_id,
			$product_data->post_modified,
			$product_data->min_price ?? '',
			$product_data->max_price ?? '',
			$product_data->stock_quantity ?? '',
			$product_data->stock_status ?? '',
			$product_data->onsale ?? '',
			COCART_VERSION
		);

		return md5( $hash_data );
	} // END get_single_product_etag_hash()

	/**
	 * Get ETag hash for product collection based on query parameters.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return string|null Collection ETag hash or null if no products.
	 */
	protected function get_product_collection_etag_hash( $request ) {
		global $wpdb;

		// Get the most recently modified product.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_modified = $wpdb->get_var(
			"SELECT MAX(post_modified) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'"
		);

		if ( empty( $latest_modified ) ) {
			return null;
		}

		// Get total published products count for added uniqueness.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$product_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'"
		);

		// Include relevant query parameters in hash for cache variation.
		$query_params = array(
			'page'     => $request->get_param( 'page' ) ?? 1,
			'per_page' => $request->get_param( 'per_page' ) ?? 10,
			'orderby'  => $request->get_param( 'orderby' ) ?? 'date',
			'order'    => strtoupper( $request->get_param( 'order' ) ?? 'DESC' ),
			'category' => $request->get_param( 'category' ) ?? '',
			'tag'      => $request->get_param( 'tag' ) ?? '',
			'search'   => $request->get_param( 'search' ) ?? '',
		);

		$params_hash = md5( wp_json_encode( $query_params ) );

		return md5( 'cocart_products_' . $latest_modified . '_' . $product_count . '_' . $params_hash . COCART_VERSION );
	} // END get_product_collection_etag_hash()

	/**
	 * Get ETag hash for taxonomy (categories, tags, attributes).
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string          $taxonomy The taxonomy name.
	 * @param int|null        $term_id  Optional term ID for single term.
	 * @param WP_REST_Request $request  The request object.
	 *
	 * @return string|null Taxonomy ETag hash or null.
	 */
	protected function get_taxonomy_etag_hash( $taxonomy, $term_id, $request ) {
		global $wpdb;

		if ( $term_id ) {
			// Single term - get term info.
			$term = get_term( $term_id, $taxonomy );

			if ( ! $term || is_wp_error( $term ) ) {
				return null;
			}

			// Include term count for cache invalidation when products change.
			return md5( 'cocart_term_' . $term_id . '_' . $term->count . COCART_VERSION );
		}

		// Collection - get all terms count and latest term.
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'number'     => 1,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
			)
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		$total_terms = wp_count_terms( array( 'taxonomy' => $taxonomy ) );
		$latest_term = $terms[0];

		// Include query parameters.
		$query_params = array(
			'page'     => $request->get_param( 'page' ) ?? 1,
			'per_page' => $request->get_param( 'per_page' ) ?? 10,
		);

		$params_hash = md5( wp_json_encode( $query_params ) );

		return md5( 'cocart_taxonomy_' . $taxonomy . '_' . $total_terms . '_' . $latest_term->term_id . '_' . $params_hash . COCART_VERSION );
	} // END get_taxonomy_etag_hash()

	/**
	 * Get ETag hash for product variations collection.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param int             $product_id The parent product ID.
	 * @param WP_REST_Request $request    The request object.
	 *
	 * @return string|null Variations ETag hash or null.
	 */
	protected function get_product_variations_etag_hash( $product_id, $request ) {
		global $wpdb;

		// Get latest variation modification date for the parent product.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_modified = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(post_modified) FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'product_variation' AND post_status = 'publish'",
				$product_id
			)
		);

		if ( empty( $latest_modified ) ) {
			return null;
		}

		// Get variation count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$variation_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'product_variation' AND post_status = 'publish'",
				$product_id
			)
		);

		// Include query parameters.
		$query_params = array(
			'page'     => $request->get_param( 'page' ) ?? 1,
			'per_page' => $request->get_param( 'per_page' ) ?? 10,
		);

		$params_hash = md5( wp_json_encode( $query_params ) );

		return md5( 'cocart_variations_' . $product_id . '_' . $latest_modified . '_' . $variation_count . '_' . $params_hash . COCART_VERSION );
	} // END get_product_variations_etag_hash()

	/**
	 * Get ETag hash for current user's reviews.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return string|null User reviews ETag hash or null.
	 */
	protected function get_user_reviews_etag_hash( $request ) {
		global $wpdb;

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return null;
		}

		// Get latest review for this user.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_review = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(comment_date) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = '1' AND user_id = %d",
				$user_id
			)
		);

		// Get review count for this user.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$review_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = '1' AND user_id = %d",
				$user_id
			)
		);

		if ( empty( $latest_review ) ) {
			return null;
		}

		// Include query parameters.
		$query_params = array(
			'page'     => $request->get_param( 'page' ) ?? 1,
			'per_page' => $request->get_param( 'per_page' ) ?? 10,
		);

		$params_hash = md5( wp_json_encode( $query_params ) );

		return md5( 'cocart_user_reviews_' . $user_id . '_' . $latest_review . '_' . $review_count . '_' . $params_hash . COCART_VERSION );
	} // END get_user_reviews_etag_hash()

	/**
	 * Get ETag hash for product reviews.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param int|null        $review_id Optional review ID for single review.
	 * @param WP_REST_Request $request   The request object.
	 *
	 * @return string|null Review ETag hash or null.
	 */
	protected function get_review_etag_hash( $review_id, $request ) {
		global $wpdb;

		if ( $review_id ) {
			// Single review.
			$comment = get_comment( $review_id );

			if ( ! $comment ) {
				return null;
			}

			return md5( 'cocart_review_' . $review_id . '_' . $comment->comment_date . COCART_VERSION );
		}

		// Collection - get latest approved review.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_review = $wpdb->get_var(
			"SELECT MAX(comment_date) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = '1'"
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$review_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = '1'"
		);

		if ( empty( $latest_review ) ) {
			return null;
		}

		// Include query parameters.
		$query_params = array(
			'page'     => $request->get_param( 'page' ) ?? 1,
			'per_page' => $request->get_param( 'per_page' ) ?? 10,
			'product'  => $request->get_param( 'product' ) ?? '',
		);

		$params_hash = md5( wp_json_encode( $query_params ) );

		return md5( 'cocart_reviews_' . $latest_review . '_' . $review_count . '_' . $params_hash . COCART_VERSION );
	} // END get_review_etag_hash()

	/**
	 * Get product ETag hash based on route and request.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string          $route   The request route.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return string|null ETag hash or null.
	 */
	protected function get_product_etag_hash( $route, $request ) {
		$route = ltrim( $route, '/' );

		// Single product variation - must check before single product ID.
		if ( preg_match( '/^cocart\/v2\/products\/(\d+)\/variations\/(\d+)$/', $route, $matches ) ) {
			return $this->get_single_product_etag_hash( (int) $matches[2] );
		}

		// Product variations collection.
		if ( preg_match( '/^cocart\/v2\/products\/(\d+)\/variations$/', $route, $matches ) ) {
			return $this->get_product_variations_etag_hash( (int) $matches[1], $request );
		}

		// Single product by ID.
		if ( preg_match( '/^cocart\/v2\/products\/(\d+)$/', $route, $matches ) ) {
			return $this->get_single_product_etag_hash( (int) $matches[1] );
		}

		// Product collection.
		if ( preg_match( '/^cocart\/v2\/products$/', $route ) ) {
			return $this->get_product_collection_etag_hash( $request );
		}

		// Categories.
		if ( preg_match( '/^cocart\/v2\/products\/categories\/(\d+)$/', $route, $matches ) ) {
			return $this->get_taxonomy_etag_hash( 'product_cat', (int) $matches[1], $request );
		}
		if ( preg_match( '/^cocart\/v2\/products\/categories$/', $route ) ) {
			return $this->get_taxonomy_etag_hash( 'product_cat', null, $request );
		}

		// Tags.
		if ( preg_match( '/^cocart\/v2\/products\/tags\/(\d+)$/', $route, $matches ) ) {
			return $this->get_taxonomy_etag_hash( 'product_tag', (int) $matches[1], $request );
		}
		if ( preg_match( '/^cocart\/v2\/products\/tags$/', $route ) ) {
			return $this->get_taxonomy_etag_hash( 'product_tag', null, $request );
		}

		// Attribute terms - must check before single attribute.
		if ( preg_match( '/^cocart\/v2\/products\/attributes\/(\d+)\/terms\/(\d+)$/', $route, $matches ) ) {
			$attribute_id = (int) $matches[1];
			$term_id      = (int) $matches[2];
			$attribute    = wc_get_attribute( $attribute_id );
			if ( $attribute ) {
				return $this->get_taxonomy_etag_hash( $attribute->slug, $term_id, $request );
			}
			return null;
		}
		if ( preg_match( '/^cocart\/v2\/products\/attributes\/(\d+)\/terms$/', $route, $matches ) ) {
			$attribute_id = (int) $matches[1];
			$attribute    = wc_get_attribute( $attribute_id );
			if ( $attribute ) {
				return $this->get_taxonomy_etag_hash( $attribute->slug, null, $request );
			}
			return null;
		}

		// Attributes.
		if ( preg_match( '/^cocart\/v2\/products\/attributes\/(\d+)$/', $route, $matches ) ) {
			$attribute_id = (int) $matches[1];
			$attribute    = wc_get_attribute( $attribute_id );
			if ( $attribute ) {
				return md5( 'cocart_attribute_' . $attribute_id . '_' . $attribute->name . COCART_VERSION );
			}
			return null;
		}
		if ( preg_match( '/^cocart\/v2\/products\/attributes$/', $route ) ) {
			// For attribute taxonomies, use wc_get_attribute_taxonomies.
			$attributes      = wc_get_attribute_taxonomies();
			$attribute_count = count( $attributes );

			if ( empty( $attributes ) ) {
				return null;
			}

			return md5( 'cocart_attributes_' . $attribute_count . COCART_VERSION );
		}

		// Reviews.
		if ( preg_match( '/^cocart\/v2\/products\/reviews\/(\d+)$/', $route, $matches ) ) {
			return $this->get_review_etag_hash( (int) $matches[1], $request );
		}
		if ( preg_match( '/^cocart\/v2\/products\/reviews$/', $route ) ) {
			return $this->get_review_etag_hash( null, $request );
		}

		return null;
	} // END get_product_etag_hash()

	/**
	 * Format ETag value as weak ETag from cart hash.
	 *
	 * The ETag is generated by hashing the cart hash with a salt to make it
	 * opaque and distinct from the cart_hash value returned in the response body.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $cart_hash The cart hash.
	 *
	 * @return string Formatted ETag or empty string.
	 */
	protected function format_cart_etag( $cart_hash ) {
		if ( empty( $cart_hash ) ) {
			return '';
		}

		// Generate opaque ETag by hashing cart hash with salt.
		$etag_hash = md5( 'cocart_etag_' . $cart_hash . COCART_VERSION );

		return 'W/"' . $etag_hash . '"';
	} // END format_cart_etag()

	/**
	 * Format pre-computed hash as weak ETag.
	 *
	 * Used for product ETags where the hash is already computed.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $hash The pre-computed hash.
	 *
	 * @return string Formatted ETag or empty string.
	 */
	protected function format_etag( $hash ) {
		if ( empty( $hash ) ) {
			return '';
		}

		return 'W/"' . $hash . '"';
	} // END format_etag()

	/**
	 * Parse If-None-Match header value.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array Array of ETag values from the header.
	 */
	protected function get_if_none_match_values() {
		$header = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) : '';

		if ( empty( $header ) ) {
			return array();
		}

		// Handle multiple ETags: "etag1", "etag2", W/"etag3".
		$etags = array_map( 'trim', explode( ',', $header ) );

		// Extract hash values from ETags (remove W/ prefix and quotes).
		$values = array();
		foreach ( $etags as $etag ) {
			// Match both weak (W/"hash") and strong ("hash") ETags.
			if ( preg_match( '/^(?:W\/)?"([^"]+)"$/', $etag, $matches ) ) {
				$values[] = $matches[1];
			}
		}

		return $values;
	} // END get_if_none_match_values()

	/**
	 * Get cart hash from database without loading cart.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @return string|null Cart hash or null if not found.
	 */
	protected function get_cart_hash_from_db( $cart_key ) {
		global $wpdb;

		if ( empty( $cart_key ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'cocart_carts';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$hash = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT cart_hash FROM %i WHERE cart_key = %s',
				$table,
				$cart_key
			)
		);

		return $hash;
	} // END get_cart_hash_from_db()

	/**
	 * Check for conditional request and return 304 if appropriate.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return mixed Original result or WP_REST_Response with 304 status.
	 */
	public function check_conditional_request( $result, $server, $request ) {
		// Skip cache if requested.
		if ( $this->should_skip_cache( $request ) ) {
			return $result;
		}

		// Only check GET requests.
		if ( 'GET' !== $request->get_method() ) {
			return $result;
		}

		$route = $request->get_route();

		// Check if route supports ETag.
		if ( ! $this->route_supports_etag( $route ) ) {
			return $result;
		}

		// Get If-None-Match values.
		$if_none_match = $this->get_if_none_match_values();

		if ( empty( $if_none_match ) ) {
			return $result;
		}

		$etag_hash = null;

		// Handle cart routes.
		if ( $this->is_cart_route( $route ) ) {
			$cart_key = $this->get_cart_key_from_request( $request );

			if ( empty( $cart_key ) ) {
				return $result;
			}

			// Get cart hash from database (efficient - no cart loading).
			$cart_hash = $this->get_cart_hash_from_db( $cart_key );

			if ( empty( $cart_hash ) ) {
				return $result;
			}

			// Generate the expected ETag hash for comparison.
			$etag_hash = md5( 'cocart_etag_' . $cart_hash . COCART_VERSION );
		}

		// Handle product routes.
		if ( $this->is_product_route( $route ) ) {
			$etag_hash = $this->get_product_etag_hash( $route, $request );

			if ( empty( $etag_hash ) ) {
				return $result;
			}
		}

		// Check if any If-None-Match value matches.
		if ( $etag_hash && in_array( $etag_hash, $if_none_match, true ) ) {
			// Return 304 Not Modified.
			// Use empty array (not null) to prevent fatal error in rest_filter_response_fields()
			// when the request includes a _fields parameter (array_intersect_key cannot accept null).
			$response = new WP_REST_Response( array(), 304 );
			$response->header( 'ETag', $this->format_etag( $etag_hash ) );
			$response->header( 'CoCart-Cache', 'HIT' );

			return $response;
		}

		return $result;
	} // END check_conditional_request()

	/**
	 * Get cart key from request.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return string Cart key or empty string.
	 */
	protected function get_cart_key_from_request( $request ) {
		// Check request parameter.
		$cart_key = $request->get_param( 'cart_key' );

		if ( ! empty( $cart_key ) ) {
			return sanitize_key( $cart_key );
		}

		// Check header.
		if ( ! empty( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) {
			return sanitize_key( wp_unslash( $_SERVER['HTTP_COCART_API_CART_KEY'] ) );
		}

		// Check if user is logged in.
		if ( is_user_logged_in() ) {
			return (string) get_current_user_id();
		}

		return '';
	} // END get_cart_key_from_request()

	/**
	 * Add ETag header to response.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param bool             $served  Whether the request has already been served.
	 * @param WP_HTTP_Response $result  Result to send to the client.
	 * @param WP_REST_Request  $request The request object.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @return bool
	 */
	public function add_etag_header( $served, $result, $request, $server ) {
		// Skip ETag header if cache is being skipped.
		if ( $this->should_skip_cache( $request ) ) {
			if ( method_exists( $server, 'send_header' ) ) {
				$server->send_header( 'CoCart-Cache', 'SKIP' );
			}
			return $served;
		}

		// Skip if this is already a 304 Not Modified response (cache HIT handled in check_conditional_request).
		if ( 304 === $result->get_status() ) {
			return $served;
		}

		$route = $request->get_route();

		// Check if route supports ETag.
		if ( ! $this->route_supports_etag( $route ) ) {
			return $served;
		}

		// For product routes: Only GET requests (products are read-only resources).
		// For cart routes: All methods (GET, POST, PUT, DELETE) because mutations return cart state.
		if ( $this->is_product_route( $route ) && 'GET' !== $request->get_method() ) {
			return $served;
		}

		$etag_hash = null;

		// Handle cart routes.
		if ( $this->is_cart_route( $route ) ) {
			// Get cart hash from session (already calculated by this point).
			$cart_hash = '';

			if ( WC()->session && method_exists( WC()->session, 'get_cart_hash' ) ) {
				$cart_hash = WC()->session->get_cart_hash();
			}

			// Fallback: try to get from response data if available.
			if ( empty( $cart_hash ) && $result instanceof WP_REST_Response ) {
				$data = $result->get_data();
				if ( is_array( $data ) && isset( $data['cart_hash'] ) ) {
					$cart_hash = $data['cart_hash'];
				}
			}

			if ( ! empty( $cart_hash ) && is_string( $cart_hash ) ) {
				// Use format_cart_etag for cart hashes (applies additional hashing).
				$etag_hash = $this->format_cart_etag( $cart_hash );
			}
		}

		// Handle product routes.
		if ( $this->is_product_route( $route ) ) {
			$hash = $this->get_product_etag_hash( $route, $request );

			if ( ! empty( $hash ) ) {
				// Product hashes are already fully computed, just format.
				$etag_hash = $this->format_etag( $hash );
			}
		}

		// Add ETag header if we have a valid hash.
		if ( ! empty( $etag_hash ) ) {
			if ( method_exists( $server, 'send_header' ) ) {
				$server->send_header( 'ETag', $etag_hash );
				$server->send_header( 'CoCart-Cache', 'MISS' );
			}
		}

		return $served;
	} // END add_etag_header()

	/**
	 * Expose ETag header in CORS.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array $headers CORS headers array.
	 *
	 * @return array Modified headers array.
	 */
	public function expose_etag_header( $headers ) {
		$headers[] = 'ETag';
		$headers[] = 'CoCart-Cache';

		return $headers;
	} // END expose_etag_header()

	/**
	 * Check if cache should be skipped for this request.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return bool True if cache should be skipped.
	 */
	protected function should_skip_cache( $request ) {
		$skip = $request->get_param( '_skip_cache' );

		return ! empty( $skip ) && in_array( $skip, array( 'true', '1', true, 1 ), true );
	} // END should_skip_cache()

	/**
	 * Allow If-None-Match header in CORS.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array $headers CORS headers array.
	 *
	 * @return array Modified headers array.
	 */
	public function allow_if_none_match_header( $headers ) {
		$headers[] = 'If-None-Match';

		return $headers;
	} // END allow_if_none_match_header()
} // END class

return new CoCart_ETag();
