<?php
/**
 * CoCart - Calculate controller
 *
 * Handles the request to calculate the cart with /calculate endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Calculate controller class.
 *
 * @package CoCart/API
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
	 */
	public function register_routes() {
		// Calculate Cart Total - cocart/v1/calculate (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'calculate_totals' ),
			'args'     => array(
				'return' => array(
					'default'     => false,
					'description' => __( 'Returns the cart totals once calculated.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				)
			)
		) );
	} // register_routes()

	/**
	 * Calculate Cart Totals.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   array $data
	 * @return  WP_REST_Response
	 */
	public function calculate_totals( $data = array() ) {
		if ( $this->get_cart_contents_count( array( 'return' => 'numeric' ) ) <= 0 ) {
			$message = __( 'No items in cart to calculate totals.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'notice' );

			/**
			 * Filters message about no items in cart to calculate totals.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_no_items_to_calculate_message', $message );

			return new WP_REST_Response( $message, 200 );
		}

		WC()->cart->calculate_totals();

		// Was it requested to return all totals once calculated?
		if ( $data['return'] ) {
			return $this->get_totals( $data );
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

		return new WP_REST_Response( $message, 200 );
	} // END calculate_totals()

} // END class
