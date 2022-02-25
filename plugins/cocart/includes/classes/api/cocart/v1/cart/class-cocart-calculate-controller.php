<?php
/**
 * CoCart - Calculate controller
 *
 * Handles the request to calculate the cart with /calculate endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\v1
 * @since    2.1.0
 * @version  2.7.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Calculate controller class.
 *
 * @package CoCart\API
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
	 * @access  public
	 * @since   1.0.0
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
	 * @access  public
	 * @since   1.0.0
	 * @version 2.7.0
	 * @param   array $data
	 * @return  WP_REST_Response
	 */
	public function calculate_totals( $data = array() ) {
		WC()->cart->calculate_totals();

		// Was it requested to return all totals once calculated?
		if ( $data['return'] ) {
			return CoCart_Totals_Controller::get_totals( $data );
		}

		$message = __( 'Cart totals have been calculated.', 'cart-rest-api-for-woocommerce' );

		CoCart_Logger::log( $message, 'notice' );

		/**
		 * Filters message about cart totals have been calculated.
		 *
		 * @since 2.1.0
		 * @param string $message Message.
		 */
		$message = apply_filters( 'cocart_totals_calculated_message', $message );

		return $this->get_response( $message, $this->rest_base );
	} // END calculate_totals()

} // END class
