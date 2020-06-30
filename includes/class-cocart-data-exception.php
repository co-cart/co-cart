<?php
/**
 * CoCart Data Exception Class
 *
 * Extends exception to provide additional data.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
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
	 * @var    array
	 */
	public $additional_data = array();

	/**
	 * Setup exception.
	 *
	 * @access public
	 * @param  string $code             Machine-readable error code, e.g `cocart_invalid_product_id`.
	 * @param  string $message          User-friendly translated error message, e.g. 'Product ID is invalid'.
	 * @param  int    $http_status_code Proper HTTP status code to respond with, e.g. 400.
	 * @param  array  $data             Extra error data.
	 */
	public function __construct( $error_code, $message, $http_status_code = 400, $additional_data = array() ) {
		$this->error_code      = $error_code;
		$this->additional_data = array_filter( (array) $additional_data );

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
