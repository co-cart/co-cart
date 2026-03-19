<?php
/**
 * REST API: CoCart_Totals_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v1
 * @since   2.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the cart totals (API v1).
 *
 * Handles the request to get the totals of the cart with /totals endpoint.
 *
 * @since 2.1.0 Introduced.
 *
 * @see CoCart_API_Controller
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
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 5.0.0 Added schema support for the response.
	 */
	public function register_routes() {
		// Get Cart Totals - cocart/v1/totals (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_totals' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'html' => array(
							'required'          => false,
							'default'           => false,
							'description'       => __( 'Returns the totals pre-formatted.', 'cocart-core' ),
							'type'              => 'boolean',
							'sanitize_callback' => 'rest_sanitize_boolean',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // END register_routes()

	/**
	 * Returns all calculated totals.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response Response data.
	 */
	public static function get_totals( $request = array() ) {
		if ( ! empty( WC()->cart->totals ) ) {
			$totals = WC()->cart->get_totals();
		} else {
			$totals = WC()->session->get( 'cart_totals' );
		}

		$pre_formatted = isset( $request['html'] ) ? $request['html'] : false;

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
				} elseif ( is_string( $sum ) ) {
					$new_totals[ $type ] = html_entity_decode( wp_strip_all_tags( wc_price( $sum ) ) );
				} else {
					$new_totals[ $type ] = html_entity_decode( wp_strip_all_tags( wc_price( strval( $sum ) ) ) );
				}
			}

			$totals = $new_totals;
		}

		return new WP_REST_Response( $totals, 200 );
	} // END get_totals()

	/**
	 * Get the cart totals schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cart_totals',
			'type'       => 'object',
			'properties' => array(
				'subtotal'            => array(
					'description' => __( 'Subtotal of all items in the cart. Returns formatted price if html=true.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'subtotal_tax'        => array(
					'description' => __( 'Subtotal tax amount. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'shipping_total'      => array(
					'description' => __( 'Shipping total cost. Returns formatted price if html=true.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'shipping_tax'        => array(
					'description' => __( 'Shipping tax amount. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'shipping_taxes'      => array(
					'description' => __( 'Array of shipping tax rates.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type' => 'number',
					),
				),
				'discount_total'      => array(
					'description' => __( 'Total discount amount. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'discount_tax'        => array(
					'description' => __( 'Discount tax amount. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_contents_total' => array(
					'description' => __( 'Cart contents total. Returns formatted price if html=true.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_contents_tax'   => array(
					'description' => __( 'Cart contents tax. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_contents_taxes' => array(
					'description' => __( 'Array of cart content tax rates.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type' => 'number',
					),
				),
				'fee_total'           => array(
					'description' => __( 'Fee total amount. Returns formatted price if html=true.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'fee_tax'             => array(
					'description' => __( 'Fee tax amount. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'fee_taxes'           => array(
					'description' => __( 'Array of fee tax rates.', 'cocart-core' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type' => 'number',
					),
				),
				'total'               => array(
					'description' => __( 'Total amount of the cart including tax. Returns formatted price if html=true.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'total_tax'           => array(
					'description' => __( 'Total tax amount. Returns formatted price if html=true.', 'cocart-core' ),
					'oneOf'       => array(
						array( 'type' => 'number' ),
						array( 'type' => 'string' ),
					),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $schema;
	} // END get_item_schema()
} // END class
