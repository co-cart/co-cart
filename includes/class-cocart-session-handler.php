<?php
/**
 * Handle data for the customers cart.
 *
 * Forked from WC_Session_Handler, changed default variables, 
 * database table used, filters and made adjustments to accommodate 
 * support for guest customers as well as registered customers.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/Session
 * @since    2.1.0
 * @version  2.4.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Checks that WooCommerce session class exists first.
if ( ! class_exists('WC_Session') ) {
	return;
}

/**
 * Session handler class.
 */
class CoCart_Session_Handler extends WC_Session {

	/**
	 * Cookie name used for the cart.
	 *
	 * @var string cookie name
	 */
	protected $_cookie;

	/**
	 * Stores cart expiry.
	 *
	 * @var string cart due to expire timestamp
	 */
	protected $_cart_expiring;

	/**
	 * Stores cart due to expire timestamp.
	 *
	 * @var string cart expiration timestamp
	 */
	protected $_cart_expiration;

	/**
	 * True when the cookie exists.
	 *
	 * @var bool Based on whether a cookie exists.
	 */
	protected $_has_cookie = false;

	/**
	 * Table name for cart data.
	 *
	 * @var string Custom cart table name
	 */
	protected $_table;

	/**
	 * Constructor for the session class.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->_cookie = apply_filters( 'cocart_cookie', 'wp_cocart_session_' . COOKIEHASH );
		$this->_table  = $GLOBALS['wpdb']->prefix . 'cocart_carts';
	}

	/**
	 * Init hooks and cart data.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 2.3.0
	 */
	public function init() {
		// Current user ID. If user is NOT logged in then the customer is a guest.
		$current_user_id = strval( get_current_user_id() );

		$this->init_session_cookie( $current_user_id );

		add_action( 'woocommerce_set_cart_cookies', array( $this, 'set_customer_cart_cookie' ), 20 );
		add_action( 'shutdown', array( $this, 'save_cart' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy_cart' ) );

		/**
		 * When a user is logged out, ensure they have a unique nonce by using the customer/cart ID.
		 *
		 * @since   2.1.2
		 * @version 2.3.0
		 */
		if ( CoCart_Helpers::is_rest_api_request() && is_numeric( $current_user_id ) && $current_user_id < 1 ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ) );
		}
	} // END init()

	/**
	 * Setup cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 2.1.7
	 * @param   int $current_user_id
	 */
	public function init_session_cookie( $current_user_id = 0 ) {
		// Get cart cookie... if any.
		$cookie = $this->get_session_cookie();

		// Does a cookie exist?
		if ( $cookie ) {
			// Get cookie details.
			$this->_customer_id     = $cookie[0];
			$this->_cart_expiration = $cookie[1];
			$this->_cart_expiring   = $cookie[2];
			$this->_has_cookie      = true;
		}

		// Check if we requested to load a specific cart.
		if ( isset( $_REQUEST['cart_key'] ) || isset( $_REQUEST['id'] ) ) {
			$cart_id = isset( $_REQUEST['cart_key'] ) ? $_REQUEST['cart_key'] : $_REQUEST['id'];

			// Set customer ID in session.
			$this->_customer_id = $cart_id;
		}

		// Override cookie check to force load the authenticated users cart if switched without logging out first.
		$override_cookie_check = apply_filters( 'cocart_override_cookie_check', false );

		if ( is_numeric( $current_user_id ) && $current_user_id > 0 ) {
			if ( $override_cookie_check || ! $cookie ) {
				$this->_customer_id = $current_user_id;
			}
		}

		// If cart retrieved then update cart.
		if ( $cookie || $this->_customer_id ) {
			$this->_data = $this->get_cart_data();

			// If the user logged in, update cart.
			if ( is_numeric( $current_user_id ) && $current_user_id > 0 && $current_user_id !== $this->_customer_id ) {
				// Destroy old cookie.
				$this->set_customer_cart_cookie( false );

				// Update customer ID details.
				$guest_cart_id      = $this->_customer_id;
				$this->_customer_id = $current_user_id;

				// Save cart data under customers ID number and remove old guest cart.
				$this->save_cart( $guest_cart_id );

				// Save new cookie for cart.
				$this->set_customer_cart_cookie( true );
			}

			// Update cart if its close to expiring.
			if ( time() > $this->_cart_expiring || empty( $this->_cart_expiring ) ) {
				$this->set_cart_expiration();
				$this->update_cart_timestamp( $this->_customer_id, $this->_cart_expiration );
			}
		}
		// New guest customer.
		else {
			$this->set_cart_expiration();
			$this->_customer_id = $this->generate_customer_id();
			$this->_data        = $this->get_cart_data();
		}
	} // END init_session_cookie()

