<?php
/**
 * REST API: CoCart_REST_Calculate_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for calculating cart totals, tax, fees and shipping (API v2).
 *
 * This REST API controller handles the request to calculate the cart
 * via "cocart/v2/cart/calculate" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_v2_Controller
 */
class CoCart_REST_Calculate_v2_Controller extends CoCart_REST_Cart_v2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/calculate';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Allowed route to be requested in a batch request.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Calculate Cart Total - cocart/v2/cart/calculate (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'calculate_totals' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	} // register_routes()

	/**
	 * Calculate Cart Totals.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function calculate_totals( $request = array() ) {
		try {
			$this->get_cart_instance()->calculate_totals();

			// Was it requested to return all totals once calculated?
			if ( isset( $request['return_totals'] ) && is_bool( $request['return_totals'] ) && $request['return_totals'] ) {
				$fields   = $this->get_fields_for_response( $request );
				$response = $this->get_cart_totals( $request, $this->get_cart_instance(), $fields );
			} else {
				// Get cart contents.
				$response = $this->get_cart_contents( $request );
			}

			return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END calculate_totals()

	/**
	 * Get the query params for calculating totals.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array $params Query parameters for calculating totals.
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'return_totals' => array(
				'required'          => false,
				'default'           => false,
				'description'       => __( 'Returns the cart totals once calculated if requested.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
