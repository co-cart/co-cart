<?php
/**
 * REST API: CoCart_REST_Totals_v2_Controller class
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
 * Controller for getting the cart totals (API v2).
 *
 * This REST API controller handles the request to get the totals of the cart
 * via "cocart/v2/cart/totals" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_v2_Controller
 */
class CoCart_REST_Totals_v2_Controller extends CoCart_REST_Cart_v2_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/totals';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Get Cart Totals - cocart/v2/cart/totals (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_totals' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
			),
		);
	} // register_routes()

	/**
	 * Returns all calculated totals.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 * @since 4.0.0 Accesses the cart totals from the cart controller.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_totals( $request ) {
		try {
			$this->calculate_totals();
			$fields = $this->get_fields_for_response( $request );
			$totals = $this->get_cart_totals( $request, $fields );

			if ( empty( $totals['total'] ) ) {
				$message = esc_html__( 'This cart has no items.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_cart_totals_empty', $message, 404 );
			}

			return CoCart_Response::get_response( $totals, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_totals()

} // END class
