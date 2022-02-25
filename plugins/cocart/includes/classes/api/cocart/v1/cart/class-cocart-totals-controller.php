<?php
/**
 * CoCart - Totals controller
 *
 * Handles the request to get the totals of the cart with /totals endpoint.
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
 * REST API Totals controller class.
 *
 * @package CoCart\API
 */
class CoCart_Totals_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'totals';

	/**
	 * Register routes.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 2.7.0
	 */
	public function register_routes() {
		// Get Cart Totals - cocart/v1/totals (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_totals' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'html' => array(
						'required'          => false,
						'default'           => false,
						'description'       => __( 'Returns the totals pre-formatted.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'boolean',
						'validate_callback' => 'rest_validate_request_arg',
					),
				),
			)
		);
	} // register_routes()

	/**
	 * Returns all calculated totals.
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 2.1.2
	 * @param   array $data
	 * @return  WP_REST_Response
	 */
	public static function get_totals( $data = array() ) {
		if ( ! empty( WC()->cart->totals ) ) {
			$totals = WC()->cart->get_totals();
		} else {
			$totals = WC()->session->get( 'cart_totals' );
		}

		$pre_formatted = isset( $data['html'] ) ? $data['html'] : false;

		if ( $pre_formatted ) {
			$new_totals = array();

			$ignore_convert = array(
				'shipping_taxes',
				'cart_contents_taxes',
				'fee_taxes',
			);

			foreach ( $totals as $type => $sum ) {
				if ( in_array( $type, $ignore_convert ) ) {
					$new_totals[ $type ] = $sum;
				} else {
					if ( is_string( $sum ) ) {
						$new_totals[ $type ] = html_entity_decode( strip_tags( wc_price( $sum ) ) );
					} else {
						$new_totals[ $type ] = html_entity_decode( strip_tags( wc_price( strval( $sum ) ) ) );
					}
				}
			}

			$totals = $new_totals;
		}

		return new WP_REST_Response( $totals, 200 );
	} // END get_totals()

} // END class
