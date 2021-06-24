<?php
/**
 * Handles default cart extension callback.
 *
 * @author   Sébastien Dumont
 * @category Abstracts
 * @package  CoCart\Abstracts
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class CoCart_Cart_Extension_Callback {

	/**
	 * Extenstion Callback name defined by extending this class.
	 *
	 * @access protected
	 * @var    string
	 */
	protected $name = '';

	/**
	 * Returns the name of the extenstion callback.
	 *
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	} // END get_name()

	/**
	 * Runs the extension callback.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 */
	public function callback( $request ) {
		try {
			throw new CoCart_Data_Exception( 'cocart_no_callback_found', sprintf( __( 'A "callback" function must be registered when extending class "%s"', 'cart-rest-api-for-woocommerce' ), __CLASS__ ), 400 );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END callback()

}