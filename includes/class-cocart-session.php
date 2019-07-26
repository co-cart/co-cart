<?php
/**
 * CoCart REST API session
 *
 * Handles sessions, stores a copy of the cart in the database.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/Session
 * @since    2.1.0
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

		// Generate a new unique ID if the customer is a guest and is adding the first item.
		add_action( 'woocommerce_add_to_cart', array( $this, 'maybe_generate_unique_id' ), 0 );

		// Update the saved cart data.
		add_action( 'woocommerce_add_to_cart', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_cart_item_set_quantity', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_applied_coupon', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_removed_coupon', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'maybe_save_cart_data' ), 99 );
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'maybe_save_cart_data' ), 99 );

		// Clears the cart data.
		add_action( 'woocommerce_cart_item_removed', array( $this, 'maybe_clear_cart' ), 99 );
		add_action( 'woocommerce_cart_emptied', array( $this, 'maybe_clear_cart' ), 99 );
	} // END __construct()

	/**
	 * Check if we need to create a unique ID for the user in session.
	 *
	 * @access public
	 */
	public function maybe_generate_unique_id() {
		if ( ! isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$value = $this->generate_customer_id();

			if ( ! empty( $value ) ) {
				// Set cookie with unique ID.
				wc_setcookie( $this->cookie_name, $value );

				// Temporarily store the unique id generated in session.
				WC()->session->set( 'cocart_id', $value );

				// Create a new option in the database with the unique ID as the option name.
				add_option( 'cocart_' . $value, '1' );
			}
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
		$cart_saved = get_option( 'cocart_' . $cart_id );

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

		update_option( 'cocart_' . $cart_id, $cart );
	} // END save_cart_data()

	/**
	 * If the cart ID exists then save the cart data.
	 *
	 * @access public
	 */
	public function maybe_save_cart_data() {
		$cart_id = WC()->session->get( 'cocart_id' );

		if ( isset( $cart_id ) ) {
			$cart_saved = $this->is_cart_saved( $cart_id );

			if ( $cart_saved ) {
				$this->save_cart_data( $cart_id );

				// Now destroy ID stored in session as we will check the cookie from now on.
				WC()->session->set( 'cocart_id', null );
			}
		}

		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$cart_id = $_COOKIE[ $this->cookie_name ];

			$cart_saved = $this->is_cart_saved( $cart_id );

			if ( $cart_saved ) {
				$this->save_cart_data( $cart_id );
			}
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
					delete_option( 'cocart_' . $cart_id );

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