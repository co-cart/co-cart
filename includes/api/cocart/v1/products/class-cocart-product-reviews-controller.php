<?php
/**
 * CoCart - Product Reviews controller
 *
 * Handles requests to the /products/reviews/ endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v1
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Reviews controller class.
 *
 * @package CoCart/API
 * @extends WP_REST_Controller
 */
class CoCart_Product_Reviews_Controller extends WC_REST_Controller {

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
	protected $rest_base = 'products/reviews';

	/**
	 * Register the routes for product reviews.
	 *
	 * @access public
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
					'id'         => array(
						'description' => __( 'Unique identifier for the review.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array_merge(
						$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
						array(
							'product_id'     => array(
								'required'    => true,
								'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
							),
							'review'         => array(
								'required'    => true,
								'type'        => 'string',
								'description' => __( 'Review content.', 'cart-rest-api-for-woocommerce' ),
							),
							'reviewer'       => array(
								'required'    => true,
								'type'        => 'string',
								'description' => __( 'Name of the reviewer.', 'cart-rest-api-for-woocommerce' ),
							),
							'reviewer_email' => array(
								'required'    => true,
								'type'        => 'string',
								'description' => __( 'Email of the reviewer.', 'cart-rest-api-for-woocommerce' ),
							),
						)
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the review.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_review_exists' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check if the requested product review exists before returning.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function check_review_exists( $request ) {
		$id     = (int) $request['id'];
		$review = get_comment( $id );

		// If the review does not exist then it's not a review.
		if ( ! $review ) {
			return new WP_Error( 'cocart_cannot_view_review', __( 'Sorry, this product review does not exist.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$product = wc_get_product( $review->comment_post_ID );

		// If the comment is not assigned to a product then it's not a review.
		if ( ! $product ) {
			return new WP_Error( 'cocart_cannot_view_review', __( 'Sorry, this product review does not exist.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Check if the user has permission to create a new product review.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		$verified = false;

		if ( 'product' === get_post_type( $request['product_id'] ) ) {
			$user_data = get_user_by( 'email', $request['reviewer_email'] );
			$user_id   = $user_data->ID;
			$verified  = wc_customer_bought_product( $request['reviewer_email'], $user_id, $request['product_id'] );
		}

		if ( ! $verified ) {
			return new WP_Error( 'cocart_cannot_create', __( 'Sorry, you are not allowed to create a review for this product.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Get all reviews.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	public function get_items( $request ) {
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();

		/**
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'reviewer'         => 'author__in',
			'reviewer_exclude' => 'author__not_in',
			'exclude'          => 'comment__not_in',
			'include'          => 'comment__in',
			'offset'           => 'offset',
			'order'            => 'order',
			'per_page'         => 'number',
			'product'          => 'post__in',
			'search'           => 'search',
		);

		$prepared_args = array();

		/**
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $prepared_args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$prepared_args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Ensure certain parameter values default to empty strings.
		foreach ( array( 'author_email', 'search' ) as $param ) {
			if ( ! isset( $prepared_args[ $param ] ) ) {
				$prepared_args[ $param ] = '';
			}
		}

		if ( isset( $registered['orderby'] ) ) {
			$prepared_args['orderby'] = $this->normalize_query_param( $request['orderby'] );
		}

		if ( isset( $prepared_args['status'] ) ) {
			$prepared_args['status'] = 'approved';
		}

		$prepared_args['no_found_rows'] = false;
		$prepared_args['date_query']    = array();

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['before'], $request['before'] ) ) {
			$prepared_args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['after'], $request['after'] ) ) {
			$prepared_args['date_query'][0]['after'] = $request['after'];
		}

		if ( isset( $registered['page'] ) && empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $prepared_args['number'] * ( absint( $request['page'] ) - 1 );
		}

		/**
		 * Filters arguments, before passing to WP_Comment_Query, when querying reviews via the REST API.
		 *
		 * @link  https://developer.wordpress.org/reference/classes/wp_comment_query/
		 * @param array           $prepared_args Array of arguments for WP_Comment_Query.
		 * @param WP_REST_Request $request       The current request.
		 */
		$prepared_args = apply_filters( 'cocart_product_review_query', $prepared_args, $request );

