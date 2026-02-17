<?php
/**
 * CoCart - Abstract REST Terms Controller
 *
 * Shared base class for all term/taxonomy controllers.
 * Contains all common business logic for retrieving and managing taxonomy terms.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Terms
 * @since   5.0.0 Introduced as shared abstract base.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Terms controller class.
 *
 * Provides shared functionality for all taxonomy term endpoints.
 * Child classes must define $rest_base and $taxonomy properties.
 */
abstract class CoCart_REST_Taxonomy_Terms_Controller extends CoCart_REST_Controller {

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = '';

	/**
	 * Get taxonomy.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return int|WP_Error
	 */
	protected function get_taxonomy( $request ) {
		// Check if taxonomy is defined.
		// Prevents check for attribute taxonomy more than one time for each query.
		if ( '' !== $this->taxonomy ) {
			return $this->taxonomy;
		}

		if ( ! empty( $request['attribute_id'] ) ) {
			$taxonomy = wc_attribute_taxonomy_name_by_id( (int) $request['attribute_id'] );

			$this->taxonomy = $taxonomy;
		}

		return $this->taxonomy;
	} // END get_taxonomy()

	/**
	 * Check if a given request has access to read the terms.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request );

		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new \WP_Error( 'cocart_cannot_list_resources', __( 'Sorry, you cannot list resources.', 'cocart-core' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Check if a given request has access to read a term.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$permissions = $this->check_permissions( $request, 'read' );

		if ( is_wp_error( $permissions ) ) {
			return $permissions;
		}

		if ( ! $permissions ) {
			return new \WP_Error( 'cocart_cannot_view_resource', __( 'Sorry, you cannot view this resource.', 'cocart-core' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_item_permissions_check()

	/**
	 * Check permissions.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error
	 */
	protected function check_permissions( $request ) {
		// Get taxonomy.
		$taxonomy = $this->get_taxonomy( $request );

		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return new \WP_Error( 'cocart_taxonomy_invalid', __( 'Taxonomy does not exist.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		// Check permissions for a single term.
		$id = intval( $request['id'] );

		if ( $id ) {
			$term = get_term( $id, $taxonomy );

			if ( is_wp_error( $term ) || ! $term || $term->taxonomy !== $taxonomy ) {
				return new \WP_Error( 'cocart_term_invalid', __( 'Term does not exist.', 'cocart-core' ), array( 'status' => 404 ) );
			}

			return true;
		}

		return true;
	} // END check_permissions()

	/**
	 * Get terms associated with a taxonomy.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$taxonomy = $this->get_taxonomy( $request );

		$prepared_args = array(
			'exclude'    => $request['exclude'], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'include'    => $request['include'],
			'order'      => $request['order'],
			'orderby'    => $request['orderby'],
			'product'    => $request['product'],
			'hide_empty' => $request['hide_empty'],
			'number'     => $request['per_page'],
			'search'     => $request['search'],
			'slug'       => $request['slug'],
		);

		if ( ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
		}

		$taxonomy_obj = get_taxonomy( $taxonomy );

		if ( $taxonomy_obj->hierarchical && isset( $request['parent'] ) ) {
			if ( 0 === $request['parent'] ) {
				// Only query top-level terms.
				$prepared_args['parent'] = 0;
			} elseif ( $request['parent'] ) {
				$prepared_args['parent'] = $request['parent'];
			}
		}

		/**
		 * Filter the query arguments, before passing them to `get_terms()`.
		 *
		 * Enables adding extra arguments or setting defaults for a terms
		 * collection request.
		 *
		 * @see https://developer.wordpress.org/reference/functions/get_terms/
		 *
		 * @param array           $prepared_args Array of arguments to be passed to get_terms.
		 * @param WP_REST_Request $request       The request object.
		 */
		$prepared_args = apply_filters( "cocart_rest_{$taxonomy}_query", $prepared_args, $request ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		if ( ! empty( $prepared_args['product'] ) ) {
			$query_result = $this->get_terms_for_product( $prepared_args, $request );
			$total_terms  = $this->total_terms;
		} else {
			$query_result = get_terms( $taxonomy, $prepared_args ); // phpcs:ignore WordPress.WP.DeprecatedParameters.Get_termsParam2Found, todo: Update first parameter to merge with second parameter.

			$count_args = $prepared_args;
			unset( $count_args['number'] );
			unset( $count_args['offset'] );
			$total_terms = wp_count_terms( $taxonomy, $count_args ); // phpcs:ignore WordPress.WP.DeprecatedParameters.Wp_count_termsParam2Found, todo: Update first parameter to merge with second parameter.

			// Ensure we don't return results when offset is out of bounds.
			// See https://core.trac.wordpress.org/ticket/35935.
			if ( $prepared_args['offset'] && $prepared_args['offset'] >= $total_terms ) {
				$query_result = array();
			}

			// wp_count_terms can return a falsy value when the term has no children.
			if ( ! $total_terms ) {
				$total_terms = 0;
			}
		}

		$response = array();

		foreach ( $query_result as $term ) {
			$data       = $this->prepare_item_for_response( $term, $request );
			$response[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $response );

		// Add pagination headers using utility class.
		$per_page  = (int) $prepared_args['number'];
		$max_pages = $per_page > 0 ? ceil( $total_terms / $per_page ) : 1;

		$response = ( new CoCart_REST_Utilities_Pagination() )->add_headers( $response, $request, $total_terms, $max_pages );

		return $response;
	} // END get_items()

	/**
	 * Get a single term from a taxonomy.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Request|WP_Error
	 */
	public function get_item( $request ) {
		$taxonomy = $this->get_taxonomy( $request );
		$term     = get_term( (int) $request['id'], $taxonomy );

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$response = $this->prepare_item_for_response( $term, $request );

		return rest_ensure_response( $response );
	} // END get_item()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param object          $term    Term object.
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array Links for the given term.
	 */
	protected function prepare_links( $term, $request ) {
		$term_id = $term->term_id;

		// Get current route from request.
		$route = $request->get_route();

		// Build self link (append term ID if single item route).
		$self_route = preg_match( '/\/\d+$/', $route ) ? $route : $route . '/' . $term_id;

		// Build collection link (remove term ID).
		$collection_route = preg_replace( '/\/\d+$/', '', $route );

		// Build links with both REST hrefs and frontend permalinks.
		$links = array(
			'self'       => array(
				'permalink' => cocart_get_permalink( get_term_link( $term ) ),
				'href'      => rest_url( ltrim( $self_route, '/' ) ),
			),
			'collection' => array(
				'permalink' => cocart_get_permalink( wc_get_page_permalink( 'shop' ) ),
				'href'      => rest_url( ltrim( $collection_route, '/' ) ),
			),
		);

		// Add parent link if hierarchical.
		if ( $term->parent ) {
			$parent_term = get_term( (int) $term->parent, $term->taxonomy );
			if ( $parent_term && ! is_wp_error( $parent_term ) ) {
				// Replace existing ID or append parent ID if no ID in route.
				if ( preg_match( '/\/\d+$/', $route ) ) {
					// Single item route - replace the ID.
					$parent_route = preg_replace( '/\/\d+$/', '/' . $parent_term->term_id, $route );
				} else {
					// Collection route - append the parent ID.
					$parent_route = $route . '/' . $parent_term->term_id;
				}

				$links['up'] = array(
					'permalink' => cocart_get_permalink( get_term_link( $parent_term ) ),
					'href'      => rest_url( ltrim( $parent_route, '/' ) ),
				);
			}
		}

		return $links;
	} // END prepare_links()

	/**
	 * Prepare a single taxonomy term output for response.
	 *
	 * This method provides the base implementation with field filtering
	 * and conditional link preparation. Child classes can override to add additional
	 * fields specific to their taxonomy.
	 *
	 * V1 controllers override this completely without calling parent.
	 * V2 controllers can call parent::prepare_item_for_response() and extend the data.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param \WP_Term         $item    Term object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get fields being requested.
		$fields = $this->get_fields_for_response( $request );

		$data = array();

		// ID.
		if ( rest_is_field_included( 'id', $fields ) ) {
			$data['id'] = (int) $item->term_id;
		}

		// Name.
		if ( rest_is_field_included( 'name', $fields ) ) {
			$data['name'] = $item->name;
		}

		// Slug.
		if ( rest_is_field_included( 'slug', $fields ) ) {
			$data['slug'] = $item->slug;
		}

		// Description.
		if ( rest_is_field_included( 'description', $fields ) ) {
			$data['description'] = $item->description;
		}

		// Count.
		if ( rest_is_field_included( 'count', $fields ) ) {
			$data['count'] = (int) $item->count;
		}

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, 'view' );

		$response = rest_ensure_response( $data );

		// Only prepare links if requested (WordPress 6.1+ optimization).
		if ( rest_is_field_included( '_links', $fields ) || rest_is_field_included( '_embedded', $fields ) ) {
			$response->add_links( $this->prepare_links( $item, $request ) );
		}

		/**
		 * Filter a term item returned from the API.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param \WP_Term          $item     Term object used to create response.
		 * @param \WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "cocart_rest_prepare_{$this->taxonomy}", $response, $item, $request );
	} // END prepare_item_for_response()

	/**
	 * Get the taxonomy term schema, conforming to JSON Schema.
	 *
	 * This method provides the base schema. Child classes can override
	 * to add additional properties specific to their taxonomy.
	 *
	 * V1 controllers override this completely without calling parent.
	 * V2 controllers can call parent::get_item_schema() and merge additional properties.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		// Return cached schema if available.
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->taxonomy,
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the term.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'Name of the term.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'slug'        => array(
					'description' => __( 'An alphanumeric identifier for the term unique to its type.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'HTML description of the term.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'count'       => array(
					'description' => __( 'Number of published products for the term.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		// Cache the schema.
		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	} // END get_item_schema()

	/**
	 * Get the terms attached to a product.
	 *
	 * This is an alternative to `get_terms()` that uses `get_the_terms()`
	 * instead, which hits the object cache. There are a few things not
	 * supported, notably `include`, `exclude`. In `self::get_items()` these
	 * are instead treated as a full query.
	 *
	 * @access protected
	 *
	 * @param array           $prepared_args Arguments for `get_terms()`.
	 * @param WP_REST_Request $request       Full details about the request.
	 *
	 * @return array List of term objects. (Total count in `$this->total_terms`).
	 */
	protected function get_terms_for_product( $prepared_args, $request ) {
		$taxonomy = $this->get_taxonomy( $request );

		$query_result = get_the_terms( $prepared_args['product'], $taxonomy );

		if ( empty( $query_result ) ) {
			$this->total_terms = 0;
			return array();
		}

		// get_items() verifies that we don't have `include` set, and default.
		// ordering is by `name`.
		if ( ! in_array( $prepared_args['orderby'], array( 'name', 'none', 'include' ), true ) ) {
			switch ( $prepared_args['orderby'] ) {
				case 'id':
					$this->sort_column = 'term_id';
					break;
				case 'slug':
				case 'term_group':
				case 'description':
				case 'count':
					$this->sort_column = $prepared_args['orderby'];
					break;
			}
			usort( $query_result, array( $this, 'compare_terms' ) );
		}

		if ( strtolower( $prepared_args['order'] ) !== 'asc' ) {
			$query_result = array_reverse( $query_result );
		}

		// Pagination.
		$this->total_terms = count( $query_result );
		$query_result      = array_slice( $query_result, $prepared_args['offset'], $prepared_args['number'] );

		return $query_result;
	} // END get_terms_for_product()

	/**
	 * Comparison function for sorting terms by a column.
	 *
	 * Uses `$this->sort_column` to determine field to sort by.
	 *
	 * @access protected
	 *
	 * @param stdClass $left  Term object.
	 * @param stdClass $right Term object.
	 *
	 * @return int <0 if left is higher "priority" than right, 0 if equal, >0 if right is higher "priority" than left.
	 */
	protected function compare_terms( $left, $right ) {
		$col       = $this->sort_column;
		$left_val  = $left->$col;
		$right_val = $right->$col;

		if ( is_int( $left_val ) && is_int( $right_val ) ) {
			return $left_val - $right_val;
		}

		return strcmp( $left_val, $right_val );
	} // END compare_terms()

	/**
	 * Get the query params for collections
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		if ( '' !== $this->taxonomy && taxonomy_exists( $this->taxonomy ) ) {
			$taxonomy = get_taxonomy( $this->taxonomy );
		} else {
			$taxonomy               = new stdClass();
			$taxonomy->hierarchical = true;
		}

		$params['context']['default'] = 'view';

		$params['exclude'] = array( // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'description'       => __( 'Ensure result set excludes specific IDs.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['include'] = array(
			'description'       => __( 'Limit result set to specific ids.', 'cocart-core' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		if ( ! $taxonomy->hierarchical ) {
			$params['offset'] = array(
				'description'       => __( 'Offset the result set by a specific number of items.', 'cocart-core' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			);
		}
		$params['order']      = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'asc',
			'enum'              => array(
				'asc',
				'desc',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby']    = array(
			'description'       => __( 'Sort collection by resource attribute.', 'cocart-core' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'name',
			'enum'              => array(
				'id',
				'include',
				'name',
				'slug',
				'term_group',
				'description',
				'count',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['hide_empty'] = array(
			'description'       => __( 'Whether to hide resources not assigned to any products.', 'cocart-core' ),
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);
		if ( $taxonomy->hierarchical ) {
			$params['parent'] = array(
				'description'       => __( 'Limit result set to resources assigned to a specific parent.', 'cocart-core' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			);
		}
		$params['product'] = array(
			'description'       => __( 'Limit result set to resources assigned to a specific product.', 'cocart-core' ),
			'type'              => 'integer',
			'default'           => null,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['slug']    = array(
			'description'       => __( 'Limit result set to resources with a specific slug.', 'cocart-core' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	} // END get_collection_params()
} // END abstract
