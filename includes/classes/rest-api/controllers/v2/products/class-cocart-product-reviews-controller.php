<?php
/**
 * REST API: CoCart_REST_Product_Reviews_V2_Controller class.
 *
 * Handles requests to the /products/reviews/ endpoint.
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

class_alias( 'CoCart_REST_Product_Reviews_V2_Controller', 'CoCart_Product_Reviews_V2_Controller' );

/**
 * CoCart REST API v2 - Product Reviews controller class.
 *
 * @extends CoCart_Product_Reviews_Controller
 */
class CoCart_REST_Product_Reviews_V2_Controller extends CoCart_Product_Reviews_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string Path regex.
	 */
	public static function get_path_regex() {
		return '/products/reviews';
	} // END get_path_regex()

	/**
	 * Get the path of this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_path() {
		return self::get_path_regex();
	} // END get_path()

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
		);
	} // END get_args()

	/**
	 * Route namespace.
	 *
	 * @deprecated 5.0.0 Use $this->namespace from the REST API class instead.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Version of route.
	 *
	 * @deprecated 5.0.0 Version is registered in the REST API class instead.
	 */
	protected $version = 'v2';

	/**
	 * Get version of route.
	 *
	 * @deprecated 5.0.0 Version is registered in the REST API class instead.
	 */
	public function get_version() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		return $this->version;
	} // END get_version()

	/**
	 * Register routes.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
	 *
	 * @access public
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
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
} // END class
