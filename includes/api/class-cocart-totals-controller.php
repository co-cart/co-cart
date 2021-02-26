<?php
/**
 * CoCart - Totals controller
 *
 * Handles the request to get the totals of the cart with /cart/totals endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\v2
 * @since    3.0.0
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
class CoCart_Totals_v2_Controller extends CoCart_Totals_Controller {

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
	protected $rest_base = 'cart/totals';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart Totals - cocart/v2/cart/totals (GET)
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
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request - Full details about the request.
	 * @return  WP_REST_Response
	 */
	public static function get_totals( $request = array() ) {
		try {
			$pre_formatted = isset( $request['html'] ) ? $request['html'] : false;

			$controller = new CoCart_Cart_V2_Controller();

			$totals            = array();
			$totals_calculated = false;

			if ( ! empty( $controller->get_cart_instance()->totals ) ) {
				$totals            = $controller->get_cart_instance()->get_totals();
				$totals_calculated = true;
			}

			$ignore_convert = array(
				'shipping_taxes',
				'cart_contents_taxes',
				'fee_taxes',
			);

			if ( ! $totals_calculated ) {
				$message = esc_html__( 'This cart either has no items or was not calculated.', 'cart-rest-api-for-woocommerce' );

				throw new CoCart_Data_Exception( 'cocart_cart_totals_empty', $message, 404 );
			}

			// Was it requested to have the totals preformatted?
			if ( $pre_formatted ) {
				$new_totals = array();

				foreach ( $totals as $type => $total ) {
					if ( in_array( $type, $ignore_convert ) ) {
						$new_totals[ $type ] = $total;
					} else {
						if ( is_string( $total ) ) {
							$new_totals[ $type ] = html_entity_decode( strip_tags( wc_price( $total ) ) );
						} else {
							$new_totals[ $type ] = html_entity_decode( strip_tags( wc_price( strval( $total ) ) ) );
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

} // END class
