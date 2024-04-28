<?php
/**
 * Handles default cart extension callback.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Abstracts
 * @since   3.1.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class CoCart_Cart_Extension_Callback {

	/**
	 * Extension Callback name defined by extending this class.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Returns the name of the extension callback.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	} // END get_name()

	/**
	 * Runs the extension callback.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.1.0  Introduced.
	 * @since 3.13.0 Added the cart controller as a parameter.
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param object          $controller The cart controller.
	 */
	public function callback( $request, $controller ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		try {
			throw new CoCart_Data_Exception(
				'cocart_no_callback_found',
				sprintf(
					/* translators: %s: Class name */
					esc_html__( 'A "callback" function must be registered when extending class "%s"', 'cart-rest-api-for-woocommerce' ),
					__CLASS__
				),
				400
			);
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END callback()
}
