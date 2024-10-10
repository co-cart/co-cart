<?php
/**
 * Class: CoCart_Session_Handler.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles session data for the cart.
 *
 * Our session handler extends "WC_Session_Handler" class and accommodates the
 * required support for handling a customers cart session via the REST API
 * for a true headless experience.
 *
 * @since 2.1.0 Introduced.
 */
class CoCart_Session_Handler extends WC_Session_Handler {

	/**
	 * Stores cart expiry.
	 *
	 * @access protected
	 *
	 * @var string Cart due to expire timestamp.
	 */
	protected $cart_expiring;

	/**
	 * Stores cart due to expire timestamp.
	 *
	 * @access protected
	 *
	 * @var string Cart expiration timestamp.
	 */
	protected $cart_expiration;

	/**
	 * Stores cart source.
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @var string Cart source.
	 */
	protected $cart_source;

	/**
	 * Stores cart hash.
	 *
	 * @access protected
	 *
	 * @var string $cart_hash Cart hash.
	 */
	protected $cart_hash;

	/**
	 * Constructor for the session class.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		parent::__construct();

		// Override table used for sessions.
		$this->_table = $GLOBALS['wpdb']->prefix . 'cocart_carts';
	}

	/**
	 * Init hooks and cart data.
	 *
	 * @uses CoCart::is_rest_api_request()
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.2.0 Rest requests don't require the use of cookies.
	 */
	public function init() {
		// Load the session based on native or decoupled request.
		if ( CoCart::is_rest_api_request() ) {
			$this->cart_source = 'cocart';

			$this->init_session_cocart();
			$this->set_cart_hash();

			add_action( 'shutdown', array( $this, 'save_data' ), 20 );
			add_action( 'wp_logout', array( $this, 'destroy_cart' ) );
		} else {
			$this->cart_source = 'woocommerce';
			parent::init();
		}
	} // END init()

