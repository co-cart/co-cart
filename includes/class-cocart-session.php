<?php
/**
 * CoCart REST API session
 *
 * Handles sessions, stores a copy of the cart in the database.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/Session
 * @since    2.0.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API session class.
 *
 * @package CoCart REST API/Session
 */
class CoCart_API_Session {

	private $cookie_name;

	/**
	 * Setup class.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->cookie_name = 'cocart_cart_id';

		add_action( 'woocommerce_add_to_cart', array( $this, 'maybe_generate_unique_id' ), 0 );
		add_action( 'woocommerce_add_to_cart', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_cart_item_set_quantity', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'maybe_clear_cart' ), 99 );
	} // END __construct()

	/**
	 * Check if we need to create a unique ID for the user in session.
	 *
	 * @access public
	 */
	public function maybe_generate_unique_id() {
		$value = $this->generate_customer_id();

		if ( ! isset( $_COOKIE[ $this->cookie_name ] ) ) {
			// Set cookie with unique ID.
			wc_setcookie( $this->cookie_name, $value );

			// Create a new option in the database with the unique ID as the option name.
			add_option( $value, '1' );
		}
	} // END maybe_generate_unique_id()

	/**
	 * Generate a unique ID for guest customers.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return string
	 */
	public function generate_customer_id() {
		$customer_id = '';

		if ( ! is_user_logged_in() ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';

			$hasher      = new PasswordHash( 8, false );
			$customer_id = md5( $hasher->get_random_bytes( 32 ) );
		}

		return $customer_id;
	} // END generate_customer_id()

	/**
	 * Returns true or false if the cart ID is saved in the database.
	 *
	 * @access public
	 * @param  string $cart_id
	 * @return bool
	 */
	public function is_cart_saved( $cart_id ) {
		$cart_saved = get_option( $cart_id );

		if ( ! empty( $cart_saved ) ) {
			return true;
		}

		return false;
	} // END maybe_save_cart_data()

	/**
	 * Save cart data under the cart ID provided.
	 *
	 * @access public
	 */
	public function save_cart_data( $cart_id ) {
		$cart = WC()->cart;

		update_option( $cart_id, $cart );
	} // END save_cart_data()

	/**
	 * If the cart ID exists then save the cart data.
	 *
	 * @access public
	 */
	public function maybe_save_cart_data() {
		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$cart_id = $_COOKIE[ $this->cookie_name ];

			$cart_saved = $this->is_cart_saved( $cart_id );

			if ( $cart_saved ) {
				$this->save_cart_data( $cart_id );
			}
		}
		else {
			$this->maybe_generate_unique_id();
			$this->maybe_save_cart_data();
		}
	} // END maybe_save_cart_data()

	/**
	 * Checks if the cart has any items before deciding 
	 * to delete cart data or update it.
	 *
	 * @access public
	 */
	public function maybe_clear_cart() {
		$count = WC()->cart->get_cart_contents_count();

		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$cart_id = $_COOKIE[ $this->cookie_name ];

			$cart_saved = $this->is_cart_saved( $cart_id );

			if ( $cart_saved ) {
				if ( $count < 1 ) {
					delete_option( $cart_id );

					wc_setcookie( $this->cookie_name, 0, time() - HOUR_IN_SECONDS );
					unset( $_COOKIE[ $this->cookie_name ] );
				}
				else {
					$this->save_cart_data( $cart_id );
				}
			}
		}
	} // END maybe_clear_cart()

} // END class

return new CoCart_API_Session();