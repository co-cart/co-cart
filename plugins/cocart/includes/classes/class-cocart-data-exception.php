<?php
/**
 * Class: CoCart_Data_Exception class.
 *
 * @author  Sébastien Dumont
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

use CoCart\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data exception.
 *
 * Extends exception to provide additional data.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_Data_Exception extends Exception {

	/**
	 * Sanitized error code.
	 *
	 * @access public
	 *
	 * @var string
	 */
	public $error_code;

	/**
	 * Error extra data.
	 *
	 * @access public
	 *
	 * @var array $additional_data - Additional error data.
	 */
	public $additional_data = array();

	/**
	 * Setup exception.
	 *
	 * @see Logger::log()
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Passed plugin slug used to identify error logs for.
	 *
	 * @param string $error_code       Machine-readable error code, e.g `cocart_invalid_product_id`.
	 * @param string $message          User-friendly translated error message, e.g. 'Product ID is invalid'.
	 * @param int    $http_status_code Proper HTTP status code to respond with, e.g. 400.
	 * @param array  $additional_data  Extra error data.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct( $error_code, $message, $http_status_code = 400, $additional_data = array() ) {
		$this->error_code      = $error_code;
		$this->additional_data = array_filter( (array) $additional_data );

		$plugin = isset( $this->additional_data['plugin'] ) ? esc_html( $this->additional_data['plugin'] ) : '';

		Logger::log( $message, 'error', $plugin );

		parent::__construct( $message, $http_status_code );
	} // END __construct()

	/**
	 * Returns the error code.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getErrorCode() {
		return $this->error_code;
	} // END getErrorCode()

	/**
	 * Returns additional error data.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function getAdditionalData() {
		return $this->additional_data;
	} // END getAdditionalData()

} // END class