	/**
	 * Is Cookie support enabled?
	 *
	 * Determines if a cookie should manage the cart for guest customers.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_cookie_supported() {
		return apply_filters( 'cocart_cookie_supported', true );
	} // END is_cookie_supported()

	/**
	 * Sets the cart cookie on-demand.
	 *
	 * Warning: Cookies will only be set if this is called before the headers are sent.
	 *
	 * @access public
	 * @param  bool $set Should the cart cookie be set.
	 */
	public function set_customer_cart_cookie( $set = true ) {
		if ( ! $this->is_cookie_supported() ) {
			return;
		}

		if ( $set ) {
			$to_hash           = $this->_customer_id . '|' . $this->_cart_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_customer_id . '||' . $this->_cart_expiration . '||' . $this->_cart_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

			// If no cookie exists then create a new.
			if ( ! isset( $_COOKIE[ $this->_cookie ] ) || $_COOKIE[ $this->_cookie ] !== $cookie_value ) {
				$this->cocart_setcookie( $this->_cookie, $cookie_value, $this->_cart_expiration, $this->use_secure_cookie() );
			}
		} else {
			// If cookies exists, destroy it.
			if ( isset( $_COOKIE[ $this->_cookie ] ) ) {
				$this->cocart_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie() );
				unset( $_COOKIE[ $this->_cookie ] );
			}
		}
	} // END set_customer_cart_cookie()

	/**
	 * Returns the cookie name.
	 *
	 * @access public
	 * @return string
	 */
	public function get_cookie_name() {
		return $this->_cookie;
	} // END get_cookie_name()

	/**
	 * Should the cart cookie be secure?
	 *
	 * @access protected
	 * @return bool
	 */
	protected function use_secure_cookie() {
		return apply_filters( 'cocart_cart_use_secure_cookie', wc_site_is_https() && is_ssl() );
	} // END use_secure_cookie()

	/**
	 * Set a cookie - wrapper for setcookie using WP constants.
	 *
	 * @access public
	 * @param  string  $name     Name of the cookie being set.
	 * @param  string  $value    Value of the cookie.
	 * @param  integer $expire   Expiry of the cookie.
	 * @param  bool    $secure   Whether the cookie should be served only over https.
	 */
	public function cocart_setcookie( $name, $value, $expire = 0, $secure = false ) {
		if ( ! headers_sent() ) {
			setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, false );
		} elseif ( defined( 'WP_DEBUG' ) ) {
			headers_sent( $file, $line );
			trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
		}
	} // END cocart_cookie()

	/**
	 * Return true if the current user has an active cart, i.e. a cookie to retrieve values.
	 *
	 * @access public
	 * @return bool
	 */
	public function has_session() {
		if ( isset( $_COOKIE[ $this->_cookie ] ) ) {
			return true;
		}

		// Current user ID. If value is above zero then user is logged in.
		$current_user_id = strval( get_current_user_id() );
		if ( is_numeric( $current_user_id ) && $current_user_id > 0 ) {
			return true;
		}

		if ( ! empty( $this->_customer_id ) ) {
			return true;
		}

		return false;
	} // END has_session()

	/**
	 * Set cart expiration.
	 *
	 * @access public
	 */
	public function set_cart_expiration() {
		$this->_cart_expiring   = time() + intval( apply_filters( 'cocart_cart_expiring', DAY_IN_SECONDS * 29 ) ); // 29 Days.
		$this->_cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiration', DAY_IN_SECONDS * 30 ) ); // 30 Days.
	} // END set_cart_expiration()

	/**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @access public
	 * @return string
	 */
	public function generate_customer_id() {
		$customer_id = '';

		$current_user_id = strval( get_current_user_id() );
		if ( is_numeric( $current_user_id ) && $current_user_id > 0 ) {
			$customer_id = $current_user_id;
		}

		if ( empty( $customer_id ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';

			$hasher      = new PasswordHash( 8, false );
			$customer_id = apply_filters( 'cocart_customer_id', md5( $hasher->get_random_bytes( 32 ) ), $hasher );
		}

		return $customer_id;
	} // END generate_customer_id()

	/**
	 * Get the cart cookie, if set. Otherwise return false.
	 *
	 * Cart cookies without a customer ID are invalid.
	 *
	 * @access public
	 * @return bool|array
	 */
	public function get_session_cookie() {
		$cookie_value = isset( $_COOKIE[ $this->_cookie ] ) ? wp_unslash( $_COOKIE[ $this->_cookie ] ) : false;

		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}

		$cookie_value = explode( '||', $cookie_value );

		$customer_id     = $cookie_value[0];
		$cart_expiration = $cookie_value[1];
		$cart_expiring   = $cookie_value[2];
		$cookie_hash     = $cookie_value[3];

		if ( empty( $customer_id ) ) {
			return false;
		}

		// Validate hash.
		$to_hash = $customer_id . '|' . $cart_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $customer_id, $cart_expiration, $cart_expiring, $cookie_hash );
	} // END get_session_cookie()

	/**
	 * Get cart data.
	 *
	 * @access public
	 * @return array
	 */
	public function get_cart_data() {
		return $this->has_session() ? (array) $this->get_cart( $this->_customer_id, array() ) : array();
	} // END get_cart_data()

	/**
	 * Gets a cache prefix. This is used in cart names so the entire cache can be invalidated with 1 function call.
	 *
	 * @access private
	 * @return string
	 */
	private function get_cache_prefix() {
		return WC_Cache_Helper::get_cache_prefix( COCART_CART_CACHE_GROUP );
	} // END get_cache_prefix()

	/**
	 * Save cart data and delete previous cart data.
	 *
	 * @access public
	 * @param  int $old_cart_key cart ID before user logs in.
	 * @global $wpdb
	 */
	public function save_cart( $old_cart_key = 0 ) {
		if ( $this->has_session() ) {
			global $wpdb;

			/** 
			 * Set cart to expire after 6 hours if cart is empty.
			 * This helps clear empty carts stored in the database when the cron job is run.
			 */
			if ( empty( $this->_data ) ) {
				$this->_cart_expiration = apply_filters( 'cocart_empty_cart_expiration', HOUR_IN_SECONDS * 6 );
			}

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_expiry`) VALUES (%s, %s, %d)
 					ON DUPLICATE KEY UPDATE `cart_value` = VALUES(`cart_value`), `cart_expiry` = VALUES(`cart_expiry`)",
					$this->_customer_id,
					maybe_serialize( $this->_data ),
					$this->_cart_expiration
				)
			);

			wp_cache_set( $this->get_cache_prefix() . $this->_customer_id, $this->_data, COCART_CART_CACHE_GROUP, $this->_cart_expiration - time() );

			// Customer is now registered so we delete the previous cart as guest to prevent duplication.
			if ( get_current_user_id() != $old_cart_key && ! is_object( get_user_by( 'id', $old_cart_key ) ) ) {
				$this->delete_cart( $old_cart_key );
			}
		}
	} // END save_cart()

	/**
	 * Destroy all cart data.
	 *
	 * @access public
	 */
	public function destroy_cart() {
		$this->delete_cart( $this->_customer_id );
		$this->forget_cart();
	} // END destroy_cart()

	/**
	 * Forget all cart data without destroying it.
	 *
	 * @access public
	 */
	public function forget_cart() {
		$this->cocart_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie() );

		// Empty cart.
		wc_empty_cart();

		$this->_data        = array();
		$this->_customer_id = $this->generate_customer_id();
	} // END forget_cart()

	/**
	 * When a user is logged out, ensure they have a unique nonce by using the customer/cart ID.
	 *
	 * @access public
	 * @since  2.1.2
	 * @param  int $uid User ID.
	 * @return string
	 */
	public function nonce_user_logged_out( $uid ) {
		return $this->has_session() && $this->_customer_id ? $this->_customer_id : $uid;
	} // END nonce_user_logged_out()

	/**
	 * Cleanup cart data from the database and clear caches.
	 *
	 * @access public
	 * @global $wpdb
	 */
	public function cleanup_sessions() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE cart_expiry < %d", time() ) );

		// Invalidate cache group.
		if ( class_exists( 'WC_Cache_Helper' ) ) {
			WC_Cache_Helper::invalidate_cache_group( COCART_CART_CACHE_GROUP );
		}
	} // END cleanup_sessions()

	/**
	 * Returns the cart.
	 *
	 * @access public
	 * @param  string $customer_id Customer ID.
	 * @param  mixed  $default Default cart value.
	 * @global $wpdb
	 * @return string|array
	 */
	public function get_cart( $customer_id, $default = false ) {
		global $wpdb;

		// Try to get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get( $this->get_cache_prefix() . $customer_id, COCART_CART_CACHE_GROUP );

		if ( false === $value ) {
			$value = $wpdb->get_var( $wpdb->prepare( "SELECT cart_value FROM $this->_table WHERE cart_key = %s", $customer_id ) );

			if ( is_null( $value ) ) {
				$value = $default;
			}

			$cache_duration = $this->_cart_expiration - time();
			if ( 0 < $cache_duration ) {
				wp_cache_add( $this->get_cache_prefix() . $customer_id, $value, COCART_CART_CACHE_GROUP, $cache_duration );
			}
		}

		return maybe_unserialize( $value );
	} // END get_cart()

	/**
	 * Create a new cart.
	 *
	 * @access public
	 * @global $wpdb
	 */
	public function create_new_cart() {
		global $wpdb;

		if ( ! empty( $this->_data ) && ! is_numeric( $this->_customer_id ) ) {
			$wpdb->insert( $this->_table,
				array(
					'cart_key'    => $this->_customer_id,
					'cart_value'  => maybe_serialize( $this->_data ),
					'cart_expiry' => $this->_cart_expiration
				),
				array( '%s', '%s', '%d' )
			);
		}
	} // END create_new_cart()

	/**
	 * Update cart.
	 *
	 * @access public
	 * @param  string $customer_id
	 * @global $wpdb
	 */
	public function update_cart( $customer_id ) {
		global $wpdb;

		$wpdb->update( $this->_table,
			array(
				'cart_value' => maybe_serialize( $this->_data ),
				'cart_expiry' => $this->_cart_expiration
			),
			array( 'cart_key' => $customer_id ),
			array( '%d' )
		);
	} // END update_cart()

	/**
	 * Delete the cart from the cache and database.
	 *
	 * @access public
	 * @param  string $customer_id Customer ID.
	 * @global $wpdb
	 */
	public function delete_cart( $customer_id ) {
		global $wpdb;

		// Delete cache
		wp_cache_delete( $this->get_cache_prefix() . $customer_id, COCART_CART_CACHE_GROUP );

		// Delete cart from database.
		$wpdb->delete( $this->_table, array( 'cart_key' => $customer_id ) );
	} // END delete_cart()

	/**
	 * Update the cart expiry timestamp.
	 *
	 * @access public
	 * @param  string $customer_id Customer ID.
	 * @param  int    $timestamp Timestamp to expire the cookie.
	 * @global $wpdb
	 */
	public function update_cart_timestamp( $customer_id, $timestamp ) {
		global $wpdb;

		$wpdb->update( $this->_table, array( 'cart_expiry' => $timestamp ), array( 'cart_key' => $customer_id ), array( '%d' ) );
	} // END update_cart_timestamp()

} // END class
