<?php
/**
 * CoCart Data Exception Class
 *
 * Extends exception to provide additional data.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
 * @version 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data exception class.
 */
class CoCart_Data_Exception extends Exception {

	/**
	 * Sanitized error code.
	 *
	 * @access public
	 * @var    string
	 */
	public $error_code;

	/**
	 * Error extra data.
	 *
	 * @access public
	 * @var    array $additional_data - Additional error data.
	 */
	public $additional_data = array();

	/**
	 * Setup exception.
	 *
	 * @access  public
	 * @since   3.0.0  Introduced.
	 * @since   3.1.0  Passed plugin slug used to identify error logs for.
	 * @version 3.1.0
	 * @param   string $error_code       Machine-readable error code, e.g `cocart_invalid_product_id`.
	 * @param   string $message          User-friendly translated error message, e.g. 'Product ID is invalid'.
	 * @param   int    $http_status_code Proper HTTP status code to respond with, e.g. 400.
	 * @param   array  $additional_data  Extra error data.
	 */
	public function __construct( $error_code, $message, $http_status_code = 400, $additional_data = array() ) {
		$this->error_code      = $error_code;
		$this->additional_data = array_filter( (array) $additional_data );

		$plugin = isset( $this->additional_data['plugin'] ) ? esc_html( $this->additional_data['plugin'] ) : '';

		CoCart_Logger::log( $message, 'error', $plugin );

		parent::__construct( $message, $http_status_code );
	}

	/**
	 * Returns the error code.
	 *
	 * @access public
	 * @return string
	 */
	public function getErrorCode() {
		return $this->error_code;
	}

	/**
	 * Returns additional error data.
	 *
	 * @return array
	 */
	public function getAdditionalData() {
		return $this->additional_data;
	}

}
