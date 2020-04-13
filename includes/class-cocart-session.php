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
	public function init() {
		$this->cookie_name = 'cocart_cart_key';

		// Generate a new unique key and store it in a cookie if adding the first item.
		add_action( 'woocommerce_add_to_cart', array( $this, 'maybe_generate_unique_key' ), 0 );

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

		// Loads a cart in session if still valid.
		add_action( 'wp_loaded', array( $this, 'load_cart_action' ), 20 );
	} // END __construct()

	/**
	 * Check if we need to create a unique key for the user in session.
	 *
	 * @access public
	 */
	public function maybe_generate_unique_key() {
		$cart_key = WC()->session->get( 'cocart_key' );

		if ( ! isset( $cart_key ) ) {
			$value = apply_filters( 'cocart_customer_id', $this->generate_customer_id() );

			if ( ! empty( $value ) ) {
				if ( apply_filters( 'cocart_session_set_cookie', false ) ) {
				// Set cookie with unique key.
				wc_setcookie( $this->cookie_name, $value );
				}

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
		$cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiration', DAY_IN_SECONDS * 30 ) ); // Default: 30 Days.

		// Serialize cart data if not already.
		if ( ! is_serialized( $cart ) ) {
			$cart = maybe_serialize( $cart );
		}

		// If cart is not saved, new data is inserted. If cart does already exist then it will update the data.
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_expiry`) VALUES (%s, %s, %d)
				 ON DUPLICATE KEY UPDATE `cart_value` = VALUES(`cart_value`), `cart_expiry` = VALUES(`cart_expiry`)",
				$cart_key,
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

			if ( ! $cart_saved ) {
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
	 * @static
	 * @global $wpdb
	 * @param  string $cart_key
	 * @return array
	 */
	public static function get_cart( $cart_key ) {
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

	/**
	 * Load cart action.
	 *
	 * Loads a cart in session if still valid and overrides the current cart. 
	 * Unless specified not to override, the carts will merge the current cart 
	 * and the loaded cart items together.
	 *
	 * @todo 1. Load cart items and update session.
	 * @todo 2. Apply cart discounts and validate.
	 * @todo 3. Calculate the cart totals.
	 *
	 * @access public
	 * @static
	 */
	public static function load_cart_action() {
		// If we did not request to load a cart then just return.
		if ( ! isset( $_REQUEST['cocart-load-cart'] ) ) {
			return;
		}

		$cart_key        = trim( wp_unslash( $_REQUEST['cocart-load-cart'] ) );
		$override_cart   = true;  // Override the cart by default.
		$notify_customer = false; // Don't notify the customer by default.
		$redirect        = false; // Don't safely redirect the customer to the cart after loading by default.

		wc_nocache_headers();

		// Check if we are keeping the cart currently set via the web.
		if ( ! empty( $_REQUEST['keep-cart'] ) && is_bool( $_REQUEST['keep-cart'] ) !== true ) {
			$override_cart = false;
		}

		// Check if we are notifying the customer via the web.
		if ( ! empty( $_REQUEST['notify'] ) && is_bool( $_REQUEST['notify'] ) !== true ) {
			$notify_customer = true;
		}

		// Check if we are safely redirecting the customer to the cart via the web.
		if ( ! empty( $_REQUEST['redirect'] ) && is_bool( $_REQUEST['redirect'] ) !== true ) {
			$redirect = true;
		}

		// Check if the cart key is not a valid string.
		if ( is_numeric( $cart_key ) ) {
			CoCart_Logger::log( sprintf( __( 'Cart key %s was not valid!', 'cart-rest-api-for-woocommerce' ), $cart_key ), 'error' );

			if ( $notify_customer ) {
				wc_add_notice( __( 'This cart key is not valid!', 'cart-rest-api-for-woocommerce' ), 'error' );
			}

			return;
		}

		// Check if the cart session exists.
		if ( ! $this->is_cart_saved( $cart_key ) ) {
			CoCart_Logger::log( sprintf( __( 'Unable to find cart for: %s', 'cart-rest-api-for-woocommerce' ), $cart_key ), 'info' );

			if ( $notify_customer ) {
				wc_add_notice( __( 'Sorry but this cart in session has expired!', 'cart-rest-api-for-woocommerce' ), 'error' );
			}

			return;
		}

		// Get the cart in the database.
		$stored_cart = (array) $this->get_cart( $cart_key );

		// Get the cart currently in session.
		$cart_in_session = isset( WC()->cart ) ? WC()->cart->get_cart() : WC()->session->cart;

		$new_cart = array();

		// Check if we are overriding the cart currently in session via the web.
		if ( $override_cart ) {
			// Only clear the cart if it's not already empty.
			if ( ! WC()->cart->is_empty() ) {
				WC()->cart->empty_cart( true );
			}

			$new_cart['cart'] = $stored_cart->cart_contents;
			$new_cart['applied_coupons'] = $stored_cart->applied_coupons;
			$new_cart['coupon_discount_totals'] = $stored_cart->coupon_discount_totals;
			$new_cart['coupon_discount_tax_totals'] = $stored_cart->coupon_discount_tax_totals;
			$new_cart['removed_cart_contents'] = $stored_cart->removed_cart_contents;

			do_action( 'cocart_override_cart_in_session', $new_cart, $stored_cart );
		} else {
			$new_cart['cart'] = array_merge( $stored_cart->cart_contents, $cart_in_session );
			$new_cart['applied_coupons'] = array_merge( $stored_cart->applied_coupons, WC()->cart->get_applied_coupons() );
			$new_cart['coupon_discount_totals'] = array_merge( $stored_cart->coupon_discount_totals, WC()->cart->get_coupon_discount_totals() );
			$new_cart['coupon_discount_tax_totals'] = array_merge( $stored_cart->coupon_discount_tax_totals, WC()->cart->get_coupon_discount_tax_totals() );
			$new_cart['removed_cart_contents'] = array_merge( $stored_cart->removed_cart_contents, WC()->cart->get_removed_cart_contents() );

			do_action( 'cocart_load_cart_in_session', $new_cart, $stored_cart, $cart_in_session );
		}

		//print_r( $stored_cart['cart_contents'] );
		//wp_die();

		// Sets the php session data for the loaded cart.
		WC()->session->set( 'cart', $new_cart['cart'] );
		WC()->session->set( 'applied_coupons', $new_cart['applied_coupons'] );
		WC()->session->set( 'coupon_discount_totals', $new_cart['coupon_discount_totals'] );
		WC()->session->set( 'coupon_discount_tax_totals', $new_cart['coupon_discount_tax_totals'] );
		WC()->session->set( 'removed_cart_contents', $new_cart['removed_cart_contents'] );

		// Recalculate the cart totals.
		WC()->cart->calculate_totals();

		// If true, notify the customer that there cart has transferred over via the web.
		if ( $notify_customer ) {
			wc_add_notice( sprintf( __( 'Your 🛒 cart has been transferred over. You may %1$scontinue shopping%3$s or %2$scheckout%3$s.', 'cart-rest-api-for-woocommerce' ), '<a href="shop">', '<a href="checkout">', '</a>' ), 'notice' );
		}

		// If true, redirect the customer to the cart safely.
		if ( $redirect ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	} // END load_cart_action()

} // END class
