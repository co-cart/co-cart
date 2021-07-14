<?php
/**
 * Handle data for the current customers session.
 *
 * Forked from WC_Session and added a few tweaks to support our features.
 *
 * @link https://github.com/woocommerce/woocommerce/blob/master/includes/abstracts/abstract-wc-session.php
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Abstracts
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Session
 */
abstract class CoCart_Session {

	/**
	 * Customer ID.
	 *
	 * @access protected
	 * @var    string $_customer_id Customer ID.
	 */
	protected $_customer_id;

	/**
	 * Session Data.
	 *
	 * @access protected
	 * @var    array $_data Data array.
	 */
	protected $_data = array();

	/**
	 * Dirty when the session needs saving.
	 *
	 * @access protected
	 * @var    bool $_dirty When something changes
	 */
	protected $_dirty = false;

	/**
	 * Stores cart hash.
	 *
	 * @access protected
	 * @var    string $_cart_hash cart hash
	 */
	protected $_cart_hash;

	/**
	 * Init hooks and session data. Extended by child classes.
	 *
	 * @access public
	 */
	public function init() {}

	/**
	 * Cleanup session data. Extended by child classes.
	 *
	 * @access public
	 */
	public function cleanup_sessions() {}

	/**
	 * Magic get method.
	 *
	 * @access public
	 * @param  mixed $key Key to get.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic set method.
	 *
	 * @access public
	 * @param  mixed $key Key to set.
	 * @param  mixed $value Value to set.
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Magic isset method.
	 *
	 * @access public
	 * @param  mixed $key Key to check.
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}

	/**
	 * Magic unset method.
	 *
	 * @access public
	 * @param  mixed $key Key to unset.
	 */
	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_dirty = true;
		}
	}

	/**
	 * Get a session variable.
	 *
	 * @access public
	 * @param  string $key Key to get.
	 * @param  mixed  $default used if the session variable isn't set.
	 * @return array|string value of session variable
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @access public
	 * @param  string $key Key to set.
	 * @param  mixed  $value Value to set.
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty                        = true;
		}
	}

	/**
	 * Get customer ID.
	 *
	 * @access public
	 * @return string
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}

	/**
	 * Set customer ID.
	 *
	 * @access public
	 * @param  string $customer_id Customer ID.
	 */
	public function set_customer_id( $customer_id ) {
		$this->_customer_id = $customer_id;
	}

	/**
	 * Get session data
	 *
	 * @access public
	 * @return array
	 */
	public function get_data() {
		return $this->_data;
	}

	/**
	 * Get cart hash
	 *
	 * @access public
	 * @return string
	 */
	public function get_cart_hash() {
		return $this->_cart_hash;
	}

}
