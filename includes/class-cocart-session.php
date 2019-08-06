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
		$this->cookie_name = 'cocart_cart_key';

		// Generate a new unique key and store it in a cookie if adding the first item.
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

		// Cleans up carts from the database that have expired.
		add_action( 'cocart_cleanup_carts', array( $this, 'cleanup_carts' ) );
	} // END __construct()

	/**
	 * Check if we need to create a unique key for the user in session.
	 *
	 * @access public
	 */
	public function maybe_generate_unique_id() {
		if ( ! isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$value = apply_filters( 'cocart_customer_id', $this->generate_customer_id() );

			if ( ! empty( $value ) ) {
				// Set cookie with unique key.
				wc_setcookie( $this->cookie_name, $value );

				// Temporarily store the unique id generated in session.
				WC()->session->set( 'cocart_key', $value );
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
	 * Returns true or false if the cart key is saved in the database.
	 *
	 * @access public
	 * @param  string $cart_key
	 * @return bool
	 */
	public function is_cart_saved( $cart_key ) {
		$cart_saved = $this->get_cart( $cart_key );

		if ( ! empty( $cart_saved ) ) {
			return true;
		}

		return false;
	} // END maybe_save_cart_data()

	/**
	 * Save cart data under the cart key provided.
	 *
	 * @access public
	 * @global $wpdb
	 * @param  string $cart_key
	 */
	public function save_cart_data( $cart_key ) {
		global $wpdb;

		$cart = WC()->cart;

		// Sets the expiration time for the cart.
		$cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiration', 60 * 60 * 48 ) ); // Default: 48 Hours.

		// Serialize cart data if not already.
		if ( ! is_serialized( $cart ) ) {
			$cart = maybe_serialize( $cart );
		}

		// If cart is not saved, new data is inserted. If cart does already exist then it will update the data.
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_expiry`) VALUES (%s, %s, %d)
				 ON DUPLICATE KEY UPDATE `cart_value` = VALUES(`cart_value`), `cart_expiry` = VALUES(`cart_expiry`)",
				$this->_customer_id,
				$cart,
				$cart_expiration
			)
		);
	} // END save_cart_data()

	/**
	 * If the cart key exists then save the cart data.
	 *
	 * @access public
	 */
	public function maybe_save_cart_data() {
		$cart_key = WC()->session->get( 'cocart_key' );

		if ( isset( $cart_key ) ) {
			$cart_saved = $this->is_cart_saved( $cart_key );

			if ( $cart_saved ) {
				$this->save_cart_data( $cart_key );

				// Now destroy key stored in session as we will check the cookie from now on.
				WC()->session->set( 'cocart_key', null );
			}
		}

		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$cart_key = $_COOKIE[ $this->cookie_name ];

			$cart_saved = $this->is_cart_saved( $cart_key );

			if ( $cart_saved ) {
				$this->save_cart_data( $cart_key );
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
			$cart_key = $_COOKIE[ $this->cookie_name ];

			$cart_saved = $this->is_cart_saved( $cart_key );

			if ( $cart_saved ) {
				if ( $count < 1 ) {
					$this->delete_cart( $cart_key );

					wc_setcookie( $this->cookie_name, 0, time() - HOUR_IN_SECONDS );
					unset( $_COOKIE[ $this->cookie_name ] );
				}
				else {
					$this->save_cart_data( $cart_key );
				}
			}
		}
	} // END maybe_clear_cart()

	/**
	 * Gets a cart stored in the database.
	 *
	 * @access public
	 * @global $wpdb
	 * @param  string $cart_key
	 * @return array
	 */
	public function get_cart( $cart_key ) {
		global $wpdb;

		$value = $wpdb->get_var( $wpdb->prepare( "SELECT cart_value FROM {$wpdb->prefix}cocart_carts WHERE cart_key = %s", $cart_key ) );

		// If no cart data is found then return false.
		if ( is_null( $value ) ) {
			$value = false;
		}

		// Un-Serialize cart data if not already.
		if ( is_serialized( $value ) ) {
			$value = maybe_unserialize( $value );
		}

		return $value;
	} // END get_cart()

	/**
	 * Deletes a cart stored in the database.
	 *
	 * @access public
	 * @global $wpdb
	 * @param  string $cart_key
	 */
	public function delete_cart( $cart_key ) {
		global $wpdb;

		$value = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cocart_carts WHERE cart_key = %s", $cart_key ) );

		// If cart data is found then proceed to delete the cart.
		if ( ! is_null( $value ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cocart_carts WHERE cart_key = %s", $cart_key ) );
		}
	} // END delete_cart()

	/**
	 * Cleans up carts from the database that have expired.
	 *
	 * @access public
	 * @global $wpdb
	 */
	public function cleanup_carts() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cocart_carts WHERE cart_expiry < %d", time() ) );
	} // END cleanup_carts()

	/**
	 * Clears all carts from the database.
	 *
	 * @access public
	 * @static
	 * @global $wpdb
	 */
	public static function clear_carts() {
		global $wpdb;

		$wpdb->query( "TRUNCATE {$wpdb->prefix}cocart_carts" );
	} // END clear_cart()

} // END class

return new CoCart_API_Session();