<?php
/**
 * REST API: CoCart_REST_Product_Reviews_V2_Controller class.
 *
 * Handles requests to the /products/reviews/ endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Product_Reviews_V2_Controller', 'CoCart_Product_Reviews_V2_Controller' );

/**
 * CoCart REST API v2 - Product Reviews controller class.
 *
 * @extends CoCart_Product_Reviews_Controller
 */
class CoCart_REST_Product_Reviews_V2_Controller extends CoCart_Product_Reviews_Controller {

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
	 * Register the routes for product reviews.
	 *
	 * @access public
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
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
								'description'       => __( 'Unique identifier for the product.', 'cocart-core' ),
								'type'              => 'integer',
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
								'validate_callback' => 'rest_validate_request_arg',
							),
							'review'         => array(
								'description'       => __( 'Review content.', 'cocart-core' ),
								'type'              => 'string',
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
								'validate_callback' => 'rest_validate_request_arg',
							),
							'reviewer'       => array(
								'description'       => __( 'Name of the reviewer.', 'cocart-core' ),
								'type'              => 'string',
								'required'          => false,
								'sanitize_callback' => 'sanitize_text_field',
								'validate_callback' => 'rest_validate_request_arg',
							),
							'reviewer_email' => array(
								'description'       => __( 'Email of the reviewer.', 'cocart-core' ),
								'type'              => 'string',
								'required'          => false,
								'sanitize_callback' => 'sanitize_email',
								'validate_callback' => 'rest_validate_request_arg',
							),
						)
					),
				),
				'allow_batch' => array( 'v1' => true ),
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/mine',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_my_reviews' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);
	} // END register_routes()

	/**
	 * Check if the user has permission to create a new product review.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		$verified = false;

		$product_id = ! isset( $request['product_id'] ) ? 0 : wc_clean( wp_unslash( $request['product_id'] ) );

		$product_id = CoCart_Utilities_Cart_Helpers::validate_product_id( $product_id );

		// Return failed product ID validation if any.
		if ( is_wp_error( $product_id ) ) {
			return $product_id;
		}

		if ( ! is_user_logged_in() ) {
			$customers_email = sanitize_text_field( wp_unslash( $request['reviewer_email'] ) );
			$user_data       = get_user_by( 'email', $customers_email );
			$user_id         = $user_data->ID;
		} else {
			$user            = get_userdata( get_current_user_id() );
			$customers_email = $user->user_email;
		}

		$verified = wc_customer_bought_product( $customers_email, $user_id, $request['product_id'] );

		if ( ! $verified ) {
			return new \WP_Error( 'cocart_cannot_create', __( 'Sorry, you are not allowed to create a review for this product.', 'cocart-core' ), array( 'status' => 403 ) );
		}

		return true;
	} // END create_item_permissions_check()

	/**
	 * Check if the user has logged in before returning their reviews.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function check_permission( $request ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'cocart_customer_authentication_required', __( 'This endpoint requires the customer to be logged in.', 'cocart-core' ), array( 'status' => 403 ) );
		}

		return true;
	} // END check_permission()

	/**
	 * Get the prepared arguments for the request.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function get_prepared_args_force_user_id( $prepared_args ) {
		// Force only reviews by the current user.
		$current_user             = wp_get_current_user();
		$prepared_args['user_id'] = $current_user->ID;

		return $prepared_args;
	} // END get_prepared_args_force_user_id()

	/**
	 * Get reviews posted by registered customer.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array|WP_Error
	 */
	public function get_my_reviews( $request ) {
		$prepared_args = $this->get_prepared_args( $request );
		$prepared_args = $this->get_prepared_args_force_user_id( $prepared_args );

		// Query reviews.
		$query        = new WP_Comment_Query();
		$query_result = $query->query( $prepared_args );

		$reviews = array();

		foreach ( $query_result as $review ) {
			$data      = $this->prepare_item_for_response( $review, $request );
			$reviews[] = $this->prepare_response_for_collection( $data );
		}

		return $this->get_review_response( $request, $prepared_args, $query, $reviews );
	} // END get_my_reviews()
}