		// Make sure that returns only reviews.
		$prepared_args['type'] = 'review';

		// Query reviews.
		$query        = new WP_Comment_Query();
		$query_result = $query->query( $prepared_args );
		$reviews      = array();

		foreach ( $query_result as $review ) {
			if ( ! wc_rest_check_product_reviews_permissions( 'read', $review->comment_ID ) ) {
				continue;
			}

			$data      = $this->prepare_item_for_response( $review, $request );
			$reviews[] = $this->prepare_response_for_collection( $data );
		}

		$total_reviews = (int) $query->found_comments;
		$max_pages     = (int) $query->max_num_pages;

		if ( $total_reviews < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $prepared_args['number'], $prepared_args['offset'] );

			$query                  = new WP_Comment_Query();
			$prepared_args['count'] = true;

			$total_reviews = $query->query( $prepared_args );
			$max_pages     = ceil( $total_reviews / $request['per_page'] );
		}

		$response = rest_ensure_response( $reviews );
		$response->header( 'X-WP-Total', $total_reviews );
		$response->header( 'X-WP-TotalPages', $max_pages );

		$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $request['page'] > 1 ) {
			$prev_page = $request['page'] - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $request['page'] ) {
			$next_page = $request['page'] + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Create a single review.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'cocart_review_exists', __( 'Cannot create existing product review.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 400 ) );
		}

		$product_id = (int) $request['product_id'];

		if ( 'product' !== get_post_type( $product_id ) ) {
			return new WP_Error( 'cocart_product_invalid_id', __( 'Invalid product ID.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$prepared_review = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_review ) ) {
			return $prepared_review;
		}

		$prepared_review['comment_type'] = 'review';

		/**
		 * Do not allow a comment to be created with missing or empty comment_content. See wp_handle_comment_submission().
		 */
		if ( empty( $prepared_review['comment_content'] ) ) {
			return new WP_Error( 'cocart_review_content_invalid', __( 'Invalid review content.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 400 ) );
		}

		// Setting remaining values before wp_insert_comment so we can use wp_allow_comment().
		if ( ! isset( $prepared_review['comment_date_gmt'] ) ) {
			$prepared_review['comment_date_gmt'] = current_time( 'mysql', true );
		}

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && rest_is_ip_address( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) ) {
			$prepared_review['comment_author_IP'] = wc_clean( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$prepared_review['comment_author_IP'] = '127.0.0.1';
		}

		if ( ! empty( $request['author_user_agent'] ) ) {
			$prepared_review['comment_agent'] = $request['author_user_agent'];
		} elseif ( $request->get_header( 'user_agent' ) ) {
			$prepared_review['comment_agent'] = $request->get_header( 'user_agent' );
		} else {
			$prepared_review['comment_agent'] = '';
		}

		$check_comment_lengths = wp_check_comment_data_max_lengths( $prepared_review );
		if ( is_wp_error( $check_comment_lengths ) ) {
			$error_code = str_replace( array( 'comment_author', 'comment_content' ), array( 'reviewer', 'review_content' ), $check_comment_lengths->get_error_code() );
			return new WP_Error( 'cocart_' . $error_code, __( 'Product review field exceeds maximum length allowed.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 400 ) );
		}

		$prepared_review['comment_parent']     = 0;
		$prepared_review['comment_author_url'] = '';
		$prepared_review['comment_approved']   = wp_allow_comment( $prepared_review, true );

		if ( is_wp_error( $prepared_review['comment_approved'] ) ) {
			$error_code    = $prepared_review['comment_approved']->get_error_code();
			$error_message = $prepared_review['comment_approved']->get_error_message();

			if ( 'comment_duplicate' === $error_code ) {
				return new WP_Error( 'cocart_' . $error_code, $error_message, array( 'status' => 409 ) );
			}

			if ( 'comment_flood' === $error_code ) {
				return new WP_Error( 'cocart_' . $error_code, $error_message, array( 'status' => 400 ) );
			}

			return $prepared_review['comment_approved'];
		}

		/**
		 * Filters a review before it is inserted via the REST API.
		 *
		 * Allows modification of the review right before it is inserted via wp_insert_comment().
		 * Returning a WP_Error value from the filter will short circuit insertion and allow
		 * skipping further processing.
		 *
		 * @param array|WP_Error  $prepared_review The prepared review data for wp_insert_comment().
		 * @param WP_REST_Request $request          Request used to insert the review.
		 */
		$prepared_review = apply_filters( 'cocart_pre_insert_product_review', $prepared_review, $request );

		if ( is_wp_error( $prepared_review ) ) {
			return $prepared_review;
		}

		$review_id = wp_insert_comment( wp_filter_comment( wp_slash( (array) $prepared_review ) ) );

		if ( ! $review_id ) {
			return new WP_Error( 'cocart_review_failed_create', __( 'Creating product review failed.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 500 ) );
		}

		if ( isset( $request['status'] ) ) {
			$this->handle_status_param( $request['status'], $review_id );
		}

		update_comment_meta( $review_id, 'rating', ! empty( $request['rating'] ) ? $request['rating'] : '0' );

		$review = get_comment( $review_id );

		/**
		 * Fires after a comment is created via the REST API.
		 *
		 * @param WP_Comment      $review   Inserted comment object.
		 * @param WP_REST_Request $request  Request object.
		 * @param bool            $creating True when creating a comment, false when updating.
		 */
		do_action( 'cocart_insert_product_review', $review, $request, true );

		$fields_update = $this->update_additional_fields_for_object( $review, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$context = current_user_can( 'moderate_comments' ) ? 'edit' : 'view';
		$request->set_param( 'context', $context );

		$response = $this->prepare_item_for_response( $review, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $review_id ) ) );

		return $response;
	}

	/**
	 * Get a single product review.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$review = $this->get_review( $request['id'] );
		if ( is_wp_error( $review ) ) {
			return $review;
		}

		$data     = $this->prepare_item_for_response( $review, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Prepare a single product review output for response.
	 *
	 * @access public
	 * @param  WP_Comment      $review Product review object.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $review, $request ) {
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$fields  = $this->get_fields_for_response( $request );
		$data    = array();

		if ( in_array( 'id', $fields, true ) ) {
			$data['id'] = (int) $review->comment_ID;
		}
		if ( in_array( 'date_created', $fields, true ) ) {
			$data['date_created'] = wc_rest_prepare_date_response( $review->comment_date );
		}
		if ( in_array( 'date_created_gmt', $fields, true ) ) {
			$data['date_created_gmt'] = wc_rest_prepare_date_response( $review->comment_date_gmt );
		}
		if ( in_array( 'product_id', $fields, true ) ) {
			$data['product_id'] = (int) $review->comment_post_ID;
		}
		if ( in_array( 'status', $fields, true ) ) {
			$data['status'] = $this->prepare_status_response( (string) $review->comment_approved );
		}
		if ( in_array( 'reviewer', $fields, true ) ) {
			$data['reviewer'] = $review->comment_author;
		}
		if ( in_array( 'review', $fields, true ) ) {
			$data['review'] = 'view' === $context ? wpautop( $review->comment_content ) : $review->comment_content;
		}
		if ( in_array( 'rating', $fields, true ) ) {
			$data['rating'] = (int) get_comment_meta( $review->comment_ID, 'rating', true );
		}
		if ( in_array( 'verified', $fields, true ) ) {
			$data['verified'] = wc_review_is_from_verified_owner( $review->comment_ID );
		}
		if ( in_array( 'reviewer_avatar_urls', $fields, true ) ) {
			$data['reviewer_avatar_urls'] = rest_get_avatar_urls( $review->comment_author_email );
		}

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $review ) );

		/**
		 * Filter product reviews object returned from the REST API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Comment       $review   Product review object used to create response.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'cocart_prepare_product_review', $response, $review, $request );
	}

	/**
	 * Prepare a single product review to be inserted into the database.
	 *
	 * @access protected
	 * @param  WP_REST_Request $request Request object.
	 * @return array|WP_Error  $prepared_review
	 */
	protected function prepare_item_for_database( $request ) {
		if ( isset( $request['id'] ) ) {
			$prepared_review['comment_ID'] = (int) $request['id'];
		}

		if ( isset( $request['review'] ) ) {
			$prepared_review['comment_content'] = $request['review'];
		}

		if ( isset( $request['product_id'] ) ) {
			$prepared_review['comment_post_ID'] = (int) $request['product_id'];
		}

		if ( isset( $request['reviewer'] ) ) {
			$prepared_review['comment_author'] = $request['reviewer'];
		}

		if ( isset( $request['reviewer_email'] ) ) {
			$prepared_review['comment_author_email'] = $request['reviewer_email'];
		}

		if ( ! empty( $request['date_created'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date_created'] );

			if ( ! empty( $date_data ) ) {
				list( $prepared_review['comment_date'], $prepared_review['comment_date_gmt'] ) = $date_data;
			}
		} elseif ( ! empty( $request['date_created_gmt'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date_created_gmt'], true );

			if ( ! empty( $date_data ) ) {
				list( $prepared_review['comment_date'], $prepared_review['comment_date_gmt'] ) = $date_data;
			}
		}

		/**
		 * Filters a review after it is prepared for the database.
		 *
		 * Allows modification of the review right after it is prepared for the database.
		 *
		 * @param array           $prepared_review The prepared review data for `wp_insert_comment`.
		 * @param WP_REST_Request $request         The current request.
		 */
		return apply_filters( 'cocart_preprocess_product_review', $prepared_review, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 * @param  WP_Comment $review Product review object.
	 * @return array Links for the given product review.
	 */
	protected function prepare_links( $review ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $review->comment_ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( 0 !== (int) $review->comment_post_ID ) {
			$links['product'] = array(
				'href'      => rest_url( sprintf( '/%s/products/%d', $this->namespace, $review->comment_post_ID ) ),
				'permalink' => get_permalink( $review->comment_post_ID ),
			);
		}

		if ( 0 !== (int) $review->user_id ) {
			$links['reviewer'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $review->user_id ),
				'embeddable' => true,
			);
		}

		return $links;
	}

	/**
	 * Get the Product Review's schema, conforming to JSON Schema.
	 *
	 * @access public
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'product_review',
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'Unique identifier for the review.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created'     => array(
					'description' => __( "The date the review was created, in the site's timezone.", 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created_gmt' => array(
					'description' => __( 'The date the review was created, as GMT.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'product_id'       => array(
					'description' => __( 'Unique identifier for the product that the review belongs to.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'status'           => array(
					'description' => __( 'Status of the review.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'default'     => 'approved',
					'enum'        => array( 'approved', 'hold', 'spam', 'unspam', 'trash', 'untrash' ),
					'context'     => array( 'view', 'edit' ),
				),
				'reviewer'         => array(
					'description' => __( 'Reviewer name.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'reviewer_email'   => array(
					'description' => __( 'Reviewer email.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'edit' ),
				),
				'review'           => array(
					'description' => __( 'The content of the review.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'rating'           => array(
					'description' => __( 'Review rating (0 to 5).', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'verified'         => array(
					'description' => __( 'Shows if the reviewer bought the product or not.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		$avatar_properties = array();
		$avatar_sizes      = rest_get_avatar_sizes();

		foreach ( $avatar_sizes as $size ) {
			$avatar_properties[ $size ] = array(
				/* translators: %d: avatar image size in pixels */
				'description' => sprintf( __( 'Avatar URL with image size of %d pixels.', 'cart-rest-api-for-woocommerce' ), $size ),
				'type'        => 'string',
				'format'      => 'uri',
				'context'     => array( 'embed', 'view', 'edit' ),
			);
		}
		$schema['properties']['reviewer_avatar_urls'] = array(
			'description' => __( 'Avatar URLs for the object reviewer.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
			'properties'  => $avatar_properties,
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @access public
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['after']            = array(
			'description' => __( 'Limit response to reviews published after a given ISO8601 compliant date.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'string',
			'format'      => 'date-time',
		);
		$params['before']           = array(
			'description' => __( 'Limit response to reviews published before a given ISO8601 compliant date.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'string',
			'format'      => 'date-time',
		);
		$params['exclude']          = array(
			'description' => __( 'Ensure result set excludes specific IDs.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);
		$params['include']          = array(
			'description' => __( 'Limit result set to specific IDs.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);
		$params['offset']           = array(
			'description' => __( 'Offset the result set by a specific number of items.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'integer',
		);
		$params['order']            = array(
			'description' => __( 'Order sort attribute ascending or descending.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'string',
			'default'     => 'desc',
			'enum'        => array(
				'asc',
				'desc',
			),
		);
		$params['orderby']          = array(
			'description' => __( 'Sort collection by object attribute.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'string',
			'default'     => 'date_gmt',
			'enum'        => array(
				'date',
				'date_gmt',
				'id',
				'include',
				'product',
			),
		);
		$params['reviewer']         = array(
			'description' => __( 'Limit result set to reviews assigned to specific user IDs.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);
		$params['reviewer_exclude'] = array(
			'description' => __( 'Ensure result set excludes reviews assigned to specific user IDs.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);
		$params['product']          = array(
			'default'     => array(),
			'description' => __( 'Limit result set to reviews assigned to specific product IDs.', 'cart-rest-api-for-woocommerce' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		/**
		 * Filter collection parameters for the reviews controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_Comment_Query parameter. Use the
		 * `wc_rest_review_query` filter to set WP_Comment_Query parameters.
		 *
		 * @param array $params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'cocart_product_review_collection_params', $params );
	}

	/**
	 * Get the review, if the ID is valid.
	 *
	 * @access protected
	 * @param  int $id Supplied ID.
	 * @return WP_Comment|WP_Error Comment object if ID is valid, WP_Error otherwise.
	 */
	protected function get_review( $id ) {
		$id    = (int) $id;
		$error = new WP_Error( 'cocart_review_invalid_id', __( 'Invalid review ID.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );

		if ( 0 >= $id ) {
			return $error;
		}

		$review = get_comment( $id );
		if ( empty( $review ) ) {
			return $error;
		}

		if ( ! empty( $review->comment_post_ID ) ) {
			$post = get_post( (int) $review->comment_post_ID );

			if ( 'product' !== get_post_type( (int) $review->comment_post_ID ) ) {
				return new WP_Error( 'cocart_product_invalid_id', __( 'Invalid product ID.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
			}
		}

		return $review;
	}

	/**
	 * Prepends internal property prefix to query parameters to match our response fields.
	 *
	 * @access protected
	 * @param  string $query_param Query parameter.
	 * @return string
	 */
	protected function normalize_query_param( $query_param ) {
		$prefix = 'comment_';

		switch ( $query_param ) {
			case 'id':
				$normalized = $prefix . 'ID';
				break;
			case 'product':
				$normalized = $prefix . 'post_ID';
				break;
			case 'include':
				$normalized = 'comment__in';
				break;
			default:
				$normalized = $prefix . $query_param;
				break;
		}

		return $normalized;
	}

	/**
	 * Checks comment_approved to set comment status for single comment output.
	 *
	 * @access protected
	 * @param  string|int $comment_approved comment status.
	 * @return string Comment status.
	 */
	protected function prepare_status_response( $comment_approved ) {
		switch ( $comment_approved ) {
			case 'hold':
			case '0':
				$status = 'hold';
				break;
			case 'approve':
			case '1':
				$status = 'approved';
				break;
			case 'spam':
			case 'trash':
			default:
				$status = $comment_approved;
				break;
		}

		return $status;
	}

	/**
	 * Sets the comment_status of a given review object when creating a review.
	 *
	 * @access protected
	 * @param  string|int $new_status New review status.
	 * @param  int        $id         Review ID.
	 * @return bool Whether the status was changed.
	 */
	protected function handle_status_param( $new_status, $id ) {
		$old_status = wp_get_comment_status( $id );

		if ( $new_status === $old_status ) {
			return false;
		}

		switch ( $new_status ) {
			case 'approved':
			case 'approve':
			case '1':
				$changed = wp_set_comment_status( $id, 'approve' );
				break;
			case 'hold':
			case '0':
				$changed = wp_set_comment_status( $id, 'hold' );
				break;
			case 'spam':
				$changed = wp_spam_comment( $id );
				break;
			case 'unspam':
				$changed = wp_unspam_comment( $id );
				break;
			case 'trash':
				$changed = wp_trash_comment( $id );
				break;
			case 'untrash':
				$changed = wp_untrash_comment( $id );
				break;
			default:
				$changed = false;
				break;
		}

		return $changed;
	}

}
