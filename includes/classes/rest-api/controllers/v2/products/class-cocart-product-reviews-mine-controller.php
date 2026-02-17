<?php
/**
 * REST API: CoCart_REST_Product_Reviews_Mine_V2_Controller class.
 *
 * Handles requests to the /products/reviews/mine endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Reviews Mine controller class.
 *
 * @extends CoCart_REST_Product_Reviews_V2_Controller
 */
class CoCart_REST_Product_Reviews_Mine_V2_Controller extends CoCart_REST_Product_Reviews_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/reviews/mine';
	} // END get_path_regex()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array Method arguments.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_my_reviews' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()

	/**
	 * Check if the user has logged in before returning their reviews.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
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
	 * @param array $prepared_args Prepared arguments.
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
} // END class
