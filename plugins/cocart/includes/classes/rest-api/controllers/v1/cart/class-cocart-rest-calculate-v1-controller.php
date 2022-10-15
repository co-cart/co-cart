<?php
/**
 * REST API: CoCart_Calculate_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v1
 * @since   2.1.0 Introduced.
 * @version 2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate cart totals. (API v1)
 *
 * Handles the request to calculate the cart totals via /calculate endpoint.
 *
 * @since 2.1.0 Introduced.
 *
 * @see CoCart_API_Controller
 */
class CoCart_Calculate_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'calculate';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @since   2.5.0 Added permission callback set to return true due to a change to the REST API in WordPress v5.5
	 * @version 2.7.0
	 */
	public function register_routes() {
		// Calculate Cart Total - cocart/v1/calculate (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'calculate_totals' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'return' => array(
						'required'          => false,
						'default'           => false,
						'description'       => __( 'Returns the cart totals once calculated.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'boolean',
						'validate_callback' => 'rest_validate_request_arg',
					),
				),
			)
		);
	} // register_routes()

	/**
	 * Calculate Cart Totals.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @see CoCart_Totals_Controller::get_totals()
	 * @see CoCart_API_Controller::get_response()
	 * @see Logger::log()
	 *
	 * @param array $data
	 *
	 * @return WP_REST_Response
	 */
	public function calculate_totals( $data = array() ) {
		WC()->cart->calculate_totals();

		// Was it requested to return all totals once calculated?
		if ( $data['return'] ) {
			return CoCart_Totals_Controller::get_totals( $data );
		}

		$message = __( 'Cart totals have been calculated.', 'cart-rest-api-for-woocommerce' );

		CoCart\Logger::log( $message, 'notice' );

		/**
		 * Filters message about cart totals have been calculated.
		 *
		 * @since 2.1.0 Introduced.
		 *
		 * @param string $message Message.
		 */
		$message = apply_filters( 'cocart_totals_calculated_message', $message );

		return $this->get_response( $message, $this->rest_base );
	} // END calculate_totals()

} // END class
