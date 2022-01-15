<?php
/**
 * CoCart - Calculate controller
 *
 * Handles the request to calculate the cart with /cart/calculate endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Calculate controller class.
 *
 * @package CoCart\API
 */
class CoCart_Calculate_v2_Controller extends CoCart_Calculate_Controller {

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
	 */
	public function register_routes() {
		// Calculate Cart Total - cocart/v2/cart/calculate (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'calculate_totals' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			)
		);
	} // register_routes()

	/**
	 * Calculate Cart Totals.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request - Full details about the request.
	 * @return  WP_REST_Response
	 */
	public function calculate_totals( $request = array() ) {
		try {
			$controller = new CoCart_Cart_V2_Controller();

			$controller->get_cart_instance()->calculate_totals();

			// Was it requested to return all totals once calculated?
			if ( isset( $request['return_totals'] ) && is_bool( $request['return_totals'] ) && $request['return_totals'] ) {
				$response = CoCart_Totals_Controller::get_totals( $request );
			}

			cocart_deprecated_filter( 'cocart_totals_calculated_message', array(), '3.0.0', null, null );

			// Get cart contents.
			$response = $controller->get_cart_contents( $request );

			return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END calculate_totals()

	/**
	 * Get the query params for calculating totals.
	 *
	 * @access public
	 * @since  3.1.0
	 * @return array $params
	 */
	public function get_collection_params() {
		$controller = new CoCart_Cart_V2_Controller();

		// Cart query parameters.
		$params = $controller->get_collection_params();

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