	/**
	 * Get requested cart.
	 *
	 * Returns the cart key requested from parameters or via header.
	 *
	 * @access public
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @return string Cart key.
	 */
	public function get_requested_cart() {
		$cart_key = '';

		// Are we requesting via url parameter?
		if ( isset( $_REQUEST['cart_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$cart_key = (string) trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Are we requesting via custom header?
		if ( ! empty( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$cart_key = (string) trim( sanitize_key( wp_unslash( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Filter allows the cart key to be overridden.
		 *
		 * Developer Note: Really only here so I don't have to create
		 * a new session handler to inject a customer ID with the POS Support Add-on.
		 *
		 * @since 4.2.0 Introduced.
		 *
		 * @ignore Function ignored when parsed into Code Reference.
		 */
		return apply_filters( 'cocart_requested_cart_key', $cart_key );
	} // END get_requested_cart()

	/**
	 * Setup cart session.
	 *
	 * Cart session is decoupled without the use of a cookie.
	 *
	 * Supports customers guest and registered. It also allows
	 * administrators to create a cart session and associate a
	 * registered customer.
	 *
	 * @access public
	 *
	 * @since 4.2.0 Introduced.
	 */
	public function init_session_cocart() {
		// Current user ID. If user is NOT logged in then the customer is a guest.
		$current_user_id = 0;

		if ( is_user_logged_in() ) {
			$current_user_id = strval( get_current_user_id() );
		}

		$this->_customer_id = $this->get_requested_cart();

		// Get cart session requested.
		if ( ! empty( $this->_customer_id ) ) {
			// Get cart.
			$this->_data = $this->get_session_data();

			// If the user logs in, and there is a requested cart that is not a customer then update session configuration.
			if ( is_user_logged_in() && ! empty( $this->_customer_id ) && ! $this->is_user_customer( $this->_customer_id ) && $current_user_id !== $this->_customer_id ) {
				$guest_session_id   = $this->_customer_id;
				$this->_customer_id = $current_user_id;
				$this->save_data( $guest_session_id );
			}

			// Update cart if its close to expiring.
			if ( time() > $this->cart_expiring || empty( $this->cart_expiring ) ) {
				$this->set_cart_expiration();
				$this->update_cart_timestamp( $this->_customer_id, $this->cart_expiration );
			}
		} else {
			// New cart session created or authenticated user.
			$this->set_cart_expiration();
			$this->_customer_id = 0 === $current_user_id ? $this->generate_key() : $current_user_id;
			$this->_data        = $this->get_session_data();
		}
	} // END init_session_cocart()

	/**
	 * Detect if the user is a customer.
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool Returns true if user is a customer, otherwise false.
	 */
	public function is_user_customer( $user_id ) {
		if ( ! is_numeric( $user_id ) || 0 === $user_id ) {
			return false;
		}

		$current_user = get_userdata( $user_id );

		if ( ! empty( $current_user ) ) {
			$user_roles = $current_user->roles;

			if ( in_array( 'customer', $user_roles, true ) ) {
				return true;
			}
		}

		return false;
	} // END is_user_customer()

	/**
	 * Return true if the current customer has an active cart.
	 *
	 * Either a cookie, a user ID or a cart key to retrieve values.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function has_session() {
		// If we are loading a session via REST API then identify cart key.
		if ( ! empty( $this->_customer_id ) && CoCart::is_rest_api_request() ) {
			return true;
		}

		if ( parent::has_session() ) {
			return true;
		}

		return false;
	} // END has_session()

	/**
	 * Set cart expiration.
	 *
	 * This session expiration is used for the REST API and is set for 7 days by default.
	 *
	 * @access public
	 */
	public function set_cart_expiration() {
		/**
		 * Filter allows you to change the amount of time before the cart starts to expire.
		 *
		 * Default is (DAY_IN_SECONDS * 6) = 6 Days
		 *
		 * @since 2.1.0 Introduced.
		 */
		$this->cart_expiring = time() + intval( apply_filters( 'cocart_cart_expiring', DAY_IN_SECONDS * 6 ) );
		/**
		 * Filter allows you to change the amount of time before the cart has expired.
		 *
		 * Default is (DAY_IN_SECONDS * 7) = 7 Days
		 *
		 * @since 2.1.0 Introduced.
		 */
		$this->cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiration', DAY_IN_SECONDS * 7 ) );
	} // END set_cart_expiration()

	/**
	 * Generate a unique key.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @access public
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @return string A unique key.
	 */
	public function generate_key() {
		require_once ABSPATH . 'wp-includes/class-phpass.php';

		$hasher        = new \PasswordHash( 8, false );
		$generated_key = apply_filters( 'cocart_generate_key', substr( md5( $hasher->get_random_bytes( 32 ) ), 2 ) );

		return $generated_key;
	} // END generate_key()

	/**
	 * Gets a cache prefix.
	 *
	 * This is used in cart names so the entire cache can be invalidated with 1 function call.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cache_prefix() {
		return WC_Cache_Helper::get_cache_prefix( COCART_CART_CACHE_GROUP );
	} // END get_cache_prefix()

	/**
	 * Save data and delete guest session.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @param int $old_cart_key Cart key used before.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function save_data( $old_cart_key = 0 ) {
		if ( $this->has_session() ) {
			global $wpdb;

			/**
			 * Filter is used to set an empty cart expiration.
			 *
			 * @deprecated 2.7.2 No replacement.
			 */
			cocart_do_deprecated_filter(
				'cocart_empty_cart_expiration',
				'2.7.2',
				null,
				sprintf(
					/* translators: %s: Filter name */
					__( '%s is no longer used.', 'cart-rest-api-for-woocommerce' ),
					'cocart_empty_cart_expiration'
				)
			);

			// Check the data exists before continuing.
			if ( ! $this->_data || empty( $this->_data ) || is_null( $this->_data ) ) {
				return true;
			}

			// Check the source to determine cart expiration to utilize.
			if ( $this->cart_source === 'cocart' ) {
				$cart_expiration = (int) $this->cart_expiration;
			} else {
				$cart_expiration = (int) $this->_session_expiration;
			}

			/**
			 * Filter source of cart.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string $cart_source
			 */
			$cart_source = apply_filters( 'cocart_cart_source', $this->cart_source );

			/**
			 * Set the cart hash.
			 *
			 * @since 3.0.0 Introduced.
			 */
			$this->set_cart_hash();

			// Save or update cart data.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_created`, `cart_expiry`, `cart_source`, `cart_hash`) VALUES (%s, %s, %d, %d, %s, %s)
 					ON DUPLICATE KEY UPDATE `cart_value` = VALUES(`cart_value`), `cart_expiry` = VALUES(`cart_expiry`), `cart_hash` = VALUES(`cart_hash`)",
					$this->_customer_id,
					maybe_serialize( $this->_data ),
					time(),
					$cart_expiration,
					$cart_source,
					$this->cart_hash
				)
			);

			wp_cache_set( $this->get_cache_prefix() . $this->_customer_id, $this->_data, COCART_CART_CACHE_GROUP, $cart_expiration - time() );

			/**
			 * Hook: Fires after session data is saved.
			 *
			 * @since 4.2.0 Introduced.
			 *
			 * @param int    $customer_id     Customer ID.
			 * @param array  $data            Cart data.
			 * @param int    $cart_expiration Cart expiration.
			 * @param string $cart_source     Cart source.
			 */
			do_action( 'cocart_after_session_saved_data', $this->_customer_id, $this->_data, $cart_expiration, $cart_source );

			$this->_dirty = false;

			// Customer is now registered so we delete the previous cart as guest to prevent duplication.
			if ( get_current_user_id() !== $old_cart_key && ! is_object( get_user_by( 'id', $old_cart_key ) ) ) {
				$this->delete_cart( $old_cart_key );
			}
		}
	} // END save_data()

	/**
	 * Destroy all cart data.
	 *
	 * @access public
	 */
	public function destroy_cart() {
		$this->delete_cart( $this->_customer_id );
		$this->forget_session();
	} // END destroy_cart()

	/**
	 * Overrides destroy session function so we use the
	 * correct column from our session table.
	 *
	 * @access public
	 *
	 * @since 3.0.13 Introduced.
	 */
	public function destroy_session() {
		$this->destroy_cart();
	} // END destroy_session()

	/**
	 * Cleanup cart data from the database and clear caches.
	 *
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"DELETE FROM $this->_table WHERE cart_expiry < %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				time()
			)
		);

		// Invalidate cache group.
		if ( class_exists( 'WC_Cache_Helper' ) ) {
			WC_Cache_Helper::invalidate_cache_group( COCART_CART_CACHE_GROUP );
		}
	} // END cleanup_sessions()

	/**
	 * Returns the session.
	 *
	 * @access public
	 *
	 * @param string $cart_key      The customer ID or cart key.
	 * @param mixed  $default_value Default cart value.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string|array
	 */
	public function get_session( $cart_key, $default_value = false ) {
		global $wpdb;

		// There will be no sessions retrieved while WordPress setup is due.
		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		// Try to get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get( $this->get_cache_prefix() . $cart_key, COCART_CART_CACHE_GROUP );

		if ( false === $value ) {
			$value = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT cart_value FROM $this->_table WHERE cart_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$cart_key
				)
			);

			if ( is_null( $value ) ) {
				$value = $default_value;
			}

			$cache_duration = $this->cart_expiration - time();
			if ( 0 < $cache_duration ) {
				wp_cache_add( $this->get_cache_prefix() . $cart_key, $value, COCART_CART_CACHE_GROUP, $cache_duration );
			}
		}

		return maybe_unserialize( $value );
	} // END get_session()

	/**
	 * Update cart.
	 *
	 * @access public
	 *
	 * @param string $cart_key Cart to update.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function update_cart( $cart_key ) {
		global $wpdb;

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->_table,
			array(
				'cart_value'  => maybe_serialize( $this->_data ),
				'cart_expiry' => (int) $this->_cart_expiration,
			),
			array( 'cart_key' => $cart_key ),
			array( '%s', '%d' ),
			array( '%s' )
		);
	} // END update_cart()

	/**
	 * Delete the cart from the cache and database.
	 *
	 * @access public
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function delete_cart( $cart_key ) {
		global $wpdb;

		// Delete cache.
		wp_cache_delete( $this->get_cache_prefix() . $cart_key, COCART_CART_CACHE_GROUP );

		// Delete cart from database.
		$wpdb->delete( $this->_table, array( 'cart_key' => $cart_key ), array( '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	} // END delete_cart()

	/**
	 * Update the cart expiry timestamp.
	 *
	 * @access public
	 *
	 * @param string $cart_key  The cart key.
	 * @param int    $timestamp Timestamp to expire the cookie.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function update_cart_timestamp( $cart_key, $timestamp ) {
		global $wpdb;

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->_table,
			array( 'cart_expiry' => $timestamp ),
			array( 'cart_key' => $cart_key ),
			array( '%d' ),
			array( '%s' )
		);
	} // END update_cart_timestamp()

	/**
	 * Set the cart hash based on the carts contents and total.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 */
	public function set_cart_hash() {
		$cart_session = $this->get( 'cart' );
		$cart_totals  = $this->get( 'cart_totals' );

		$cart_total = isset( $cart_totals ) ? maybe_unserialize( $cart_totals ) : array( 'total' => 0 );
		$hash       = ! empty( $cart_session ) ? md5( wp_json_encode( $cart_session ) . $cart_total['total'] ) : '';

		$this->cart_hash = $hash;
	} // END set_cart_hash()

	/**
	 * Get the session table name.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 */
	public function get_table_name() {
		return $this->_table;
	} // END get_table_name()

	/**
	 * Get customer ID.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	} // END get_customer_id()

	/**
	 * Set customer ID.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param string $customer_id Customer ID.
	 */
	public function set_customer_id( $customer_id ) {
		$this->_customer_id = $customer_id;
	} // END set_customer_id()

	/**
	 * Get cart hash
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_hash() {
		return $this->_cart_hash;
	} // END get_cart_hash()

	/**
	 * Get cart is expiring.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_is_expiring() {
		return $this->cart_expiring;
	} // END get_cart_is_expiring()

	/**
	 * Get carts expiration.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @return string
	 */
	public function get_carts_expiration() {
		return $this->cart_expiration;
	} // END get_carts_expiration()

	/**
	 * Update the session expiry timestamp.
	 *
	 * @param string $customer_id Customer ID.
	 * @param int    $timestamp Timestamp to expire the cookie.
	 */
	public function update_session_timestamp( $customer_id, $timestamp ) {
		global $wpdb;

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->_table,
			array(
				'cart_expiry' => $timestamp,
			),
			array(
				'cart_key' => $customer_id,
			),
			array(
				'%d',
			)
		);
	} // END update_session_timestamp()

	/* Functions below this line are deprecated! */

	/**
	 * Is Cookie support enabled?
	 *
	 * Determines if a cookie should manage the cart for customers.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 *
	 * @return bool
	 */
	public function is_cookie_supported() {
		cocart_deprecated_function( 'CoCart_Session_Handler::is_cookie_supported', '4.2.0', null );

		return cocart_do_deprecated_filter(
			'cocart_cookie_supported',
			'4.2.0',
			null,
			sprintf(
				/* translators: %s: Filter name */
				__( '%s is no longer used.', 'cart-rest-api-for-woocommerce' ),
				'cocart_cookie_supported'
			)
		);
	} // END is_cookie_supported()

	/**
	 * Returns the cookie name.
	 *
	 * @access public
	 *
	 * @deprecated 4.2.0 No replacement.
	 *
	 * @return string
	 */
	public function get_cookie_name() {
		cocart_deprecated_function( 'CoCart_Session_Handler::get_cookie_name', '4.2.0', null );

		return $this->_cookie;
	} // END get_cookie_name()

	/**
	 * Set a cookie - wrapper for setcookie using WP constants.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 *
	 * @param string  $name Name of the cookie being set.
	 * @param string  $value Value of the cookie.
	 * @param integer $expire Expiry of the cookie.
	 * @param bool    $secure Whether the cookie should be served only over https.
	 * @param bool    $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript. @since 2.7.2.
	 */
	public function cocart_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::cocart_setcookie', '4.2.0', null );

		if ( ! headers_sent() ) {
			// samesite - Set to None by default and only available to those using PHP 7.3 or above. @since 2.9.1.
			if ( version_compare( PHP_VERSION, '7.3.0', '>=' ) ) {
				setcookie( $name, $value, apply_filters( 'cocart_set_cookie_options', array( 'expires' => $expire, 'secure' => $secure, 'path' => COOKIEPATH ? COOKIEPATH : '/', 'domain' => COOKIE_DOMAIN, 'httponly' => apply_filters( 'cocart_cookie_httponly', $httponly, $name, $value, $expire, $secure ), 'samesite' => apply_filters( 'cocart_cookie_samesite', 'Lax' ) ), $name, $value ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			} else {
				setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, apply_filters( 'cocart_cookie_httponly', $httponly, $name, $value, $expire, $secure ) );
			}
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
		}
	} // END cocart_cookie()

	/**
	 * Get cart data.
	 *
	 * Similar to the `WC()->session->get_session_data()` function but we needed to access our own table query.
	 *
	 * @access public
	 *
	 * @deprecated 4.2.0 Replaced with original `WC()->session->get_session_data()` function.
	 *
	 * @return array
	 */
	public function get_cart_data() {
		cocart_deprecated_function( 'CoCart_Session_Handler::get_cart_data', '4.2.0', 'CoCart_Session_Handler::get_session_data' );

		return $this->has_session() ? (array) $this->get_cart( $this->_customer_id, array() ) : array();
	} // END get_cart_data()

	/**
	 * Save cart data and delete previous cart data.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 Use `WC()->session->save_data()` instead.
	 *
	 * @param int $old_cart_key Cart key used before.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function save_cart( $old_cart_key = 0 ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::save_cart', '4.2.0', 'CoCart_Session_Handler::save_data' );

		$this->save_data( $old_cart_key );
	} // END save_cart()

	/**
	 * Destroy cart cookie.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 */
	public function destroy_cookie() {
		cocart_deprecated_function( 'CoCart_Session_Handler::destroy_cookie', '4.2.0', null );

		$this->cocart_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie(), $this->use_httponly() );
	} // END destroy_cookie()

