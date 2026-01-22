<?php
/**
 * REST API: CoCart_REST_Calculate_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Calculate_V2_Controller', 'CoCart_Calculate_V2_Controller' );

/**
 * Controller for calculating cart totals, tax, fees and shipping (API v2).
 *
 * This REST API controller handles the request to calculate the cart
 * via "cocart/v2/cart/calculate" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Calculate_V2_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/calculate';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/calculate';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'calculate_cart_totals' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
		);
	} // END get_args()

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
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Calculate Cart Total - cocart/v2/cart/calculate (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Calculate Cart Totals.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 5.0.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function calculate_cart_totals( $request ) {
		try {
			parent::calculate_totals();

			// Was it requested to return all totals once calculated?
			if ( isset( $request['return_totals'] ) && is_bool( $request['return_totals'] ) && $request['return_totals'] ) {
				$request['fields'] = 'totals';
			}

			// Get cart.
			$request['dont_check'] = true;
			$response              = $this->get_cart( $request );

			$response = rest_ensure_response( $response );

			// Return the totals without the parent.
			if ( isset( $request['return_totals'] ) && is_bool( $request['return_totals'] ) && $request['return_totals'] ) {
				$response = isset( $response->data['totals'] ) ? $response->data['totals'] : array();
			}

			$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
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
				'description'       => __( 'Returns the cart totals once calculated.', 'cocart-core' ),
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()
} // END class
