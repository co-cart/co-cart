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
	 * @since   1.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_totals( $request = array() ) {
		try {
			$pre_formatted = isset( $request['html'] ) ? $request['html'] : false;

			$controller = new CoCart_REST_Cart_v2_Controller();

			$totals            = $controller->get_cart_instance()->get_totals();
			$totals_calculated = false;

			if ( ! empty( $totals['total'] ) ) {
				$totals_calculated = true;
			}

			if ( ! $totals_calculated ) {
				$message = esc_html__( 'This cart either has no items or was not calculated.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_cart_totals_empty', $message, 404 );
			}

			$ignore_convert = array(
				'shipping_taxes',
				'cart_contents_taxes',
				'fee_taxes',
			);

			// Was it requested to have the totals preformatted?
			if ( $pre_formatted ) {
				$new_totals = array();

				foreach ( $totals as $type => $total ) {
					if ( in_array( $type, $ignore_convert ) ) {
						$new_totals[ $type ] = $total;
					} else {
						if ( is_string( $total ) ) {
							$new_totals[ $type ] = cocart_price_no_html( $total );
						} else {
							$new_totals[ $type ] = cocart_price_no_html( strval( $total ) );
						}
					}
				}

				$totals = $new_totals;
			}

			return CoCart_Response::get_response( $totals, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_totals()

	/**
	 * Get the query params for cart totals.
	 *
	 * @access public
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'html' => array(
				'required'          => false,
				'default'           => false,
				'description'       => __( 'Returns the totals pre-formatted.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