	/**
	 * Returns the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 Use `WC()->session->get_session()` instead.
	 *
	 * @param string $cart_key The cart key.
	 * @param mixed  $default  Default cart value.
	 */
	public function get_cart( $cart_key, $default = false ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::get_cart', '4.2.0', 'WC()->session->get_session()' );

		$this->get_session( $cart_key, $default );
	} // END get_cart()

	/**
	 * Returns the timestamp the cart was created.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 Use `cocart_get_timestamp()` instead.
	 *
	 * @see cocart_get_timestamp()
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string
	 */
	public function get_cart_created( $cart_key ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::get_cart_created', '4.2.0', 'cocart_get_timestamp' );

		global $wpdb;

		$value = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT cart_created FROM $this->_table WHERE cart_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$cart_key
			)
		);

		return $value;
	} // END get_cart_created()

	/**
	 * Returns the timestamp the cart expires.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 Use `cocart_get_timestamp()` instead.
	 *
	 * @see cocart_get_timestamp()
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string
	 */
	public function get_cart_expiration( $cart_key ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::get_cart_expiration', '4.2.0', 'cocart_get_timestamp' );

		global $wpdb;

		$value = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT cart_expiry FROM $this->_table WHERE cart_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$cart_key
			)
		);

		return $value;
	} // END get_cart_expiration()

	/**
	 * Returns the source of the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 Use `cocart_get_source()` instead.
	 *
	 * @see cocart_get_source()
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string
	 */
	public function get_cart_source( $cart_key ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::get_cart_source', '4.2.0', 'cocart_get_source' );

		global $wpdb;

		$value = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT cart_source FROM $this->_table WHERE cart_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$cart_key
			)
		);

		return $value;
	} // END get_cart_source()

	/**
	 * Checks if data is still validated to create a cart or update a cart in session.
	 *
	 * @access protected
	 *
	 * @since 2.7.2 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 *
	 * @param array  $data     The cart data to validate.
	 * @param string $cart_key The cart key.
	 *
	 * @return array $data Returns the original cart data or a boolean value.
	 */
	protected function is_cart_data_valid( $data, $cart_key ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::is_cart_data_valid', '4.2.0', null );

		if ( ! empty( $data ) && empty( $this->get_cart( $cart_key ) ) ) {
			// If the cart value is empty then the cart data is not valid.
			if ( ! isset( $data['cart'] ) || empty( maybe_unserialize( $data['cart'] ) ) ) {
				$data = false;
			}
		}

		$data = apply_filters( 'cocart_is_cart_data_valid', $data );

		return $data;
	} // END is_cart_data_valid()

	/**
	 * Whether the cookie is only accessible over HTTP.
	 * Returns true by default for the frontend and false by default via the REST API.
	 *
	 * @uses CoCart::is_rest_api_request()
	 *
	 * @access protected
	 *
	 * @since 2.7.2 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 *
	 * @return boolean
	 */
	protected function use_httponly() {
		cocart_deprecated_function( 'CoCart_Session_Handler::use_httponly', '4.2.0', null );

		$httponly = true;

		if ( CoCart::is_rest_api_request() ) {
			$httponly = false;
		}

		return $httponly;
	} // END use_httponly()

	/**
	 * Forget all cart data without destroying it.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 */
	public function forget_cart() {
		cocart_deprecated_function( 'CoCart_Session_Handler::forget_cart', '4.2.0', null );

		$this->destroy_cookie();

		// Empty cart.
		wc_empty_cart();

		$this->_data        = array();
		$this->_customer_id = $this->generate_customer_id();
	} // END forget_cart()

	/**
	 * Create a blank new cart and returns cart key if successful.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 No replacement.
	 *
	 * @param string $cart_key        The cart key passed to create the cart.
	 * @param array  $cart_value      The cart data.
	 * @param string $cart_expiration Timestamp of cart expiration.
	 * @param string $cart_source     Cart source.

	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return $cart_key
	 */
	public function create_new_cart( $cart_key = '', $cart_value = array(), $cart_expiration = '', $cart_source = '' ) {
		cocart_deprecated_function( 'CoCart_Session_Handler::create_new_cart', '4.2.0', null );

		global $wpdb;

		if ( empty( $cart_key ) ) {
			$cart_key = $this->generate_key();
		}

		if ( empty( $cart_expiration ) ) {
			$cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiring', DAY_IN_SECONDS * 7 ) );
		}

		if ( empty( $cart_source ) ) {
			$cart_source = apply_filters( 'cocart_cart_source', $this->_cart_source );
		}

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->_table,
			array(
				'cart_key'     => $cart_key,
				'cart_value'   => maybe_serialize( $cart_value ),
				'cart_created' => time(),
				'cart_expiry'  => $cart_expiration,
				'cart_source'  => $cart_source,
			),
			array( '%s', '%s', '%d', '%d', '%s' )
		);

		// Returns the cart key if cart successfully created.
		if ( $result ) {
			return $cart_key;
		}
	} // END create_new_cart()
} // END class
