<?php
/**
 * Handles data for the cart session.
 *
 * Extends WC_Session and added a few tweaks to support our features.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Abstracts
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Session
 */
abstract class CoCart_Session extends WC_Session {

	/**
	 * Stores cart expiry.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @var string Cart due to expire timestamp.
	 */
	protected $_cart_expiring;

	/**
	 * Stores cart due to expire timestamp.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @var string Cart expiration timestamp.
	 */
	protected $_cart_expiration;

	/**
	 * Stores cart hash.
	 *
	 * @access protected
	 *
	 * @var string $_cart_hash cart hash
	 */
	protected $_cart_hash;

	/**
	 * Get customer ID.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}

	/**
	 * Set customer ID.
	 *
	 * @access public
	 *
	 * @param string $customer_id Customer ID.
	 */
	public function set_customer_id( $customer_id ) {
		$this->_customer_id = $customer_id;
	}

	/**
	 * Get cart is expiring.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_is_expiring() {
		return $this->_cart_expiring;
	}

	/**
	 * Get carts expiration.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_carts_expiration() {
		return $this->_cart_expiration;
	}

	/**
	 * Get session data
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->_data;
	}

	/**
	 * Get cart hash
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_cart_hash() {
		return $this->_cart_hash;
	}
} // END class
