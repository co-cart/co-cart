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

namespace CoCart\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Session
 */
abstract class Session extends \WC_Session {

	/**
	 * Stores cart key.
	 *
	 * @access protected
	 *
	 * @var string $_cart_key cart key
	 */
	protected $_cart_key;

	/**
	 * Stores user ID.
	 *
	 * @access protected
	 *
	 * @var int $_cart_user_id user ID
	 */
	protected $_cart_user_id;

	/**
	 * Stores customer ID.
	 *
	 * @access protected
	 *
	 * @var int $_customer_id customer ID
	 */
	protected $_customer_id;

	/**
	 * Stores cart expiry.
	 *
	 * @access protected
	 *
	 * @var string cart due to expire timestamp
	 */
	protected $_cart_expiring;

	/**
	 * Stores cart due to expire timestamp.
	 *
	 * @access protected
	 *
	 * @var string cart expiration timestamp
	 */
	protected $_cart_expiration;

	/**
	 * Stores cart source.
	 *
	 * @access protected
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @var string cart source
	 */
	protected $_cart_source;

	/**
	 * Stores cart hash.
	 *
	 * @access protected
	 *
	 * @var string $_cart_hash cart hash
	 */
	protected $_cart_hash;

	/**
	 * Get cart key.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_key() {
		return $this->_cart_key;
	}

	/**
	 * Set cart key.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $cart_key Cart key.
	 */
	public function set_cart_key( $cart_key ) {
		$this->_cart_key = $cart_key;
	}

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

}
